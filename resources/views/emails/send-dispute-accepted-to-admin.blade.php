@extends('emails.master')
@section('content')
    <section style="width: 90%; background-color: #ffffff; padding: 2%;">
        <div style="margin-top: 50px; width: 100%; flex-direction: column;">
            <!-- greeting here with user first name-->
            <p style="font-size: 20px; font-weight: 500;"> Hello Mukisa,</p>
            <p style="line-height:2rem">
                Please be informed that the dispute raised on order {{ $order->orderRef }} requesting for a
                <strong>{{ $option }}</strong>
                has been accepted by the vendor, {{ $order->seller->name }}. <br>
                Kindly review and take neccessary actions.
            </p>
            <table class="text-left small border-bottom">
                <thead>
                    <tr class="gry-color" style="background: #eceff4;">
                        <th colspan="2" class="text-left" style="padding: 0.5rem">Dispute Details</th>
                    </tr>
                </thead>
                <tbody class="strong">
                    <tr class="gry-color" style="background: #eceff4;">
                        <th width="15%" class="text-left" style="padding: 0.5rem">Buyer's Request</th>
                        <td width="35%" class="text-left">{{ ucfirst($option) }}</td>
                    </tr>
                    <tr class="gry-color" style="background: #eceff4;">
                        <th width="15%" class="text-left" style="padding: 0.5rem">Dispute Reference</th>
                        <td width="35%" class="text-left">{{ $dispute->dispute_referenceid }}</td>
                    </tr>
                    <tr class="gry-color" style="background: #eceff4;">
                        <th width="15%" class="text-left" style="padding: 0.5rem">Dispute Category</th>
                        <td width="35%" class="text-left">{{ $dispute->dispute_category }}</td>
                    </tr>
                    <tr class="gry-color" style="background: #eceff4;">
                        <th width="15%" class="text-left" style="padding: 0.5rem">Dispute Description</th>
                        <td width="35%" class="text-left">{{ $dispute->dispute_description }}</td>
                    </tr>
                    <tr class="gry-color" style="background: #eceff4;">
                        <th width="15%" class="text-left" style="padding: 0.5rem">Delivery Type</th>
                        <td width="35%" class="text-left">{{ $order->delivery_type }}</td>
                    </tr>
                    @if ($order->delivery_type == \App\Models\Order::TYPE_DELIVERY)
                        <tr class="gry-color" style="background: #eceff4;">
                            <th width="15%" class="text-left" style="padding: 0.5rem">Sendy Fulfilment ID</th>
                            <td width="35%" class="text-left">{{ $order->orderLogistics->fulfilment_request_id }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        @include('emails.order-details')
        <div style="margin-top: 50px; width: 100%;">
            <hr style="width: 100%; background-color:#E3E3E3" />
        </div>
        @include('emails.order-summary')
    </section>
@endsection