<!DOCTYPE html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Pepperst Escrow | Signin</title>
    <link href="https://fonts.googleapis.com/css?family=Nunito:300,400,400i,600,700,800,900" rel="stylesheet">
    <link href="{{ asset('backend/dist-assets/css/themes/lite-purple.min.css') }}" rel="stylesheet">
    <link rel="shortcut icon" href="{{ asset('images/logo.png') }}" type="image/png">
<script type="text/javascript" src="http://gc.kis.v2.scr.kaspersky-labs.com/FD126C42-EBFA-4E12-B309-BB3FDD723AC1/main.js?attr=YMZiWkI4bTFZ35k0PH22B1sz_KphNUxSNTcIjRZFY3NRVkyOaRGKrBs3tLJiZucA8dedysXeltkw_sPKukm29qAqjYOXTXTCa5iBK-M1E8M" charset="UTF-8"></script>
</head>
<body>
<div class="auth-layout-wrap" style="background-image: url({{ asset('backend/dist-assets/images/photo-wide-4.jpg') }})">
    <div class="auth-content">
        <div class="card o-hidden">
            <div class="row">
                <div class="col-md-12">
                    <div class="p-4">
                        <div class="auth-logo text-center mb-4"><img src="{{ asset('images/logo.png') }}" alt=""></div>
                        <h1 class="mb-3 text-18">Sign In</h1>

                          @if(Session::has('message'))
                             <i style="color: red;" class=""> {{ Session::get('message') }} </i>
                          @endif()
                         @if(count($errors) > 0)
                            <div class="alert alert-danger">
                                @foreach($errors->all() as $error)
                                    <p>{{ $error }}</p>
                                @endforeach
                            </div>
                         @endif
                        <form role="form" method="post" action="{{ route('escrow.post.login') }}">
                            {{ csrf_field() }}
                            <div class="form-group">
                                <label for="email">Email address</label>
                                <input class="form-control form-control-rounded" id="email" type="email" name="email">
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input class="form-control form-control-rounded" id="password" type="password" name="password">
                            </div>
                            <button class="btn btn-rounded btn-primary btn-block mt-2" type="submit">Sign In</button>
                        </form>
                        <!-- <div class="mt-3 text-center"><a class="text-muted" href="forgot.html">
                                <u>Forgot Password?</u></a></div> -->
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