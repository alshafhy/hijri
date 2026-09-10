<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <th>{{ __('ID') }}</th>
            <th>{{ __('Name') }}</th>
            <th>{{ __('Category') }}</th>
            <th>{{ __('Price') }}</th>
            <th>{{ __('Quantity') }}</th>
            <th>{{ __('Unit') }}</th>
            <th>{{ __('Status') }}</th>
            <th>{{ __('Actions') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($products as $product)
        <tr>
            <td>{{ $product->id }}</td>
            <td>{{ $product->name }}</td>
            <td>{{ $product->category->name ?? '-' }}</td>
            <td>{{ number_format($product->sell_price, 2) }}</td>
            <td>
                <span class="badge bg-{{ $product->isLowStock() ? 'danger' : 'info' }}">
                    {{ number_format($product->quantity, 2) }}
                </span>
            </td>
            <td>{{ $product->unit->name ?? '-' }}</td>
            <td>
                <span class="badge bg-{{ $product->is_active ? 'success' : 'secondary' }}">
                    {{ $product->is_active ? __('Active') : __('Inactive') }}
                </span>
            </td>
            <td>
                <a href="{{ route('dashboard.products.show', $product->id) }}" class="btn btn-sm btn-info">{{ __('View') }}</a>
                <a href="{{ route('dashboard.products.edit', $product->id) }}" class="btn btn-sm btn-primary">{{ __('Edit') }}</a>
                @can('product.delete')
                <form action="{{ route('dashboard.products.destroy', $product->id) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('{{ __('Are you sure?') }}')">{{ __('Delete') }}</button>
                </form>
                @endcan
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="8" class="text-center">{{ __('No results') }}</td>
        </tr>
        @endforelse
    </tbody>
</table>
<div class="d-flex justify-content-between align-items-center mt-3">
    <div>
        {{ __('Showing') }} {{ $products->firstItem() ?? 0 }} {{ __('to') }} {{ $products->lastItem() ?? 0 }} {{ __('of') }} {{ $products->total() }} {{ __('results') }}
    </div>
    <div>
        {{ $products->links() }}
    </div>
</div>