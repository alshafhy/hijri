{{-- Inline checkbox group: $name (posted as name[]), $options (list or value=>label), $selected (list) --}}
@php
  $isList = array_is_list($options);
  $selected = array_map('strval', (array) ($selected ?? []));
  $idBase = 'cg-' . md5($name);
@endphp
<div class="d-flex flex-wrap gap-1">
  @foreach ($options as $value => $label)
    @php $value = $isList ? $label : $value; @endphp
    <div class="form-check">
      <input class="form-check-input" type="checkbox" name="{{ $name }}[]" value="{{ $value }}" id="{{ $idBase }}-{{ $loop->index }}"
             @checked(in_array((string) $value, $selected, true))>
      <label class="form-check-label" for="{{ $idBase }}-{{ $loop->index }}">{{ $label }}</label>
    </div>
  @endforeach
</div>
