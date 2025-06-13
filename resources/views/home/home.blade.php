<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('ui.app_name') }}</title>
</head>
<body>
<h1>{{ __('ui.welcome_message') }}, Laravel!</h1>
<p>{{ __('ui.home_description', ['name' => config('app.name')]) }}</p>
</body>
</html>