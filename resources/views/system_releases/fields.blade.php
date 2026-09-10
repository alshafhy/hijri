<!-- Version Number Field -->
<div class="form-group col-sm-6">
    {!! html()->label(__('models/systemReleases.fields.version_number').':', 'version_number') !!}
    {!! html()->text('version_number')->class('form-control')->attribute('required', 'required')->id('version_number') !!}
</div>

<!-- Release Date Field -->
<div class="form-group col-sm-4">
    <x-date-pickr name="release_date" :labelTitle="__('models/systemReleases.fields.release_date')"></x-date-pickr>
    
</div>