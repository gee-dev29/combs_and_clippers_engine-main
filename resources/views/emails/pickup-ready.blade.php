@extends('emails.master')
@section('content')
    <section style="width: 90%; background-color: #ffffff; padding: 2%;">
        @include('emails.order-details')
        <div style="margin-top: 50px; width: 100%;">
            <hr style="width: 100%; background-color:#E3E3E3" />
        </div>
        @include('emails.order-summary')
        <div style="margin-top: 50px; width: 100%; flex-direction: column;">
            <!-- greeting here with user first name-->
            <p style="font-size: 20px; font-weight: 500;"> Hello {{ $order->buyer->name }},</p>
            <p style="font-size: 16px; font-weight: 400; color: #344054; margin-top: 30px; line-height:2rem;">
                Thank you for shopping with {{env("APP_NAME")}}. <br>
                Your order {{ $order->orderRef }} is ready for pickup. <br>
                The package can be picked up at the following address: <strong>
                    {{ optional($order->pickupAddress)->street . ', ' . optional($order->pickupAddress)->city . ', ' . optional($order->pickupAddress)->state }}</strong>.
                You can contact the seller on the following number: {{ $order->seller->phone }} <br>
                Please be informed that the seller will request you to confirm your order once you’re satisfied with it
                before releasing the package to you. <br>
                Thanks for your understanding and thank you once again for choosing {{env("APP_NAME")}}.
            </p>
            <!-- confirm order button -->
            <a href="{{ cc('order_confirmation_url') . $order->orderRef . '/' . $order->confirmation_pin }}"
                style="text-decoration: none; color: #7F56D9;">
                <button
                    style="display: flex; margin-top: 30px; background-color: #7F56D9; border-radius: 8px; border: 1px solid #7F56D9; padding: 10px 18px; color: #FFFFFF;">
                    Confirm Order <img src="{{ asset('img/arrow-right.png') }}" alt="arrow_right"
                        style="margin-left: 5px; height: 20px; width: 20px;">
                </button>
            </a>
        </div>
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
