<!-- Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('name', __('Name').':') !!}
    {!! Form::text('name', null, ['class' => 'form-control', 'required', 'maxlength' => 255]) !!}
</div>


<!-- Price Field -->
<div class="form-group col-sm-6">
    {!! Form::label('price', __('Price').':') !!}
    {!! Form::number('price', null, ['class' => 'form-control', 'required', 'step' => '0.01']) !!}
</div>

<!-- Size Field -->
<div class="form-group col-sm-6">
    {!! Form::label('size_id', __('Size').':') !!}
    {!! Form::select('size_id', $sizes, null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Color Field -->
<div class="form-group col-sm-6">
    {!! Form::label('color_id', __('Color').':') !!}
    {!! Form::select('color_id', $colors, null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Images Field -->
<div class="form-group col-sm-12">
    {!! Form::label('images', __('Images').':') !!}
    <input type="file" name="images[]" class="form-control" multiple accept="image/*">
    <small class="form-text text-muted">{{ __('You can select multiple images (JPEG, PNG, JPG, GIF, max 2MB each)') }}</small>
</div>