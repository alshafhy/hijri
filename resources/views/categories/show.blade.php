@extends('layouts.app')

@section('title', __('Category') . ': ' . $category->name)

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard.categories.index') }}">{{ __('Categories') }}</a></li>
<li class="breadcrumb-item active">{{ $category->name }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $category->name }}</h4>
                <a href="{{ route('dashboard.categories.index') }}" class="btn btn-secondary">Back to List</a>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th>ID:</th>
                        <td>{{ $category->id }}</td>
                    </tr>
                    <tr>
                        <th>Name:</th>
                        <td>{{ $category->name }}</td>
                    </tr>
                    <tr>
                        <th>Parent:</th>
                        <td>{{ $category->parent->name ?? '-' }}</td>
                    </tr>
                </table>
                <div class="mt-3">
                    <a href="{{ route('dashboard.categories.edit', $category->id) }}" class="btn btn-primary">Edit</a>
                    @can('category.delete')
                    <form action="{{ route('dashboard.categories.destroy', $category->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
@endsection