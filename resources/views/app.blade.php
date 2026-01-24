<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    dir="{{ ($page['props']['isRtl'] ?? false) ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="light only">
    <link rel="icon" type="image/png" href="/site-icon.png?v=1">
    <link rel="apple-touch-icon" href="/site-icon.png?v=1">
    <link rel="shortcut icon" href="/site-icon.png?v=1">
    <title inertia>{{ config('app.name', 'Servio') }}</title>
    @routes
    @vite(['resources/js/app.ts'])
    @inertiaHead
</head>

<body class="font-sans antialiased" style="background-color: #ffffff !important; color: #1f2937 !important;">
    @inertia
</body>

</html>