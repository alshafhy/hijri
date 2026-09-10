<!-- BEGIN: Footer-->
<footer
  class="footer footer-light {{($configData['footerType'] === 'footer-hidden') ? 'd-none' : ''}} {{$configData['footerType']}}">
  <p class="clearfix mb-0">
    <span class="float-md-start d-block d-md-inline-block mt-25">
      {{ __('All rights reserved.') }} &copy; {{ now()->year }}
      <a class="ms-25" href="{{ config('company.url') }}" target="_blank" rel="noopener">
        {{ app()->getLocale() === 'ar' ? config('company.name_ar') : config('company.name') }}
      </a>
    </span>
    <span class="float-md-end d-none d-md-block"><x-today-date /></span>
  </p>
</footer>
<button class="btn btn-primary btn-icon scroll-top" type="button"><i data-feather="arrow-up"></i></button>
<!-- END: Footer-->
