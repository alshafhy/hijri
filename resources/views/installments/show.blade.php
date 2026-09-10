@extends('layouts.app')

@section('title', __('Installment') . ' #' . $installment->id)

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard.installments.index') }}">Installments</a></li>
<li class="breadcrumb-item active">#{{ $installment->id }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Installment #{{ $installment->id }}</h4>
                <a href="{{ route('dashboard.installments.index') }}" class="btn btn-secondary">Back to List</a>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th>Customer:</th>
                                <td>{{ $installment->customer->name ?? $installment->client_name }}</td>
                            </tr>
                            <tr>
                                <th>Amount:</th>
                                <td>{{ number_format($installment->amount, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Due Date:</th>
                                <td>{{ $installment->collect_date }}</td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    <span class="badge bg-{{ $installment->status === 'paid' ? 'success' : 'warning' }}">
                                        {{ $installment->status }}
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                @if($installment->status !== 'paid')
                <form action="{{ route('dashboard.installments.collect', $installment->id) }}" method="POST" class="mt-3">
                    @csrf
                    <div class="row">
                        <div class="col-md-3">
                            <select name="pay_type" class="form-control" required>
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="transfer">Transfer</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="paid_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-success">Collect</button>
                        </div>
                    </div>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection