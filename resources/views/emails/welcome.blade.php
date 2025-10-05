@extends('emails.master')
@section('content')
    <section style="width: 90%; background-color: #ffffff; padding: 2%;">
        <div style="margin-top: 50px; width: 100%; flex-direction: column;">
            <!-- greeting here with user first name-->
            <p style="font-size: 20px; font-weight: 500;"> Hello friend,</p>
            <p style="font-size: 16px; font-weight: 400; color: #344054; margin-top: 30px; line-height:2rem;">
                My name is Mukisa. I’m your success guide at {{env("APP_NAME")}}. People actually call me The Business Profit Multiplier.
                <br> You can call me that, too! 
            </p>

            <p style="font-size: 16px; font-weight: 400; color: #344054; margin-top: 30px; line-height:2rem;">
                At {{env("APP_NAME")}}, we’ve taken an unconventional path, and I’m so excited you’ve joined us on this journey!
            </p>
            {{-- <p style="font-size: 16px; font-weight: 400; color: #344054; margin-top: 30px; line-height:2rem;">
                To introduce you to {{env("APP_NAME")}} and what we represent, I’d like to share a personal story with you, it is a story
                from our Group CEO, Banky.
            </p> --}}
            {{-- <p style="font-size: 16px; font-weight: 400; color: #344054; margin-top: 30px; line-height:2rem;">For you to experience the humanness behind our brand,
                I believe it’s important to tell you the stories of how we got here,
                the immense struggles we had to face, and the strength and grit we conjured up to overcome the said
                struggles
                (and continue to do so) and now serving you… how can my heart not be bullish?</p> --}}
            {{-- <p style="font-size: 16px; font-weight: 400; color: #344054; margin-top: 30px; line-height:2rem;">
                You can read my story <a href="{{ cc('my_story') }}" style="text-decoration: none; color: #7F56D9;">here</a>
            </p> --}}

            <p style="font-size: 16px; font-weight: 400; color: #344054; margin-top: 30px; line-height:2rem;">{{ $user->name }}, If you haven't launched your website yet, you can watch this video to learn
                how to do so, by clicking <a href="{{ cc('how_to_video') }}" style="text-decoration: none; color: #7F56D9;">here</a>.
                Till I come your way tomorrow to walk you through your very first feature on {{env("APP_NAME")}}, keep winning, and
                remember we're all rooting for your business over here!</p>

            <p style="font-size: 16px; font-weight: 400; color: #344054; margin-top: 30px; line-height:2rem;"> Mukisa from {{env("APP_NAME")}}.</p>

            <p style="font-size: 16px; font-weight: 400; color: #344054; margin-top: 30px;">
                P.S: We always love to hear from you. Connect with us on <a href="{{ cc('twitter') }}" style="text-decoration: none; color: #7F56D9;">twitter</a> and <a href="{{ cc('instagram') }}" style="text-decoration: none; color: #7F56D9;">instagram</a>, or join our community <a href="{{ cc('community_link') }}" style="text-decoration: none; color: #7F56D9;">here</a>.
            </p>
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
