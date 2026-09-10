@extends('layouts.app')

@section('title', __('Treasury Report'))

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="#">{{ __('Reports') }}</a></li>
<li class="breadcrumb-item active">{{ __('Treasury Report') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">{{ __('Filters') }}</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('dashboard.reports.treasury') }}" method="GET" class="row">
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
    <div class="col-md-4">
        <div class="card bg-secondary text-white text-center p-3">
            <h6>{{ __('Opening Balance') }}</h6>
            <h3>{{ number_format($summary['opening_balance'], 2) }}</h3>
        </div>
    </div>
    <div class="col-md-4 text-center">
        <!-- Spacer or additional info -->
    </div>
    <div class="col-md-4">
        <div class="card bg-dark text-white text-center p-3">
            <h6>{{ __('Closing Balance') }}</h6>
            <h3>{{ number_format($summary['closing_balance'], 2) }}</h3>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="card border-success text-center p-3">
            <h6>{{ __('Total Deposits') }}</h6>
            <h3 class="text-success">+{{ number_format($summary['total_deposits'], 2) }}</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-danger text-center p-3">
            <h6>{{ __('Total Withdrawals') }}</h6>
            <h3 class="text-danger">-{{ number_format($summary['total_withdrawals'], 2) }}</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-warning text-center p-3">
            <h6>{{ __('Total Expenses') }}</h6>
            <h3 class="text-warning">-{{ number_format($summary['total_expenses'], 2) }}</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-info text-center p-3">
            <h6>{{ __('Total Purchases') }}</h6>
            <h3 class="text-info">-{{ number_format($summary['total_purchases'], 2) }}</h3>
        </div>
    </div>
</div>
@endsection
