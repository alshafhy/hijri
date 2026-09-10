@extends('layouts.app')

@section('title', __('Units'))

@section('breadcrumbs', __('Units'))

@section('content')
@include('flash::message')
<div class="clearfix"></div>
<div class="row" id="table-bordered">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <a href="{{ route('dashboard.units.create') }}" class="btn btn-primary">{{ __("Create New Unit") }}</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    @include('units.table')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection