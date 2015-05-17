<!doctype html>
<html class="no-js" lang="">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <title>Dropcogs</title>
  <meta name="description" content="">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="apple-touch-icon" href="apple-touch-icon.png">

  <link rel="stylesheet" type="text/css" href="{{ asset(elixir('css/base.css')) }}">

  <script type="text/javascript" src="{{ asset('js/header.bundle.js') }}"></script>
</head>
<body>

  @yield('content')

  <script type="text/javascript" src="{{ asset('js/libs.bundle.js') }}"></script>
</body>
</html>