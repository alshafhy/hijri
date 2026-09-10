<div>
    {!! html()->hidden('redirectAction', 'index')->id('redirectAction') !!}
    @if (($action ?? '') === 'create')
        <button type="button" class="btn btn-primary" data-submit-mode="edit">{{ __('crud.saveAndGoToEdit') }}</button>
    @else
        <button type="button" class="btn btn-primary" data-submit-mode="edit">{{ __('crud.saveAndEdit') }}</button>
    @endif
    <button type="button" class="btn btn-primary" data-submit-mode="index">{{ __('crud.saveAndClose') }}</button>
    <a href="{{ route($cancelroute) }}" class="btn btn-default">
        @lang('crud.cancel')
    </a>
</div>
