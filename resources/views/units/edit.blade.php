@extends('layouts.app')

@section('title', __('Edit Unit'))

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard.units.index') }}">Units</a></li>
<li class="breadcrumb-item active">{{ __('Edit') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('dashboard.units.update', $unit->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('units.fields')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection