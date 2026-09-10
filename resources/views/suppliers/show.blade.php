@extends('layouts.app')

@section('title', __('Supplier') . ': ' . $supplier->name)

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard.suppliers.index') }}">{{ __('Suppliers') }}</a></li>
<li class="breadcrumb-item active">{{ $supplier->name }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $supplier->name }}</h4>
                <a href="{{ route('dashboard.suppliers.index') }}" class="btn btn-secondary">Back to List</a>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th>Phone:</th>
                                <td>{{ $supplier->phone ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Address:</th>
                                <td>{{ $supplier->address ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Branch:</th>
                                <td>{{ $supplier->branch->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    <span class="badge bg-{{ $supplier->is_active ? 'success' : 'danger' }}">
                                        {{ $supplier->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th>Total Purchased:</th>
                                <td>{{ number_format($supplier->total_purchased, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Paid:</th>
                                <td>{{ number_format($supplier->paid_amount, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Current Balance:</th>
                                <td>
                                    <span class="badge bg-{{ $supplier->current_balance > 0 ? 'warning' : 'success' }}">
                                        {{ number_format($supplier->current_balance, 2) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Opening Balance:</th>
                                <td>{{ number_format($supplier->opening_balance, 2) }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                @if($supplier->notes)
                <div class="mt-3">
                    <strong>Notes:</strong>
                    <p>{{ $supplier->notes }}</p>
                </div>
                @endif
                <div class="mt-3">
                    <a href="{{ route('dashboard.suppliers.edit', $supplier->id) }}" class="btn btn-primary">Edit</a>
                    @can('supplier.delete')
                    <form action="{{ route('dashboard.suppliers.destroy', $supplier->id) }}" method="POST" style="display:inline;">
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