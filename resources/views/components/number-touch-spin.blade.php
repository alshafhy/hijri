<div>
    {!! html()->label(($labelTitle ?? '').':', $name) !!}
    @if (isset($defaultValue))
        {!! html()->number($name, $defaultValue)->class('touchspin input-group-lg')->id($name) !!}
    @else
        {!! html()->number($name)->class('touchspin input-group-lg')->id($name) !!}
    @endif
</div>
