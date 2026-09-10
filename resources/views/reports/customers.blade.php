@extends('layouts.app')

@section('title', __('Customer Debts Report'))

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="#">{{ __('Reports') }}</a></li>
<li class="breadcrumb-item active">{{ __('Customer Debts') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">{{ __('Customers with Outstanding Debts') }}</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>{{ __('Customer') }}</th>
                                <th>{{ __('Phone') }}</th>
                                <th>{{ __('Total Invoiced') }}</th>
                                <th>{{ __('Paid') }}</th>
                                <th>{{ __('Current Debt') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($debts as $debt)
                            <tr>
                                <td>{{ $debt->name }}</td>
                                <td>{{ $debt->phone }}</td>
                                <td>{{ number_format($debt->total_invoiced, 2) }}</td>
                                <td>{{ number_format($debt->paid_amount, 2) }}</td>
                                <td>
                                    <span class="badge bg-warning">
                                        {{ number_format($debt->current_debt, 2) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('dashboard.customers.show', $debt->id) }}" class="btn btn-sm btn-info">{{ __('View Profile') }}</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
