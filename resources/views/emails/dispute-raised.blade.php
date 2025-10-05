@extends('emails.master')
@section('content')
    <section style="width: 90%; background-color: #ffffff; padding: 2%;">
        <div style="margin-top: 50px; width: 100%; flex-direction: column;">
            <!-- greeting here with user first name-->
            <p style="font-size: 20px; font-weight: 500;"> Hello {{ $order->buyer->name }},</p>
            <p style="line-height:2rem">
                We have received your dispute requesting for a <strong>{{$option}}</strong> on order {{ $order->orderRef }}.
                Please be informed that your dispute is receiving all necessary attention.
                We'll let you know once we have a resolution to your dispute. <br>
                Thank you!
            </p>
        </div>
        @include('emails.order-details')
        <div style="margin-top: 50px; width: 100%;">
            <hr style="width: 100%; background-color:#E3E3E3" />
        </div>
        @include('emails.order-summary')
    </section>
@endsection

@section('footer')
    <p style="font-size: 14px; font-weight: 400; color: #667085;">
        This email was sent to <a href="mailto:{{ $order->buyer->email }}"
            style="text-decoration: none; color: #7F56D9;">{{ $order->buyer->email }}</a> If you'd rather not receive
        this kind of email, you can <a href="#" style="text-decoration: none; color: #7F56D9;">unsubscribe</a> or <a
            href="#" style="text-decoration: none; color: #7F56D9;">manage your email preferences</a>.
    </p>
@endsection
