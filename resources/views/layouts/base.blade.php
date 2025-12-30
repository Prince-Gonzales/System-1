<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'App' }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @php
        $assets = $assets ?? ['resources/js/app.js'];
    @endphp
    @vite($assets)
    @stack('head')
</head>
<body>
    @yield('content')
    @stack('scripts')
</body>
</html>

