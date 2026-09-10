<!-- Comp Name Field -->
<div class="form-group col-sm-6">
    {!! html()->label(__('models/systemComponents.fields.comp_name').':', 'comp_name') !!}
    {!! html()->text('comp_name')->class('form-control')->id('comp_name') !!}
</div>

<!-- Comp Ar Label Field -->
<div class="form-group col-sm-6">
    {!! html()->label(__('models/systemComponents.fields.comp_ar_label').':', 'comp_ar_label') !!}
    {!! html()->text('comp_ar_label')->class('form-control')->id('comp_ar_label') !!}
</div><!-- Comp Ar Label Field -->

<!-- Comp Type Field -->
<div class="form-group col-sm-6">
    {!! html()->label(__('models/systemComponents.fields.comp_type').':', 'comp_type') !!}
    {!! html()->select('comp_type', $comp_type_list ?? [], $systemComponent->comp_type ?? '')->id('comp_type')->class('select2 form-select') !!}
</div>

<!-- Route Name Field -->
<div class="form-group col-sm-6">
    {!! html()->label(__('models/systemComponents.fields.route_name').':', 'route_name') !!}
    {!! html()->text('route_name')->class('form-control')->id('route_name') !!}
</div>

<!-- prefix Name Field -->
<div class="form-group col-sm-6">
    {!! html()->label(__('models/systemComponents.fields.prefix').':', 'prefix') !!}
    {!! html()->text('prefix')->class('form-control')->id('prefix') !!}
</div>

<!-- Parent Id Field -->
<div class="form-group col-sm-6">
    <div class="select2-primary">
    {!! html()->label(__('models/systemComponents.fields.parent_id').':', 'parent_id') !!}
    {!! html()->select('parent_id', $parents_list ?? [], $systemComponent->parent_id ?? '')->id('parent_id')->class('select2 form-select') !!}
    </div>
</div>


<div class="form-group col-sm-6">
    {!! html()->label(__('models/systemComponents.fields.icon_name').':', 'icon_name') !!}
    {!! html()->text('icon_name')->class('form-control')->id('icon_name') !!}
</div>
<div class="form-group col-sm-6">
    {!! html()->label(__('models/systemComponents.fields.description').':', 'description') !!}
    {!! html()->text('description')->class('form-control')->id('description') !!}
</div>

<div class="form-group col-sm-12">
    {!! html()->label(__('models/systemComponents.fields.config').':', 'config') !!}
    {!! html()->textarea('config')->class('form-control system-component-config')->id('myText') !!}
</div>
