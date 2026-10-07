<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <title>{{ __('Invoice') }} — {{ $contractor->name }}</title>
  <style>
    @page { margin: 28px 34px; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; direction: rtl; text-align: right; color: #1a1a1a; }
    h1 { font-size: 18px; text-align: center; color: #1f3864; margin: 0; }
    .head { text-align: center; color: #555; margin: 4px 0 16px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
    th, td { border: 1px solid #d0d5dd; padding: 6px 8px; }
    th { background: #F79C20; color: #fff; text-align: center; }
    td.num { text-align: center; }
    tfoot td { font-weight: bold; background: #f5f5f5; }
  </style>
</head>
<body>
  <h1>{{ __('Invoice') }}</h1>
  <div class="head">
    {{ $company?->name ?: config('app.name') }} — {{ __('Issue date') }}: {{ $issuedAt->format('Y/m/d') }}
  </div>

  <table>
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

  <table>
    <thead>
      <tr><th>#</th><th>{{ __('Reference') }}</th><th>{{ __('City') }}</th><th>{{ __('Neighborhood') }}</th><th>{{ __('Plan number') }}</th><th>{{ __('Fees') }}</th><th>{{ __('Status') }}</th></tr>
    </thead>
    <tbody>
      @forelse ($contracts as $contract)
        @php($location = $contract->valuationRequest?->property?->location)
        <tr>
          <td class="num">{{ $loop->iteration }}</td>
          <td class="num">{{ $contract->valuationRequest?->reference ?? '—' }}</td>
          <td>{{ $location?->city?->name_ar ?? '—' }}</td>
          <td>{{ $location?->neighborhood?->name_ar ?? '—' }}</td>
          <td class="num">{{ $location?->sketch_no ?: '—' }}</td>
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
</body>
</html>
