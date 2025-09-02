<!doctype html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <title>@yield('title') - Referral Tracker</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<div class="container">
    <div class="header">
        <h1>@yield('page-title')</h1>
        <div>
            @yield('header-buttons')
        </div>
    </div>

    @yield('content')
</div>
</body>
</html>
