@extends('emails.master')
@section('content')
    <section style="width: 90%; background-color: #ffffff; padding: 2%;">
        <div style="margin-top: 50px; width: 100%; flex-direction: column;">
            <!-- greeting here with user first name-->
            <p style="font-size: 20px; font-weight: 500;">Hi There!</p>
            <p style="font-size: 16px; font-weight: 400; color: #344054; margin-top: 30px; line-height:2rem;">
                This is just a test mail. So, don't dwindle!!!
            </p>
            <p style="font-size: 16px; font-weight: 400; color: #344054; margin-top: 30px; line-height:2rem;"> With love &#128156;
                from <br> The {{env("APP_NAME")}} team.</p>
        </div>
    </section>
@endsection
