<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="branch_id">{{ __('Branch') }}</label>
            <select name="branch_id" id="branch_id" class="form-control" required>
                <option value="">{{ __('Select Branch') }}</option>
                @foreach(\App\Models\Branch::all() as $branch)
                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="customer_id">{{ __('Customer') }}</label>
            <select name="customer_id" id="customer_id" class="form-control">
                <option value="">{{ __('Select Customer (Optional)') }}</option>
                @foreach(\App\Models\Customer::all() as $customer)
                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="invoice_date">{{ __('Invoice Date') }}</label>
            <input type="date" name="invoice_date" id="invoice_date" class="form-control" value="{{ date('Y-m-d') }}" required>
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="payment_type">{{ __('Payment Type') }}</label>
            <select name="payment_type" id="payment_type" class="form-control" required>
                <option value="cash">{{ __('Cash') }}</option>
                <option value="credit">{{ __('Credit') }}</option>
                <option value="partial">{{ __('Partial') }}</option>
            </select>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="discount_amount">{{ __('Discount Amount') }}</label>
            <input type="number" name="discount_amount" id="discount_amount" class="form-control" step="0.01" value="0">
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="discount_type">{{ __('Discount Type') }}</label>
            <select name="discount_type" id="discount_type" class="form-control">
                <option value="fixed">{{ __('Fixed') }}</option>
                <option value="percentage">{{ __('Percentage') }}</option>
            </select>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="paid_amount">{{ __('Paid Amount') }}</label>
            <input type="number" name="paid_amount" id="paid_amount" class="form-control" step="0.01" value="0">
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="notes">{{ __('Notes') }}</label>
            <textarea name="notes" id="notes" class="form-control" rows="2"></textarea>
        </div>
    </div>
</div>
<div class="mb-3">
    <button type="submit" class="btn btn-primary">{{ __('Create Invoice') }}</button>
    <a href="{{ route('dashboard.sale-invoices.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
</div>