<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    @vite(['resources/css/auth.css','resources/js/app.js'])
</head>
<body class="auth-page">
    <div class="auth-shell">
        @yield('content')
    </div>
</body>
</html>
