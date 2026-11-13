@extends('layouts/fullLayoutMaster')

@section('title', __('Muqayem platform'))

@section('page-style')
  <style>
    .landing { max-width: 1140px; margin: 0 auto; padding: 0 16px; }
    .landing-nav { display: flex; justify-content: space-between; align-items: center; padding: 18px 0; }
    .landing-hero { padding: 72px 0 56px; text-align: center; }
    .landing-hero h1 { font-size: 2.4rem; font-weight: 700; }
    .landing-hero p { font-size: 1.15rem; color: #6e6b7b; max-width: 640px; margin: 12px auto 28px; }
    .landing section { padding: 48px 0; }
    .feature-card { height: 100%; }
    .feature-card h5 { color: #7367f0; }
  </style>
@endsection

@section('content')
@php
  $features = [
      [__('Automated valuation workflow'), __('Every request follows a paperless documentary cycle from receiving the order to the final approval by a certified evaluator.')],
      [__('Client and contract management'), __('Track contractors, clients, price offers and their valuations in one place, with invoices and payment status.')],
      [__('Complete archive'), __('Search previous valuations by city, client, deed number or status at any time.')],
      [__('Organised teamwork'), __('Assign coordinators and evaluators, delegate final approval and watch overdue requests turn red.')],
      [__('Professional reports'), __('Issue Arabic and English valuation reports, drafts and Enforcement Center reports as PDF.')],
      [__('Time saving'), __('Turnaround settings and dashboards show where valuations are delayed so they can be fixed quickly.')],
  ];
@endphp
<div class="landing">
  <nav class="landing-nav">
    <h3 class="mb-0 text-primary fw-bolder">{{ __('Muqayem platform') }}</h3>
    <div class="d-flex gap-1">
      <a href="#features" class="btn btn-flat-secondary">{{ __('Features') }}</a>
      <a href="#contact" class="btn btn-flat-secondary">{{ __('Contact us') }}</a>
      @auth
        <a href="{{ route('home') }}" class="btn btn-primary">{{ __('Dashboard') }}</a>
      @else
        <a href="{{ route('login') }}" class="btn btn-primary">{{ __('Login') }}</a>
      @endauth
    </div>
  </nav>

  <header class="landing-hero">
    <h1>{{ __('Welcome to the Muqayem platform') }}</h1>
    <p>{{ __('An integrated portal to manage real-estate valuation requests professionally.') }}</p>
    <a href="#contact" class="btn btn-lg btn-primary">{{ __('Contact us') }}</a>
  </header>

  <section id="features">
    <h2 class="text-center mb-2">{{ __('What makes us different') }}</h2>
    <div class="row g-2">
      @foreach ($features as [$title, $text])
        <div class="col-md-4">
          <div class="card feature-card"><div class="card-body"><h5>{{ $title }}</h5><p class="mb-0">{{ $text }}</p></div></div>
        </div>
      @endforeach
    </div>
  </section>

  <section id="contact">
    <div class="row justify-content-center">
      <div class="col-lg-7">
        <div class="card">
          <div class="card-body">
            <h2 class="mb-1">{{ __('Contact us') }}</h2>
            @if (session('contact_sent'))
              <div class="alert alert-success p-1" id="contact-sent">{{ __('Thank you! We will contact you soon.') }}</div>
            @endif
            <form method="POST" action="{{ route('landing.contact') }}" class="row g-1">
              @csrf
              <div class="d-none" aria-hidden="true"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
              @foreach (['name' => [__('Name'), 'text'], 'email' => [__('Email'), 'email'], 'company' => [__('Company'), 'text'], 'phone_number' => [__('Phone'), 'text']] as $field => [$label, $type])
                <div class="col-md-6">
                  <label class="form-label" for="contact-{{ $field }}">{{ $label }} <span class="text-danger">*</span></label>
                  <input type="{{ $type }}" name="{{ $field }}" id="contact-{{ $field }}" value="{{ old($field) }}" class="form-control @error($field) is-invalid @enderror" required>
                  @error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              @endforeach
              <div class="col-12">
                <label class="form-label" for="contact-message">{{ __('Message') }} <span class="text-danger">*</span></label>
                <textarea name="message" id="contact-message" rows="4" maxlength="3000" class="form-control @error('message') is-invalid @enderror" required>{{ old('message') }}</textarea>
                @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-12"><button class="btn btn-primary">{{ __('Send') }}</button></div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>

  <footer class="text-center text-muted py-2">© {{ now()->year }} {{ config('app.name') }}</footer>
</div>
@endsection
