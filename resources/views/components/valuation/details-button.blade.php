@props(['request', 'label' => null])
@php
  $sla = app(\App\Support\Valuation\ValuationSla::class);
  $status = $sla->status($request);
  $title = match ($status) {
      \App\Support\Valuation\ValuationSla::ON_TIME => __('Within the turnaround time'),
      \App\Support\Valuation\ValuationSla::OVERDUE => __('Over the turnaround time'),
      default => null,
  };
@endphp
<a href="{{ route('dashboard.valuation-requests.show', $request) }}" {{ $attributes->class(['btn btn-sm rounded', $sla->buttonClass($request)]) }} data-sla="{{ $status ?? 'none' }}" @if ($title) title="{{ $title }}" @endif>{{ $label ?? __('View') }}</a>
