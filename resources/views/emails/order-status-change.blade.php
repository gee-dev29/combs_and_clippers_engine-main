@extends('emails.master')
@section('content')
    <section style="width: 90%; background-color: #ffffff; padding: 2%;">
        @include('emails.order-details')
        <div style="margin-top: 50px; width: 100%;">
            <hr style="width: 100%; background-color:#E3E3E3" />
        </div>
        @include('emails.order-summary')
        @if ($status == 'Processing')
        <div style="margin-top: 50px; width: 100%; flex-direction: column;">
            <!-- greeting here with user first name-->
            <p style="font-size: 20px; font-weight: 500;"> Hello {{ $order->buyer->name }},</p>
            <p style="font-size: 16px; font-weight: 400; color: #344054; margin-top: 30px; line-height:2.5rem;">
                Your order {{$order->orderRef}} is being processed and will be shipped out soon. <br>
                Your package will be delivered by our delivery agent to the following address: <b>{{ $order->orderAddress->address }}</b>. <br>
                You will receive an SMS on <b>{{ $order->orderAddress->phone }}</b> when the package is out for delivery with the details of our delivery agent. <br>
                To know more about our delivery timelines, track your order below.
            </p>
            <!-- track order button -->
            <a href="{{ cc('tracking_url') . $order->orderRef }}"
                style="text-decoration: none; color: #7F56D9;">
                <button
                    style="display: flex; margin-top: 30px; background-color: #7F56D9; border-radius: 8px; border: 1px solid #7F56D9; padding: 10px 18px; color: #FFFFFF;">
                    Track Order <img src="{{ asset('img/arrow-right.png') }}" alt="arrow_right"
                        style="margin-left: 5px; height: 20px; width: 20px;">
                </button>
            </a>
        </div>
        @endif

        @if ($status == 'Shipped')
        <div style="margin-top: 50px; width: 100%; flex-direction: column;">
            <!-- greeting here with user first name-->
            <p style="font-size: 20px; font-weight: 500;"> Hello {{ $order->buyer->name }},</p>
            <p style="font-size: 16px; font-weight: 400; color: #344054; margin-top: 30px; line-height:2.5rem;">
                Your order {{$order->orderRef}} has been shipped and out for delivery. <br>
                Your package will be delivered by our delivery agent to the following address: <b>{{ $order->orderAddress->address }}</b>. <br>
                To know more about our delivery timelines, track your order below.
            </p>
            <!-- track order button -->
            <a href="{{ cc('tracking_url') . $order->orderRef }}"
                style="text-decoration: none; color: #7F56D9;">
                <button
                    style="display: flex; margin-top: 30px; background-color: #7F56D9; border-radius: 8px; border: 1px solid #7F56D9; padding: 10px 18px; color: #FFFFFF;">
                    Track Order <img src="{{ asset('img/arrow-right.png') }}" alt="arrow_right"
                        style="margin-left: 5px; height: 20px; width: 20px;">
                </button>
            </a>
        </div>
        @endif

        @if ($status == 'Delivered')
        <div style="margin-top: 50px; width: 100%; flex-direction: column;">
            <!-- greeting here with user first name-->
            <p style="font-size: 20px; font-weight: 500;"> Hello {{ $order->buyer->name }},</p>
            <p style="font-size: 16px; font-weight: 400; color: #344054; margin-top: 30px; line-height:2.5rem;">
                We are happy to inform you that our delivery agent has delivered a package with order {{$order->orderRef}}. <br>
                Satisfy with your order?  Please confirm the order below.
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
        @endif

        {{-- @if ($status == 'Canceled')
        <div style="margin-top: 50px; width: 100%; flex-direction: column;">
            <!-- greeting here with user first name-->
            <p style="font-size: 20px; font-weight: 500;"> Hello {{ $order->buyer->name }},</p>
            <p style="font-size: 16px; font-weight: 400; color: #344054; margin-top: 30px; line-height:2.5rem;">
                We are sorry to inform you that your order {{$order->orderRef}} has been canceled. <br>
                Kindly request a refund by clicking the button below.
            </p>
            <!-- request refund button -->
            <a href="{{ cc('refund_url') . $order->orderRef }}"
                style="text-decoration: none; color: #7F56D9;">
                <button
                    style="display: flex; margin-top: 30px; background-color: #7F56D9; border-radius: 8px; border: 1px solid #7F56D9; padding: 10px 18px; color: #FFFFFF;">
                    Request Refund <img src="{{ asset('img/arrow-right.png') }}" alt="arrow_right"
                        style="margin-left: 5px; height: 20px; width: 20px;">
                </button>
            </a>
        </div>
        @endif --}}
        
    </section>
@endsection

@section('footer')
    <p style="font-size: 14px; font-weight: 400; color: #667085;">
        This email was sent to <a href="mailto:{{$order->buyer->email}}"
            style="text-decoration: none; color: #7F56D9;">{{$order->buyer->email}}</a> If you'd rather not receive
        this kind of email, you can <a href="#" style="text-decoration: none; color: #7F56D9;">unsubscribe</a> or <a
            href="#" style="text-decoration: none; color: #7F56D9;">manage your email preferences</a>.
    </p>
@endsection
