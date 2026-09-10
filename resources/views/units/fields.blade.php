<div class="mb-3">
    <label for="name">{{ __("Name") }}</label>
    <input type="text" name="name" id="name" class="form-control" value="{{ isset($unit) ? $unit->name : old('name') }}" required>
</div>
<div class="mb-3">
    <button type="submit" class="btn btn-primary">Save Unit</button>
    <a href="{{ route('dashboard.units.index') }}" class="btn btn-secondary">Cancel</a>
</div>