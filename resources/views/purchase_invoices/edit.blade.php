@extends('layouts.app')

@section('title', __('Edit Purchase Invoice'))

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard.purchase-invoices.index') }}">{{ __('Purchase Invoices') }}</a></li>
<li class="breadcrumb-item active">{{ __('Edit') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('dashboard.purchase-invoices.update', $purchaseInvoice->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('purchase_invoices.fields')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection