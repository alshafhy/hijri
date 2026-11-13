<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <title>{{ __('Invoice') }} — {{ $contractor->name }}</title>
</head>
<body>
<div class="doc-invoice">
  <h1>{{ __('Invoice') }}</h1>
  <div class="head">
    {{ $company?->name ?: config('app.name') }} — {{ __('Issue date') }}: {{ $issuedAt->format('Y/m/d') }}
  </div>

  <table class="invoice">
    <thead>
      <tr><th>{{ __('Client') }}</th><th>{{ __('Email') }}</th><th>{{ __('Phone') }}</th><th>{{ __('Fees per valuation') }}</th><th>{{ __('Contract period') }}</th></tr>
    </thead>
    <tbody>
      <tr>
        <td>{{ $contractor->name }}</td>
        <td>{{ $contractor->email ?: '—' }}</td>
        <td class="num">{{ $contractor->phone_number ?: '—' }}</td>
        <td class="num">{{ number_format($fees, 2) }}</td>
        <td class="num">{{ $contractor->date_from?->format('Y/m/d') ?? '—' }} — {{ $contractor->date_to?->format('Y/m/d') ?? '—' }}</td>
      </tr>
    </tbody>
  </table>

  <table class="invoice">
    <thead>
      <tr><th>#</th><th>{{ __('Reference') }}</th><th>{{ __('City') }}</th><th>{{ __('Neighborhood') }}</th><th>{{ __('Plan number') }}</th><th>{{ __('Fees') }}</th><th>{{ __('Status') }}</th></tr>
    </thead>
    <tbody>
      @forelse ($contracts as $contract)
        <tr>
          <td class="num">{{ $loop->iteration }}</td>
          <td class="num">{{ $contract->valuationRequest?->reference ?? '—' }}</td>
          <td>{{ $contract->valuationRequest?->property?->location?->city?->name_ar ?? '—' }}</td>
          <td>{{ $contract->valuationRequest?->property?->location?->neighborhood?->name_ar ?? '—' }}</td>
          <td class="num">{{ $contract->valuationRequest?->property?->location?->sketch_no ?: '—' }}</td>
          <td class="num">{{ number_format($fees, 2) }}</td>
          <td class="num">{{ $contract->isPaid() ? __('Paid') : __('Unpaid') }}</td>
        </tr>
      @empty
        <tr><td colspan="7" class="num">{{ __('No records found') }}</td></tr>
      @endforelse
    </tbody>
    <tfoot>
      <tr><td colspan="5">{{ __('Total') }}</td><td class="num" colspan="2">{{ number_format($totals['all'], 2) }} {{ __('SAR') }}</td></tr>
      <tr><td colspan="5">{{ __('Paid') }}</td><td class="num" colspan="2">{{ number_format($totals['paid'], 2) }} {{ __('SAR') }}</td></tr>
      <tr><td colspan="5">{{ __('Amount due') }}</td><td class="num" colspan="2">{{ number_format($totals['unpaid'], 2) }} {{ __('SAR') }}</td></tr>
    </tfoot>
  </table>
</div>
</body>
</html>
