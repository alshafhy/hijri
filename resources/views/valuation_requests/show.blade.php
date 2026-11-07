@extends('layouts/contentLayoutMaster')

@section('title', __('Valuation details') . ' #' . $request->reference)

@section('content')
@php
  $location = $property?->location;
  $total = $property?->total;
  $clientName = $request->contracts->first()?->contractor?->name
      ?? $request->partnerContracts->first()?->partner?->name
      ?? $request->offers->first()?->partner?->name;
  $subject = $property ? trim($property->property_type.' - '.__('in').' - '.($location?->city?->name_ar ?? '')) : '—';
  $money = static fn ($v) => $v !== null ? number_format((float) $v, 2).' '.__('SAR') : '—';
  $date = static fn ($v) => $v ? $v->format('Y-m-d') : '—';
@endphp
<section>
  @include('valuation_requests.partials.flash')

  <div class="card mb-2">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-1">
      <h4 class="card-title mb-0">{{ __('Valuation details') }} — {{ __('Valuation number') }} {{ $request->reference }}</h4>
      <span class="badge bg-primary fs-6">{{ $request->stateLabel() }}</span>
    </div>
    <div class="card-body">
      <div class="row g-1 mb-1">
        @foreach ([
            __('Transaction number') => $request->number ?: '—',
            __('Subject') => $subject,
            __('Client') => $clientName ?? '—',
            __('Owner name') => $property?->owner_name ?? '—',
            __('Created at') => $date($request->started_at ?? $request->created_at),
            __('Attachments') => $request->attachments->count(),
            __('Photos') => $property?->pictures->count() ?? 0,
            __('Qima') => $request->uploaded_on_qima ? __('Uploaded to Qima') : __('Not uploaded'),
            __('Total valuation amount') => $money($total?->total_amount),
            __('Manual amount') => $money($finalAmount['manual_amount'] ?? null),
            __('Final amount') => $money($finalAmount['amount'] ?? null),
            __('Final approval date') => $date($request->ended_at),
        ] as $label => $value)
          <div class="col-6 col-md-3">
            <small class="text-muted d-block">{{ $label }}</small>
            <span class="fw-bolder">{{ $value }}</span>
          </div>
        @endforeach
      </div>
      @if ($request->isQimaLocked())
        <div class="alert alert-warning p-1 mb-1">{{ __('This request is locked after official Qima submission.') }}</div>
      @endif

      @include('valuation_requests.show.toolbar')

      <div class="table-responsive mt-1">
        <table class="table table-sm table-bordered text-center mb-0">
          <thead class="table-light">
            <tr>
              @foreach ($timeline as $step)
                @if (! $loop->first)<th>{{ __('Duration') }}</th>@endif
                <th>{{ $step['label'] }}</th>
              @endforeach
            </tr>
          </thead>
          <tbody>
            <tr>
              @foreach ($timeline as $step)
                @if (! $loop->first)<td class="text-muted">{{ $step['duration'] ?? '—' }}</td>@endif
                <td>{{ $step['at'] ? $step['at']->format('Y-m-d H:i') : '—' }}</td>
              @endforeach
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  @include('valuation_requests.show.media-attachments')

  @if ($property)
    @include('valuation_requests.show.property')
    @include('valuation_requests.show.media-photos')
    @include('valuation_requests.show.valuation')
  @endif

  @include('valuation_requests.show.links')
  @include('valuation_requests.show.modals')
</section>
@endsection
