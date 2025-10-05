<!DOCTYPE html>
<html lang="en" dir="">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>{{config('app.name')}} | Admin dashboard</title>
    <link href="https://fonts.googleapis.com/css?family=Nunito:300,400,400i,600,700,800,900" rel="stylesheet" />
    <link href="{{ asset('css/themes/lite-purple.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/plugins/perfect-scrollbar.min.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="{{asset('css/plugins/datatables.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('src/output.css') }}">
</head>

<body class="text-left">

    @yield('content')
    <script src="{{ asset('js/plugins/jquery-3.3.1.min.js') }}"></script>
    <script src="{{ asset('js/plugins/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/plugins/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('js/scripts/script.min.js') }}"></script>
    <script src="{{ asset('js/scripts/sidebar.large.script.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables.min.js') }}"></script>
    <script src="{{ asset('js/scripts/datatables.script.min.js') }}"></script>
</body>

</html>