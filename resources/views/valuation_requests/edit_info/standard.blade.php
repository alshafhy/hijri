@php
  $select = config('constants.select');
  $current = static fn (string $field) => old($field, $property->{$field});
  $selectField = static function (string $field, string $listKey, string $label, string $col = 'col-md-4') use ($select, $current) {
      return compact('field', 'listKey', 'label', 'col') + ['options' => $select[$listKey]['options'] ?? [], 'value' => $current($field)];
  };
  $selects = [
      $selectField('valuation_usage', 'valuation_usage', __('Intended use')),
      $selectField('valuation_type', 'valuation_type_new', __('Valuation purpose')),
      $selectField('base_value', 'base_value', __('Basis of value')),
  ];
  $wayRows = [
      'market' => __('Market approach'),
      'income' => __('Income approach'),
      'cost' => __('Cost approach'),
  ];
  $textareas = [
      'note_base_value' => __('Basis of value note'),
      'note_valuation_way_type' => __('Valuation approach note'),
      'type_of_getings_value' => __('Value derivation'),
      'requested_papers' => __('Requested documents'),
      'assumptions' => __('Assumptions'),
      'important_assumptions' => __('Special assumptions'),
      'area_of_search' => __('Scope of research'),
      'area_approval_way' => __('Area verification method'),
      'informations_source' => __('Information sources'),
      'conclusion_value' => __('Value conclusion'),
      'other_users' => __('Other users'),
  ];
  $customRows = $request->customTableRows;
  $customTitle = old('custom_table.title', $customRows->first()?->table_title);
  $customData = old('custom_table.rows', $customRows->map(fn ($r) => ['label' => $r->label, 'values' => (array) $r->values])->all());
  $columnCount = max(1, collect($customData)->map(fn ($r) => count((array) ($r['values'] ?? [])))->max() ?? 1);
@endphp

<div class="row g-1">
  @foreach ($selects as $s)
    <div class="{{ $s['col'] }}">
      <label class="form-label" for="std-{{ $s['field'] }}">{{ $s['label'] }}</label>
      <select name="{{ $s['field'] }}" id="std-{{ $s['field'] }}" class="form-select">
        @if ($s['value'] !== null && $s['value'] !== '' && ! array_key_exists((string) $s['value'], $s['options']))
          <option value="{{ $s['value'] }}" selected>{{ $s['value'] }}</option>
        @endif
        @foreach ($s['options'] as $value => $label)
          <option value="{{ $value }}" @selected((string) $s['value'] === (string) $value)>{{ trim($label) }}</option>
        @endforeach
      </select>
    </div>
  @endforeach
  <div class="col-md-6">
    <label class="form-label">{{ __('Intended use details') }}</label>
    <input type="text" name="valuation_usage_desc" class="form-control" maxlength="250" value="{{ $current('valuation_usage_desc') }}">
  </div>
  <div class="col-md-6">
    <label class="form-label">{{ __('Valuation purpose details') }}</label>
    <input type="text" name="valuation_type_desc" class="form-control" value="{{ $current('valuation_type_desc') }}">
  </div>
</div>

<h6 class="mt-2">{{ __('Valuation approaches') }}</h6>
<div class="table-responsive">
  <table class="table table-sm table-bordered align-middle">
    <thead class="table-light"><tr><th>{{ __('Approach') }}</th><th>{{ __('Usage') }}</th><th>{{ __('Method') }}</th></tr></thead>
    <tbody>
      @foreach ($wayRows as $prefix => $label)
        <tr>
          <td>{{ $label }}</td>
          @foreach (['main', 'sub'] as $part)
            @php $field = "{$prefix}_valuation_way_{$part}"; @endphp
            <td>
              <select name="{{ $field }}" class="form-select form-select-sm">
                @foreach ($select[$field]['options'] ?? [] as $value => $optLabel)
                  <option value="{{ $value }}" @selected((string) $current($field) === (string) $value)>{{ $optLabel }}</option>
                @endforeach
              </select>
            </td>
          @endforeach
        </tr>
      @endforeach
    </tbody>
  </table>
</div>

<div class="row g-1 mt-50">
  @foreach ($textareas as $field => $label)
    <div class="col-md-6">
      <label class="form-label">{{ $label }}</label>
      <textarea name="{{ $field }}" class="form-control" rows="2">{{ $current($field) }}</textarea>
    </div>
  @endforeach
  <div class="col-md-6">
    <label class="form-label">{{ __('Deposit number') }}</label>
    <input type="text" name="deposit_number" class="form-control" maxlength="200" value="{{ $current('deposit_number') }}">
  </div>
</div>

<h5 class="mt-2">{{ __('Custom table') }}</h5>
<div class="mb-1">
  <input type="text" name="custom_table[title]" class="form-control" placeholder="{{ __('Table title') }}" value="{{ $customTitle }}">
</div>
<div class="table-responsive">
  <table class="table table-sm table-bordered" id="custom-table" data-columns="{{ $columnCount }}">
    <tbody>
      @foreach ($customData as $r => $row)
        <tr>
          <td><input type="text" class="form-control form-control-sm" name="custom_table[rows][{{ $r }}][label]" placeholder="{{ __('Label') }}" value="{{ $row['label'] ?? '' }}"></td>
          @for ($c = 0; $c < $columnCount; $c++)
            <td><input type="text" class="form-control form-control-sm" name="custom_table[rows][{{ $r }}][values][]" value="{{ ((array) ($row['values'] ?? []))[$c] ?? '' }}"></td>
          @endfor
          <td class="text-center"><button type="button" class="btn btn-sm btn-flat-danger" data-remove-row>&times;</button></td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>
<button type="button" class="btn btn-sm btn-outline-primary" id="custom-add-row">{{ __('Add row') }}</button>
<button type="button" class="btn btn-sm btn-outline-secondary" id="custom-add-column">{{ __('Add column') }}</button>

@push('page-script')
  <script>
    (function () {
      var table = document.getElementById('custom-table');
      var body = table.querySelector('tbody');
      var nextIndex = body.rows.length;
      function cell(name, placeholder) {
        var td = document.createElement('td');
        var input = document.createElement('input');
        input.type = 'text';
        input.className = 'form-control form-control-sm';
        input.name = name;
        if (placeholder) { input.placeholder = placeholder; }
        td.appendChild(input);
        return td;
      }
      document.getElementById('custom-add-row').addEventListener('click', function () {
        var i = nextIndex++, tr = document.createElement('tr');
        tr.appendChild(cell('custom_table[rows][' + i + '][label]', @json(__('Label'))));
        for (var c = 0; c < parseInt(table.dataset.columns, 10); c++) {
          tr.appendChild(cell('custom_table[rows][' + i + '][values][]'));
        }
        var td = document.createElement('td');
        td.className = 'text-center';
        td.innerHTML = '<button type="button" class="btn btn-sm btn-flat-danger" data-remove-row>&times;</button>';
        tr.appendChild(td);
        body.appendChild(tr);
      });
      document.getElementById('custom-add-column').addEventListener('click', function () {
        table.dataset.columns = parseInt(table.dataset.columns, 10) + 1;
        Array.prototype.forEach.call(body.rows, function (tr) {
          var label = tr.querySelector('input[name$="[label]"]');
          var name = label.name.replace('[label]', '[values][]');
          tr.insertBefore(cell(name), tr.lastElementChild);
        });
      });
      body.addEventListener('click', function (e) {
        if (e.target.closest('[data-remove-row]')) { e.target.closest('tr').remove(); }
      });
    })();
  </script>
@endpush
