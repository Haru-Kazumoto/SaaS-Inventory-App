<?php

namespace Tests\Feature\Platform;

use App\Domain\Platform\Actions\ChangeSubscription;
use App\Domain\Platform\Actions\RecordPayment;
use App\Domain\Platform\Actions\SaveOrganization;
use App\Domain\Platform\Actions\VoidInvoice;
use App\Domain\Platform\Data\OrganizationData;
use App\Domain\Platform\Data\PaymentData;
use App\Domain\Platform\Enums\BillingCycle;
use App\Domain\Platform\Enums\InvoiceStatus;
use App\Domain\Organization\Enums\OrganizationStatus;
use App\Domain\Organization\Enums\OrganizationTemplate;
use App\Domain\Platform\Enums\PaymentMethod;
use App\Domain\Platform\Enums\SubscriptionStatus;
use App\Domain\Platform\Exceptions\BillingException;
use App\Domain\Platform\Models\Plan;
use App\Domain\Organization\Enums\OrganizationRole;
use App\Domain\Organization\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformBillingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PlanSeeder::class);
    }

    private function platformAdmin(): User
    {
        return User::create([
            'username' => 'dev'.uniqid(),
            'fullname' => 'Dev',
            'password' => 'password',
            'is_platform_admin' => true,
        ]);
    }

    private function organizationOwner(): User
    {
        $org = Organization::create(['name' => 'Org A', 'slug' => 'org-a'.uniqid(), 'status' => 'active']);

        return User::create([
            'username' => 'owner'.uniqid(),
            'fullname' => 'Owner',
            'password' => 'password',
            'organization_id' => $org->id,
            'organization_role' => OrganizationRole::Owner,
        ]);
    }

    private function paidPlan(): Plan
    {
        return Plan::where('code', 'basic')->firstOrFail(); // dari PlanSeeder
    }

    public function test_only_platform_admin_can_open_platform_area(): void
    {
        $this->actingAs($this->organizationOwner())
            ->get(route('platform.dashboard'))
            ->assertForbidden();

        $this->actingAs($this->platformAdmin())
            ->get(route('platform.dashboard'))
            ->assertOk();
    }

    public function test_paid_plan_issues_first_invoice_and_full_payment_settles_it(): void
    {
        $org = app(SaveOrganization::class)->handle(new OrganizationData(
            name: 'PT Contoh',
            template: OrganizationTemplate::Office,
            planId: $this->paidPlan()->id,
            billingCycle: BillingCycle::Monthly,
        ));

        $this->assertSame(OrganizationStatus::Active, $org->status);
        $invoice = $org->invoices()->sole();
        $this->assertSame(InvoiceStatus::Unpaid, $invoice->status);
        $this->assertSame(99000, $invoice->total);
        $this->assertMatchesRegularExpression('/^INV-\d{6}-0001$/', $invoice->number);

        // bayar sebagian → masih unpaid
        app(RecordPayment::class)->handle($invoice, new PaymentData(40000, PaymentMethod::BankTransfer, now()));
        $this->assertSame(InvoiceStatus::Unpaid, $invoice->fresh()->status);

        // lunasi sisa → paid
        app(RecordPayment::class)->handle($invoice, new PaymentData(59000, PaymentMethod::Qris, now()));
        $this->assertSame(InvoiceStatus::Paid, $invoice->fresh()->status);
        $this->assertNotNull($invoice->fresh()->paid_at);
    }

    public function test_overpayment_and_paying_void_invoice_are_rejected(): void
    {
        $org = app(SaveOrganization::class)->handle(new OrganizationData(
            name: 'Sekolah Uji',
            template: OrganizationTemplate::School,
            planId: $this->paidPlan()->id,
        ));
        $invoice = $org->invoices()->sole();

        try {
            app(RecordPayment::class)->handle($invoice, new PaymentData(200000, PaymentMethod::Cash, now()));
            $this->fail('Overpayment should be rejected');
        } catch (BillingException) {
        }

        app(VoidInvoice::class)->handle($invoice, 'salah paket');
        $this->assertSame(InvoiceStatus::Void, $invoice->fresh()->status);

        $this->expectException(BillingException::class);
        app(RecordPayment::class)->handle($invoice->fresh(), new PaymentData(1000, PaymentMethod::Cash, now()));
    }

    public function test_trial_has_no_invoice_and_changing_plan_cancels_previous_subscription(): void
    {
        $org = app(SaveOrganization::class)->handle(new OrganizationData(
            name: 'Komunitas Uji',
            template: OrganizationTemplate::Community,
            planId: $this->paidPlan()->id,
            trialDays: 14,
        ));

        $this->assertSame(OrganizationStatus::Trial, $org->status);
        $this->assertSame(0, $org->invoices()->count());

        app(ChangeSubscription::class)->handle($org, $this->paidPlan(), BillingCycle::Yearly);

        $this->assertSame(1, $org->subscriptions()->whereIn('status', SubscriptionStatus::running())->count());
        $this->assertSame(SubscriptionStatus::Cancelled, $org->subscriptions()->oldest('id')->first()->status);
        $this->assertSame(990000, $org->invoices()->sole()->total);
        $this->assertSame(OrganizationStatus::Active, $org->fresh()->status);
    }
}
