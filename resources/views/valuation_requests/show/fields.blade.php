{{-- $title: string, $fields: array<label, scalar|null> --}}
<div class="card mb-2">
  <div class="card-header"><h5 class="card-title mb-0">{{ $title }}</h5></div>
  <div class="card-body">
    <div class="row g-1">
      @foreach ($fields as $label => $value)
        <div class="{{ $col ?? 'col-6 col-md-3' }}">
          <small class="text-muted d-block">{{ $label }}</small>
          <span class="fw-bold" style="white-space: pre-line">{{ ($value === null || $value === '') ? '—' : $value }}</span>
        </div>
      @endforeach
    </div>
  </div>
</div>
