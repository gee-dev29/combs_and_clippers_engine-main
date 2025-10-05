@extends('emails.master')
@section('content')
    <section style="width: 90%; background-color: #ffffff; padding: 2%;">
        <div style="margin-top: 50px; width: 100%; flex-direction: column;">
            <table class="text-left small border-bottom">
                <thead>
                    <tr class="gry-color" style="background: #eceff4;">
                        <th colspan="2" class="text-left" style="padding: 0.5rem">Customer Information</th>
                    </tr>
                </thead>
                <tbody class="strong">
                    <tr class="gry-color" style="background: #eceff4;">
                        <th width="35%" class="text-left" style="padding: 0.5rem">Name</th>
                        <td width="15%" class="text-left">{{ $name }}</td>
                    </tr>
                    <tr class="gry-color" style="background: #eceff4;">
                        <th width="35%" class="text-left" style="padding: 0.5rem">Email</th>
                        <td width="15%" class="text-left">{{ $email }}</td>
                    </tr>
                </tbody>
            </table>
            <p style="font-size: 16px; font-weight: 400; color: #344054; margin-top: 10px; line-height:2rem;">
                {{ $contents }}
            </p>
        </div>
    </section>
@endsection
