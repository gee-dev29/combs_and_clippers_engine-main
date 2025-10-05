@extends('emails.master')
@section('content')
<section style="width: 90%; background-color: #ffffff; padding: 2%;">
    <div style="margin-top: 50px; width: 100%; flex-direction: column;">
        <p style="font-size: 20px; font-weight: 500;"> Hello {{ $user->name }},</p>
        <p style="font-size: 16px; font-weight: 400; color: #344054; margin-top: 30px; line-height:2rem;">
        <p>Your phone number verification OTP is: <br>
            <strong>{{ $otp }}</strong>
        </p>
        <br>
        <p>This OTP will expire in 10 minutes.</p>
        </p>
        <p style="font-size: 16px; font-weight: 400; color: #344054; margin-top: 30px; line-height:2rem;"> With love
            &#128156;
            from <br> The {{env("APP_NAME")}} team.</p>
    </div>
</section>
@endsection

@section('footer')
<p style="font-size: 14px; font-weight: 400; color: #667085;">
    This email was sent to <a href="mailto:{{ $user->email }}" style="text-decoration: none; color: #7F56D9;">{{
        $user->email }}</a> If you'd rather not receive
    this kind of email, you can <a href="#" style="text-decoration: none; color: #7F56D9;">unsubscribe</a> or <a
        href="#" style="text-decoration: none; color: #7F56D9;">manage your email preferences</a>.
</p>
@endsection