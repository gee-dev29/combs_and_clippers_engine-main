@extends('emails.master')
@section('content')
    <section style="width: 90%; background-color: #ffffff; padding: 2%;">
        <div style="margin-top: 50px; width: 100%; flex-direction: column;">
            <!-- greeting here with user first name-->
            <p style="font-size: 20px; font-weight: 500;"> Hello Mukisa,</p>
            <p style="line-height:2rem">
                Please be informed that {{ $user->name }} has just created an account on {{env("APP_NAME")}}. <br>
                Kindly review and take neccessary actions.
            </p>
            <a href="{{ cc('backend_base_url')}}"
                style="text-decoration: none; color: #7F56D9;">
                <button
                    style="display: flex; margin-top: 30px; background-color: #7F56D9; border-radius: 8px; border: 1px solid #7F56D9; padding: 10px 18px; color: #FFFFFF;">
                    Admin Login <img src="{{ asset('img/arrow-right.png') }}" alt="arrow_right"
                        style="margin-left: 5px; height: 20px; width: 20px;">
                </button>
            </a>
            <p style="font-size: 16px; font-weight: 400; color: #344054; margin-top: 30px; line-height:2rem;">With love &#128156; from
                <br>The {{env("APP_NAME")}} team.</p>
        </div>
    </section>
@endsection
