<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Installment;
use App\Services\InstallmentService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InstallmentController extends Controller
{
    public function __construct(
        private readonly InstallmentService $installmentService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('installment.view');

        $branchId = auth()->user()->branch_id;

        $installments = $branchId 
            ? Installment::forBranch($branchId)
                ->with(['saleInvoice', 'customer'])
                ->when(
                    $request->status,
                    fn($q, $s) => $q->where('status', $s)
                )
                ->when(
                    $request->customer_id,
                    fn($q, $c) => $q->forCustomer((int) $c)
                )
                ->when(
                    $request->date_from && $request->date_to,
                    fn($q) => $q->dueBetween($request->date_from, $request->date_to)
                )
                ->when(
                    $request->overdue,
                    fn($q) => $q->overdue()
                )
                ->orderBy('collect_date')
                ->paginate(25)
            : new LengthAwarePaginator([], 0, 25);

        $overdueCount = $branchId ? Installment::forBranch($branchId)->overdue()->count() : 0;

        return view('installments.index', compact('installments', 'overdueCount'));
    }

    public function overdue(): View
    {
        $this->authorize('installment.view_overdue');

        $branchId = auth()->user()->branch_id;

        $installments = $branchId ? $this->installmentService->getOverdue($branchId) : collect();

        return view('installments.overdue', compact('installments'));
    }

    public function show(Installment $installment): View
    {
        $this->authorize('installment.view');

        $installment->load(['saleInvoice.items', 'customer']);

        return view('installments.show', compact('installment'));
    }

    public function collect(Request $request, Installment $installment): RedirectResponse
    {
        $this->authorize('installment.collect');

        $validated = $request->validate([
            'pay_type'  => 'required|string|in:cash,card,transfer',
            'paid_date' => 'required|date',
        ]);

        try {
            $this->installmentService->collect($installment, $validated);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['collect' => $e->getMessage()]);
        }

        return redirect()
            ->route('dashboard.installments.index')
            ->with('success', "تم تحصيل القسط بنجاح — {$installment->client_name}");
    }

    public function destroy(Installment $installment): RedirectResponse
    {
        $this->authorize('installment.delete');

        if ($installment->status === Installment::STATUS_PAID) {
            return back()->withErrors([
                'delete' => 'لا يمكن حذف قسط مدفوع.',
            ]);
        }

        $installment->delete();

        return redirect()
            ->route('dashboard.installments.index')
            ->with('success', 'تم حذف القسط بنجاح.');
    }
}