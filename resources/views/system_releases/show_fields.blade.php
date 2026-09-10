<!-- Version Number Field -->
<div class="col-sm-6">
    <div class="mb-1 row">
        <div class="col-sm-3 fw-bolder bold">
                {!! html()->label(__('models/systemReleases.fields.version_number').':', 'version_number') !!}
                </div>
        <div class="col-sm-9 ">
            <p>{{ $systemRelease->version_number }}</p>
        </div>
    </div>
</div>

<!-- Release Date Field -->
<div class="col-sm-6">
    <div class="mb-1 row">
        <div class="col-sm-3 fw-bolder bold">
                {!! html()->label(__('models/systemReleases.fields.release_date').':', 'release_date') !!}
                </div>
        <div class="col-sm-9 ">
            <p>{{ $systemRelease->release_date }}</p>
        </div>
    </div>
</div>

<!-- Created At Field -->
<div class="col-sm-6">
    <div class="mb-1 row">
        <div class="col-sm-3 fw-bolder bold">
                {!! html()->label(__('models/systemReleases.fields.created_at').':', 'created_at') !!}
                </div>
        <div class="col-sm-9 ">
            <p>{{ $systemRelease->created_at }}</p>
        </div>
    </div>
</div>

<!-- Updated At Field -->
<div class="col-sm-6">
    <div class="mb-1 row">
        <div class="col-sm-3 fw-bolder bold">
                {!! html()->label(__('models/systemReleases.fields.updated_at').':', 'updated_at') !!}
                </div>
        <div class="col-sm-9 ">
            <p>{{ $systemRelease->updated_at }}</p>
        </div>
    </div>
</div>

