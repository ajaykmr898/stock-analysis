<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Basic Laravel website</title>
        {{-- css --}}
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/sass/app.css') }}">
        <link rel="stylesheet" type="text/css"
              href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <script src="https://code.jquery.com/jquery-3.5.1.min.js" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
        <script src="{{ asset('assets/js/app.js') }}"></script>
    </head>
    <body>
    @include('includes.navbar')

    <div class="container">
        <div class="row">
            <div class="col-lg-8 col-md-8">
                @yield('content')
            </div>
        </div>
    </div>
    <footer id="footer" class="text-center">
        <p>copyright 2020 &copy; Developed by Kumar</p>
    </footer>
    </body>
</html>
