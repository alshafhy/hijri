@foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info'] as $key => $class)
  @if (session($key))
    <div class="alert alert-{{ $class }} p-1">{{ session($key) }}</div>
  @endif
@endforeach
