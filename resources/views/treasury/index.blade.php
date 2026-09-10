@extends('layouts.app')

@section('title', __('Treasury'))

@section('breadcrumbs', __('Treasury'))

@section('content')
@include('flash::message')
<div class="clearfix"></div>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4>{{ __("Current Balance") }}: {{ number_format($currentBalance, 2) }}</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    @include('treasury.table')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection