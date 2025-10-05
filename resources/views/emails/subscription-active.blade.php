@extends('emails.master')
@section('content')
    <section style="width: 90%; background-color: #ffffff; padding: 2%;">
        <div style="margin-top: 50px; width: 100%; flex-direction: column;">
            <!-- greeting here with user first name-->
            <p style="font-size: 20px; font-weight: 500;"> Hello {{ $user->name }},</p>
            <p style="font-size: 16px; font-weight: 400; color: #344054; margin-top: 30px; line-height:2rem;">
                Your subscription to use the online store on the MTN app has been activated. <br>
                Please find below your billing invoice.
            </p>
            <div>
                <table class="text-left small border-bottom">
                    <thead>
                        <tr class="gry-color" style="background: #eceff4;">
                            <th width="35%" class="text-left" style="padding: 0.5rem">Invoice no</th>
                            <td width="15%" class="text-left">{{ $invoice->invoice_number }}</td>
                        </tr>
                    </thead>
                    <tbody class="strong">
                        <tr class="gry-color" style="background: #eceff4;">
                            <th width="35%" class="text-left" style="padding: 0.5rem">Plan</th>
                            <td width="15%" class="text-left">{{ $invoice->plan }}</td>
                        </tr>
                        <tr class="gry-color" style="background: #eceff4;">
                            <th width="35%" class="text-left" style="padding: 0.5rem">Currency</th>
                            <td width="15%" class="text-left">{{ $invoice->currency }}</td>
                        </tr>
                        <tr class="gry-color" style="background: #eceff4;">
                            <th width="35%" class="text-left" style="padding: 0.5rem">Amount</th>
                            <td width="15%" class="text-left currency">{{ number_format($invoice->amount, 2) }}</td>
                        </tr>
                        <tr class="gry-color" style="background: #eceff4;">
                            <th width="35%" class="text-left" style="padding: 0.5rem">Status</th>
                            <td width="15%" class="text-left">{{ $invoice->status == 1 ? 'Paid' : 'Unpaid' }}</td>
                        </tr>
                        <tr class="gry-color" style="background: #eceff4;">
                            <th width="35%" class="text-left" style="padding: 0.5rem">Billing date</th>
                            <td width="15%" class="text-left">{{ $invoice->formatted_billing_date() }} </td>
                        </tr>
                        <tr class="gry-color" style="background: #eceff4;">
                            <th width="35%" class="text-left" style="padding: 0.5rem">Next Billing date</th>
                            <td width="15%" class="text-left">{{ $invoice->formatted_next_billing_date() }} </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p style="font-size: 16px; font-weight: 400; color: #344054; margin-top: 30px; line-height:2rem;"> With love &#128156;
                from <br> The {{env("APP_NAME")}} team.</p>
        </div>
    </section>
@endsection

@section('footer')
    <p style="font-size: 14px; font-weight: 400; color: #667085;">
        This email was sent to <a href="mailto:{{ $user->email }}"
            style="text-decoration: none; color: #7F56D9;">{{ $user->email }}</a> If you'd rather not receive
        this kind of email, you can <a href="#" style="text-decoration: none; color: #7F56D9;">unsubscribe</a> or <a
            href="#" style="text-decoration: none; color: #7F56D9;">manage your email preferences</a>.
    </p>
@endsection
