@extends('layouts/contentLayoutMaster')

@section('title', __('Valuation request') . ' #' . $request->id)

@section('content')
@php
  $feeShare = $request->feeShares->first();
  $manualAmount = $finalAmount['manual_amount'] ?? null;
@endphp
<section>
  @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
  @endif

  <div class="card mb-2">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-1">
      <h4 class="card-title mb-0">{{ __('Valuation request') }} #{{ $request->id }}</h4>
      <div class="d-flex align-items-center gap-1 flex-wrap">
        <span class="badge bg-primary">{{ $request->state }}</span>
        @can('exportPdf', $request)
          <div class="btn-group">
            <button type="button" class="btn btn-sm btn-outline-danger dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
              {{ __('Export PDF') }}
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li>
                <a class="dropdown-item" href="{{ route('dashboard.valuation-requests.exports.queue', [$request, 'enforcement']) }}">
                  {{ __('Enforcement Center report') }}
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="{{ route('dashboard.valuation-requests.exports.queue', [$request, 'full']) }}">
                  {{ __('Full report') }}
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="{{ route('dashboard.valuation-requests.exports.queue', [$request, 'full-draft']) }}">
                  {{ __('Full report draft') }}
                </a>
              </li>
            </ul>
          </div>
        @endcan
      </div>
    </div>
    <div class="card-body">
      <div class="row mb-1">
        <div class="col-md-3"><strong>{{ __('Reference') }}:</strong> {{ $request->reference }}</div>
        <div class="col-md-3"><strong>{{ __('Number') }}:</strong> {{ $request->number }}</div>
        <div class="col-md-3"><strong>{{ __('Deposit number') }}:</strong> {{ $request->deposit_number }}</div>
        <div class="col-md-3">
          <strong>{{ __('Qima') }}:</strong>
          {{ $request->uploaded_on_qima ? __('Yes') : __('No') }}
          @if ($request->isQimaLocked())
            <span class="badge bg-warning">{{ __('Locked') }}</span>
          @endif
        </div>
        <div class="col-md-3"><strong>{{ __('Coordinator') }}:</strong> {{ $request->coordinator?->name ?? '—' }}</div>
        <div class="col-md-3"><strong>{{ __('Evaluator') }}:</strong> {{ $request->evaluator?->name ?? '—' }}</div>
        <div class="col-md-3">
          <strong>{{ __('Final amount') }}:</strong>
          {{ $finalAmount['amount'] !== null ? number_format((float) $finalAmount['amount'], 2) : '—' }}
          @if (!empty($finalAmount['is_manual']))
            <span class="badge bg-info">{{ __('Manual') }}</span>
          @endif
        </div>
      </div>

      <div class="d-flex flex-wrap gap-50 mb-1">
        @can('update', $request)
          <a href="{{ route('dashboard.valuation-requests.edit', $request) }}" class="btn btn-sm btn-outline-secondary">{{ __('Coordinator edit') }}</a>
          <a href="{{ route('dashboard.valuation-requests.edit-info', $request) }}" class="btn btn-sm btn-outline-secondary">{{ __('Evaluator edit') }}</a>
        @endcan
        <a href="{{ route('dashboard.valuation-requests.barcode', $request) }}" class="btn btn-sm btn-outline-secondary">{{ __('Barcode') }}</a>
        @can('downloadAttachments', $request)
          <a href="{{ route('dashboard.valuation-requests.attachments-zip', $request) }}" class="btn btn-sm btn-outline-secondary">{{ __('Download attachments ZIP') }}</a>
        @endcan
        @can('send', $request)
          <form method="post" action="{{ route('dashboard.valuation-requests.send', $request) }}">@csrf<button class="btn btn-sm btn-primary">{{ __('Send') }}</button></form>
          <form method="post" action="{{ route('dashboard.valuation-requests.under-evaluation', $request) }}">@csrf<button class="btn btn-sm btn-outline-primary">{{ __('Under evaluation') }}</button></form>
        @endcan
        @can('markEvaluated', $request)
          <form method="post" action="{{ route('dashboard.valuation-requests.mark-evaluated', $request) }}">@csrf<button class="btn btn-sm btn-success">{{ __('Mark evaluated') }}</button></form>
        @endcan
        @can('approveFinal', $request)
          <form method="post" action="{{ route('dashboard.valuation-requests.approve', $request) }}">@csrf<button class="btn btn-sm btn-success">{{ __('Approve') }}</button></form>
        @endcan
        @can('unapprove', $request)
          <form method="post" action="{{ route('dashboard.valuation-requests.unapprove', $request) }}">@csrf<button class="btn btn-sm btn-warning">{{ __('Unapprove') }}</button></form>
        @endcan
        @can('reject', $request)
          <form method="post" action="{{ route('dashboard.valuation-requests.reject', $request) }}">@csrf<button class="btn btn-sm btn-outline-warning">{{ __('Reject') }}</button></form>
        @endcan
        @can('cancel', $request)
          <form method="post" action="{{ route('dashboard.valuation-requests.cancel', $request) }}" onsubmit="return confirm(@json(__('Are you sure?')))">@csrf<button class="btn btn-sm btn-outline-danger">{{ __('Cancel valuation') }}</button></form>
        @endcan
        @can('duplicate', $request)
          <form method="post" action="{{ route('dashboard.valuation-requests.duplicate', $request) }}">@csrf<button class="btn btn-sm btn-outline-primary">{{ __('Duplicate') }}</button></form>
        @endcan
      </div>

      @if ($request->property)
        <hr>
        <h5>{{ __('Property') }}</h5>
        <p class="mb-25">{{ $request->property->customer_name }} / {{ $request->property->owner_name }}</p>
        <p class="mb-25">{{ $request->property->instrument_no }} — {{ $request->property->property_kind }} / {{ $request->property->property_type }}</p>
        @if ($request->property->location)
          <p class="mb-25 text-muted">
            {{ $request->property->location->city?->name_ar }}
            {{ $request->property->location->street }}
            ({{ $request->property->location->x_axis }}, {{ $request->property->location->y_axis }})
          </p>
        @endif

        @if ($request->property->pictures->isNotEmpty())
          <h6 class="mt-2">{{ __('Property pictures') }}</h6>
          <div class="d-flex flex-wrap gap-1">
            @foreach ($request->property->pictures as $picture)
              <div>
                <x-property-picture-thumb :picture="$picture" />
                @can('deleteAttachment', $request)
                  <form method="post" action="{{ route('dashboard.valuation-requests.pictures.destroy', [$request, $picture]) }}"
                        onsubmit="return confirm(@json(__('Are you sure?')))">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('Delete') }}</button>
                  </form>
                @endcan
              </div>
            @endforeach
          </div>
          @php $missingCount = $request->property->pictures->where('file_exists', false)->count(); @endphp
          @if ($missingCount > 0)
            <p class="text-muted small mb-0">{{ __('Missing pictures count', ['count' => $missingCount]) }}</p>
          @endif
        @endif
      @endif
    </div>
  </div>

  <div class="row match-height">
    @can('changeEvaluator', $request)
      <div class="col-md-6">
        <div class="card">
          <div class="card-header"><h5 class="card-title mb-0">{{ __('Change evaluator') }}</h5></div>
          <form method="post" action="{{ route('dashboard.valuation-requests.change-evaluator', $request) }}">
            @csrf
            <div class="card-body">
              <select name="evaluator_user_id" class="form-select" required>
                <option value="">{{ __('Select') }}</option>
                @foreach ($evaluators as $id => $name)
                  <option value="{{ $id }}" @selected($request->evaluator_user_id == $id)>{{ $name }}</option>
                @endforeach
              </select>
            </div>
            <div class="card-footer"><button class="btn btn-sm btn-primary">{{ __('Save') }}</button></div>
          </form>
        </div>
      </div>
    @endcan

    @can('changeCoordinator', $request)
      <div class="col-md-6">
        <div class="card">
          <div class="card-header"><h5 class="card-title mb-0">{{ __('Change coordinator') }}</h5></div>
          <form method="post" action="{{ route('dashboard.valuation-requests.change-coordinator', $request) }}">
            @csrf
            <div class="card-body">
              <select name="coordinator_user_id" class="form-select" required>
                <option value="">{{ __('Select') }}</option>
                @foreach ($coordinators as $id => $name)
                  <option value="{{ $id }}" @selected($request->coordinator_user_id == $id)>{{ $name }}</option>
                @endforeach
              </select>
            </div>
            <div class="card-footer"><button class="btn btn-sm btn-primary">{{ __('Save') }}</button></div>
          </form>
        </div>
      </div>
    @endcan

    @can('changePropertyType', $request)
      <div class="col-md-6">
        <div class="card">
          <div class="card-header"><h5 class="card-title mb-0">{{ __('Change property type') }}</h5></div>
          <form method="post" action="{{ route('dashboard.valuation-requests.change-property-type', $request) }}">
            @csrf
            <div class="card-body row g-1">
              <div class="col-md-6">
                <input type="text" name="property_kind" class="form-control" placeholder="{{ __('Property kind') }}"
                       value="{{ $request->property?->property_kind }}" required>
              </div>
              <div class="col-md-6">
                <input type="text" name="property_type" class="form-control" placeholder="{{ __('Property type') }}"
                       value="{{ $request->property?->property_type }}" required>
              </div>
            </div>
            <div class="card-footer"><button class="btn btn-sm btn-primary">{{ __('Save') }}</button></div>
          </form>
        </div>
      </div>
    @endcan

    @can('overrideAmount', $request)
      <div class="col-md-6">
        <div class="card">
          <div class="card-header"><h5 class="card-title mb-0">{{ __('Override manual amount') }}</h5></div>
          <form method="post" action="{{ route('dashboard.valuation-requests.override-amount', $request) }}">
            @csrf
            <div class="card-body">
              <input type="number" step="any" min="0" name="total_amount_manual" class="form-control"
                     value="{{ $manualAmount }}" placeholder="{{ __('Leave empty to clear') }}">
            </div>
            <div class="card-footer"><button class="btn btn-sm btn-primary">{{ __('Save') }}</button></div>
          </form>
        </div>
      </div>
    @endcan

    @can('manageFeeShares', $request)
      <div class="col-md-6">
        <div class="card">
          <div class="card-header"><h5 class="card-title mb-0">{{ __('Fee shares') }}</h5></div>
          <form method="post" action="{{ route('dashboard.valuation-requests.fee-shares', $request) }}">
            @csrf
            @method('PUT')
            <div class="card-body row g-1">
              <div class="col-4">
                <label class="form-label">{{ __('Coordinator share') }}</label>
                <input type="number" min="0" max="100" name="coordinator_share" class="form-control"
                       value="{{ $feeShare?->coordinator_share ?? 0 }}" required>
              </div>
              <div class="col-4">
                <label class="form-label">{{ __('Evaluator share') }}</label>
                <input type="number" min="0" max="100" name="evaluator_share" class="form-control"
                       value="{{ $feeShare?->evaluator_share ?? 0 }}" required>
              </div>
              <div class="col-4">
                <label class="form-label">{{ __('Manager share') }}</label>
                <input type="number" min="0" max="100" name="manager_share" class="form-control"
                       value="{{ $feeShare?->manager_share ?? 0 }}" required>
              </div>
            </div>
            <div class="card-footer"><button class="btn btn-sm btn-primary">{{ __('Save') }}</button></div>
          </form>
        </div>
      </div>
    @endcan

    @can('toggleQimaStatus', $request)
      <div class="col-md-6">
        <div class="card">
          <div class="card-header"><h5 class="card-title mb-0">{{ __('Qima status') }}</h5></div>
          <form method="post" action="{{ route('dashboard.valuation-requests.qima-toggle', $request) }}">
            @csrf
            <div class="card-body">
              <select name="uploaded_on_qima" class="form-select">
                <option value="0" @selected(! $request->uploaded_on_qima)>{{ __('Not uploaded') }}</option>
                <option value="1" @selected($request->uploaded_on_qima)>{{ __('Uploaded to Qima') }}</option>
              </select>
            </div>
            <div class="card-footer"><button class="btn btn-sm btn-primary">{{ __('Save') }}</button></div>
          </form>
        </div>
      </div>
    @endcan
  </div>

  @can('uploadOfficialReport', $request)
    <div class="card mt-1">
      <div class="card-header">
        <h4 class="card-title">{{ __('Official Qima report') }}</h4>
      </div>
      <div class="card-body">
        @if ($request->official_report_path)
          <p class="text-muted">{{ __('Current file') }}: {{ $request->official_report_path }}</p>
        @endif
        @if ($request->isQimaLocked())
          <div class="alert alert-warning mb-0">{{ __('This request is locked after official Qima submission.') }}</div>
        @else
          <form method="post" action="{{ route('dashboard.valuation-requests.official-report', $request) }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-1">
              <input type="file" name="official_report" class="form-control" required>
              @error('official_report') <div class="text-danger">{{ $message }}</div> @enderror
            </div>
            <button type="submit" class="btn btn-primary">{{ __('Upload and lock') }}</button>
          </form>
        @endif
      </div>
    </div>
  @endcan
</section>
@endsection
