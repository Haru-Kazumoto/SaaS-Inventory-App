<?php

namespace App\Http\Controllers\Borrowing;

use App\Domain\Borrowing\Actions\BorrowItems;
use App\Domain\Borrowing\Actions\ReturnItems;
use App\Domain\Borrowing\Enums\TxType;
use App\Domain\Borrowing\Models\Transaction;
use App\Domain\Borrowing\Queries\BorrowFormQuery;
use App\Domain\Borrowing\Queries\BorrowingQuery;
use App\Domain\Organization\Support\Access;
use App\Http\Controllers\Controller;
use App\Http\Requests\Borrowing\ReturnBorrowingRequest;
use App\Http\Requests\Borrowing\StoreBorrowingRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BorrowingController extends Controller
{
    public function index(Request $request, BorrowingQuery $query): Response
    {
        return Inertia::render('Borrowing/Index', [
            'transactions' => $query->paginate($request->only(['borrower_name', 'status', 'unit_id'])),
            'canCreate' => Access::operableUnitIds() !== [],
        ]);
    }

    public function create(BorrowFormQuery $query): Response
    {
        abort_if(Access::operableUnitIds() === [], 403, 'Anda tidak punya akses mencatat peminjaman.');

        return Inertia::render('Borrowing/Create', $query->createOptions());
    }

    public function store(StoreBorrowingRequest $request, BorrowItems $borrow): RedirectResponse
    {
        $tx = $borrow->handle($request->toData());

        return redirect()->route('borrowing.show', $tx)->with('success', "Peminjaman {$tx->tx_number} dicatat");
    }

    public function show(Transaction $transaction, BorrowingQuery $query): Response
    {
        abort_unless($transaction->tx_type === TxType::Borrow, 404);
        Access::authorizeView($transaction->unit_id);

        return Inertia::render('Borrowing/Detail', $query->detail($transaction));
    }

    public function returnForm(Transaction $transaction, BorrowFormQuery $query): Response
    {
        abort_unless($transaction->tx_type === TxType::Borrow, 404);
        Access::authorizeOperate($transaction->unit_id);

        return Inertia::render('Borrowing/Return', $query->returnOptions($transaction));
    }

    public function returnStore(ReturnBorrowingRequest $request, Transaction $transaction, ReturnItems $return): RedirectResponse
    {
        $tx = $return->handle($transaction, $request->input('lines', []), $request->input('description'));

        return redirect()->route('borrowing.show', $transaction)->with('success', "Pengembalian {$tx->tx_number} dicatat");
    }
}
