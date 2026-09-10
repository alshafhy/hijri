<div class="form-group col-sm-6">
    {!! html()->label(__('models/users.fields.name').':', 'name') !!}
    {!! html()->text('name')->class('form-control')->id('name') !!}
</div>

<div class="form-group col-sm-6">
    {!! html()->label(__('models/users.fields.email').':', 'email') !!}
    {!! html()->email('email')->class('form-control')->id('email') !!}
</div>

<div class="form-group col-sm-6">
    {!! html()->label(__('models/users.fields.username').':', 'username') !!}
    {!! html()->text('username')->class('form-control')->id('username') !!}
</div>

<div class="form-group col-sm-6">
    {!! html()->label(__('models/users.fields.branch_name').':', 'branch_id') !!}
    {!! html()->select('branch_id', $branches ?? [])->class('form-control select2')->id('branch_id') !!}
</div>
