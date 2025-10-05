@extends('emails.master')
@section('content')
    <section style="width: 90%; background-color: #ffffff; padding: 2%;">
        <div style="margin-top: 50px; width: 100%; flex-direction: column;">
            <!-- greeting here with user first name-->
            <p style="font-size: 20px; font-weight: 500;"> Hello {{ $order->seller->name }},</p>
            <p style="line-height:2rem">
                Please be informed that the dispute raised on order {{ $order->orderRef }} requesting for a
                <strong>{{ $option }}</strong>
                has been accepted and currently being reviewed by our supoort team.
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
                        <th width="15%" class="text-left" style="padding: 0.5rem">Category</th>
                        <td width="35%" class="text-left">{{ $dispute->dispute_category }}</td>
                    </tr>
                    <tr class="gry-color" style="background: #eceff4;">
                        <th width="15%" class="text-left" style="padding: 0.5rem">Description</th>
                        <td width="35%" class="text-left">{{ $dispute->dispute_description }}</td>
                    </tr>
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

@section('footer')
    <p style="font-size: 14px; font-weight: 400; color: #667085;">
        This email was sent to <a href="mailto:{{ $order->seller->email }}"
            style="text-decoration: none; color: #7F56D9;">{{ $order->seller->email }}</a> If you'd rather not receive
        this kind of email, you can <a href="#" style="text-decoration: none; color: #7F56D9;">unsubscribe</a> or <a
            href="#" style="text-decoration: none; color: #7F56D9;">manage your email preferences</a>.
    </p>
@endsection
