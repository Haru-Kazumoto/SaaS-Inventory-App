<?php

namespace Tests\Feature\Domain;

use App\Domain\Borrowing\Actions\BorrowItems;
use App\Domain\Borrowing\Actions\ReturnItems;
use App\Domain\Borrowing\Data\BorrowData;
use App\Domain\Borrowing\Queries\LoanStatusQuery;
use App\Domain\Inventory\Actions\RestockProduct;
use App\Domain\Inventory\Actions\SaveProduct;
use App\Domain\Inventory\Actions\WriteOffProduct;
use App\Domain\Inventory\Data\ProductData;
use App\Domain\Inventory\Data\StockLine;
use App\Domain\Inventory\Enums\ProductType;
use App\Domain\Inventory\Enums\StockCondition;
use App\Domain\Inventory\Ledger\StockLedger;
use App\Domain\Inventory\Models\Product;
use App\Domain\Organization\Enums\OrganizationRole;
use App\Domain\Organization\Enums\UnitRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Feature\Concerns\InteractsWithTenant;
use Tests\TestCase;

class InventoryAndBorrowingTest extends TestCase
{
    use InteractsWithTenant, RefreshDatabase;

    private function product(array $md, int $unitId, ProductType $type, int $openingGood = 10): Product
    {
        return app(SaveProduct::class)->handle(new ProductData(
            unitId: $unitId,
            name: 'BARANG '.uniqid(),
            code: 'BRG'.uniqid(),
            type: $type,
            categoryId: $md['category']->id,
            uomId: $md['uom']->id,
            brandName: null,
            price: 1000,
            labelingDate: null,
            image: null,
            stock: [new StockLine($md['warehouse']->id, StockCondition::Good, $openingGood)],
        ));
    }

    private function borrow(int $unitId, array $lines): \App\Domain\Borrowing\Models\Transaction
    {
        return app(BorrowItems::class)->handle(new BorrowData($unitId, 'Budi', 'NIS001', '0812', 'Pak Guru', 'Lab 1', null, null, $lines));
    }

    public function test_full_loan_cycle_updates_stock_and_status(): void
    {
        $org = $this->makeOrganization();
        $unit = $this->makeUnit($org, 'TJKT');
        $md = $this->masterData($org);
        $this->actAs($this->makeUser($org));

        $ledger = app(StockLedger::class);
        $tool = $this->product($md, $unit->id, ProductType::Durable, 10);
        $cable = $this->product($md, $unit->id, ProductType::Consumable, 50);

        $tx = $this->borrow($unit->id, [
            ['product_id' => $tool->id, 'warehouse_id' => $md['warehouse']->id, 'quantity' => 3],
            ['product_id' => $cable->id, 'warehouse_id' => $md['warehouse']->id, 'quantity' => 5],
        ]);

        $this->assertSame(7, $ledger->onHand($tool->id, $md['warehouse']->id));
        $this->assertSame(45, $ledger->onHand($cable->id, $md['warehouse']->id));
        $status = app(LoanStatusQuery::class);
        $this->assertSame('POSTED', $status->statusFor($tx->id));

        $toolLine = $tx->lines()->where('product_id', $tool->id)->first();

        // kembali sebagian, 1 rusak
        app(ReturnItems::class)->handle($tx, [['borrow_line_id' => $toolLine->id, 'quantity' => 1, 'condition' => 'DAMAGED']]);
        $this->assertSame('PARTIAL_RETURNED', $status->statusFor($tx->id));
        $this->assertSame(1, $ledger->onHand($tool->id, $md['warehouse']->id, StockCondition::Damaged));

        // tidak boleh melebihi sisa
        try {
            app(ReturnItems::class)->handle($tx, [['borrow_line_id' => $toolLine->id, 'quantity' => 5]]);
            $this->fail('Pengembalian melebihi sisa harus ditolak');
        } catch (ValidationException) {
        }

        // sisanya kembali → selesai
        app(ReturnItems::class)->handle($tx, [['borrow_line_id' => $toolLine->id, 'quantity' => 2]]);
        $this->assertSame('COMPLETED', $status->statusFor($tx->id));
        $this->assertSame(9, $ledger->onHand($tool->id, $md['warehouse']->id));
        $this->assertNotNull($tx->fresh()->returned_at);
    }

    public function test_borrowing_more_than_stock_is_rejected_and_nothing_is_saved(): void
    {
        $org = $this->makeOrganization();
        $unit = $this->makeUnit($org, 'TE');
        $md = $this->masterData($org);
        $this->actAs($this->makeUser($org));
        $tool = $this->product($md, $unit->id, ProductType::Durable, 2);

        try {
            $this->borrow($unit->id, [['product_id' => $tool->id, 'warehouse_id' => $md['warehouse']->id, 'quantity' => 3]]);
            $this->fail('Harus gagal');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('lines.0.quantity', $e->errors());
        }

        $this->assertSame(0, \DB::table('transactions')->count(), 'Transaksi harus di-rollback');
        $this->assertSame(2, app(StockLedger::class)->onHand($tool->id, $md['warehouse']->id));
    }

    public function test_restock_write_off_and_reconcile_on_edit(): void
    {
        $org = $this->makeOrganization();
        $unit = $this->makeUnit($org, 'AK');
        $md = $this->masterData($org);
        $this->actAs($this->makeUser($org));
        $ledger = app(StockLedger::class);
        $p = $this->product($md, $unit->id, ProductType::Durable, 5);
        $wh = $md['warehouse']->id;

        app(RestockProduct::class)->handle($p, $wh, 4);
        $this->assertSame(9, $ledger->onHand($p->id, $wh));

        app(WriteOffProduct::class)->handle($p, $wh, 2, StockCondition::Good, 'Rusak berat');
        $this->assertSame(7, $ledger->onHand($p->id, $wh));
        $this->assertSame(2, $ledger->onHand($p->id, $wh, StockCondition::Off));
        $this->assertArrayNotHasKey("{$wh}|OFF", $ledger->balances($p->id));

        // ubah barang: target 10 bagus + 3 rusak → penyesuaian otomatis
        app(SaveProduct::class)->handle(new ProductData(
            $unit->id, $p->name, $p->code, ProductType::Durable, $md['category']->id, $md['uom']->id, null, 1000, null, null,
            [new StockLine($wh, StockCondition::Good, 10), new StockLine($wh, StockCondition::Damaged, 3)],
        ), $p);
        $this->assertSame(10, $ledger->onHand($p->id, $wh));
        $this->assertSame(3, $ledger->onHand($p->id, $wh, StockCondition::Damaged));
        $this->assertSame(2, $ledger->onHand($p->id, $wh, StockCondition::Off), 'Riwayat penghapusan tidak boleh tersentuh');
    }

    public function test_unit_roles_limit_what_members_can_do(): void
    {
        $org = $this->makeOrganization();
        $tjkt = $this->makeUnit($org, 'TJKT');
        $te = $this->makeUnit($org, 'TE');
        $md = $this->masterData($org);

        $this->actAs($this->makeUser($org));
        $teProduct = $this->product($md, $te->id, ProductType::Durable, 5);

        // staf TJKT: boleh pinjam di TJKT, tidak boleh kelola barang, tidak boleh menyentuh TE
        $staff = $this->makeUser($org, OrganizationRole::Member, [$tjkt->id => UnitRole::Staff]);
        $this->actAs($staff);

        $this->expectException(HttpException::class);
        $this->borrow($te->id, [['product_id' => $teProduct->id, 'warehouse_id' => $md['warehouse']->id, 'quantity' => 1]]);
    }

    public function test_product_from_another_organization_is_invisible(): void
    {
        $a = $this->makeOrganization('A');
        $b = $this->makeOrganization('B');
        $unitA = $this->makeUnit($a, 'U');
        $this->actAs($this->makeUser($a));
        $productA = $this->product($this->masterData($a), $unitA->id, ProductType::Durable);

        $this->actAs($this->makeUser($b));
        $this->assertNull(Product::find($productA->id));
        $this->get(route('products.show', $productA->id))->assertNotFound();
    }
}
