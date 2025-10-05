@extends('emails.master')
@section('content')
    <section style="width: 90%; background-color: #ffffff; padding: 2%;">
        <div style="margin-top: 50px; width: 100%; flex-direction: column;">
            <!-- greeting here with user first name-->
            <p style="font-size: 20px; font-weight: 500;">Hey Friend,</p>
            <p style="font-size: 16px; font-weight: 400; color: #344054; margin-top: 30px; line-height:2rem;">
                We are delighted to inform you {{$referrer}} has invited you to join our thriving {{env("APP_NAME")}} community!
            </p>

            <p style="font-size: 16px; font-weight: 400; color: #344054; margin-top: 30px; line-height:2rem;">
                {{env("APP_NAME")}} is an online marketplace where vendors can connect with a larger audience,
                grow their hustle faster and enjoy hitch-free delivery while buyers discover exciting products.
            </p>

            <p style="font-size: 16px; font-weight: 400; color: #344054; margin-top: 30px; line-height:2rem;">
                We believe that your business would be a fantastic addition to our platform, and we would be so honored to
                have you join us.
            </p>

            <p style="font-size: 16px; font-weight: 400; color: #344054; margin-top: 30px; line-height:2rem;">
                By joining our waitlist, you'll have access to a whole suite of features designed to help you streamline
                your selling process and maximize your profits.
                You'll also have the opportunity to join a supportive community of fellow sellers, who will readily offer
                advice and support to help you grow.
            </p>

            <p style="font-size: 16px; font-weight: 400; color: #344054; margin-top: 30px; line-height:2rem;">
                Can't wait for you to get started. <br>
                <a href="{{ cc('waitlist_url') . $referral_code }}" style="text-decoration: none; color: #7F56D9;">
                    <button
                        style="display: flex; margin-top: 30px; background-color: #7F56D9; border-radius: 8px; border: 1px solid #7F56D9; padding: 10px 18px; color: #FFFFFF;">
                        Join Waitlist <img src="{{ asset('img/arrow-right.png') }}" alt="arrow_right"
                            style="margin-left: 5px; height: 20px; width: 20px;">
                    </button>
                </a>
            </p>

            <p style="font-size: 16px; font-weight: 400; color: #344054; margin-top: 30px; line-height:2rem;"> Best regards
                &#128156;,
                <br>
                Mukisa from {{env("APP_NAME")}}.
            </p>
        </div>
    </section>
@endsection

@section('footer')
    <p style="font-size: 14px; font-weight: 400; color: #667085;">
        This email was sent to <a href="mailto:{{ $email }}"
            style="text-decoration: none; color: #7F56D9;">{{ $email }}</a> If you'd rather not receive
        this kind of email, you can <a href="#" style="text-decoration: none; color: #7F56D9;">unsubscribe</a> or <a
            href="#" style="text-decoration: none; color: #7F56D9;">manage your email preferences</a>.
    </p>
@endsection
