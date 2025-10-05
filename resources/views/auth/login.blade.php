<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login | {{ config('app.name') }}</title>
    <link rel="icon" href="{{asset('img/logo.png')}}" />
    <link href="https://fonts.googleapis.com/css?family=Nunito:300,400,400i,600,700,800,900" rel="stylesheet">
    <link href="{{ asset('css/themes/lite-purple.min.css') }}" rel="stylesheet">

</head>
<body style="background-color: #10182a">
    <div class="auth-layout-wrap">
        <div class="auth-content">
            <div class=" text-center mb-4"><img
                    src="{{asset('img/logo.png')}}" alt="logo"></div>

            <div class="card o-hidden">
                <div class="row">
                    <div class="col-md-12">
                        <div class="p-4">
                            <form class="p-4" method="post" action="{{ route('login.post') }}">
                                @include('flash')
                                @csrf
                                <div class="form-group mt-2">
                                    <label for="email" class="mb-0">Email address</label>
                                    <input value="{{ old('email') }}" class="form-control" name="email" id="email"
                                        type="email">
                                </div>
                                <div class="form-group">
                                    <label for="password" class="mb-0">Password</label>
                                    <input name="password" class="form-control" required id="password"
                                        type="password">
                                </div>
                                <button class="btn btn-primary btn-block mt-3" type="submit">Login</button>
                            </form>
                        </div>
                    </div>
                    <!-- <div class="col-md-6 text-center" style="background-size: cover;background-image: url(../../dist-assets/images/photo-long-3.jpg)">
                    <div class="pr-3 auth-right"><a class="btn btn-rounded btn-outline-primary btn-outline-email btn-block btn-icon-text" href="signup.html"><i class="i-Mail-with-At-Sign"></i> Sign up with Email</a><a class="btn btn-rounded btn-outline-google btn-block btn-icon-text"><i class="i-Google-Plus"></i> Sign up with Google</a><a class="btn btn-rounded btn-block btn-icon-text btn-outline-facebook"><i class="i-Facebook-2"></i> Sign up with Facebook</a></div>
                </div> -->
                </div>
            </div>
        </div>
    </div>

</body>
