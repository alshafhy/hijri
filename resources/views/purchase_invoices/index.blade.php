@extends('layouts.app')

@section('title', __('Purchase Invoices'))

@section('breadcrumbs', __('Purchase Invoices'))

@section('content')
@include('flash::message')
<div class="clearfix"></div>
<div class="row" id="table-bordered">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <a href="{{ route('dashboard.purchase-invoices.create') }}" class="btn btn-primary">{{ __('Create New') }} {{ __('Purchase Invoice') }}</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    @include('purchase_invoices.table')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection