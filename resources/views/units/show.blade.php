@extends('layouts.app')

@section('title', __('Unit') . ': ' . $unit->name)

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard.units.index') }}">Units</a></li>
<li class="breadcrumb-item active">{{ $unit->name }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $unit->name }}</h4>
                <a href="{{ route('dashboard.units.index') }}" class="btn btn-secondary">Back to List</a>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th>ID:</th>
                        <td>{{ $unit->id }}</td>
                    </tr>
                    <tr>
                        <th>Name:</th>
                        <td>{{ $unit->name }}</td>
                    </tr>
                </table>
                <div class="mt-3">
                    <a href="{{ route('dashboard.units.edit', $unit->id) }}" class="btn btn-primary">Edit</a>
                    @can('unit.delete')
                    <form action="{{ route('dashboard.units.destroy', $unit->id) }}" method="POST" style="display:inline;">
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