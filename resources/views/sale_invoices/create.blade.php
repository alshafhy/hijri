@extends('layouts.app')

@section('title', __('Create Sale Invoice'))

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard.sale-invoices.index') }}">{{ __('Sale Invoices') }}</a></li>
<li class="breadcrumb-item active">{{ __('Create') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('dashboard.sale-invoices.store') }}" method="POST">
                    @csrf
                    @include('sale_invoices.fields')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection