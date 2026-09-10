<div class="form-group col-sm-6">
    {!! html()->label(__('models/roles.fields.name').':', 'name') !!}
    {!! html()->text('name')->class('form-control')->id('name') !!}
</div>

<div class="form-group col-sm-6">
    {!! html()->label(__('models/roles.fields.ar_name').':', 'ar_name') !!}
    {!! html()->text('ar_name')->class('form-control')->id('ar_name') !!}
</div>
