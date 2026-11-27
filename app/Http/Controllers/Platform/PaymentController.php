<?php

namespace App\Http\Controllers\Platform;

use App\Domain\Platform\Actions\RecordPayment;
use App\Domain\Platform\Models\Invoice;
use App\Domain\Platform\Queries\OrganizationListQuery;
use App\Domain\Platform\Queries\PaymentListQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\RecordPaymentRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function index(Request $request, PaymentListQuery $query, OrganizationListQuery $organizations): Response
    {
        return Inertia::render('Platform/Payments/Index', [
            'payments' => $query->paginate($request->only(['q', 'method', 'organization_id'])),
            'organizations' => $organizations->options(),
        ]);
    }

    public function store(RecordPaymentRequest $request, Invoice $invoice, RecordPayment $record): RedirectResponse
    {
        $record->handle($invoice, $request->toData(), $request->user()->id);

        return back()->with('success', 'Pembayaran dicatat');
    }
}
