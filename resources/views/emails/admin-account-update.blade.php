@extends('emails.master')
@section('content')
<section style="width: 90%; background-color: #ffffff; padding: 2%;">
    <div style="margin-top: 50px; width: 100%; flex-direction: column;">
        <p style="font-size: 20px; font-weight: 500;">
            Hello {{ $admin->name }},
        </p>
        <p style="font-size: 16px; font-weight: 400; color: #344054; margin-top: 20px; line-height: 2rem;">
            @if($updateType == 'account_update')
            The following changes have been made to your admin account:
            @elseif($updateType == 'role_update')
            Your account permissions have been updated:
            @elseif($updateType == 'password_change')
            Your account password has been changed successfully.
            @endif
        </p>

        @if(count($changes) > 0)
        <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
            <thead>
                <tr>
                    <th style="background-color: #f2f2f2; border: 1px solid #ddd; padding: 8px; text-align: left;">
                        Changes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($changes as $change)
                <tr>
                    <td style="border: 1px solid #ddd; padding: 8px;">{{ $change }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <div style="margin-top: 20px;">
            <a href="{{ $loginUrl }}" style="text-decoration: none;">
                <button
                    style="display: inline-block; background-color: #7F56D9; border-radius: 8px; border: 1px solid #7F56D9; padding: 10px 18px; color: #FFFFFF; font-size: 16px; cursor: pointer;">
                    Login to Admin Panel
                </button>
            </a>
        </div>

        <p style="font-size: 16px; font-weight: 400; color: #344054; margin-top: 20px; line-height: 2rem;">
            If you did not initiate these changes, please contact the system administrator immediately.
        </p>

        <p style="font-size: 16px; font-weight: 400; color: #344054; margin-top: 10px; line-height: 2rem;">
            Thanks,<br>
            The {{ config('app.name') }} Team
        </p>
    </div>
</section>
@endsection

@section('footer')
<p style="font-size: 14px; font-weight: 400; color: #667085;">
    This email was sent to <a href="mailto:{{ $admin->email }}" style="text-decoration: none; color: #7F56D9;">{{
        $admin->email }}</a>
    If you'd rather not receive this kind of email, you can <a href="#"
        style="text-decoration: none; color: #7F56D9;">unsubscribe</a>
    or <a href="#" style="text-decoration: none; color: #7F56D9;">manage your email preferences</a>.
</p>
@endsection