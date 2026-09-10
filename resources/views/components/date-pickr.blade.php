<div>
    {!! html()->label(($labelTitle ?? '').':', $name) !!}
    {!! html()
        ->text($name, $dateValue ?? null)
        ->class('form-control flatpickr-basic')
        ->id($name)
        ->attribute('placeholder', $placeholder ?? '')
        ->attribute('autocomplete', 'off')
    !!}
</div>
