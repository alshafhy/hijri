@php
  $descriptions = config('valuation_form.picture_descriptions');
  $pictures = $property->pictures->sortBy('sort_order');
@endphp
<input type="hidden" name="picture_order" id="picture-order" value="{{ $pictures->pluck('id')->implode(',') }}">

@if ($pictures->isEmpty())
  <p class="text-muted">{{ __('No photos for this property') }}</p>
@else
  <p class="text-muted small mb-50">{{ __('Drag rows to change the photo order in the report.') }}</p>
  <div class="table-responsive mb-2">
    <table class="table table-sm align-middle">
      <thead>
        <tr><th style="width:40px"></th><th>{{ __('Photo') }}</th><th>{{ __('Photo type') }}</th><th></th></tr>
      </thead>
      <tbody id="picture-rows">
        @foreach ($pictures as $picture)
          <tr data-id="{{ $picture->id }}">
            <td class="cursor-move text-muted">&#9776;</td>
            <td><x-property-picture-thumb :picture="$picture" /></td>
            <td>
              <select name="picture_descriptions[{{ $picture->id }}]" class="form-select form-select-sm">
                <option value="">{{ __('Select') }}</option>
                @foreach ($descriptions as $value)
                  <option value="{{ $value }}" @selected($picture->description === $value)>{{ $value }}</option>
                @endforeach
              </select>
            </td>
            <td>
              <button type="button" class="btn btn-sm btn-outline-danger" data-confirm-submit="delete-picture-{{ $picture->id }}">{{ __('Delete') }}</button>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
@endif

<h6>{{ __('Add photos') }}</h6>
<div id="new-picture-rows"></div>
<button type="button" class="btn btn-sm btn-outline-primary" id="add-picture-row">{{ __('Add photo') }}</button>

<template id="new-picture-template">
  <div class="row g-1 mb-50 align-items-center">
    <div class="col-md-6"><input type="file" accept="image/*" class="form-control" data-name="new_pictures"></div>
    <div class="col-md-4">
      <select class="form-select" data-name="new_picture_descriptions">
        <option value="">{{ __('Photo type') }}</option>
        @foreach ($descriptions as $value)
          <option value="{{ $value }}">{{ $value }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-md-2"><button type="button" class="btn btn-sm btn-outline-danger" data-remove-row>{{ __('Remove') }}</button></div>
  </div>
</template>

@push('page-script')
  <script src="{{ asset('vendors/js/extensions/dragula.min.js') }}"></script>
  <script>
    (function () {
      var rows = document.getElementById('picture-rows');
      if (rows && window.dragula) {
        dragula([rows]).on('drop', function () {
          document.getElementById('picture-order').value =
            Array.prototype.map.call(rows.querySelectorAll('tr[data-id]'), function (tr) { return tr.dataset.id; }).join(',');
        });
      }
      var counter = 0;
      document.getElementById('add-picture-row').addEventListener('click', function () {
        var node = document.getElementById('new-picture-template').content.cloneNode(true);
        node.querySelectorAll('[data-name]').forEach(function (el) { el.name = el.dataset.name + '[' + counter + ']'; });
        counter++;
        document.getElementById('new-picture-rows').appendChild(node);
      });
      document.addEventListener('click', function (e) {
        if (e.target.matches('[data-remove-row]')) { e.target.closest('.row').remove(); }
      });
    })();
  </script>
@endpush
