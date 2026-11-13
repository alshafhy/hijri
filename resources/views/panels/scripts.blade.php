<!-- BEGIN: Vendor JS-->
<script src="{{ asset('vendors/js/vendors.min.js') }}"></script>
<script src="{{ asset('js/core/feather-guard.js') }}"></script>
@include('panels.client-error-logger')
<!-- BEGIN Vendor JS-->
<!-- BEGIN: Page Vendor JS-->
<script src="{{asset('vendors/js/ui/jquery.sticky.js')}}"></script>
<!-- select2 Main scripts -->
<script src="{{ asset('vendors/js/forms/select/select2.full.min.js') }}"></script>

<!-- for datepicker Main scripts -->
<script src="{{ asset('vendors/js/pickers/pickadate/picker.js') }}"></script>
<script src="{{ asset('vendors/js/pickers/pickadate/picker.date.js') }}"></script>
<script src="{{ asset('vendors/js/pickers/pickadate/picker.time.js') }}"></script>
<script src="{{ asset('vendors/js/pickers/pickadate/legacy.js') }}"></script>
<script src="{{ asset('vendors/js/pickers/flatpickr/flatpickr.min.js') }}"></script>
{{-- Form Numbers --}}
<script src="{{ asset('vendors/js/forms/spinner/jquery.bootstrap-touchspin.js')}}"></script>
{{-- Custom File Input --}}
<script src="{{ asset('vendors/js/jasny/jasny-bootstrap.min.js')}}"></script>

@yield('vendor-script')
@stack('vendor-script')
<!-- END: Page Vendor JS-->
<!-- BEGIN: Theme JS-->
<script src="{{ asset('js/core/app-menu.js') }}"></script>
<script src="{{ asset('js/core/app.js') }}"></script>

<!-- custome scripts file for user -->
<script src="{{ asset('js/core/scripts.js') }}"></script>
<script src="{{ asset('js/core/today-clock.js') }}"></script>

@if($configData['blankPage'] === false)
    <script src="{{ asset('js/scripts/customizer.js') }}"></script>
@endif

<!-- select2 init script -->
<script src="{{ asset('js/scripts/forms/form-select2.js') }}"></script>

<!-- datepickers init script -->
<script src="https://npmcdn.com/flatpickr/dist/l10n/ar.js"></script>
<script src="{{ asset('js/scripts/forms/pickers/form-pickers.js') }}"></script>
{{-- Form Numbers --}}
<script src="{{ asset('js/scripts/forms/form-number-input.js')}}"></script>

<script>
    var BASE_URL = "{{ config('app.url') }}";
    var csrf_token = "{{ csrf_token() }}";
</script>

<script src="{{ asset('js/scripts/crud.js') }}"></script>

{{-- Select2 / flatpickr everywhere + JS validation instead of browser bubbles --}}
<script src="{{ asset('js/core/form-ui.js') }}" data-i18n="{{ json_encode([
    'This field is required.' => __('This field is required.'),
    'Please enter a valid email address.' => __('Please enter a valid email address.'),
    'Please enter a valid URL.' => __('Please enter a valid URL.'),
    'Please enter a valid number.' => __('Please enter a valid number.'),
    'Please enter at least {n} characters.' => __('Please enter at least {n} characters.'),
    'Please enter no more than {n} characters.' => __('Please enter no more than {n} characters.'),
    'Value must be at least {n}.' => __('Value must be at least {n}.'),
    'Value must be at most {n}.' => __('Value must be at most {n}.'),
    'Invalid format.' => __('Invalid format.'),
    'Please enter a valid value.' => __('Please enter a valid value.'),
    'No results found' => __('No results found'),
    'Searching…' => __('Searching…'),
], JSON_UNESCAPED_UNICODE) }}"></script>
<!-- END: Theme JS-->
<!-- BEGIN: Page JS-->
@yield('page-script')
@stack('page-script')
<!-- END: Page JS-->