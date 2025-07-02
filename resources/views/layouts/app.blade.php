<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Expence Tracker</title>
    <link rel="stylesheet" href="{{ asset('asset/bootstrap/boostrap.min.css') }}">
</head>
<body>
    @yield('app-content')
    <script src="{{ asset("asset/js/jquery.min.js") }}"></script>
    <script src="{{ asset('asset/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('asset/js/sweetalert.min.js') }}"></script>
    @stack('custom-scripts')
</body>
</html>
