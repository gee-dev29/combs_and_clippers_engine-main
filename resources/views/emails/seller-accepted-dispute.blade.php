@extends('emails.master')
@section('content')
    <section style="width: 90%; background-color: #ffffff; padding: 2%;">
        <div style="margin-top: 50px; width: 100%; flex-direction: column;">
            <!-- greeting here with user first name-->
            <p style="font-size: 20px; font-weight: 500;"> Hello {{ $order->buyer->name }},</p>
            <p style="line-height:2rem">
                We have received and accepted your dispute request for a <strong>{{ $option }}</strong> on order
                {{ $order->orderRef }}.
                You can now prepare your return package and wait for <strong>our pickup agent</strong> <strong>to pick it
                    up</strong> within the <strong>next 3
                    business days</strong>.
            </p>
            <p>
                kindly ensure the <strong>conditions</strong> below are followed to <strong>accept your return:</strong>
            <ol>
                <li style="font-weight: 300;">The item is returned in its original and undamaged package.</li>
                <li style="font-weight: 300;">All accessories, tags, labels, or freebies are included.</li>
                <li style="font-weight: 300;">Any password is removed from the device.</li>
            </ol>
            As soon as we receive the package, we will proceed with your <strong>{{ $option }}</strong>. This process
            may take up to <strong>3 to 7 days</strong>.
            If you wish to cancel this dispute request, Kindly inform our pickup agent when you are contacted for return
            pickup, or contact our support team at <a href="mailto:{{ cc('support_mail') }}"
                style="text-decoration: none; color: #7F56D9;">Support</a>
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
