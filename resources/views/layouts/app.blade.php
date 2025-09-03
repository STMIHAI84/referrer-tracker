<!doctype html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Admin') - Referral Tracker</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <!-- <head> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</head>
<body class="page">
<header class="container header">
    <h1 class="brand">@yield('page-title', 'Admin')</h1>
    <nav class="header-actions">
        @yield('header-buttons')
    </nav>
</header>

<main class="container">
    @yield('content')
</main>
</body>
</html>
