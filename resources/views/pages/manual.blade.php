@extends('layouts/contentLayoutMaster')

@section('title', __('User manual'))

@section('content')
@php
  $steps = [
      [__('Add your team'), [
          __('Open Users and press "Invite staff member".'),
          __('Choose the job (coordinator or evaluator) and enter the name and email.'),
          __('The member receives an activation link by email and sets a password.'),
      ]],
      [__('Add contracts and clients'), [
          __('Open Contractors or Clients and add a new record.'),
          __('Complete the data: fees, contract period, location and contacts.'),
          __('Activate the record so it can be linked to valuation requests.'),
      ]],
      [__('Create a valuation request'), [
          __('Press "New valuation" and choose a contractor or a client offer.'),
          __('Fill the initial data, the detailed data and pin the location on the map.'),
          __('Send the request to the evaluator, who enters the valuation data and marks it evaluated.'),
          __('The manager (or a delegated member) gives the final approval and exports the report.'),
      ]],
      [__('Turnaround time settings'), [
          __('Open Company profile and set the new request and under evaluation hours.'),
          __('Details buttons in the lists turn red for every overdue valuation.'),
      ]],
      [__('Fee shares'), [
          __('Set the default coordinator, evaluator and manager shares in Company profile.'),
          __('To change the shares of a single valuation open its details and use "Fee shares".'),
      ]],
  ];
@endphp
<section id="user-manual">
  <ul class="nav nav-pills mb-2" role="tablist">
    <li class="nav-item"><button type="button" class="nav-link active" data-bs-toggle="pill" data-bs-target="#manual-about">{{ __('About the platform') }}</button></li>
    <li class="nav-item"><button type="button" class="nav-link" data-bs-toggle="pill" data-bs-target="#manual-how">{{ __('How the platform works') }}</button></li>
  </ul>
  <div class="tab-content">
    <div class="tab-pane fade show active" id="manual-about">
      <div class="card"><div class="card-body">
        <p>{{ __('Muqayem helps evaluators certified by the Saudi Authority for Accredited Valuers (Taqeem) determine the fair market value of every kind of real estate: raw land, developed land, residential, commercial and industrial products.') }}</p>
        <p>{{ __('The platform automates the valuation procedures to reduce errors and multiply productivity, and gives management one screen to supervise, assign and approve requests.') }}</p>
        <p class="mb-0">{{ __('It keeps all previous valuations, clients and invoices so teams can follow their performance and improve continuously.') }}</p>
      </div></div>
    </div>
    <div class="tab-pane fade" id="manual-how">
      @foreach ($steps as $i => [$title, $items])
        <div class="card">
          <div class="card-header"><h5 class="card-title mb-0">{{ $i + 1 }}. {{ $title }}</h5></div>
          <div class="card-body"><ol class="mb-0">@foreach ($items as $item)<li>{{ $item }}</li>@endforeach</ol></div>
        </div>
      @endforeach
    </div>
  </div>
</section>
@endsection
