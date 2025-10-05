@extends('emails.master')
@section('content')
    <section style="width: 90%; background-color: #ffffff; padding: 2%;">
        @include('emails.order-details')
        <div style="margin-top: 50px; width: 100%;">
            <hr style="width: 100%; background-color:#E3E3E3" />
        </div>
        @include('emails.order-summary')
        @if ($order->delivery_type == \App\Models\Order::TYPE_DELIVERY)
            <div style="margin-top: 50px; width: 100%; flex-direction: column;">
                <!-- greeting here with user first name-->
                <p style="font-size: 20px; font-weight: 500;"> Hello {{ $order->buyer->name }},</p>
                <p style="font-size: 16px; font-weight: 400; color: #344054; margin-top: 30px; line-height:2rem;">
                    Thank you for shopping with {{env("APP_NAME")}}. <br>
                    Your order {{ $order->orderRef }} has been confirmed with payment successfully. <br>
                    Your item(s) will be shipped as soon as possible. You will receive a notification from us once the
                    item(s)
                    are ready for delivery. <br>
                    Thank you once again for choosing {{env("APP_NAME")}}.
                </p>
            </div>
        @endif
        @if ($order->delivery_type == \App\Models\Order::TYPE_PICKUP)
            <div style="margin-top: 50px; width: 100%; flex-direction: column;">
                <!-- greeting here with user first name-->
                <p style="font-size: 20px; font-weight: 500;"> Hello {{ $order->buyer->name }},</p>
                <p style="font-size: 16px; font-weight: 400; color: #344054; margin-top: 30px; line-height:2rem;">
                    Thank you for shopping with {{env("APP_NAME")}}. <br>
                    Your order {{ $order->orderRef }} has been confirmed with payment successfully. <br>
                    Your order will be processed and packaged as soon as possible. You will receive a notification from us once the
                    item(s)
                    are available for Pickup. <br>
                    Thank you once again for choosing {{env("APP_NAME")}}.
                </p>
            </div>
        @endif
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
