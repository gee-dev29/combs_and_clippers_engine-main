@extends('emails.master')
@section('content')
    <section style="width: 90%; background-color: #ffffff; padding: 2%;">
        <div style="margin-top: 50px; width: 100%; flex-direction: column;">
            <p style="font-size: 20px; font-weight: 500;"> Hello {{ $user->name }},</p>
            <p style="font-size: 16px; font-weight: 400; color: #344054; margin-top: 30px; line-height:2rem;">
                Use the following one-time password (OTP) to sign in to your {{env("APP_NAME")}} account. <br>
                This OTP will be valid for 10 minutes till {{ $user->formatted_login_otp_expires_at() }}.
            </p>
            <table width="100%" cellpadding="30">
                <thead>
                    <tr class="gry-color" style="background: #eceff4;">
                        <th colspan="2" style="padding: 0.5rem; color:#7F56D9">One Time Password (OTP)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="gry-color" style="background: #eceff4;">
                        <td colspan="2" style="text-align: center; font-size: 24px; letter-spacing: 5px; font-style: bold;">{{ $user->login_otp }}</td>
                    </tr>
                </tbody>
            </table>
            <p style="font-size: 16px; font-weight: 400; color: #344054; margin-top: 30px; line-height:2rem;"> With love &#128156;
                from <br> The {{env("APP_NAME")}} team.</p>
        </div>
    </section>
@endsection

@section('footer')
    <p style="font-size: 14px; font-weight: 400; color: #667085;">
        This email was sent to <a href="mailto:{{ $user->email }}"
            style="text-decoration: none; color: #7F56D9;">{{ $user->email }}</a> If you'd rather not receive
        this kind of email, you can <a href="#" style="text-decoration: none; color: #7F56D9;">unsubscribe</a> or <a
            href="#" style="text-decoration: none; color: #7F56D9;">manage your email preferences</a>.
    </p>
@endsection