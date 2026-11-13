@php
  $select = config('constants.select');
  $comparables = $property->comparables->keyBy('sequence');
  $adjustments = $property->adjustments->keyBy('type');
  $value = static function (int $i, string $field, string $source = 'adj') use ($comparables, $adjustments) {
      $model = $source === 'cmp' ? $comparables->get($i) : $adjustments->get($i);
      $raw = $model?->{$field};
      if ($raw instanceof \DateTimeInterface) {
          $raw = $raw->format('Y-m-d');
      }

      return old("comparables.$i.$field", $raw);
  };
  $firstAdjustment = $adjustments->first();
  $inputRow = static fn (string $label, string $field, string $type = 'text', string $source = 'adj') => compact('label', 'field', 'type', 'source');
  $basicRows = [
      $inputRow(__('Property type'), 'real_estate_type', 'text', 'cmp'),
      $inputRow(__('Transaction date'), 'transaction_date', 'date'),
      $inputRow(__('Transaction date (Gregorian)'), 'transaction_gregorian_date', 'date'),
      $inputRow(__('Front type'), 'front_type', 'text', 'cmp'),
      $inputRow(__('Street width'), 'street_width', 'number', 'cmp'),
      $inputRow(__('Area (m²)'), 'area', 'number', 'cmp'),
      $inputRow(__('Meter price'), 'price', 'number', 'cmp'),
      $inputRow(__('Coordinates URL'), 'coordinates_url', 'url', 'cmp'),
  ];
  $transactionRows = [
      ['market_conditions', __('Market conditions'), null],
      ['financing_terms', __('Financing terms'), null],
      ['sale_terms', __('Sale terms'), null],
  ];
  $propertyRows = [
      ['location_status', __('Location'), ($select['location_status_value']['values'] ?? $select['location_status_value']['options'])],
      ['easy_access', __('Ease of access'), ($select['easy_access_value']['values'] ?? $select['easy_access_value']['options'])],
      ['main_street_width', __('Main street width'), 'number'],
      ['streets_count', __('Streets count'), 'number'],
      ['land_shape', __('Land shape'), ($select['land_shape_value']['values'] ?? $select['land_shape_value']['options'])],
      ['land_topography', __('Land topography'), null],
      ['natural_factors', __('Natural factors'), null],
      ['near_main_street', __('Near main street'), ($select['near_main_street_value']['values'] ?? $select['near_main_street_value']['options'])],
      ['other', __('Other'), null],
  ];
@endphp

<div class="table-responsive">
  <table class="table table-sm table-bordered align-middle" id="comparables-table">
    <thead class="table-light">
      <tr>
        <th style="min-width:180px">{{ __('Item') }}</th>
        @for ($i = 1; $i <= 4; $i++)
          <th class="text-center" style="min-width:170px">{{ __('Comparable') }} {{ $i }}</th>
        @endfor
      </tr>
    </thead>
    <tbody>
      @foreach ($basicRows as $row)
        <tr>
          <td>{{ $row['label'] }}</td>
          @for ($i = 1; $i <= 4; $i++)
            <td><input type="{{ $row['type'] }}" @if ($row['type'] === 'number') step="any" @endif class="form-control form-control-sm" data-cmp="{{ $i }}" data-field="{{ $row['field'] }}"
                       name="comparables[{{ $i }}][{{ $row['field'] }}]" value="{{ $value($i, $row['field'], $row['source']) }}"></td>
          @endfor
        </tr>
      @endforeach
      <tr class="table-light"><td>{{ __('Total') }}</td>@for ($i = 1; $i <= 4; $i++)<td class="text-end" data-out-cmp="{{ $i }}" data-key="total">0</td>@endfor</tr>

      <tr><th colspan="5" class="table-secondary">{{ __('Transaction adjustments') }}</th></tr>
      @foreach ($transactionRows as [$field, $label])
        <tr>
          <td>{{ $label }}</td>
          @for ($i = 1; $i <= 4; $i++)
            <td>
              <div class="input-group input-group-sm">
                <input type="text" class="form-control" placeholder="{{ __('Value') }}" name="comparables[{{ $i }}][{{ $field }}_value]" value="{{ $value($i, $field.'_value') }}">
                <input type="number" step="any" class="form-control" placeholder="%" data-cmp="{{ $i }}" data-field="{{ $field }}_percentage"
                       name="comparables[{{ $i }}][{{ $field }}_percentage]" value="{{ $value($i, $field.'_percentage') }}">
              </div>
            </td>
          @endfor
        </tr>
      @endforeach
      <tr>
        <td>{{ __('Settlement ratio %') }}</td>
        @for ($i = 1; $i <= 4; $i++)
          <td><input type="number" step="any" class="form-control form-control-sm" data-cmp="{{ $i }}" data-field="settlement_ratio" name="comparables[{{ $i }}][settlement_ratio]" value="{{ $value($i, 'settlement_ratio') }}"></td>
        @endfor
      </tr>
      <tr class="table-light"><td>{{ __('Price after transaction adjustments') }}</td>@for ($i = 1; $i <= 4; $i++)<td class="text-end" data-out-cmp="{{ $i }}" data-key="first_price">0</td>@endfor</tr>

      <tr><th colspan="5" class="table-secondary">{{ __('Property adjustments') }}</th></tr>
      <tr>
        <td>{{ __('Area') }} %</td>
        @for ($i = 1; $i <= 4; $i++)
          <td><input type="number" step="any" class="form-control form-control-sm" data-cmp="{{ $i }}" data-field="area_percentage" name="comparables[{{ $i }}][area_percentage]" value="{{ $value($i, 'area_percentage') }}"></td>
        @endfor
      </tr>
      @foreach ($propertyRows as [$field, $label, $kind])
        <tr>
          <td>{{ $label }}</td>
          @for ($i = 1; $i <= 4; $i++)
            <td>
              <div class="input-group input-group-sm">
                @if (is_array($kind))
                  <select class="form-select" name="comparables[{{ $i }}][{{ $field }}_value]">
                    <option value="">—</option>
                    @foreach ($kind as $optValue => $optLabel)
                      <option value="{{ $optValue }}" @selected((string) $value($i, $field.'_value') === (string) $optValue)>{{ $optLabel }}</option>
                    @endforeach
                  </select>
                @else
                  <input type="{{ $kind === 'number' ? 'number' : 'text' }}" class="form-control" placeholder="{{ __('Value') }}" name="comparables[{{ $i }}][{{ $field }}_value]" value="{{ $value($i, $field.'_value') }}">
                @endif
                <input type="number" step="any" class="form-control" placeholder="%" style="max-width:70px" data-cmp="{{ $i }}" data-field="{{ $field }}_percentage"
                       name="comparables[{{ $i }}][{{ $field }}_percentage]" value="{{ $value($i, $field.'_percentage') }}">
              </div>
            </td>
          @endfor
        </tr>
      @endforeach
      <tr class="table-light"><td>{{ __('Final meter price') }}</td>@for ($i = 1; $i <= 4; $i++)<td class="text-end" data-out-cmp="{{ $i }}" data-key="final_price">0</td>@endfor</tr>
      <tr>
        <td>{{ __('Weight %') }}</td>
        @for ($i = 1; $i <= 4; $i++)
          <td><input type="number" step="any" class="form-control form-control-sm" data-cmp="{{ $i }}" data-field="proportional_adjustment" name="comparables[{{ $i }}][proportional_adjustment]" value="{{ $value($i, 'proportional_adjustment') }}"></td>
        @endfor
      </tr>
    </tbody>
  </table>
</div>

<div class="row g-1 mt-50">
  <div class="col-md-4">
    <label class="form-label">{{ __('Weighted meter price') }}</label>
    <input type="text" class="form-control" id="weighted-meter-price" readonly>
  </div>
  <div class="col-md-4">
    <label class="form-label">{{ __('Approximate meter price') }}</label>
    <input type="number" step="any" name="meter_price_approximately" class="form-control" value="{{ old('meter_price_approximately', $firstAdjustment?->meter_price_approximately) }}">
  </div>
  <div class="col-md-12">
    <label class="form-label">{{ __('Information source') }}</label>
    <textarea name="comparison_information_source" class="form-control" rows="2">{{ old('comparison_information_source', $comparables->first()?->information_source) }}</textarea>
  </div>
</div>

@push('page-script')
  <script>
    (function () {
      var TX = @json(\App\Support\Valuation\ComparablesCalculator::TRANSACTION_PERCENTAGES);
      var PROP = @json(\App\Support\Valuation\ComparablesCalculator::PROPERTY_PERCENTAGES);
      var fmt = function (n) { return (Math.round(n * 100) / 100).toLocaleString('en-US'); };
      function val(i, field) {
        var el = document.querySelector('[data-cmp="' + i + '"][data-field="' + field + '"]');
        return el ? (parseFloat(el.value) || 0) : 0;
      }
      // Mirrors App\Support\Valuation\ComparablesCalculator.
      function calc() {
        var meter = 0;
        for (var i = 1; i <= 4; i++) {
          var price = val(i, 'price');
          var first = TX.reduce(function (s, f) { return s + val(i, f); }, 0);
          var firstPrice = price + first * price / 100;
          var second = PROP.reduce(function (s, f) { return s + val(i, f); }, 0);
          var finalPrice = firstPrice + second * firstPrice / 100;
          var out = { total: val(i, 'area') * price, first_price: firstPrice, final_price: finalPrice };
          Object.keys(out).forEach(function (k) {
            document.querySelector('[data-out-cmp="' + i + '"][data-key="' + k + '"]').textContent = fmt(out[k]);
          });
          meter += finalPrice * val(i, 'proportional_adjustment') / 100;
        }
        document.getElementById('weighted-meter-price').value = fmt(meter);
      }
      document.getElementById('comparables-table').addEventListener('input', calc);
      calc();
    })();
  </script>
@endpush
