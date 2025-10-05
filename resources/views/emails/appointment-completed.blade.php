<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Completed</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    
    <div style="background-color: #f8f9fa; padding: 30px; border-radius: 10px; margin-bottom: 20px;">
        
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: #6f42c1; margin: 0; font-size: 28px;">{{ env('APP_NAME') }}</h1>
        </div>
        
        <div style="text-align: center; margin-bottom: 25px;">
            <div style="background-color: #6f42c1; color: white; padding: 15px 25px; border-radius: 25px; display: inline-block;">
                <span style="font-size: 18px; font-weight: bold;">🎉 Appointment Completed</span>
            </div>
        </div>
        
        <p style="font-size: 16px; margin-bottom: 15px;">
            Hi {{ $client->firstName }},
        </p>
        
        <p style="font-size: 16px; margin-bottom: 20px;">
            Your appointment with <strong>{{ $store->store_name ?? $merchant->name ?? 'the stylist' }}</strong> has been completed successfully.
        </p>
        
        <div style="background-color: white; padding: 25px; border-radius: 8px; margin: 25px 0; border-left: 5px solid #6f42c1; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <h3 style="color: #333; margin-top: 0; margin-bottom: 20px; font-size: 20px;">The appointment details are:</h3>
            
            <div style="margin-bottom: 12px;">
                <span style="font-weight: bold; color: #555; display: inline-block; width: 80px;">Date:</span>
                <span style="color: #333;">{{ \Carbon\Carbon::parse($appointment->date)->format('F j, Y') }}</span>
            </div>
            
            <div style="margin-bottom: 12px;">
                <span style="font-weight: bold; color: #555; display: inline-block; width: 80px;">Time:</span>
                <span style="color: #333;">{{ \Carbon\Carbon::parse($appointment->time)->format('g:i A') }}</span>
            </div>
            
                <div style="margin-bottom: 12px;">
                    <span style="font-weight: bold; color: #555; display: inline-block; width: 80px;">Location:</span>
                    <span style="color: #333;">
                        @if($store->storeAddress)
                            {{ $store->storeAddress->street }}, {{ $store->storeAddress->city }}, {{ $store->storeAddress->state }}
                        @else
                            Location details will be provided
                        @endif
                    </span>
                </div>
        </div>
        
        <div style="background-color: #fff8e1; padding: 20px; border-radius: 8px; border-left: 5px solid #ff9800; margin: 25px 0; text-align: center;">
            <p style="color: #e65100; margin: 0; font-size: 16px;">
                <strong>💫 We hope things went as well as you expected.</strong>
            </p>
        </div>
        
        <div style="text-align: center; background-color: #6f42c1; color: white; padding: 15px; border-radius: 8px; margin: 25px 0;">
            <p style="margin: 0; font-size: 16px; font-weight: bold;">
                See you next time! ✨
            </p>
        </div>
        
        <div style="margin-top: 30px;">
            <p style="font-size: 16px; margin-bottom: 5px;">
                Thanks.
            </p>
            
            <p style="font-weight: bold; font-size: 16px; color: #6f42c1; margin: 0;">
                Team {{ env('APP_NAME') }}
            </p>
        </div>
        
    </div>
    
    <div style="text-align: center; color: #888; font-size: 12px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
        <p style="margin: 0;">This is an automated message. Please do not reply to this email.</p>
        <p style="margin: 5px 0 0 0;">© {{ date('Y') }} {{ env('APP_NAME') }}. All rights reserved.</p>
    </div>
    
</body>
</html>