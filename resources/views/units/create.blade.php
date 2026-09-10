@extends('layouts.app')

@section('title', __('Create Unit'))

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard.units.index') }}">Units</a></li>
<li class="breadcrumb-item active">{{ __('Create') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('dashboard.units.store') }}" method="POST">
                    @csrf
                    @include('units.fields')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection