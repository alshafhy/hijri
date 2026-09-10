@extends('layouts.app')

@section('title', __('Sales Report'))

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="#">{{ __('Reports') }}</a></li>
<li class="breadcrumb-item active">{{ __('Sales Report') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">{{ __('Filters') }}</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('dashboard.reports.sales') }}" method="GET" class="row">
                    <div class="col-md-4">
                        <label>{{ __('From') }}</label>
                        <input type="date" name="from" class="form-control" value="{{ $from }}">
                    </div>
                    <div class="col-md-4">
                        <label>{{ __('To') }}</label>
                        <input type="date" name="to" class="form-control" value="{{ $to }}">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">{{ __('Filter') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Summary Cards -->
    <div class="col-md-3">
        <div class="card bg-primary text-white text-center p-3">
            <h6>{{ __('Net Sales') }}</h6>
            <h3>{{ number_format($summary['net_sales'], 2) }}</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white text-center p-3">
            <h6>{{ __('Total Profit') }}</h6>
            <h3>{{ number_format($summary['total_profit'], 2) }}</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white text-center p-3">
            <h6>{{ __('Cash Collected') }}</h6>
            <h3>{{ number_format($summary['cash_collected'], 2) }}</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white text-center p-3">
            <h6>{{ __('Total Remaining') }}</h6>
            <h3>{{ number_format($summary['total_remaining'], 2) }}</h3>
        </div>
    </div>
</div>

<div class="row">
    <!-- Top Products -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">{{ __('Top Products') }}</h4>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>{{ __('Product') }}</th>
                            <th>{{ __('Quantity') }}</th>
                            <th>{{ __('Revenue') }}</th>
                            <th>{{ __('Profit') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($top as $item)
                        <tr>
                            <td>{{ $item->product_name }}</td>
                            <td>{{ number_format($item->total_qty, 2) }}</td>
                            <td>{{ number_format($item->total_revenue, 2) }}</td>
                            <td>{{ number_format($item->total_profit, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- Payment Breakdown -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">{{ __('Payment Breakdown') }}</h4>
            </div>
            <div class="card-body">
                <ul class="list-group">
                    @foreach($breakdown as $type => $data)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        {{ __($type) }} ({{ $data['count'] }})
                        <span>{{ number_format($data['total'], 2) }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
