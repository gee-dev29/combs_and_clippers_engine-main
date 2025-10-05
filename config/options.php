<?php

return  [

    // Base url to api
    'api' => [
        'base' => config('app.url').'/api',
    ],
    // hours before activation expires
    'default_currency' => 'GBP',
    'environment' => env('APP_ENV', 'production'),
    'activation_ttl' => 2,
    'engage_public_key' => env('ENGAGE_PUB_KEY'),
    'engage_private_key' => env('ENGAGE_PRIV_KEY'),
    'shipbubble_token' => env("SHIPBUBBLE_TOKEN"),
    'my_story' => 'https://prosperofpepperest.substack.com/publish/post/57930344',
    'community_link' => 'https://chat.whatsapp.com/FnEuJ266l3rJIQ5wMKJoXh',
    'how_to_video' => 'https://youtu.be/JLPthdkz9-k',
    'twitter' => 'https://twitter.com/',
    'instagram' => 'https://www.instagram.com/',
    'sub_url' => env('APP_ENV') == 'production' ? env("FRONTEND_HOST_URL_LIVE") . 'merchant/subscription' : 'https://combsandclippers.com/merchant/subscription', 
    'dispute_url' => env('APP_ENV') == 'production' ? env("FRONTEND_HOST_URL_LIVE") . 'merchant/issues' : 'https://combsandclippers.com/merchant/issues', 
    'password_reset_url' => env('APP_ENV') == 'production' ? env("FRONTEND_HOST_URL_LIVE") . 'new-password?token=' : 'https://combsandclippers.com/create-new-password?token=', 
    'login_url' => env('APP_ENV') == 'production' ? env("FRONTEND_HOST_URL_LIVE") . 'signin' : env("FRONTEND_HOST_URL_TEST") . 'signin', 
    'frontend_base_url' => env('APP_ENV') == 'production' ? env("FRONTEND_HOST_URL_LIVE1") : env("FRONTEND_HOST_URL_TEST1"), 
    'merchant_store_base_url' => env('APP_ENV') == 'production' ? env("FRONTEND_HOST_URL_LIVE") . 'b/store/' : env("FRONTEND_HOST_URL_TEST") . 'b/store/',
    'waitlist_url' => env('APP_ENV') == 'production' ? env("FRONTEND_HOST_URL_LIVE") . 'wait-list?referral_code=' : env("FRONTEND_HOST_URL_TEST") . 'wait-list?referral_code=',
    'verification_url' => env('APP_ENV') == 'production' ? env("FRONTEND_HOST_URL_LIVE") . 'verify-email/' : env("FRONTEND_HOST_URL_TEST") . 'verify-email/',
    'order_confirmation_url' => env('APP_ENV') == 'production' ? env("FRONTEND_HOST_URL_LIVE") . 'confirm-order/' : env("FRONTEND_HOST_URL_TEST") . 'confirm-order/',
    'tracking_url' => env('APP_ENV') == 'production' ? env("FRONTEND_HOST_URL_LIVE") . 'track-order/' : env("FRONTEND_HOST_URL_TEST") . 'track-order/',
    'refund_url' => env('APP_ENV') == 'production' ? env("FRONTEND_HOST_URL_LIVE") . 'refund-order/' : env("FRONTEND_HOST_URL_TEST") . 'refund-order/',
    'onboarding_social_redirect_url' => env('APP_ENV') == 'production' ? env("FRONTEND_HOST_URL_LIVE") . 'onboarding' : env("FRONTEND_HOST_URL_TEST") . 'onboarding',
    'settings_social_redirect_url' => env('APP_ENV') == 'production' ? env("FRONTEND_HOST_URL_LIVE") . 'merchant/settings' : env("FRONTEND_HOST_URL_TEST") . 'merchant/settings',
    'settings_url' => env('APP_ENV') == 'production' ? env("FRONTEND_HOST_URL_LIVE") . 'merchant/settings' : env("FRONTEND_HOST_URL_TEST") . 'merchant/settings',
    'backend_base_url' => env('APP_ENV') == 'production' ? env("API_HOST_URL_TEST") : env("API_HOST_URL_TEST"),
    'api_url' => env('APP_ENV') == 'production' ? env("API_HOST_URL_TEST") . 'api/' : env("API_HOST_URL_TEST") . 'api/',
    'api_base_url' => env('APP_ENV') == 'production' ? env("API_HOST_URL_TEST") . 'api/' : env("API_HOST_URL_TEST") . 'api/',
    'mail_from' => 'transactions@mypeppa.co.uk',
    'support_mail' => 'Mukisa@mypeppa.co.uk',
    'pepperestfee_percentage' => 2.5,
    'pepperestfee_cap' => 15000,
    'wallet_withdrawal_fee' => 20,
     //Transaction status
    'transaction' => [
        'statusArray' => ['Canceled' => 0, 'Unpaid' => 1, 'Paid' => 2, 'Processing' => 3, 'Shipped' => 4, 'Delivered' => 5, 'Completed' => 6, 'Disputed' => 7, 'Refunded' => 8, 'Replaced' => 9, 'In-Review' => 10],
        'indexed_status' => [0 => 'Canceled', 1 => 'Unpaid', 2 => 'Paid', 3 => 'Processing', 4 => 'Shipped', 5 => 'Delivered', 6 => 'Completed', 7 => 'Disputed', 8 => 'Refunded', 9 => 'Replaced', 10 => 'In-Review'],
        'status' => ['Canceled', 'Unpaid', 'Paid', 'Processing', 'Shipped', 'Delivered', 'Completed', 'Disputed', 'Refunded', 'Replaced', 'In-Review'],
        'sellerControlledOrderStatus' => ['Processing', 'Shipped', 'Delivered'],
    ],

    'disputes' => [
        'status' => ['Open', 'Processing', 'Closed-refunded', 'Closed-replaced', 'Rejected', 'Accepted']
    ],

    // Time to live for cache.
    'cache' => [
        'ttl' => 60*24,
    ],

    'backend' => [
        'perpage' => 50,
        'concat'  => '_pepuser',
    ],
    'image' => [
        'quality' => 75,
        'allowed_extensions' => '.jpg , .jpeg',

        /**
         * Region that resize or fit function will focus on when cropping
         *
         * Possible values: top-left, top, top-right, left, center, right,
         * bottom-left, bottom, bottom-right
         */
        'focus' => 'top',
        'sizes' => [
            'mini_' => [25, 25],
            'small_' => [48, 48],
            'normal_' => [100, 100],
            'medium_' => [200, 200],
            'profile_' => [300, 300],
            'big_' => [400, 400],
        ],
        'max' => 1024,
        // convert the above kilobytes to MB. Will be displayed to users
        'MB' => 1,
    ],
    // avatar name and sizes
    'avatar' => [
        'sizes' => [
            'mini_' => [25, 25],
            'small_' => [48, 48],
            'normal_' => [100, 100],
            'medium_' => [200, 200],
            'profile_' => [300, 300],
            'big_' => [400, 400],
        ],
        'max' => 1024,
        // convert the above kilobytes to MB. Will be displayed to users
        'MB' => 1,
    ],


];
