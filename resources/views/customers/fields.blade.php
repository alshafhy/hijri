<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="branch_id">{{ __('Branch') }}</label>
            <select name="branch_id" id="branch_id" class="form-control" required>
                <option value="">{{ __('Select Branch') }}</option>
                @foreach($branches as $branch)
                <option value="{{ $branch->id }}" {{ (isset($customer) && $customer->branch_id == $branch->id) ? 'selected' : '' }}>{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="name">{{ __('Name') }}</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ isset($customer) ? $customer->name : old('name') }}" required>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="phone">{{ __('Phone') }}</label>
            <input type="text" name="phone" id="phone" class="form-control" value="{{ isset($customer) ? $customer->phone : old('phone') }}">
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="address">{{ __('Address') }}</label>
            <input type="text" name="address" id="address" class="form-control" value="{{ isset($customer) ? $customer->address : old('address') }}">
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="credit_limit">{{ __('Credit Limit') }}</label>
            <input type="number" name="credit_limit" id="credit_limit" class="form-control" step="0.0001" value="{{ isset($customer) ? $customer->credit_limit : 0 }}">
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="price_type">{{ __('Price Type') }}</label>
            <select name="price_type" id="price_type" class="form-control">
                <option value="1" {{ (isset($customer) && $customer->price_type == 1) ? 'selected' : '' }}>سعر بيع 1</option>
                <option value="2" {{ (isset($customer) && $customer->price_type == 2) ? 'selected' : '' }}>سعر بيع 2</option>
                <option value="3" {{ (isset($customer) && $customer->price_type == 3) ? 'selected' : '' }}>سعر بيع 3</option>
            </select>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="is_active">{{ __('Status') }}</label>
            <select name="is_active" id="is_active" class="form-control">
                <option value="1" {{ (isset($customer) && $customer->is_active) ? 'selected' : '' }}>{{ __('Active') }}</option>
                <option value="0" {{ (isset($customer) && !$customer->is_active) ? 'selected' : '' }}>{{ __('Inactive') }}</option>
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="notes">{{ __('Notes') }}</label>
            <textarea name="notes" id="notes" class="form-control" rows="2">{{ isset($customer) ? $customer->notes : old('notes') }}</textarea>
        </div>
    </div>
</div>
<div class="mb-3">
    <button type="submit" class="btn btn-primary">{{ __('Save Customer') }}</button>
    <a href="{{ route('dashboard.customers.index') }}" class="btn btn-secondary">Cancel</a>
</div>