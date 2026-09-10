@extends('layouts.app')

@section('title', __('Create Supplier'))

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard.suppliers.index') }}">{{ __('Suppliers') }}</a></li>
<li class="breadcrumb-item active">{{ __('Create') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('dashboard.suppliers.store') }}" method="POST">
                    @csrf
                    @include('suppliers.fields')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection