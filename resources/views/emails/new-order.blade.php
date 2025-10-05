@extends('emails.master')
@section('content')
    <section style="width: 90%; background-color: #ffffff; padding: 2%;">
        <div style="margin-top: 50px; width: 100%; flex-direction: column;">
            <!-- greeting here with user first name-->
            <p style="font-size: 20px; font-weight: 500;"> Hello {{ $order->seller->name }},</p>
            @if ($order->delivery_type == \App\Models\Order::TYPE_DELIVERY)
                <p style="line-height:2rem">
                    We come with great news!, <br>
                    An order with reference ID: {{ $order->orderRef }} has just been placed on your {{env("APP_NAME")}} store. <br>
                    You are expected to package the item(s) for our delivery partner's pickup and shipping as soon as
                    possible.
                </p>

                <p style="font-size: 16px; font-weight: 400; color: #344054; margin-top: 30px;">
                    Our delivery partner will contact you soon. Thank you once again for choosing {{env("APP_NAME")}}.
                </p>
            @else
                <p style="line-height:2rem">
                    We come with great news!, <br>
                    An order with reference ID: {{ $order->orderRef }} has just been placed on your {{env("APP_NAME")}} store. <br>
                    You are expected to package the item(s) for the buyer to pickup at your address:
                <address>
                    {{ optional($order->pickupAddress)->street . ', ' . optional($order->pickupAddress)->city . ', ' . optional($order->pickupAddress)->state }}
                </address> as soon as possible.
                </p>

                <p style="font-size: 16px; font-weight: 400; color: #344054; margin-top: 30px;">
                    The Buyer will contact you soon. Thank you once again for choosing {{env("APP_NAME")}}.
                </p>
            @endif
            <p style="font-size: 14px; font-weight: 400; color: #344054; margin-top: 10px;">
                Keep up the great work {{ $order->seller->name }}, sell more, and keep earning.
            </p>

            @if (!$order->seller->hasActiveSubscription())
                <p style="font-size: 14px; font-weight: bold; color: crimson; margin-top: 10px;">
                    NB: You cannot fulfill this Order as you do not have an active subscription. <br>
                    Kindly click <a href="{{ cc('sub_url') }}" style="text-decoration: none; color: #7F56D9;">here</a> to
                    renew your subscription.
                </p>
            @endif

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
        This email was sent to <a href="mailto:{{ $order->seller->email }}"
            style="text-decoration: none; color: #7F56D9;">{{ $order->seller->email }}</a> If you'd rather not receive
        this kind of email, you can <a href="#" style="text-decoration: none; color: #7F56D9;">unsubscribe</a> or <a
            href="#" style="text-decoration: none; color: #7F56D9;">manage your email preferences</a>.
    </p>
@endsection
