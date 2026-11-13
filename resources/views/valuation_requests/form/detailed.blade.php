@php
  $p = $p ?? null;
  $location = $p?->location;
  $border = $p?->borders?->first();
  $neighborhoods = $neighborhoods ?? collect();
  $loc = static fn (string $field) => old($field, $location?->{$field});
  $selectedKind = old('property_kind', $p?->property_kind);
  $selectedType = old('property_type', $p?->property_type);
  $types = config('valuation_form.attachment_types');
@endphp
<div class="row g-1">
  <div class="col-md-4">
    <label class="form-label" for="city_id">{{ __('City') }} <span class="text-danger">*</span></label>
    <select name="city_id" id="city_id" class="form-select" required data-neighborhood-target="#neighborhood_id">
      <option value="">{{ __('Select') }}</option>
      @foreach ($cities as $id => $name)
        <option value="{{ $id }}" @selected((string) $loc('city_id') === (string) $id)>{{ $name }}</option>
      @endforeach
    </select>
  </div>
  <div class="col-md-4">
    <label class="form-label" for="neighborhood_id">{{ __('Neighborhood') }} <span class="text-danger">*</span></label>
    <select name="neighborhood_id" id="neighborhood_id" class="form-select" required data-selected="{{ $loc('neighborhood_id') }}">
      <option value="">{{ __('Select') }}</option>
      @foreach ($neighborhoods as $id => $name)
        <option value="{{ $id }}" @selected((string) $loc('neighborhood_id') === (string) $id)>{{ $name }}</option>
      @endforeach
    </select>
  </div>
  <div class="col-md-4">
    <label class="form-label" for="street">{{ __('Street') }}</label>
    <input type="text" name="street" id="street" class="form-control" maxlength="45" value="{{ $loc('street') }}">
  </div>
  <div class="col-md-4">
    <label class="form-label" for="part_no">{{ __('Block number') }}</label>
    <input type="text" name="part_no" id="part_no" class="form-control" maxlength="45" value="{{ $loc('part_no') }}">
  </div>
  <div class="col-md-4">
    <label class="form-label" for="sketch_no">{{ __('Plan number') }}</label>
    <input type="text" name="sketch_no" id="sketch_no" class="form-control" maxlength="45" value="{{ $loc('sketch_no') }}">
  </div>
  <div class="col-md-4">
    <label class="form-label" for="piece_number">{{ __('Plot number') }}</label>
    <input type="text" name="piece_number" id="piece_number" class="form-control" maxlength="100" value="{{ $loc('piece_number') }}">
  </div>

  @isset($kinds)
    <div class="col-md-4">
      <label class="form-label" for="property_kind">{{ __('Property kind') }} <span class="text-danger">*</span></label>
      <select name="property_kind" id="property_kind" class="form-select" required>
        <option value="">{{ __('Select') }}</option>
        @foreach (array_keys($kinds) as $kind)
          <option value="{{ $kind }}" @selected($selectedKind === $kind)>{{ $kind }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label" for="property_type">{{ __('Property type') }} <span class="text-danger">*</span></label>
      <select name="property_type" id="property_type" class="form-select" required data-selected="{{ $selectedType }}">
        <option value="">{{ __('Select') }}</option>
        @foreach ($kinds[$selectedKind] ?? [] as $type)
          <option value="{{ $type }}" @selected($selectedType === $type)>{{ $type }}</option>
        @endforeach
      </select>
    </div>
  @else
    <div class="col-md-4">
      <label class="form-label">{{ __('Property kind') }} / {{ __('Property type') }}</label>
      <input type="text" class="form-control" readonly value="{{ $p?->property_kind }} / {{ $p?->property_type }}">
      <small class="text-muted">{{ __('Change it from the request page (Change property type).') }}</small>
    </div>
  @endisset

  <div class="col-md-4">
    <label class="form-label" for="evaluator_user_id">{{ __('Evaluator') }} <span class="text-danger">*</span></label>
    <select name="evaluator_user_id" id="evaluator_user_id" class="form-select" required>
      <option value="">{{ __('Select') }}</option>
      @foreach ($evaluators as $id => $name)
        <option value="{{ $id }}" @selected((string) old('evaluator_user_id', $request->evaluator_user_id ?? null) === (string) $id)>{{ $name }}</option>
      @endforeach
    </select>
  </div>
</div>

@isset($kinds)
  @php $oldGrid = $selectedKind && $selectedType ? \App\Support\Valuation\ValuationFieldGrid::rows($selectedKind, $selectedType) : []; @endphp
  <div id="create-area-grid" class="mt-1" @if ($oldGrid === []) style="display:none" @endif>
    <h6>{{ __('Areas') }}</h6>
    <div class="row g-1" id="create-area-grid-rows">
      @foreach ($oldGrid as $row)
        <div class="col-md-3">
          <label class="form-label">{{ $row['title'] }}</label>
          <input type="number" step="any" min="0" class="form-control" name="components[{{ $row['key'] }}][area_value]" value="{{ old("components.{$row['key']}.area_value") }}">
        </div>
      @endforeach
    </div>
  </div>
@endisset

<h6 class="mt-2">{{ __('Borders and lengths') }}</h6>
<div class="row g-1">
  @foreach (['north' => __('North'), 'south' => __('South'), 'east' => __('East'), 'west' => __('West')] as $side => $label)
    <div class="col-md-3">
      <label class="form-label">{{ $label }}</label>
      <input type="text" name="borders[{{ $side }}]" class="form-control" maxlength="255" value="{{ old("borders.$side", $border?->{$side}) }}">
      <input type="number" step="any" min="0" name="borders[{{ $side }}_length]" class="form-control mt-50" placeholder="{{ __('Length (m)') }}" value="{{ old("borders.{$side}_length", $border?->{"{$side}_length"}) }}">
    </div>
  @endforeach
</div>

<div class="mt-1">
  <label class="form-label" for="notes_real_estate">{{ __('Property notes') }}</label>
  <textarea name="notes_real_estate" id="notes_real_estate" class="form-control" rows="3">{{ old('notes_real_estate', $p?->notes_real_estate) }}</textarea>
</div>

<h6 class="mt-2">{{ __('Upload attachments') }}</h6>
<div id="attachment-rows">
  <div class="row g-1 mb-50 attachment-row">
    <div class="col-md-7"><input type="file" name="new_attachments[0][file]" class="form-control"></div>
    <div class="col-md-4">
      <select name="new_attachments[0][type]" class="form-select">
        @foreach ($types as $type)
          <option value="{{ $type }}">{{ $type }}</option>
        @endforeach
      </select>
    </div>
  </div>
</div>
<button type="button" class="btn btn-sm btn-outline-primary" id="add-attachment-row">{{ __('Add another file') }}</button>

@include('valuation_requests.partials.neighborhood-script')

@push('page-script')
  <script>
    (function () {
      var container = document.getElementById('attachment-rows');
      var next = 1;
      document.getElementById('add-attachment-row').addEventListener('click', function () {
        var row = container.querySelector('.attachment-row').cloneNode(true);
        row.querySelectorAll('input, select').forEach(function (el) {
          el.name = el.name.replace(/new_attachments\[\d+\]/, 'new_attachments[' + next + ']');
          if (el.type === 'file') { el.value = ''; }
        });
        next++;
        container.appendChild(row);
      });

      document.querySelectorAll('[data-exclusive-with]').forEach(function (input) {
        input.addEventListener('input', function () {
          var other = document.getElementById(input.dataset.exclusiveWith);
          if (input.value && other) { other.value = ''; }
        });
      });

      var kind = document.getElementById('property_kind');
      var type = document.getElementById('property_type');
      if (!kind || !type) { return; }
      var grid = document.getElementById('create-area-grid');
      var gridRows = document.getElementById('create-area-grid-rows');
      var typesUrl = @json(route('dashboard.valuation-lookups.property-types', ['kind' => '__KIND__']));
      var gridUrl = @json(route('dashboard.valuation-lookups.grid', ['kind' => '__KIND__', 'type' => '__TYPE__']));
      var getJson = function (url) { return fetch(url, { headers: { 'Accept': 'application/json' } }).then(function (r) { return r.json(); }); };

      kind.addEventListener('change', function () {
        type.innerHTML = '<option value="">' + @json(__('Select')) + '</option>';
        grid.style.display = 'none';
        gridRows.innerHTML = '';
        if (!kind.value) { return; }
        getJson(typesUrl.replace('__KIND__', encodeURIComponent(kind.value))).then(function (types) {
          types.forEach(function (t) {
            var opt = document.createElement('option');
            opt.value = t; opt.textContent = t;
            type.appendChild(opt);
          });
        });
      });
      type.addEventListener('change', function () {
        gridRows.innerHTML = '';
        grid.style.display = 'none';
        if (!kind.value || !type.value) { return; }
        getJson(gridUrl.replace('__KIND__', encodeURIComponent(kind.value)).replace('__TYPE__', encodeURIComponent(type.value))).then(function (rows) {
          rows.forEach(function (row) {
            var col = document.createElement('div');
            col.className = 'col-md-3';
            var label = document.createElement('label');
            label.className = 'form-label';
            label.textContent = row.title;
            var input = document.createElement('input');
            input.type = 'number'; input.step = 'any'; input.min = '0';
            input.className = 'form-control';
            input.name = 'components[' + row.key + '][area_value]';
            col.appendChild(label); col.appendChild(input);
            gridRows.appendChild(col);
          });
          grid.style.display = rows.length ? '' : 'none';
        });
      });
    })();
  </script>
@endpush
