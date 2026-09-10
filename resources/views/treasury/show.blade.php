@extends('layouts.app')

@section('title', __('Treasury'))

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard.treasury.index') }}">Treasury</a></li>
<li class="breadcrumb-item active">{{ $treasury->id }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Transaction #{{ $treasury->id }}</h4>
                <a href="{{ route('dashboard.treasury.index') }}" class="btn btn-secondary">Back to List</a>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th>ID:</th>
                        <td>{{ $treasury->id }}</td>
                    </tr>
                    <tr>
                        <th>Date:</th>
                        <td>{{ $treasury->transaction_date }}</td>
                    </tr>
                    <tr>
                        <th>Type:</th>
                        <td>{{ $treasury->type }}</td>
                    </tr>
                    <tr>
                        <th>Amount:</th>
                        <td>{{ number_format($treasury->amount, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Notes:</th>
                        <td>{{ $treasury->notes ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection