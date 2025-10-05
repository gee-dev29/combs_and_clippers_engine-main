@extends('emails.master')
@section('content')
    <section style="width: 90%; background-color: #ffffff; padding: 2%;">
        <div style="margin-top: 50px; width: 100%; flex-direction: column;">
            <!-- greeting here with user first name-->
            <p style="font-size: 20px; font-weight: 500;"> Hello {{ $order->seller->name }},</p>
            <p style="line-height:2rem">
                Please be informed that a dispute has been raised requesting for a <strong>{{ $option }}</strong> on order
                {{ $order->orderRef }}.
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
            <p style="font-size: 16px; font-weight: 400; color: #344054; margin-top: 30px; line-height:2rem;">
                Kindly visit your {{env("APP_NAME")}} store on MyMTNapp to review the dispute and provide a resolution, or click the
                button below. <br>
                <a href="{{ cc('dispute_url') }}" style="text-decoration: none; color: #7F56D9;">
                    <button
                        style="display: flex; margin-top: 30px; background-color: #7F56D9; border-radius: 8px; border: 1px solid #7F56D9; padding: 10px 18px; color: #FFFFFF;">
                        View Dispute <img src="{{ asset('img/arrow-right.png') }}" alt="arrow_right"
                            style="margin-left: 5px; height: 20px; width: 20px;">
                    </button>
                </a>
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
        This email was sent to <a href="mailto:{{ $order->seller->email }}"
            style="text-decoration: none; color: #7F56D9;">{{ $order->seller->email }}</a> If you'd rather not receive
        this kind of email, you can <a href="#" style="text-decoration: none; color: #7F56D9;">unsubscribe</a> or <a
            href="#" style="text-decoration: none; color: #7F56D9;">manage your email preferences</a>.
    </p>
@endsection
