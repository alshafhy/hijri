@extends('layouts.app')

@section('title', __('Invoice') . ' #' . $saleInvoice->invoice_number)

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard.sale-invoices.index') }}">{{ __('Sale Invoices') }}</a></li>
<li class="breadcrumb-item active">{{ $saleInvoice->invoice_number }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Invoice #{{ $saleInvoice->invoice_number }}</h4>
                <a href="{{ route('dashboard.sale-invoices.index') }}" class="btn btn-secondary">Back to List</a>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <strong>Date:</strong> {{ $saleInvoice->invoice_date }}
                    </div>
                    <div class="col-md-4">
                        <strong>Customer:</strong> {{ $saleInvoice->customer->name ?? 'N/A' }}
                    </div>
                    <div class="col-md-4">
                        <strong>Cashier:</strong> {{ $saleInvoice->cashier_name }}
                    </div>
                    <div class="col-md-4">
                        <strong>Branch:</strong> {{ $saleInvoice->branch->name ?? 'N/A' }}
                    </div>
                    <div class="col-md-4">
                        <strong>Payment Type:</strong> {{ $saleInvoice->payment_type }}
                    </div>
                    <div class="col-md-4">
                        <strong>Status:</strong>
                        <span class="badge bg-{{ $saleInvoice->status === 'paid' ? 'success' : ($saleInvoice->status === 'partial' ? 'warning' : 'danger') }}">
                            {{ $saleInvoice->status }}
                        </span>
                    </div>
                </div>

                <h5>Items</h5>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($saleInvoice->items as $item)
                        <tr>
                            <td>{{ $item->product->name ?? 'N/A' }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ number_format($item->unit_price, 2) }}</td>
                            <td>{{ number_format($item->total_price, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center">{{ __('No items found') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="row mt-4">
                    <div class="col-md-6 offset-md-6">
                        <table class="table">
                            <tr>
                                <th>Subtotal:</th>
                                <td>{{ number_format($saleInvoice->subtotal_amount, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Discount:</th>
                                <td>{{ number_format($saleInvoice->discount_amount, 2) }} ({{ $saleInvoice->discount_type }})</td>
                            </tr>
                            <tr>
                                <th>Total:</th>
                                <td><strong>{{ number_format($saleInvoice->total_amount, 2) }}</strong></td>
                            </tr>
                            <tr>
                                <th>Paid:</th>
                                <td>{{ number_format($saleInvoice->paid_amount, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Due:</th>
                                <td><strong>{{ number_format($saleInvoice->due_amount, 2) }}</strong></td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($saleInvoice->due_amount > 0 && $saleInvoice->payment_type !== 'cash')
                <div class="row mt-3">
                    <div class="col-md-6">
                        <form action="{{ route('dashboard.sale-invoices.collect', $saleInvoice->id) }}" method="POST" class="d-flex gap-2">
                            @csrf
                            <input type="number" name="amount" class="form-control" placeholder="Amount" step="0.01" min="0.01" required>
                            <button type="submit" class="btn btn-success">Collect Payment</button>
                        </form>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection