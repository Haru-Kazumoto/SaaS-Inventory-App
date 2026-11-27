<?php

namespace App\Http\Controllers\Platform;

use App\Domain\Platform\Actions\IssueInvoice;
use App\Domain\Platform\Actions\VoidInvoice;
use App\Domain\Platform\Models\Invoice;
use App\Domain\Organization\Models\Organization;
use App\Domain\Platform\Queries\InvoiceListQuery;
use App\Domain\Platform\Queries\OrganizationListQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\StoreInvoiceRequest;
use App\Http\Requests\Platform\VoidInvoiceRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function index(Request $request, InvoiceListQuery $query, OrganizationListQuery $organizations): Response
    {
        return Inertia::render('Platform/Invoices/Index', [
            'invoices' => $query->paginate($request->only(['q', 'status', 'organization_id'])),
            'organizations' => $organizations->options(),
        ]);
    }

    public function store(StoreInvoiceRequest $request, IssueInvoice $issue): RedirectResponse
    {
        $invoice = $issue->handle(
            organization: Organization::findOrFail($request->integer('organization_id')),
            lines: $request->lines(),
            dueInDays: $request->integer('due_in_days'),
            discount: $request->integer('discount'),
            tax: $request->integer('tax'),
            notes: $request->input('notes'),
        );

        return redirect()
            ->route('platform.invoices.show', $invoice)
            ->with('success', "Tagihan {$invoice->number} dibuat");
    }

    public function show(Invoice $invoice, InvoiceListQuery $query): Response
    {
        return Inertia::render('Platform/Invoices/Show', [
            'invoice' => $query->detail($invoice),
        ]);
    }

    public function void(VoidInvoiceRequest $request, Invoice $invoice, VoidInvoice $void): RedirectResponse
    {
        $void->handle($invoice, $request->input('reason'));

        return back()->with('success', "Tagihan {$invoice->number} dibatalkan");
    }
}
