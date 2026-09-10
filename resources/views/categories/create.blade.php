@extends('layouts.app')

@section('title', __('Create Category'))

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard.categories.index') }}">{{ __('Categories') }}</a></li>
<li class="breadcrumb-item active">{{ __('Create') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('dashboard.categories.store') }}" method="POST">
                    @csrf
                    @include('categories.fields')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection