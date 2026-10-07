@extends('layouts/contentLayoutMaster')

@section('title', __('Releases'))

@section('content')
@php
  $summary = [
      [__('Reports development'), __('Page splitting, maps and custom tables in the report.'), __('More organised and professional reports for clients.')],
      [__('Accuracy and calculations'), __('Decimal numbers, building ratios and the forced-sale discount.'), __('Accurate financial results that comply with the standards.')],
      [__('New additions'), __('Valuation standards, valuation approaches and methods, and the plantations item.'), __('The platform now covers more complex property cases.')],
      [__('System improvements'), __('Performance, company data updates and interface changes.'), __('A faster and smoother experience.')],
  ];
@endphp
<section id="system-releases">
  <div class="card">
    <div class="card-header"><h4 class="card-title mb-0">{{ __('Summary of the latest updates (changelog)') }}</h4></div>
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr>
            <th>{{ __('Category') }}</th>
            <th>{{ __('Highlights') }}</th>
            <th>{{ __('User benefit') }}</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($summary as [$category, $highlights, $benefit])
            <tr>
              <td class="fw-bolder">{{ $category }}</td>
              <td>{{ $highlights }}</td>
              <td class="text-primary">{{ $benefit }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      @if ($releases->isEmpty())
        <p class="text-muted mb-0">{{ __('No releases yet.') }}</p>
      @else
        <ul class="timeline ms-50 mb-0">
          @foreach ($releases as $release)
            <li class="timeline-item">
              <span class="timeline-point {{ $loop->first ? 'timeline-point-success' : 'timeline-point-primary' }} timeline-point-indicator"></span>
              <div class="timeline-event">
                <div class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                  <h6 class="mb-50">
                    {{ __('Version :version', ['version' => $release->version_number]) }}
                    @if ($loop->first)<span class="badge rounded-pill bg-light-success ms-50">{{ __('Current version') }}</span>@endif
                  </h6>
                  <span class="timeline-event-time">{{ $release->release_date?->format('d-m-Y') }}</span>
                </div>
                <ul class="mb-0 ps-1">
                  @foreach ($release->features as $feature)
                    <li>{{ $feature->title }}</li>
                  @endforeach
                </ul>
              </div>
            </li>
          @endforeach
        </ul>
      @endif
    </div>
  </div>
</section>
@endsection
