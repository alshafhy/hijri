@extends('layouts.app')

@section('title', __('Customers'))

@section('breadcrumbs', __('Customers'))

@section('content')
@include('flash::message')
<div class="clearfix"></div>
<div class="row" id="table-bordered">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <a href="{{ route('dashboard.customers.create') }}" class="btn btn-primary">{{ __('Create New') }} {{ __('Customer') }}</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    @include('customers.table')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection