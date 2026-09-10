@extends('layouts.app')

@section('title', __('Installments'))

@section('breadcrumbs', __('Installments'))

@section('content')
@include('flash::message')
<div class="clearfix"></div>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <a href="{{ route('dashboard.installments.overdue') }}" class="btn btn-warning">View Overdue ({{ $overdueCount }})</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    @include('installments.table')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection