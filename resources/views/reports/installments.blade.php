@extends('layouts.app')

@section('title', __('Overdue Installments Report'))

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard.reports.installments') }}">{{ __('Reports') }}</a></li>
<li class="breadcrumb-item active">{{ __('Overdue Installments') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">{{ __('Overdue Installments') }}</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>{{ __('Invoice Number') }}</th>
                                <th>{{ __('Customer') }}</th>
                                <th>{{ __('Amount') }}</th>
                                <th>{{ __('Due Date') }}</th>
                                <th>{{ __('Days Overdue') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($overdue as $installment)
                            <tr>
                                <td>{{ $installment->invoice_number }}</td>
                                <td>
                                    {{ $installment->client_name }}
                                    @if($installment->guarantor_name)
                                        <br><small class="text-muted">{{ __('Guarantor') }}: {{ $installment->guarantor_name }} ({{ $installment->guarantor_phone }})</small>
                                    @endif
                                </td>
                                <td>{{ number_format($installment->amount, 2) }}</td>
                                <td>{{ $installment->collect_date }}</td>
                                <td>
                                    <span class="badge bg-danger">
                                        {{ $installment->days_overdue }} {{ __('Days') }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('dashboard.sale-invoices.show', $installment->id) }}" class="btn btn-sm btn-info">{{ __('View Invoice') }}</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">{{ __('No results found') }}</td>
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
