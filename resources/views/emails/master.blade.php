<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
</head>

<body style="margin:0; padding:0; background:#0E0E0E; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background:#0E0E0E;">
        <tr>
            <td align="center" style="padding: 32px 16px;">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="600" style="width:600px; max-width:100%; background:#1A1A1A; color:#EAEAEA; border-radius:8px; overflow:hidden;">
                    <tr>
                        <td align="center" style="padding: 24px 24px 16px 24px; border-top: 4px solid #C8A94D;">
                            <a href="{{ cc('frontend_base_url') }}" style="text-decoration:none;">
                                <img src="{{ asset('img/logo.png') }}" alt="{{ env('APP_NAME') }}" style="display:block; height:40px;">
                            </a>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 0 24px 24px 24px;">
    @yield('content')
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 0 24px 24px 24px;">
            @yield('footer')
                            <p style="margin:16px 0 0 0; font-size:12px; line-height:18px; color:#9CA3AF;">© {{ date('Y') }} {{ env('APP_NAME') }}. All rights reserved.</p>
                        </td>
                    </tr>
                </table>

                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="600" style="width:600px; max-width:100%; margin-top:16px;">
                    <tr>
                        <td align="center" style="padding: 8px 24px;">
                            <a href="{{ cc('help_center') }}" style="color:#EAEAEA; text-decoration:none; font-size:12px;">Help Center</a>
                            <span style="color:#4B5563; font-size:12px;"> • </span>
                            <a href="{{ cc('frontend_base_url') }}" style="color:#EAEAEA; text-decoration:none; font-size:12px;">Website</a>
                            <span style="color:#4B5563; font-size:12px;"> • </span>
                            <a href="{{ cc('faqs') }}" style="color:#EAEAEA; text-decoration:none; font-size:12px;">FAQs</a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
