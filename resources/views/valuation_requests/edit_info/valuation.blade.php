@php
  $opt = config('valuation_form');
  $select = config('constants.select');
  $total = $property->total;
  $question = $property->question;
  $qVal = static fn (string $field, $default = null) => old("question.$field", $question?->{$field} ?? $default);
  $assumption = (int) old('value_assumption', $property->value_assumption);
@endphp

<h5>{{ __('Cost approach') }}</h5>
@if ($gridRows === [])
  <div class="alert alert-warning p-1">{{ __('No area grid is defined for this property type. Set the property type first.') }}</div>
@else
  <div class="table-responsive mb-1">
    <table class="table table-sm table-bordered align-middle" id="cost-grid">
      <thead class="table-light">
        <tr><th>{{ __('Item') }}</th><th>{{ __('Area (m²)') }}</th><th>{{ __('Meter price') }}</th><th>{{ __('Total') }}</th></tr>
      </thead>
      <tbody>
        @foreach ($gridRows as $row)
          @php $comp = $components->get($row['key']); @endphp
          <tr data-row-key="{{ $row['key'] }}">
            <td>{{ $row['title'] }}</td>
            <td><input type="number" step="any" class="form-control form-control-sm" data-calc data-area
                       name="components[{{ $row['key'] }}][area_value]" value="{{ old("components.{$row['key']}.area_value", $comp?->area_value !== null ? (float) $comp->area_value : null) }}"></td>
            <td><input type="number" step="any" class="form-control form-control-sm" data-calc data-price
                       name="components[{{ $row['key'] }}][price_value]" value="{{ old("components.{$row['key']}.price_value", $comp?->price_value !== null ? (float) $comp->price_value : null) }}"></td>
            <td class="text-end" data-row-total>0</td>
          </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr class="table-light"><th colspan="3">{{ __('Total') }}</th><th class="text-end" id="calc-total">0</th></tr>
      </tfoot>
    </table>
  </div>
@endif

<div class="row g-1 mb-1">
  <div class="col-md-3">
    <label class="form-label" for="profit-percentage">{{ __('Developer profit %') }}</label>
    <input type="number" step="any" name="totals[profit_percentage]" id="profit-percentage" class="form-control" data-calc
           value="{{ old('totals.profit_percentage', $total?->profit_percentage) }}">
  </div>
  <div class="col-md-3">
    <label class="form-label" for="depreciation-type">{{ __('Depreciation method') }}</label>
    <select name="totals[depreciation_type]" id="depreciation-type" class="form-select" data-calc>
      <option value="">{{ __('None') }}</option>
      @foreach ($opt['depreciation_types'] as $value => $label)
        <option value="{{ $value }}" @selected((string) old('totals.depreciation_type', $total?->depreciation_type) === (string) $value)>{{ $label }}</option>
      @endforeach
    </select>
  </div>
  <div class="col-md-3">
    <label class="form-label" for="movables">{{ __('Movables') }}</label>
    <input type="number" step="any" name="totals[movables]" id="movables" class="form-control" data-calc value="{{ old('totals.movables', $total?->movables) }}">
  </div>
  <div class="col-md-3">
    <label class="form-label" for="value-assumption">{{ __('Value assumption') }}</label>
    <select name="value_assumption" id="value-assumption" class="form-select" data-calc>
      @foreach ($select['value_assumption']['options'] as $value => $label)
        <option value="{{ $value }}" @selected($assumption === (int) $value)>{{ $label }}</option>
      @endforeach
    </select>
  </div>
  <div class="col-md-3" id="forced-sale-wrapper">
    <label class="form-label" for="forced-sale">{{ __('Forced sale %') }}</label>
    <input type="number" min="0" max="100" name="forced_sale_percentage" id="forced-sale" class="form-control" data-calc
           value="{{ old('forced_sale_percentage', $property->forced_sale_percentage) }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">{{ __('Evaluation date') }}</label>
    <input type="date" name="evaluation_date" class="form-control" value="{{ old('evaluation_date', optional($property->evaluation_date)->format('Y-m-d')) }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">{{ __('Evaluation date (Hijri)') }}</label>
    <input type="text" name="evaluation_date_hijri" class="form-control" maxlength="64" placeholder="1447/01/01" value="{{ old('evaluation_date_hijri', $property->evaluation_date_hijri) }}">
  </div>
</div>

<div class="table-responsive mb-2">
  <table class="table table-sm mb-0" id="calc-summary">
    <tbody>
      <tr><th>{{ __('Land value') }}</th><td class="text-end" data-out="land">0</td></tr>
      <tr><th>{{ __('Developer profit') }}</th><td class="text-end" data-out="profit">0</td></tr>
      <tr><th>{{ __('Depreciation') }}</th><td class="text-end" data-out="depreciation">0</td></tr>
      <tr><th>{{ __('Movables') }}</th><td class="text-end" data-out="movables">0</td></tr>
      <tr><th>{{ __('Forced sale discount') }}</th><td class="text-end" data-out="forced">0</td></tr>
      <tr class="table-primary"><th>{{ __('Estimated value') }}</th><td class="text-end fw-bolder" data-out="total">0</td></tr>
    </tbody>
  </table>
  @if ($total?->total_amount !== null)
    <small class="text-muted">{{ __('Saved value') }}: {{ number_format((float) $total->total_amount, 2) }} — {{ app(\App\Support\Valuation\ArabicAmountInWords::class)->convert((float) $total->total_amount) }}</small>
  @endif
</div>

<h5>{{ __('General notes') }}</h5>
<div class="table-responsive mb-1">
  <table class="table table-sm align-middle">
    <tbody>
      @if ($isBuilding)
        @foreach ($opt['building_questions'] as $field => $label)
          <tr>
            <td>{{ $label }}</td>
            <td>
              @foreach ($opt['yes_no'] as $answer)
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="question[{{ $field }}]" value="{{ $answer }}" id="q-{{ $field }}-{{ $loop->index }}" @checked($qVal($field) === $answer)>
                  <label class="form-check-label" for="q-{{ $field }}-{{ $loop->index }}">{{ $answer }}</label>
                </div>
              @endforeach
            </td>
          </tr>
        @endforeach
      @endif
      <tr>
        <td>{{ $opt['question_labels']['need_cost'] }}</td>
        <td>
          @foreach ($opt['yes_no'] as $answer)
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="question[need_cost]" value="{{ $answer }}" id="q-need-cost-{{ $loop->index }}" @checked($qVal('need_cost', 'لا') === $answer)>
              <label class="form-check-label" for="q-need-cost-{{ $loop->index }}">{{ $answer }}</label>
            </div>
          @endforeach
          <div class="input-group input-group-sm mt-50" id="cost-wrapper">
            <input type="text" name="question[cost]" class="form-control" maxlength="45" placeholder="{{ $opt['question_labels']['cost'] }}" value="{{ $qVal('cost') }}">
            <span class="input-group-text">{{ __('SAR') }}</span>
          </div>
        </td>
      </tr>
      <tr>
        <td>{{ $opt['question_labels']['best_use'] }}</td>
        <td>
          @foreach ([1 => 'نعم', 0 => 'لا'] as $value => $label)
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="question[best_use]" value="{{ $value }}" id="q-best-use-{{ $value }}" @checked((int) $qVal('best_use', 1) === $value)>
              <label class="form-check-label" for="q-best-use-{{ $value }}">{{ $label }}</label>
            </div>
          @endforeach
        </td>
      </tr>
      @foreach (['inside_inspection', 'outside_inspection'] as $field)
        <tr>
          <td>{{ $opt['question_labels'][$field] }}</td>
          <td>
            @foreach ($opt['yes_no'] as $answer)
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="question[{{ $field }}]" value="{{ $answer }}" id="q-{{ $field }}-{{ $loop->index }}" @checked($qVal($field, 'لا') === $answer)>
                <label class="form-check-label" for="q-{{ $field }}-{{ $loop->index }}">{{ $answer }}</label>
              </div>
            @endforeach
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>
<label class="form-label">{{ __('Evaluator notes') }}</label>
<textarea name="notes_property" class="form-control" rows="3">{{ old('notes_property', $property->notes_property) }}</textarea>

@push('page-script')
  <script>
    (function () {
      var LAND_KEYS = @json(\App\Support\Valuation\ValuationFieldGrid::LAND_KEYS);
      var fmt = function (n) { return (Math.round(n * 100) / 100).toLocaleString('en-US'); };
      var num = function (sel) { var el = document.querySelector(sel); return el ? (parseFloat(el.value) || 0) : 0; };

      // Mirrors App\Support\Valuation\CostApproachCalculator; the server recomputes on save.
      function calculate() {
        var total = 0, rowTotals = {};
        document.querySelectorAll('#cost-grid tr[data-row-key]').forEach(function (tr) {
          var area = parseFloat(tr.querySelector('[data-area]').value) || 0;
          var price = parseFloat(tr.querySelector('[data-price]').value) || 0;
          rowTotals[tr.dataset.rowKey] = area * price;
          total += area * price;
          tr.querySelector('[data-row-total]').textContent = fmt(area * price);
        });
        var land = 0;
        for (var i = 0; i < LAND_KEYS.length; i++) { if (rowTotals[LAND_KEYS[i]]) { land = rowTotals[LAND_KEYS[i]]; break; } }
        var profit = (total - land) * num('#profit-percentage') / 100;
        var buildAgeEl = document.getElementById('build-age');
        var buildAge = buildAgeEl ? (parseFloat(buildAgeEl.value) || 0) : 0;
        var defaultAge = num('#default-build-age'), remaining = num('#remaining-build-age');
        var type = document.getElementById('depreciation-type').value, dep = 0;
        if (defaultAge > 0) {
          if (type === '1') { dep = buildAge / defaultAge * ((total - land) + profit); }
          if (type === '2') { dep = (defaultAge - remaining) / defaultAge * ((total - land) + profit); }
        }
        dep = Math.round(dep);
        var movables = num('#movables');
        var result = total + profit - dep + movables;
        var forced = 0;
        var forcedSale = document.getElementById('value-assumption').value === '4';
        document.getElementById('forced-sale-wrapper').style.display = forcedSale ? '' : 'none';
        if (forcedSale) { forced = Math.round(total * num('#forced-sale') / 100); result -= forced; }
        var totalCell = document.getElementById('calc-total');
        if (totalCell) { totalCell.textContent = fmt(total); }
        var out = { land: land, profit: profit, depreciation: dep, movables: movables, forced: forced, total: result };
        Object.keys(out).forEach(function (k) { document.querySelector('[data-out="' + k + '"]').textContent = fmt(out[k]); });
      }
      function toggleCost() {
        var yes = document.querySelector('input[name="question[need_cost]"]:checked');
        document.getElementById('cost-wrapper').style.display = yes && yes.value === 'نعم' ? '' : 'none';
      }
      document.addEventListener('input', function (e) { if (e.target.matches('[data-calc]')) { calculate(); } });
      document.addEventListener('change', function (e) {
        if (e.target.matches('[data-calc]')) { calculate(); }
        if (e.target.name === 'question[need_cost]') { toggleCost(); }
      });
      calculate();
      toggleCost();
    })();
  </script>
@endpush
