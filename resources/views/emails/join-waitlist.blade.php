@extends('emails.master')
@section('content')
    <section style="width: 90%; background-color: #ffffff; padding: 2%;">
        <div style="margin-top: 50px; width: 100%; flex-direction: column;">
            <!-- greeting here with user first name-->
            <p style="font-size: 20px; font-weight: 500;"> Gyebale ko'</p>
            <p style="font-size: 16px; font-weight: 400; color: #344054; margin-top: 30px; line-height:2rem;">
                Now we know that you love to make some ka money, and social media has been helping you. 
                But do you remember the hassle you go through to send that order, receive your cash, and convince the customer to pay before delivery?
            </p>
            <p style="font-size: 16px; font-weight: 400; color: #344054; margin-top: 30px; line-height:2rem;">
                Well, {{env("APP_NAME")}} is here for you to take away all these problems. 
                We shall give your more visibility through an online store, access to more customers, a guarantee for your cash at delivery of your products, and the icing on the cake is a last-mile delivery to all your customers - nationwide!
            </p>
            <p style="font-size: 16px; font-weight: 400; color: #344054; margin-top: 30px; line-height:2rem;">
                So, hang in there! set your timers for a few weeks, and we will get back to you with the next steps. Keep an eye open! &#128521;
            </p>

            <p style="font-size: 16px; font-weight: 400; color: #344054; margin-top: 30px; line-height:2rem;"> Speak soon,
                <br>
                Mukisa from {{env("APP_NAME")}}.
            </p>
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
