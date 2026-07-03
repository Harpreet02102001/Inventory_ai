<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Mini Inventory System') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body style="background-color: #f1f5f9; min-height: 100vh; display: flex; align-items: center; justify-content: center;">

    {{--
        Guest Layout — used for login, forgot-password, reset-password pages.

        IMPORTANT: Must use {!! $slot !!} not {{ $slot }}.
    {{ }} escapes HTML entities — turning <form> into &lt;form&gt; which
        renders as literal text. {!! !!} outputs raw HTML safely here because
        the slot content comes from our own trusted Blade views, not user input.
        --}}
        {!! $slot !!}

</body>

</html>