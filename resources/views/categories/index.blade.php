@extends('layouts.app')

@section('title', __('Categories'))

@section('breadcrumbs', __('Categories'))

@section('content')
@include('flash::message')
<div class="clearfix"></div>
<div class="row" id="table-bordered">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <a href="{{ route('dashboard.categories.create') }}" class="btn btn-primary">{{ __('Create New') }} {{ __('Category') }}</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    @include('categories.table')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection