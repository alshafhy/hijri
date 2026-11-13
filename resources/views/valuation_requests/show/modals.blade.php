@php
  $feeShare = $request->feeShares->first();
  $routes = static fn (string $name) => route("dashboard.valuation-requests.$name", $request);
@endphp

@can('send', $request)
  <x-valuation.action-modal id="sendModal" :title="__('Send valuation')" :action="$routes('send')">
    {{ __('Send this valuation to the next stage?') }}
  </x-valuation.action-modal>
@endcan

@can('approveFinal', $request)
  <x-valuation.action-modal id="approveModal" :title="__('Final approval')" :action="$routes('approve')" submit-class="btn-success">
    {{ __('Approve this valuation finally? It cannot be edited after approval.') }}
  </x-valuation.action-modal>
@endcan

@can('reject', $request)
  <x-valuation.action-modal id="rejectModal" :title="__('Return to evaluator')" :action="$routes('reject')" submit-class="btn-warning">
    {{ __('Return this valuation to the evaluator?') }}
  </x-valuation.action-modal>
@endcan

@can('unapprove', $request)
  <x-valuation.action-modal id="unapproveModal" :title="__('Return before approval')" :action="$routes('unapprove')" submit-class="btn-warning">
    {{ __('Cancel the final approval of this valuation?') }}
  </x-valuation.action-modal>
@endcan

@can('cancel', $request)
  <x-valuation.action-modal id="cancelModal" :title="__('Cancel valuation')" :action="$routes('cancel')" submit-class="btn-danger">
    {{ __('Are you sure you want to cancel this valuation?') }}
  </x-valuation.action-modal>
@endcan

@can('duplicate', $request)
  <x-valuation.action-modal id="duplicateModal" :title="__('Duplicate report')" :action="$routes('duplicate')">
    {{ __('Create a full copy of this valuation?') }}
  </x-valuation.action-modal>
@endcan

@can('changeEvaluator', $request)
  <x-valuation.action-modal id="changeEvaluatorModal" :title="__('Change evaluator')" :action="$routes('change-evaluator')">
    <select name="evaluator_user_id" class="form-select" required>
      <option value="">{{ __('Select') }}</option>
      @foreach ($evaluators as $id => $name)
        <option value="{{ $id }}" @selected($request->evaluator_user_id == $id)>{{ $name }}</option>
      @endforeach
    </select>
  </x-valuation.action-modal>
@endcan

@can('changeCoordinator', $request)
  <x-valuation.action-modal id="changeCoordinatorModal" :title="__('Change coordinator')" :action="$routes('change-coordinator')">
    <select name="coordinator_user_id" class="form-select" required>
      <option value="">{{ __('Select') }}</option>
      @foreach ($coordinators as $id => $name)
        <option value="{{ $id }}" @selected($request->coordinator_user_id == $id)>{{ $name }}</option>
      @endforeach
    </select>
  </x-valuation.action-modal>
@endcan

@can('changePropertyType', $request)
  <x-valuation.action-modal id="changePropertyTypeModal" :title="__('Change property type')" :action="$routes('change-property-type')">
    <div class="mb-1">
      <label class="form-label" for="modal-property-kind">{{ __('Property kind') }}</label>
      <select name="property_kind" id="modal-property-kind" class="form-select" required>
        @foreach (array_keys($kinds) as $kind)
          <option value="{{ $kind }}" @selected($property?->property_kind === $kind)>{{ $kind }}</option>
        @endforeach
      </select>
    </div>
    <label class="form-label" for="modal-property-type">{{ __('Property type') }}</label>
    <select name="property_type" id="modal-property-type" class="form-select" required data-current="{{ $property?->property_type }}"></select>
  </x-valuation.action-modal>
  @push('page-script')
    <script>
      (function () {
        var kinds = @json($kinds);
        var kind = document.getElementById('modal-property-kind');
        var type = document.getElementById('modal-property-type');
        function fill() {
          type.innerHTML = '';
          (kinds[kind.value] || []).forEach(function (t) {
            var opt = document.createElement('option');
            opt.value = t; opt.textContent = t;
            if (t === type.dataset.current) { opt.selected = true; }
            type.appendChild(opt);
          });
        }
        kind.addEventListener('change', fill);
        fill();
      })();
    </script>
  @endpush
@endcan

@can('overrideAmount', $request)
  <x-valuation.action-modal id="manualAmountModal" :title="__('Change valuation amount manually')" :action="$routes('override-amount')">
    <input type="number" step="any" min="0" name="total_amount_manual" class="form-control"
           value="{{ $finalAmount['manual_amount'] ?? '' }}" placeholder="{{ __('Leave empty to clear') }}">
  </x-valuation.action-modal>
@endcan

@can('manageFeeShares', $request)
  <x-valuation.action-modal id="sharesModal" :title="__('Fee shares')" :action="$routes('fee-shares')" method="PUT">
    <div class="row g-1">
      @foreach (['coordinator' => __('Coordinator share'), 'evaluator' => __('Evaluator share'), 'manager' => __('Manager share')] as $role => $label)
        <div class="col-4">
          <label class="form-label">{{ $label }} %</label>
          <input type="number" min="0" max="100" name="{{ $role }}_share" class="form-control" value="{{ $feeShare?->{"{$role}_share"} ?? 0 }}" required>
        </div>
      @endforeach
    </div>
  </x-valuation.action-modal>
@endcan

@can('toggleQimaStatus', $request)
  <x-valuation.action-modal id="qimaStatusModal" :title="__('Qima status')" :action="$routes('qima-toggle')">
    <select name="uploaded_on_qima" class="form-select">
      <option value="0" @selected(! $request->uploaded_on_qima)>{{ __('Not uploaded') }}</option>
      <option value="1" @selected($request->uploaded_on_qima)>{{ __('Uploaded to Qima') }}</option>
    </select>
  </x-valuation.action-modal>
@endcan

@if (! $request->uploaded_on_qima)
  @can('uploadOfficialReport', $request)
    <x-valuation.action-modal id="officialReportModal" :title="__('Official Qima report')" :action="$routes('official-report')" :files="true" :submit="__('Save data')">
      <div class="mb-1">
        <label class="form-label" for="report_number">{{ __('Report number') }}</label>
        <input type="text" name="report_number" id="report_number" class="form-control" value="{{ $request->number }}">
      </div>
      <div class="mb-1">
        <label class="form-label" for="official_deposit_number">{{ __('Deposit number') }}</label>
        <input type="text" name="deposit_number" id="official_deposit_number" class="form-control" value="{{ $request->deposit_number }}">
      </div>
      <label class="form-label" for="official_report">{{ __('Official report') }}</label>
      <input type="file" name="official_report" id="official_report" class="form-control" required accept=".pdf,.jpg,.jpeg,.png">
      <small class="text-muted">{{ __('Uploading locks the request for editing.') }}</small>
    </x-valuation.action-modal>
  @endcan
@endif

@if ($errors->any())
  @push('page-script')
    <script>
      @if ($errors->hasAny(['official_report', 'report_number', 'deposit_number']))
        (function () {
          var modal = document.getElementById('officialReportModal');
          if (modal) { bootstrap.Modal.getOrCreateInstance(modal).show(); }
        })();
      @endif
    </script>
  @endpush
@endif
