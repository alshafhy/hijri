<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Branch;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Gate;

class CustomerController extends Controller
{
    public function index(): View
    {
        Gate::authorize('customer.view');
        $customers = Customer::with('branch')->latest()->paginate(20);
        return view('customers.index', compact('customers'));
    }

    public function create(): View
    {
        Gate::authorize('customer.create');
        $branches = Branch::all();
        return view('customers.create', compact('branches'));
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        Customer::create($request->validated());
        return redirect()->route('dashboard.customers.index')->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer): View
    {
        Gate::authorize('customer.view');
        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer): View
    {
        Gate::authorize('customer.edit');
        $branches = Branch::all();
        return view('customers.edit', compact('customer', 'branches'));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $customer->update($request->validated());
        return redirect()->route('dashboard.customers.index')->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        Gate::authorize('customer.delete');
        $customer->delete();
        return redirect()->route('dashboard.customers.index')->with('success', 'Customer deleted successfully.');
    }

    public function recordPayment(Request $request, Customer $customer): RedirectResponse
    {
        Gate::authorize('customer.record_payment');

        $request->validate(['amount' => 'required|numeric|gt:0']);

        $customer->recordPayment((float) $request->input('amount'));

        return back()->with('success', 'Payment recorded successfully.');
    }
}