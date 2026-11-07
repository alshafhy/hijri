@if ($request->contracts->isNotEmpty() || $request->partnerContracts->isNotEmpty() || $request->offers->isNotEmpty())
  <div class="card mb-2">
    <div class="card-header"><h5 class="card-title mb-0">{{ __('Client and contracts') }}</h5></div>
    <div class="table-responsive">
      <table class="table table-sm table-bordered align-middle mb-0">
        <thead class="table-light"><tr><th>{{ __('Type') }}</th><th>{{ __('Client') }}</th><th>{{ __('Details') }}</th><th>{{ __('State') }}</th><th></th></tr></thead>
        <tbody>
          @foreach ($request->contracts as $contract)
            <tr>
              <td>{{ __('Contractor') }}</td>
              <td>{{ $contract->contractor?->name ?? '—' }}</td>
              <td>{{ number_format((float) ($contract->contractor?->fees ?? 0), 2) }} {{ __('SAR') }}</td>
              <td>{{ $contract->isPaid() ? __('Paid') : __('Unpaid') }}</td>
              <td class="text-end"><a href="{{ route('dashboard.contracts.show', $contract) }}" class="btn btn-sm btn-outline-primary">{{ __('View') }}</a></td>
            </tr>
          @endforeach
          @foreach ($request->partnerContracts as $link)
            @php $estate = $link->offerEstate; @endphp
            <tr>
              <td>{{ __('Partner') }}</td>
              <td>{{ $link->partner?->name ?? '—' }}</td>
              <td>
                @if ($estate)
                  {{ __('Price offer') }} {{ $estate->offer?->number ?? '#'.$estate->offer_id }} —
                  {{ collect([$estate->estate_kind, $estate->estate_type, $estate->instrument_no])->filter()->implode(' / ') }}
                  @if ($estate->fees) ({{ number_format((float) $estate->fees, 2) }} {{ __('SAR') }}) @endif
                @else — @endif
              </td>
              <td>{{ $estate ? $estate->payment_status->label() : '—' }}</td>
              <td class="text-end">
                @if ($estate?->offer)
                  <a href="{{ route('dashboard.offers.show', $estate->offer) }}" class="btn btn-sm btn-outline-primary">{{ __('View') }}</a>
                @endif
              </td>
            </tr>
          @endforeach
          @foreach ($request->offers as $offer)
            <tr>
              <td>{{ __('Price offer') }}</td>
              <td>{{ $offer->partner?->name ?? $offer->partner_name }}</td>
              <td>{{ $offer->number }}</td>
              <td>{{ $offer->stateLabel() }}</td>
              <td class="text-end"><a href="{{ route('dashboard.offers.show', $offer) }}" class="btn btn-sm btn-outline-primary">{{ __('View') }}</a></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@endif
