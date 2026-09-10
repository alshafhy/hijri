<div>
    {!! html()->label(($labelTitle ?? '').':', $name) !!}
    {!! html()->select($name, $options ?? [], $defaultValue ?? null)->class($class ?? 'select2 form-select')->id($name) !!}
</div>
