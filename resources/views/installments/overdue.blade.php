@extends('layouts.app')

@section('title', __('Overdue Installments'))

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard.installments.index') }}">Installments</a></li>
<li class="breadcrumb-item active">Overdue</li>
@endsection

@section('content')
@include('flash::message')
<div class="clearfix"></div>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4>Overdue Installments ({{ $installments->count() }})</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>{{ __('ID') }}</th>
                                <th>{{ __("Customer") }}</th>
                                <th>Amount</th>
                                <th>Due Date</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($installments as $installment)
                            <tr>
                                <td>{{ $installment->id }}</td>
                                <td>{{ $installment->customer->name ?? $installment->client_name }}</td>
                                <td>{{ number_format($installment->amount, 2) }}</td>
                                <td>{{ $installment->collect_date }}</td>
                                <td>
                                    <a href="{{ route('dashboard.installments.show', $installment->id) }}" class="btn btn-sm btn-info">{{ __('View') }}</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">No overdue installments.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection