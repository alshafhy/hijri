@php
  $select = config('constants.select');
  $total = $property->total;
  $num = static fn ($v, int $d = 2) => $v !== null ? number_format((float) $v, $d) : '—';
  $option = static fn (string $list, $value) => $value === null || $value === '' ? null : trim((string) ($select[$list]['options'][$value] ?? $value));
  $words = $total?->total_amount !== null ? app(\App\Support\Valuation\ArabicAmountInWords::class)->convert((float) $total->total_amount) : null;
  $customRows = $request->customTableRows;
  $comparables = $property->comparables->sortBy('sequence')->values();
  $adjustments = $property->adjustments->keyBy('type');
@endphp

<div class="card mb-2">
  <div class="card-header"><h5 class="card-title mb-0">{{ __('Valuation') }}</h5></div>
  @if (empty($breakdown['rows']))
    <div class="card-body text-muted">{{ __('No valuation data entered yet.') }}</div>
  @else
    <div class="table-responsive">
      <table class="table table-sm table-bordered mb-0">
        <thead class="table-light"><tr><th>{{ __('Item') }}</th><th>{{ __('Area (m²)') }}</th><th>{{ __('Meter price') }}</th><th>{{ __('Total') }}</th></tr></thead>
        <tbody>
          @foreach ($breakdown['rows'] as $row)
            <tr><td>{{ $row['title'] }}</td><td>{{ $num($row['area']) }}</td><td>{{ $num($row['price']) }}</td><td>{{ $num($row['total']) }}</td></tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr class="table-light"><th colspan="3">{{ __('Total') }}</th><th>{{ $num($breakdown['total']) }}</th></tr>
          @if ($total)
            <tr><td colspan="3">{{ __('Developer profit') }} ({{ $num($total->profit_percentage, 0) }}%)</td><td>{{ $num($total->profit_amount) }}</td></tr>
            <tr><td colspan="3">{{ __('Depreciation') }} @if ($total->depreciation_type) ({{ config('valuation_form.depreciation_types')[(int) $total->depreciation_type] ?? '' }}) @endif</td><td>{{ $num($total->depreciation_amount) }}</td></tr>
            @if ((float) $total->forced_sale_amount > 0)
              <tr><td colspan="3">{{ __('Forced sale discount') }} ({{ $num($total->forced_sale_percentage, 0) }}%)</td><td>{{ $num($total->forced_sale_amount) }}</td></tr>
            @endif
            <tr><td colspan="3">{{ __('Movables') }}</td><td>{{ $num($total->movables) }}</td></tr>
            <tr class="table-primary"><th colspan="3">{{ __('Estimated value') }}</th><th>{{ $num($total->total_amount) }} {{ __('SAR') }}</th></tr>
            @if ($words)
              <tr><td colspan="4" class="text-center fw-bold">{{ $words }}</td></tr>
            @endif
          @endif
        </tfoot>
      </table>
    </div>
    @if ($breakdown['land_share'] !== null && $breakdown['land'] > 0)
      <div class="card-body pt-1">
        <small class="text-muted">{{ __('Land vs buildings price share') }}</small>
        <div class="progress" style="height: 18px;">
          <div class="progress-bar bg-success" style="width: {{ $breakdown['land_share'] }}%">{{ __('Land') }} {{ $breakdown['land_share'] }}%</div>
          <div class="progress-bar bg-info" style="width: {{ $breakdown['building_share'] }}%">{{ __('Buildings') }} {{ $breakdown['building_share'] }}%</div>
        </div>
      </div>
    @endif
  @endif
</div>

@if ($customRows->isNotEmpty())
  @php $columns = $customRows->map(fn ($r) => count((array) $r->values))->max(); @endphp
  <div class="card mb-2">
    <div class="card-header"><h5 class="card-title mb-0">{{ $customRows->first()->table_title ?: __('Custom table') }}</h5></div>
    <div class="table-responsive">
      <table class="table table-sm table-bordered mb-0">
        <tbody>
          @foreach ($customRows as $row)
            <tr>
              <th class="table-light">{{ $row->label }}</th>
              @for ($c = 0; $c < $columns; $c++)
                <td>{{ ((array) $row->values)[$c] ?? '' }}</td>
              @endfor
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@endif

@include('valuation_requests.show.fields', ['title' => __('Valuation standard data'), 'col' => 'col-12 col-md-6', 'fields' => [
    __('Intended use') => trim(($option('valuation_usage', $property->valuation_usage) ?? '').' '.($property->valuation_usage_desc ?? '')),
    __('Valuation purpose') => trim(($option('valuation_type_new', $property->valuation_type) ?? '').' '.($property->valuation_type_desc ?? '')),
    __('Basis of value') => $option('base_value', $property->base_value),
    __('Basis of value note') => $property->note_base_value,
    __('Market approach') => trim(($option('market_valuation_way_main', $property->market_valuation_way_main) ?? '').' / '.($option('market_valuation_way_sub', $property->market_valuation_way_sub) ?? ''), ' /'),
    __('Income approach') => trim(($option('income_valuation_way_main', $property->income_valuation_way_main) ?? '').' / '.($option('income_valuation_way_sub', $property->income_valuation_way_sub) ?? ''), ' /'),
    __('Cost approach') => trim(($option('cost_valuation_way_main', $property->cost_valuation_way_main) ?? '').' / '.($option('cost_valuation_way_sub', $property->cost_valuation_way_sub) ?? ''), ' /'),
    __('Valuation approach note') => $property->note_valuation_way_type,
    __('Value assumption') => $option('value_assumption', $property->value_assumption),
    __('Evaluation date') => optional($property->evaluation_date)->format('Y-m-d').($property->evaluation_date_hijri ? ' — '.$property->evaluation_date_hijri : ''),
    __('Value derivation') => $property->type_of_getings_value,
    __('Requested documents') => $property->requested_papers,
    __('Assumptions') => $property->assumptions,
    __('Special assumptions') => $property->important_assumptions,
    __('Scope of research') => $property->area_of_search,
    __('Area verification method') => $property->area_approval_way,
    __('Information sources') => $property->informations_source,
    __('Value conclusion') => $property->conclusion_value,
    __('Other users') => $property->other_users,
    __('Deposit number') => $property->deposit_number ?: $request->deposit_number,
]])

@if ($comparables->isNotEmpty())
  <div class="card mb-2">
    <div class="card-header"><h5 class="card-title mb-0">{{ __('Comparables') }}</h5></div>
    <div class="table-responsive">
      <table class="table table-sm table-bordered mb-0">
        <thead class="table-light">
          <tr><th>#</th><th>{{ __('Property type') }}</th><th>{{ __('Transaction date') }}</th><th>{{ __('Area (m²)') }}</th><th>{{ __('Meter price') }}</th><th>{{ __('Total') }}</th><th>{{ __('Street width') }}</th><th>{{ __('Front type') }}</th><th>{{ __('Location') }}</th></tr>
        </thead>
        <tbody>
          @foreach ($comparables as $comparable)
            @php $adj = $adjustments->get($comparable->sequence); @endphp
            <tr>
              <td>{{ $comparable->sequence }}</td>
              <td>{{ $comparable->real_estate_type ?: '—' }}</td>
              <td>{{ optional($adj?->transaction_date)->format('Y-m-d') ?? '—' }}</td>
              <td>{{ $num($comparable->area) }}</td>
              <td>{{ $num($comparable->price) }}</td>
              <td>{{ $num((float) $comparable->area * (float) $comparable->price) }}</td>
              <td>{{ $comparable->street_width ?? '—' }}</td>
              <td>{{ $comparable->front_type ?: '—' }}</td>
              <td>
                @if ($comparable->coordinates_url && filter_var($comparable->coordinates_url, FILTER_VALIDATE_URL))
                  <a href="{{ $comparable->coordinates_url }}" target="_blank" rel="noopener noreferrer">{{ __('View') }}</a>
                @else — @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @if ($comparables->first()->information_source)
      <div class="card-body pt-1"><small class="text-muted">{{ __('Information source') }}:</small> {{ $comparables->first()->information_source }}</div>
    @endif
  </div>
@endif

@if ($adjustments->isNotEmpty())
  @php
    $first = $adjustments->first();
    $adjRows = [
        __('Market conditions') => 'market_conditions_percentage',
        __('Financing terms') => 'financing_terms_percentage',
        __('Sale terms') => 'sale_terms_percentage',
        __('Settlement ratio %') => 'settlement_ratio',
        __('Area') => 'area_percentage',
        __('Location') => 'location_status_percentage',
        __('Ease of access') => 'easy_access_percentage',
        __('Main street width') => 'main_street_width_percentage',
        __('Streets count') => 'streets_count_percentage',
        __('Land shape') => 'land_shape_percentage',
        __('Land topography') => 'land_topography_percentage',
        __('Natural factors') => 'natural_factors_percentage',
        __('Near main street') => 'near_main_street_percentage',
        __('Other') => 'other_percentage',
    ];
  @endphp
  <div class="card mb-2">
    <div class="card-header"><h5 class="card-title mb-0">{{ __('Adjustments') }}</h5></div>
    <div class="table-responsive">
      <table class="table table-sm table-bordered mb-0 text-center">
        <thead class="table-light">
          <tr><th class="text-start">{{ __('Item') }}</th>@foreach ($adjustments as $type => $adj)<th>{{ __('Comparable') }} {{ $type }}</th>@endforeach</tr>
        </thead>
        <tbody>
          @foreach ($adjRows as $label => $field)
            <tr><td class="text-start">{{ $label }}</td>@foreach ($adjustments as $adj)<td>{{ $adj->{$field} !== null ? $num($adj->{$field}, 2).'%' : '—' }}</td>@endforeach</tr>
          @endforeach
          <tr class="table-light"><th class="text-start">{{ __('Final meter price') }}</th>@foreach ($adjustments as $adj)<th>{{ $num($adj->total_adjustments_price) }}</th>@endforeach</tr>
          <tr><td class="text-start">{{ __('Weight %') }}</td>@foreach ($adjustments as $adj)<td>{{ $adj->proportional_adjustment !== null ? $adj->proportional_adjustment.'%' : '—' }}</td>@endforeach</tr>
        </tbody>
      </table>
    </div>
    <div class="card-body pt-1">
      <strong>{{ __('Weighted meter price') }}:</strong> {{ $num($first->meter_price) }}
      @if ($first->meter_price_approximately)
        — <strong>{{ __('Approximate meter price') }}:</strong> {{ $num($first->meter_price_approximately) }}
      @endif
    </div>
  </div>
@endif
