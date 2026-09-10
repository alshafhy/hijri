<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSaleInvoiceRequest;
use App\Models\SaleInvoice;
use App\Services\SaleInvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Gate;

class SaleInvoiceController extends Controller
{
    protected SaleInvoiceService $service;

    public function __construct(SaleInvoiceService $service)
    {
        $this->service = $service;
    }

    public function index(): View
    {
        Gate::authorize('sale_invoice.view');
        $invoices = SaleInvoice::with(['customer', 'cashier', 'branch'])->latest()->paginate(20);
        return view('sale_invoices.index', compact('invoices'));
    }

    public function create(): View
    {
        Gate::authorize('sale_invoice.create');
        return view('sale_invoices.create');
    }

    public function store(StoreSaleInvoiceRequest $request): RedirectResponse
    {
        $header = $request->only([
            'branch_id', 'customer_id', 'invoice_date', 'discount_amount',
            'discount_type', 'payment_type', 'paid_amount', 'notes'
        ]);

        if (empty($header['branch_id'])) {
            $header['branch_id'] = auth()->user()->branch_id;
        }

        if (!$header['branch_id']) {
            return back()->withErrors(['branch_id' => 'No branch assigned to user.']);
        }

        $header['cashier_id'] = auth()->id();
        $header['cashier_name'] = auth()->user()->name;

        $invoice = $this->service->create($header, $request->input('items'));

        return redirect()->route('dashboard.sale-invoices.show', $invoice->id)
            ->with('success', 'Invoice created successfully.');
    }

    public function show(SaleInvoice $saleInvoice): View
    {
        Gate::authorize('sale_invoice.view');
        $saleInvoice->load(['items', 'customer', 'cashier']);
        return view('sale_invoices.show', compact('saleInvoice'));
    }

    public function edit(SaleInvoice $saleInvoice): View
    {
        Gate::authorize('sale_invoice.edit');
        $saleInvoice->load(['items', 'customer', 'branch']);
        return view('sale_invoices.edit', compact('saleInvoice'));
    }

    public function update(Request $request, SaleInvoice $saleInvoice): RedirectResponse
    {
        Gate::authorize('sale_invoice.edit');
        // Implement update logic if needed
        return redirect()->route('dashboard.sale-invoices.show', $saleInvoice->id)
            ->with('success', 'Invoice updated successfully.');
    }

    public function collectDebt(Request $request, SaleInvoice $saleInvoice): RedirectResponse
    {
        Gate::authorize('sale_invoice.collect_debt');

        $request->validate(['amount' => 'required|numeric|gt:0']);

        $this->service->collectPayment($saleInvoice, (float) $request->input('amount'));

        return back()->with('success', 'Payment recorded successfully.');
    }

    public function destroy(SaleInvoice $saleInvoice): RedirectResponse
    {
        Gate::authorize('sale_invoice.delete');
        $saleInvoice->delete();
        return redirect()->route('dashboard.sale-invoices.index')->with('success', 'Invoice deleted successfully.');
    }
}