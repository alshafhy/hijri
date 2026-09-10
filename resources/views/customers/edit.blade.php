@extends('layouts.app')

@section('title', __('Edit Customer'))

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard.customers.index') }}">{{ __('Customers') }}</a></li>
<li class="breadcrumb-item active">{{ __('Edit') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('dashboard.customers.update', $customer->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('customers.fields')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection