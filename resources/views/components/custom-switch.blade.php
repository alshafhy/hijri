<label class="form-check-label mb-50" for="{{ $name }}">{{ $labelTitle }}</label>
<div class="form-check form-switch form-check-primary">
    {!! html()->hidden($name, 0) !!}
    {!! html()->checkbox($name, '1', (bool) ($isCheckedByDefault ?? false))->class('form-check-input')->id($name) !!}
    <label class="form-check-label" for="{{ $name }}">
        <span class="switch-icon-left"><i data-feather="check"></i></span>
        <span class="switch-icon-right"><i data-feather="x"></i></span>
    </label>
</div>
