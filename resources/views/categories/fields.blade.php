<div class="mb-3">
    <label for="name">{{ __('Name') }}</label>
    <input type="text" name="name" id="name" class="form-control" value="{{ isset($category) ? $category->name : old('name') }}" required>
</div>
<div class="mb-3">
    <label for="parent_id">{{ __('Parent Category') }}</label>
    <select name="parent_id" id="parent_id" class="form-control">
        <option value="">{{ __('Select Parent (Optional)') }}</option>
        @foreach($parents as $parent)
        <option value="{{ $parent->id }}" {{ (isset($category) && $category->parent_id == $parent->id) ? 'selected' : '' }}>{{ $parent->name }}</option>
        @endforeach
    </select>
</div>
<div class="mb-3">
    <button type="submit" class="btn btn-primary">{{ __('Save Category') }}</button>
    <a href="{{ route('dashboard.categories.index') }}" class="btn btn-secondary">Cancel</a>
</div>