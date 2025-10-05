<?php
use App\Models\Order;
use App\Mail\AdminNewOrder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/test-smtp', function () {
    try {
        config(['mail.mailers.smtp.stream' => [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ],
        ]]);
        
        Mail::raw('Test email from Laravel with Zepto SMTP', function ($message) {
            $message->to('obedugwuv@gmail.com')
                    ->subject('Laravel Zepto SMTP Test');
        });
        
        return 'Email sent successfully via SMTP!';
    } catch (\Exception $e) {
        return 'SMTP Error: ' . $e->getMessage();
    }
});

Route::get('/debug-mail-config', function () {
    return [
        'default_mailer' => config('mail.default'),
        'smtp_config' => config('mail.mailers.smtp'),
        'from_config' => config('mail.from'),
    ];
});

Route::get('/notification/{id?}', function ($id = 1) {
    $order = Order::findOrFail($id);
    return new AdminNewOrder($order);
});

Route::group([
    'namespace' => 'App',
    'middleware' => 'cors',
], function () {
    Route::get('password/reset', 'UserController@reset')->name('reset');
    Route::get('auth/facebook/{redirectTo?}', 'SocialController@redirectToProvider');
    Route::get('auth/instagram/{redirectTo?}', 'SocialController@instagramRedirectToProvider');
});
