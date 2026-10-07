@php
  use App\Support\Valuation\ValuationToolbar;
  $can = ValuationToolbar::for(auth()->user(), $request);
  $cancelled = ValuationToolbar::stage($request) === ValuationToolbar::STAGE_CANCELLED;
  $sendLabel = $request->evaluator_user_id ? __('Send - under evaluation') : __('Send - valuation request');
  $more = collect([
      'feeShares' => ['modal' => 'sharesModal', 'label' => __('Fee shares')],
      'changeEvaluator' => ['modal' => 'changeEvaluatorModal', 'label' => __('Change evaluator')],
      'changePropertyType' => ['modal' => 'changePropertyTypeModal', 'label' => __('Change property type')],
      'changeCoordinator' => ['modal' => 'changeCoordinatorModal', 'label' => __('Change coordinator')],
      'manualAmount' => ['modal' => 'manualAmountModal', 'label' => __('Change valuation amount manually')],
      'qimaStatus' => ['modal' => 'qimaStatusModal', 'label' => __('Qima status')],
  ])->filter(fn ($item, $action) => $can[$action]);
  $zip = $can['downloadAttachments'] && $request->attachments->isNotEmpty();
@endphp

<div class="d-flex flex-wrap gap-50 align-items-center" id="valuation-toolbar">
  @if ($cancelled)
    <button type="button" class="btn btn-sm btn-secondary" disabled>{{ __('This valuation has been cancelled') }}</button>
  @else
    @if ($can['editInitialData'] || $can['editEvaluatorData'])
      <div class="btn-group">
        <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">{{ __('Edit') }}</button>
        <ul class="dropdown-menu">
          @if ($can['editInitialData'])
            <li><a class="dropdown-item" href="{{ route('dashboard.valuation-requests.edit', $request) }}">{{ __('Coordinator data') }}</a></li>
          @endif
          @if ($can['editEvaluatorData'])
            <li><a class="dropdown-item" href="{{ route('dashboard.valuation-requests.edit-info', $request) }}">{{ __('Evaluator data') }}</a></li>
          @endif
        </ul>
      </div>
    @endif

    @if ($can['send'])
      <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#sendModal">{{ $sendLabel }}</button>
    @endif
    @if ($can['takeOver'])
      <form method="post" action="{{ route('dashboard.valuation-requests.under-evaluation', $request) }}">@csrf
        <button class="btn btn-sm btn-outline-primary">{{ __('Take it for evaluation') }}</button>
      </form>
    @endif
    @if ($can['markEvaluated'])
      <form method="post" action="{{ route('dashboard.valuation-requests.mark-evaluated', $request) }}" onsubmit="return confirm(@json(__('Are you sure?')))">@csrf
        <button class="btn btn-sm btn-success">{{ __('Mark evaluated') }}</button>
      </form>
    @endif
    @if ($can['approve'])
      <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#approveModal">{{ __('Final approval') }}</button>
    @endif
    @if ($can['reject'])
      <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#rejectModal">{{ __('Return to evaluator') }}</button>
    @endif
    @if ($can['unapprove'])
      <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#unapproveModal">{{ __('Return before approval') }}</button>
    @endif

    @if ($can['export'])
      <div class="btn-group">
        <button type="button" class="btn btn-sm btn-outline-danger dropdown-toggle" data-bs-toggle="dropdown">{{ __('Export') }}</button>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="{{ route('dashboard.valuation-requests.exports.queue', [$request, 'enforcement']) }}">{{ __('Enforcement Center report') }}</a></li>
          <li><a class="dropdown-item" href="{{ route('dashboard.valuation-requests.exports.queue', [$request, 'full']) }}">{{ __('Full report') }}</a></li>
          <li><a class="dropdown-item" href="{{ route('dashboard.valuation-requests.exports.queue', [$request, 'full-draft']) }}">{{ __('Full report draft') }}</a></li>
        </ul>
      </div>
    @endif

    @if ($more->isNotEmpty() || $zip)
      <div class="btn-group">
        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">{{ __('More') }}</button>
        <ul class="dropdown-menu">
          @foreach ($more as $item)
            <li><button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#{{ $item['modal'] }}">{{ $item['label'] }}</button></li>
          @endforeach
          @if ($zip)
            <li><a class="dropdown-item" href="{{ route('dashboard.valuation-requests.attachments-zip', $request) }}">{{ __('Download attachments') }}</a></li>
          @endif
        </ul>
      </div>
    @endif

    @if ($request->uploaded_on_qima)
      <button type="button" class="btn btn-sm btn-outline-success" disabled>{{ __('Uploaded to Qima') }}</button>
    @elseif ($can['officialReport'])
      <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#officialReportModal">{{ __('Qima - change status') }}</button>
    @endif

    @if ($can['duplicate'])
      <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#duplicateModal">{{ __('Duplicate report') }}</button>
    @endif
    @if ($can['cancel'])
      <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">{{ __('Cancel valuation') }}</button>
    @endif
  @endif

  <a href="{{ route('dashboard.valuation-requests.barcode', $request) }}" class="btn btn-sm btn-flat-secondary">{{ __('Barcode') }}</a>
</div>
