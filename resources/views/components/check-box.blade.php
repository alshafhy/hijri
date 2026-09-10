<div>
    {!! html()->label(($labelTitle ?? '').':', $name) !!}
    {!! html()->hidden($name, 0) !!}
    {!! html()->checkbox($name, 1, (bool) ($isCheckedByDefault ?? false))->attribute('data-bootstrap-switch', 'true') !!}
</div>
