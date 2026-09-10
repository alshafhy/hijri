<!-- System Release Id Field -->
<div class="form-group col-sm-6">
    {{-- {!! html()->label(__('models/systemReleasesFeatures.fields.system_release_id').':', 'system_release_id') !!}
    {{-- {!! html()->text('system_release_id')->class('form-control')->attribute('required', 'required') !!} --}}
    <x-select2 name="system_release_id" :options="$systemReleases" :labelTitle="__('models/systemReleasesFeatures.fields.system_release_id')"></x-select2>

</div>

<!-- Title Field -->
<div class="form-group col-sm-6">
    {!! html()->label(__('models/systemReleasesFeatures.fields.title').':', 'title') !!}
    {!! html()->text('title')->class('form-control')->id('title') !!}
</div>

<!-- Description Field -->
<div class="form-group col-sm-6">
    {!! html()->label(__('models/systemReleasesFeatures.fields.description').':', 'description') !!}
    {!! html()->text('description')->class('form-control')->id('description') !!}
</div>

<!-- Feature Order Field -->
<div class="form-group col-sm-6">
    {!! html()->label(__('models/systemReleasesFeatures.fields.feature_order').':', 'feature_order') !!}
    {!! html()->text('feature_order')->class('form-control')->id('feature_order') !!}
</div>