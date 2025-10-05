<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'stripe' => [
        'model' => App\Models\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'instagram' => [
        'client_id' => '297164338007695',
        'client_secret' =>  'e48bb3be0429d8e4600d702b31df473d',
        // 'client_id' => '0e024edbc5164703b00a84e207bfd702',
        //'client_secret' => 'cf7562cb0d644890b1c420163139b1ae',
        'redirect' => 'http://127.0.0.1:8000/api/login/facebook/callback',
        'instagram_website' => 'https://pepperest.com',
        'instagram_description' => 'Sale with Instagram', 
        'instagram_scope' => 'basic'
    ],

    'instagram_graph' => [
        'facebook_app_id' => '2253727468182840',
        'facebook_api_secret' => 'abc64701d21a24e59a2c1f79d4ee5b8b',
        'facebook_default_scope' => '',
        'facebook_api_url' => 'https://graph.facebook.com/',
    ],

   
    'facebook' => [
        'client_id'     => '2253727468182840',
        'client_secret' => 'abc64701d21a24e59a2c1f79d4ee5b8b',
        //'redirect'      => 'https://pepperest-app.netlify.app/login',
        //'redirect'      => cc('frontend_base_url') .'login',
        //'redirect'      => 'https://pepperest-v2.herokuapp.com/login',
        //'redirect'      => 'http://localhost:3001/login',
        //'redirect'      => 'http://localhost:3001/products/instagram',
        //'redirect'      => 'http://localhost:8000/api/login/facebook/callback',
        //'redirect'      => 'https://pepperest.com/EscrowBackend/api/login/facebook/callback',
        //'redirect'      => 'http://localhost:8000/login/facebook/callback',
    ],
    'google' => [
        //'client_id' => '1085614773661-e36tjkli9oen6laocn1hra8anuedhn0f.apps.googleusercontent.com',
        'client_id' => '1085614773661-e36tjkli9oen6laocn1hra8anuedhn0f.apps.googleusercontent.com',
        'client_secret' => '54MuQO1Q_-E5BsP9jOIEdYFx',
        //'redirect' => 'http://localhost:3001/login',
        //'redirect'      => cc('frontend_base_url') .'login',
        //'redirect' => 'https://pepperest.com/EscrowBackend/api/login/google/callback',
        //'redirect' => 'https://pepperest.com/EscrowBackend/api/login/google/callback',
       
    ],

    'instagram_new' => [
        'client_id' => '811227066068499',
        'client_secret' => '89515b52a6d80f4e67954768f24edd86',
        'redirect' => 'https://pepperest.com/index.php/login/instagramlogin',
        'instagram_website' => 'https://pepperest.com',
        'instagram_description' => 'Sale with Instagram', 
        'instagram_scope' => 'basic'
    ],

    'paystack' => [
        'secret_key' => env('PAYSTACK_SECRET_KEY'),
        'public_key' => env('PAYSTACK_PUBLIC_KEY'),
        'payment_url' => env('PAYSTACK_PAYMENT_URL'),
    ],

    'vfd' => [
    'provider' => env('Virtual_Account_Provider', 'VFD'),
    'processing_fee' => env('Virtual_Account_Processing_Fee', 100),
],

];
