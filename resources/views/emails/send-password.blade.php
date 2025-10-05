@extends('emails.master')
@section('content')
    <section style="width: 90%; background-color: #ffffff; padding: 2%;">
        <div style="margin-top: 50px; width: 100%; flex-direction: column;">
            <p style="font-size: 20px; font-weight: 500;"> Hello {{ $user->name }},</p>
            <p style="font-size: 16px; font-weight: 400; color: #344054; margin-top: 30px; line-height:2rem;">
                You are welcome to {{env("APP_NAME")}}, we are excited you have joined us! 
                A merchant account has been created for you.
                We really want you to succeed, and we will be with you all the way to the top.
            </p>
            <p style="font-size: 16px; font-weight: 400; color: #344054; margin-top: 30px; line-height:2rem;">Your default
                password is : <strong>{{ $password }}</strong> <br> Kindly change your password after login.
            </p>
            <a href="{{ cc('login_url')}}"
                style="text-decoration: none; color: #7F56D9;">
                <button
                    style="display: flex; margin-top: 30px; background-color: #7F56D9; border-radius: 8px; border: 1px solid #7F56D9; padding: 10px 18px; color: #FFFFFF;">
                    Login <img src="{{ asset('img/arrow-right.png') }}" alt="arrow_right"
                        style="margin-left: 5px; height: 20px; width: 20px;">
                </button>
            </a>
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
