@extends('layouts.app')

@section('title', __('Suppliers'))

@section('breadcrumbs', __('Suppliers'))

@section('content')
@include('flash::message')
<div class="clearfix"></div>
<div class="row" id="table-bordered">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <a href="{{ route('dashboard.suppliers.create') }}" class="btn btn-primary">{{ __('Create New') }} {{ __('Supplier') }}</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    @include('suppliers.table')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection