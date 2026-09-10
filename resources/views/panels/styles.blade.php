<!-- BEGIN: Vendor CSS-->
@if ($configData['direction'] === 'rtl' && isset($configData['direction']))
  <link rel="stylesheet" href="{{ asset('vendors/css/vendors-rtl.min.css') }}" />
@else
  <link rel="stylesheet" href="{{ asset('vendors/css/vendors.min.css') }}" />
@endif
{{-- Select2 Styles --}}
<link rel="stylesheet" href="{{ asset('vendors/css/forms/select/select2.min.css') }}">
{{-- Datepickr Styles --}}
<link rel="stylesheet" href="{{ asset('vendors/css/pickers/pickadate/pickadate.css') }}">
<link rel="stylesheet" href="{{ asset('vendors/css/pickers/flatpickr/flatpickr.min.css') }}">
{{-- Form Numbers --}}
<link rel="stylesheet" href="{{ asset('vendors/css/forms/spinner/jquery.bootstrap-touchspin.css')}}">

{{-- Custom  File Input --}}
<link rel="stylesheet" href="{{ asset('vendors/css/jasny/jasny-bootstrap.min.css')}}">

@yield('vendor-style')
<!-- END: Vendor CSS-->

<!-- BEGIN: Theme CSS-->
@if ($configData['direction'] === 'rtl')
  <link rel="stylesheet" href="{{ asset('css-rtl/core.rtl.css') }}" />
  <link rel="stylesheet" href="{{ asset('css-rtl/base/themes/dark-layout.rtl.css') }}" />
  <link rel="stylesheet" href="{{ asset('css-rtl/base/themes/bordered-layout.rtl.css') }}" />
  <link rel="stylesheet" href="{{ asset('css-rtl/base/themes/semi-dark-layout.rtl.css') }}" />
@else
  <link rel="stylesheet" href="{{ asset('css/core.css') }}" />
  <link rel="stylesheet" href="{{ asset('css/base/themes/dark-layout.css') }}" />
  <link rel="stylesheet" href="{{ asset('css/base/themes/bordered-layout.css') }}" />
  <link rel="stylesheet" href="{{ asset('css/base/themes/semi-dark-layout.css') }}" />
@endif

<!-- BEGIN: Page CSS-->
@if ($configData['mainLayoutType'] === 'horizontal')
  <link rel="stylesheet" href="{{ asset($configData['direction'] === 'rtl' ? 'css-rtl/base/core/menu/menu-types/horizontal-menu.rtl.css' : 'css/base/core/menu/menu-types/horizontal-menu.css') }}" />
@else
  <link rel="stylesheet" href="{{ asset($configData['direction'] === 'rtl' ? 'css-rtl/base/core/menu/menu-types/vertical-menu.rtl.css' : 'css/base/core/menu/menu-types/vertical-menu.css') }}" />
@endif

{{-- DatePicker Custom Styles --}}
@if ($configData['direction'] === 'rtl')
  <link rel="stylesheet" href="{{ asset('css-rtl/base/plugins/forms/pickers/form-flat-pickr.rtl.css') }}">
  <link rel="stylesheet" href="{{ asset('css-rtl/base/plugins/forms/pickers/form-pickadate.rtl.css') }}">
@else
  <link rel="stylesheet" href="{{ asset('css/base/plugins/forms/pickers/form-flat-pickr.css') }}">
  <link rel="stylesheet" href="{{ asset('css/base/plugins/forms/pickers/form-pickadate.css') }}">
@endif

{{-- Page Styles --}}
@yield('page-style')

<!-- laravel style -->
@if ($configData['direction'] === 'rtl')
  <link rel="stylesheet" href="{{ asset('css-rtl/overrides.rtl.css') }}" />
@else
  <link rel="stylesheet" href="{{ asset('css/overrides.css') }}" />
@endif

<!-- BEGIN: Custom CSS-->
@if ($configData['direction'] === 'rtl')
  <link rel="stylesheet" href="{{ asset('css-rtl/style.rtl.css') }}" />
@else
  <link rel="stylesheet" href="{{ asset('css/style.css') }}" />
@endif
