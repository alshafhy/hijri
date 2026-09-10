@extends('layouts.app')

@section('title', __('Supplier Balances Report'))

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="#">{{ __('Reports') }}</a></li>
<li class="breadcrumb-item active">{{ __('Supplier Balances') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">{{ __('Suppliers with Outstanding Balances') }}</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>{{ __('Supplier') }}</th>
                                <th>{{ __('Phone') }}</th>
                                <th>{{ __('Current Balance') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($balances as $supplier)
                            <tr>
                                <td>{{ $supplier->name }}</td>
                                <td>{{ $supplier->phone }}</td>
                                <td>
                                    <span class="badge bg-warning">
                                        {{ number_format($supplier->net_balance, 2) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('dashboard.suppliers.show', $supplier->id) }}" class="btn btn-sm btn-info">{{ __('View Profile') }}</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
