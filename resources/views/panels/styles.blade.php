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
  @vite(['resources/scss/core.scss'])
  <link rel="stylesheet" href="{{ asset('css/base/themes/dark-layout.css') }}" />
  <link rel="stylesheet" href="{{ asset('css/base/themes/bordered-layout.css') }}" />
  <link rel="stylesheet" href="{{ asset('css/base/themes/semi-dark-layout.css') }}" />
@endif

@php $configData = Helper::applClasses(); @endphp

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
  @vite(['resources/scss/overrides.scss'])
@endif

<!-- BEGIN: Custom CSS-->
@if ($configData['direction'] === 'rtl')
  <link rel="stylesheet" href="{{ asset('css-rtl/style.rtl.css') }}" />
  <style>
      [dir='rtl'] .main-menu {
          right: 0 !important;
          left: auto !important;
      }
      [dir='rtl'] .vertical-layout.vertical-menu-modern.menu-expanded .app-content,
      [dir='rtl'] .vertical-layout.vertical-menu-modern.menu-expanded .footer {
          margin-left: 0 !important;
          margin-right: 260px !important;
      }
      [dir='rtl'] .vertical-layout.vertical-menu-modern.menu-collapsed .app-content,
      [dir='rtl'] .vertical-layout.vertical-menu-modern.menu-collapsed .footer {
          margin-left: 0 !important;
          margin-right: 80px !important;
      }
      [dir='rtl'] .header-navbar {
          right: 260px !important;
          left: 0 !important;
      }
      [dir='rtl'] .vertical-layout.vertical-menu-modern.menu-collapsed .header-navbar {
          right: 80px !important;
          left: 0 !important;
      }
  </style>
@else
  @vite(['resources/assets/scss/style.scss'])
@endif
