@extends('layouts.app')

@section('title', __('Stock Report'))

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="#">{{ __('Reports') }}</a></li>
<li class="breadcrumb-item active">{{ __('Stock Report') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">{{ __('Inventory Status') }}</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>{{ __('Code') }}</th>
                                <th>{{ __('Product') }}</th>
                                <th>{{ __('Quantity') }}</th>
                                <th>{{ __('Min Quantity') }}</th>
                                <th>{{ __('Buy Price') }}</th>
                                <th>{{ __('Sell Price') }}</th>
                                <th>{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stock as $item)
                            <tr class="{{ $item->is_low_stock ? 'table-warning' : '' }}">
                                <td>{{ $item->code_id }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ number_format($item->quantity, 2) }}</td>
                                <td>{{ number_format($item->min_quantity, 2) }}</td>
                                <td>{{ number_format($item->buy_price, 2) }}</td>
                                <td>{{ number_format($item->sell_price, 2) }}</td>
                                <td>
                                    @if($item->is_low_stock)
                                        <span class="badge bg-danger">{{ __('Low Stock') }}</span>
                                    @else
                                        <span class="badge bg-success">{{ __('In Stock') }}</span>
                                    @endif
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
