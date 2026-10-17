<!DOCTYPE html>
<html lang="{{ $locale ?? 'ar' }}" dir="{{ $direction ?? 'rtl' }}">
<head>
    <meta charset="utf-8">
    <title>{{ $report_title }}</title>
</head>
<body>
@yield('content')
</body>
</html>
