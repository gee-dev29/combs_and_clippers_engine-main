@extends('emails.master')
@section('content')
    <section style="width: 90%; background-color: #ffffff; padding: 2%;">
        <div style="margin-top: 50px; width: 100%; flex-direction: column;">
            <!-- greeting here with user first name-->
            <p style="font-size: 20px; font-weight: 500;"> Dear Admin,</p>
            @if ($order->delivery_type == \App\Models\Order::TYPE_DELIVERY)
                <p style="line-height:2rem">
                    We come with great news!, <br>
                    An order with reference ID: {{ $order->orderRef }} has just been placed on {{$order->store->store_name}} store. <br>
                    You are expected to contact our delivery partner for pickup and shipping as soon as possible. </p>
            @else
                <p style="line-height:2rem">
                    We come with great news!, <br>
                    An order with reference ID: {{ $order->orderRef }} has just been placed on {{$order->store->store_name}} store. <br>
                    You are expected to contact the vendor to schedule the buyer's pickup as soon as possible at the following address:
                <address>
                    {{ optional($order->pickupAddress)->street . ', ' . optional($order->pickupAddress)->city . ', ' . optional($order->pickupAddress)->state }}
                </address>
                </p>
            @endif
            <table style="width: 100%;">
                <thead>
                    <tr class="gry-color" style="background: #eceff4;">
                        <th colspan="2" class="text-left" style="padding: 0.5rem">Customer Information</th>
                    </tr>
                </thead>
                <tbody class="strong">
                    <tr class="gry-color" style="background: #eceff4;">
                        <th width="30%" class="text-left" style="padding: 1rem; text-align:left;">Name</th>
                        <td width="68%" class="text-left" style="padding: 1rem;">{{ $order->buyer->name }}</td>
                    </tr>
                    <tr class="gry-color" style="background: #eceff4;">
                        <th width="30%" class="text-left" style="padding: 1rem; text-align:left;">Email</th>
                        <td width="68%" class="text-left" style="padding: 1rem;">{{ $order->buyer->email }}</td>
                    </tr>
                    <tr class="gry-color" style="background: #eceff4;">
                        <th width="30%" class="text-left" style="padding: 1rem; text-align:left;">Phone</th>
                        <td width="68%" class="text-left" style="padding: 1rem;">{{ $order->buyer->phone }}</td>
                    </tr>
                    @if ($order->delivery_type == \App\Models\Order::TYPE_DELIVERY)
                    <tr class="gry-color" style="background: #eceff4;">
                        <th width="30%" class="text-left" style="padding: 1rem; text-align:left;">Delivery Address</th>
                        <td width="68%" class="text-left" style="padding: 1rem;">{{ $order->orderAddress->formatted_address }}</td>
                    </tr>
                    @endif
                    <tr class="gry-color" style="background: #eceff4;">
                        <th width="30%" class="text-left" style="padding: 1rem; text-align:left;">Time of Order</th>
                        <td width="68%" class="text-left" style="padding: 1rem;">{{ $order->humanReadableDate() }}</td>
                    </tr>
                </tbody>
            </table>

            <table style="width: 100%; margin-bottom:2rem;">
                <thead>
                    <tr class="gry-color" style="background: #eceff4;">
                        <th colspan="2" class="text-left" style="padding: 0.5rem">Vendor Information</th>
                    </tr>
                </thead>
                <tbody class="strong">
                    <tr class="gry-color" style="background: #eceff4;">
                        <th width="30%" class="text-left" style="padding: 1rem; text-align:left;">Name</th>
                        <td width="68%" class="text-left" style="padding: 1rem;">{{ $order->seller->name }}</td>
                    </tr>
                    <tr class="gry-color" style="background: #eceff4;">
                        <th width="30%" class="text-left" style="padding: 1rem; text-align:left;">Store Name</th>
                        <td width="68%" class="text-left" style="padding: 1rem;">{{ $order->store->store_name }}</td>
                    </tr>
                    <tr class="gry-color" style="background: #eceff4;">
                        <th width="30%" class="text-left" style="padding: 1rem; text-align:left;">Phone</th>
                        <td width="68%" class="text-left" style="padding: 1rem;">{{ $order->seller->phone }}</td>
                    </tr>
                    @if ($order->delivery_type == \App\Models\Order::TYPE_DELIVERY)
                    <tr class="gry-color" style="background: #eceff4;">
                        <th width="30%" class="text-left" style="padding: 1rem; text-align:left;">Pickup Address</th>
                        <td width="68%" class="text-left" style="padding: 1rem;"> {{$order->pickupAddress->formatted_address}}
                            {{-- {{ optional($order->pickupAddress)->street . ', ' . optional($order->pickupAddress)->city . ', ' . optional($order->pickupAddress)->state }} --}}
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>

            @if (!$order->seller->hasActiveSubscription())
                <p style="font-size: 16px; font-weight: bold; color: crimson; margin-top: 10px;"> NB: This vendor does not have an active subscription and cannot fulfill this order.</p>
            @endif

        </div>
        @include('emails.order-details')
        <div style="margin-top: 50px; width: 100%;">
            <hr style="width: 100%; background-color:#E3E3E3" />
        </div>
        @include('emails.order-summary')
    </section>
@endsection
