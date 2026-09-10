@extends('layouts.app')

@section('title', __('Edit Sale Invoice'))

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard.sale-invoices.index') }}">{{ __('Sale Invoices') }}</a></li>
<li class="breadcrumb-item active">{{ __('Edit') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('dashboard.sale-invoices.update', $saleInvoice->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('sale_invoices.fields')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection