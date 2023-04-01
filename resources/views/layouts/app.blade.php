<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>
        @yield('title')
    </title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">

    <style>
        body {
            font-family: 'Nunito';
        }
    </style>
</head>

<body>

<nav class="navbar navbar-light bg-light">
    <div class="container">

        @yield('navbarlinks')

    </div>
</nav>

<div class="container">

    @if(session()->has('success'))
        <div class="alert alert-success mt-3" role="alert">

            {{ session()->get('success') }}

        </div>
    @endif

    @if(session()->has('failure'))
        <div class="alert alert-danger mt-3" role="alert">

            {{ session()->get('failure') }}

        </div>
    @endif

    @yield('content')

</div>

</body>

</html>