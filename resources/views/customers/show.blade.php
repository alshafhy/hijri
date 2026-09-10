@extends('layouts.app')

@section('title', __('Customer') . ': ' . $customer->name)

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard.customers.index') }}">{{ __('Customers') }}</a></li>
<li class="breadcrumb-item active">{{ $customer->name }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $customer->name }}</h4>
                <a href="{{ route('dashboard.customers.index') }}" class="btn btn-secondary">Back to List</a>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th>Phone:</th>
                                <td>{{ $customer->phone ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Address:</th>
                                <td>{{ $customer->address ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Branch:</th>
                                <td>{{ $customer->branch->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    <span class="badge bg-{{ $customer->is_active ? 'success' : 'danger' }}">
                                        {{ $customer->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th>Total Invoiced:</th>
                                <td>{{ number_format($customer->total_invoiced, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Paid:</th>
                                <td>{{ number_format($customer->paid_amount, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Current Debt:</th>
                                <td>
                                    <span class="badge bg-{{ $customer->current_debt > 0 ? 'warning' : 'success' }}">
                                        {{ number_format($customer->current_debt, 2) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Credit Limit:</th>
                                <td>{{ $customer->credit_limit > 0 ? number_format($customer->credit_limit, 2) : 'No Limit' }}</td>
                            </tr>
                            <tr>
                                <th>Price Type:</th>
                                <td>{{ $customer->price_tier_label }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                @if($customer->notes)
                <div class="mt-3">
                    <strong>Notes:</strong>
                    <p>{{ $customer->notes }}</p>
                </div>
                @endif
                <div class="mt-3">
                    <a href="{{ route('dashboard.customers.edit', $customer->id) }}" class="btn btn-primary">Edit</a>
                    @can('customer.delete')
                    <form action="{{ route('dashboard.customers.destroy', $customer->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
@endsection