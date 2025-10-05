<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
</head>

<body style="background-color: #F9FAFB; height: 100%; width: 100%; padding: 2%; font-family: sans-serif;">
    <header style="width: 90%; background-color: #ffffff; display: flex; padding: 5%;">
        <div>
            <!-- {{env("APP_NAME")}} logo -->
            <a href="{{ cc('frontend_base_url') }}" style="text-decoration: none;">
                <img src="{{ asset('img/logo.png') }}" alt="logo" style="height: 32px; width: 70px;">
            </a>
        </div>
        <div style="display: flex; justify-content: space-between; margin-left: auto;">
            <!-- login link -->
            <a href="{{ cc('login_url') }}" style="text-decoration: none; color: #000000;">Log in</a>
            &emsp;
            <!-- social media link -->
            <a href="{{ cc('twitter') }}" style="text-decoration: none; color: #000000;">
                <img src="{{ asset('img/twitter.png') }}" alt="twitter" style="height: 20px; width: 20px;">
            </a>
            &emsp;
            {{-- <span style="margin-right: 20px">
                <a href="#" style="text-decoration: none; color: #000000;">
                    <img src="{{ asset('img/facebook.png') }}" alt="facebook" style="height: 20px; width: 20px;">
                </a>
            </span> --}}
            <a href="{{ cc('instagram') }}" style="text-decoration: none; color: #000000;">
                <img src="{{ asset('img/instagram.png') }}" alt="instagram" style="height: 20px; width: 20px;">
            </a>
        </div>
    </header>
    @yield('content')
    <footer style="padding: 5px; background-color: #FFFFFF; padding: 2%; width: 90%;">
        <div>
            <!-- user email address, unsubscribe from email link and manage email preferences link -->
            @yield('footer')

            <p style="font-size: 14px; font-weight: 400; color: #667085;">
                © {{ date("Y") }} {{env("APP_NAME")}}
            </p>
        </div>
        <div style="width: 100%; display: flex; margin-top: 50px;">
            <div>
                <!-- {{env("APP_NAME")}} logo -->
                <a href="{{ cc('frontend_base_url') }}" style="text-decoration: none;">
                    <img src="{{ asset('img/logo.png') }}" alt="logo" style="height: 32px; width: 70px;">
                </a>
            </div>
            <div style="display: flex; justify-content: space-between; margin-left: auto;">
                <!-- social media link -->
                <a href="{{ cc('twitter') }}" style="text-decoration: none; color: #000000;">
                    <img src="{{ asset('img/twitter-grey.png') }}" alt="twitter" style="height: 20px; width: 20px;">
                </a>
                &emsp;
                {{-- <span style="margin-inline: 20px;">
                    <a href="#" style="text-decoration: none; color: #000000;">
                        <img src="{{ asset('img/facebook-grey.png') }}" alt="facebook"
                            style="height: 20px; width: 20px;">
                    </a>
                </span> --}}
                <a href="{{ cc('instagram') }}" style="text-decoration: none; color: #000000;">
                    <img src="{{ asset('img/instagram-grey.png') }}" alt="instagram"
                        style="height: 20px; width: 20px;">
                </a>
            </div>
        </div>
    </footer>
</body>

</html>
