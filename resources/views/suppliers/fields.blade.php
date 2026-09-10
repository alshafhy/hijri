<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="branch_id">{{ __('Branch') }}</label>
            <select name="branch_id" id="branch_id" class="form-control" required>
                <option value="">{{ __('Select Branch') }}</option>
                @foreach($branches as $branch)
                <option value="{{ $branch->id }}" {{ (isset($supplier) && $supplier->branch_id == $branch->id) ? 'selected' : '' }}>{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="name">{{ __('Name') }}</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ isset($supplier) ? $supplier->name : old('name') }}" required>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="phone">{{ __('Phone') }}</label>
            <input type="text" name="phone" id="phone" class="form-control" value="{{ isset($supplier) ? $supplier->phone : old('phone') }}">
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="address">{{ __('Address') }}</label>
            <input type="text" name="address" id="address" class="form-control" value="{{ isset($supplier) ? $supplier->address : old('address') }}">
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="opening_balance">{{ __('Opening Balance') }}</label>
            <input type="number" name="opening_balance" id="opening_balance" class="form-control" step="0.0001" value="{{ isset($supplier) ? $supplier->opening_balance : 0 }}">
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="is_active">{{ __('Status') }}</label>
            <select name="is_active" id="is_active" class="form-control">
                <option value="1" {{ (isset($supplier) && $supplier->is_active) ? 'selected' : '' }}>{{ __('Active') }}</option>
                <option value="0" {{ (isset($supplier) && !$supplier->is_active) ? 'selected' : '' }}>{{ __('Inactive') }}</option>
            </select>
        </div>
    </div>
</div>
<div class="mb-3">
    <label for="notes">{{ __('Notes') }}</label>
    <textarea name="notes" id="notes" class="form-control" rows="2">{{ isset($supplier) ? $supplier->notes : old('notes') }}</textarea>
</div>
<div class="mb-3">
    <button type="submit" class="btn btn-primary">{{ __('Save Supplier') }}</button>
    <a href="{{ route('dashboard.suppliers.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
</div>