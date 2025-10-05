<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::group([
    'namespace' => 'App',

], function () {
    Route::post('waitlist/join', 'UserController@joinWaitlist');
    Route::post('waitlist/inviteToJoin', 'UserController@inviteToWaitlist');
    Route::post('email/verify', 'EmailController@verifyEmail')->name('verification.verify');
    Route::post('email/sendSupportEmail', 'EmailController@sendSupportEmail');
    Route::post('register', 'UserController@register');
    Route::post('login', 'UserController@login');
    Route::post('sendLoginOTP', 'UserController@sendLoginOTP');
    Route::post('loginWithOTP', 'UserController@loginWithOTP');
    Route::post('password/forgot', 'UserController@forgotPassword');
    Route::post('password/reset', 'UserController@resetPassword');
    Route::post('password/forgotByPhone', 'UserController@forgotPasswordByPhoneNo');
    Route::post('password/resetWithOTP', 'UserController@resetPasswordByOTP');
    Route::post('requestProduct', 'ProductController@requestProduct');
    Route::post('leta/webhook', 'PaymentController@letaWebhook');
    Route::post('webhook/pawapay/payout', 'PaymentController@pawapayPayout');
    Route::post('webhook/pawapay/deposit', 'PaymentController@pawapayDeposit');
    Route::post('webhook/pawapay/refund', 'PaymentController@pawapayRefund');
    Route::post('ipn/pesapal/subscription', 'PaymentController@pesapalSubscription');
    Route::post('ipn/pesapal/checkout', 'PaymentController@pesapalCheckout');

    Route::get('mailConfig', 'TestController@retrieveMailConfig');
    Route::get('sendTestMail/{to}', 'TestController@sendTestMail');
    Route::get('testLeta', 'TestController@testLeta');
    Route::get('testPawaPay', 'TestController@testPawaPay');
    Route::get('testPesaPal', 'TestController@testPesaPal');

    Route::get('login/{provider}', 'UserController@redirectToProvider');
    Route::get('login/{provider}/callback', 'UserController@handleProviderCallback');
    Route::post('login/social', 'UserController@loginWithCode');
    //social login
    Route::post('login/loginFromSocial', 'UserController@loginFromSocial');

    Route::get('instagram/products', 'InstagramController@redirectToInstagram');
    Route::get('merchant/getStore', 'ProductController@getMerchantStore');
    Route::get('merchant/getProduct/{code}', 'ProductController@getMerchantProduct');
    Route::get('merchant/getProductReviews/{code}', 'ProductController@getProductReviews');

    Route::get('invoice/getInvoiceDetail', 'InvoiceController@getInvoiceDetail');
    Route::get('order/buyerAddress', 'OrderController@getBuyerAddress');

    Route::get('cart/testShiip', 'CartController@testShiip');
    Route::post('momo/payments/callback', 'PaymentController@paymentCallback');

    Route::post('momo/checkSubscriptionStatus', 'SubscriptionController@momoCheckSubscriptionStatus');

    Route::post('order/preCheckout', 'OrderController@preCheckout');
    Route::get('checkout/getMerchantStoreAddress', 'AccountController@getMerchantStoreAddress');
    Route::get('checkout/getMerchantPickupAddress', 'AccountController@getMerchantPickupAddress');
    Route::post('order/confirm', 'OrderController@confirmOrder');
    Route::post('order/track', 'OrderController@trackOrder');
    Route::post('order/cancel', 'OrderController@cancelOrder');
    //Category routes
    Route::get('getProductCategories', 'CategoryController@getProductCategories');
    Route::get('getStoreCategories', 'CategoryController@getStoreCategories');

    //Service types
    Route::get('/service-types', 'StoreServiceTypeController@getServiceTypes');

    //marketplace route
    Route::get('marketplace/getAllStores', 'MarketController@getAllStores');
    Route::get('marketplace/getFeaturedStores', 'MarketController@getFeaturedStores');
    Route::get('marketplace/stores/categorize', 'MarketController@categorizeStore');
    Route::post('marketplace/getStoresByCategory', 'MarketController@getStoresByCategory');
    Route::post('marketplace/getStoresByLocation', 'MarketController@getStoresByLocation');
    Route::get('marketplace/getTopSellingStores', 'MarketController@getTopSellingStores');
    Route::get('marketplace/search', 'MarketController@search');
    Route::get('marketplace/getCities', 'MarketController@getCities');
    Route::get('marketplace/getFeaturedProducts', 'MarketController@getFeaturedProducts');
    Route::get('marketplace/getRecommendedProducts', 'MarketController@getRecommendedProducts');
    Route::get('marketplace/getNewProducts', 'MarketController@getNewProducts');
    Route::get('marketplace/getPopularProducts', 'MarketController@getPopularProducts');
    Route::get('marketplace/getProductsByCategory', 'MarketController@getProductsByCategory');
    Route::match(['GET', 'POST'], 'getServiceProviders', 'ServiceController@getServiceProviders');
    Route::get('getServiceProvider/{code}', 'ServiceController@getServiceProvider');
    Route::get('getServiceProviderReviews/{code}', 'ServiceController@getServiceProviderReviews');
    Route::get('getServiceProviderServices/{code}', 'ServiceController@getServiceProviderServices');
    Route::match(['GET', 'POST'], 'getManyServiceProviders', 'ServiceController@getManyServiceProviders');
    Route::get('getServiceProviderInfo/{code}', 'ServiceController@getServiceProviderInfo');

    Route::get('store/view-staffs/open', 'UserStoreController@getStoreUsers');
    Route::get('/store-service-types/open', 'StoreServiceTypeController@index');

    


    //Service routes
    Route::get('service/getService', 'ServiceController@getService');
    Route::get('service/getMerchantServices', 'ServiceController@getMerchantServices');
    Route::post('service/book', 'ServiceController@bookService');
    Route::get('service/getBookedSlots', 'ServiceController@getBookedSlots');

    //test whatsapp
    Route::post('whatsapp/sendTestOtp', 'AccountController@sendTestOtp');
    Route::post('whatsapp/sendTestPaymentSuccessful', 'AccountController@sendTestPaymentSuccessful');

    Route::get('confirmMomoAccount/{phone}', 'AccountController@confirmMomoAccount');

    Route::get('blogs', 'BlogController@index');
    Route::get('blog/{slug}', 'BlogController@show');
    Route::get('blog-categories', 'BlogController@blogCategories');

    Route::post('appointment-book', 'BookingController@bookAppointment');
    Route::post('book-Publicappointment', 'BookingController@bookAppointmentPublic');
    Route::get('appointment/payment/verify/public', 'BookingController@verifyAppointmentBookingPublic');

    //Virtual account payment
    //Route::post('vfd/webhook', 'PaymentController@VFDWebhook');

    Route::group([
        'middleware' => 'AuthToken',

    ], function () {
        //User routes
        Route::post('addPhone', 'UserController@addPhone');
        Route::post('resendPhoneOtp', 'UserController@resendPhoneOtp');
        Route::post('validateOTP', 'UserController@validateOTP');
        Route::post('logout', 'UserController@logout');
        Route::post('refreshToken', 'UserController@refreshToken');
        Route::post('manage-account', 'UserController@manageAccount');
        Route::get('no-show-policy-settings', 'UserController@getNoShowPolicy');
        Route::post('set-no-show-policy-settings', 'UserController@setNoShowPolicy');
        Route::post('calculate-no-show-fee', 'UserController@calculateNoShowFee');
        Route::post('user/set-withdrawal-method', 'SettingsController@setBankDetailsV2');
        Route::post('user/set-withdrawal-schedule', 'SettingsController@setWithdrawalSchedule');
        Route::get('user/get-withdrawal-schedule', 'SettingsController@getWithdrawalSchedule');

        Route::get('banks/getBanks', 'AccountController@getBanks');
        Route::get('banks/nameEnquiry', 'AccountController@nameEnquiryWithAccountNo');

        Route::post('user/uploadStyle', 'UserController@uploadStyles');
        Route::get('user/viewStyle', 'UserController@getStyles');
        Route::delete('user/deleteStyles', 'UserController@deleteStyles');

        //Product routes
        Route::get('myProducts', 'ProductController@myProducts');
        Route::post('product/add', 'ProductController@addProduct');
        Route::post('product/update', 'ProductController@updateProduct');
        Route::get('product/remove', 'ProductController@removeProduct');
        Route::post('product/duplicate', 'ProductController@duplicateProduct');
        Route::post('product/toggle', 'ProductController@toggleProduct');
        Route::post('product/photo/remove', 'ProductController@removeProductPhoto');
        Route::get('search/products', 'ProductController@searchProduct');
        Route::get('product/packageBoxes', 'ProductController@getPackageBoxes');
        Route::get('products/{id}', 'ProductController@showProduct');

        Route::get('instagram/callback', 'InstagramController@handleInstagramCallback');

        //Rating routes
        Route::post('rating/rateProduct', 'ProductController@rateProduct');

        //Service routes
        Route::get('myServices', 'ServiceController@myServices');
        Route::post('service/add', 'ServiceController@addService');
        Route::post('service/update', 'ServiceController@updateService');
        Route::post('service/duplicate', 'ServiceController@duplicateService');
        Route::post('service/toggle', 'ServiceController@toggleService');
        Route::post('service/delete', 'ServiceController@deleteService');
        Route::post('service/delete/multiple', 'ServiceController@deleteMultipleServices');
        Route::get('service/getRecentlyUsedProviders', 'ServiceController@getRecentlyUsedProviders');
        Route::get('service/intrestedProviders', 'ServiceController@getSuggestedServiceProviders');

        //Social routes
        Route::get('product/social/step1', 'SocialController@getSocialUser');
        Route::get('product/social/step2', 'SocialController@getProductsFromSocial');
        Route::post('product/social/addAllProducts', 'SocialController@addAllProducts');
        Route::post('product/social/add', 'SocialController@addProduct');

        //Order routes
        Route::get('myOrders', 'OrderController@myOrders');
        Route::get('order/buyerOrders', 'OrderController@getBuyerOrders');
        Route::post('order/applyCoupon', 'OrderController@applyCoupon');
        Route::post('order/prepareCheckout', 'OrderController@prepareCheckout');
        Route::post('order/checkout', 'OrderController@checkout');
        Route::post('order/payment/verify', 'OrderController@verifyPayment');
        Route::post('order/changeOrderStatus', 'OrderController@updateOrderStatus');
        Route::get('order/getShipmentRates', 'OrderController@getShipmentRates');
        Route::get('order/getStatuses', 'OrderController@getOrderStatuses');
        Route::post('order/openDispute', 'OrderController@openDispute');
        Route::post('order/acceptDispute', 'OrderController@acceptDispute');
        Route::post('order/rejectDispute', 'OrderController@rejectDispute');
        Route::get('order/getDisputedOrders', 'OrderController@getDisputedOrders');
        // Route::post('order/acceptRefund', 'OrderController@acceptRefund');
        // Route::post('order/replaceItem', 'OrderController@replaceItem');

        //Address route
        Route::get('myAddresses', 'BuyerController@myAddresses');
        Route::post('address/add', 'BuyerController@addAddress');
        Route::post('address/update', 'BuyerController@updateAddress');
        Route::post('address/remove', 'BuyerController@removeAddress');

        Route::get('myBillingAddresses', 'BuyerController@myBillingAddresses');
        Route::post('billingAddress/add', 'BuyerController@addBillingAddress');
        Route::post('billingAddress/update', 'BuyerController@updateBillingAddress');
        Route::post('billingAddress/remove', 'BuyerController@removeBillingAddress');

        Route::post('userAccount/addPickupAddress', 'AccountController@addPickupAddress');
        Route::get('userAccount/getPickupAddress', 'AccountController@getPickupAddress');
        Route::post('userAccount/addStoreAddress', 'AccountController@addStoreAddress');
        Route::get('userAccount/getStoreAddress', 'AccountController@getStoreAddress');

        //Cart routes
        Route::post('cart/addToCart', 'CartController@addToCart');
        Route::post('cart/removeFromCart', 'CartController@removeFromCart');
        Route::get('cart/buyerCart', 'CartController@getBuyerCart');
        Route::get('cart/clear', 'CartController@clearCart');

        //Attribute routes
        Route::get('getAttributes', 'AttributeController@index');

        //Activity route
        Route::get('myCustomerActivities', 'UserController@getMyCustomerActivities');

        //Store routes
        Route::post('createStore', 'UserController@createStore');
        Route::post('createStore/new', 'UserController@newCreateStore');
        Route::post('removeStore', 'UserController@removeStore');
        Route::get('removeStoreIcon', 'SettingsController@removeStoreIcon');
        Route::get('removeStoreBanner', 'SettingsController@removeStoreBanner');
        Route::get('previewStore', 'SettingsController@previewStore');

        // Resend link to verify email
        Route::get('email/resend', 'EmailController@resendVerification')->name('verification.resend');

        //Coupon routes
        Route::get('myCoupons', 'CouponController@getCoupons');
        Route::post('coupon/add', 'CouponController@addCoupon');
        Route::post('coupon/update', 'CouponController@updateCoupon');
        Route::get('coupon/remove', 'CouponController@removeCoupon');
        Route::post('coupon/apply', 'CouponController@applyCoupon');
        Route::post('coupon/activate', 'CouponController@activateCoupon');
        Route::post('coupon/deactivate', 'CouponController@deactivateCoupon');

        //Discount routes
        Route::get('myDiscounts', 'DiscountController@myDiscounts');
        Route::post('discount/add', 'DiscountController@addDiscount');
        Route::post('discount/update', 'DiscountController@updateDiscount');
        Route::post('discount/remove', 'DiscountController@removeDiscount');

        //Message Routes
        Route::post('message/sendMessage', 'MessageController@sendMessage');
        Route::post('message/outbox', 'MessageController@getOutbox');
        Route::post('message/inbox', 'MessageController@getInbox');
        Route::post('message/all', 'MessageController@getAllMessages');
        Route::post('thread/getMessages', 'MessageController@getThreadMessages');

        //subscription Routes
        Route::get('subscriptions', 'SubscriptionController@subscriptions');
        Route::get('mySubscriptions', 'SubscriptionController@mySubscriptions');
        Route::post('subscription/pay', 'SubscriptionController@subscribe');
        Route::post('subscription/verify', 'SubscriptionController@verifySubscription');
        Route::get('subscription/history', 'SubscriptionController@getBillingHistory');

        //stats routes
        Route::get('stats/totalBuyers', 'StatsController@totalBuyers');
        Route::get('stats/totalVisits', 'StatsController@totalVisits');
        Route::get('stats/totalRevenue', 'StatsController@totalRevenue');
        Route::get('stats/balanceOverTime', 'StatsController@balanceOverTime');
        Route::get('stats/recentOrders', 'StatsController@recentOrders');
        Route::get('stats/overview', 'StatsController@overview');
        Route::get('reports/download', 'StatsController@exportReport');

        //settings routes
        Route::post('settings/updatePersonalInfo', 'SettingsController@updatePersonalInfo');
        Route::post('settings/updateStore', 'SettingsController@updateStore');
        Route::post('settings/changePassword', 'SettingsController@changePassword');
        Route::get('settings/login/devices', 'SettingsController@getLoginDevices');
        Route::post('settings/updateNotificationSettings', 'SettingsController@updateNotificationSettings');
        Route::get('settings/getNotificationSettings', 'SettingsController@getNotificationSettings');
        Route::post('settings/stripe/onboard', 'SettingsController@stripeOnboard');
        Route::post('addDeliverySettings', 'SettingsController@addDeliverySettings');
        Route::get('myDeliverySettings', 'SettingsController@myDeliverySettings');
        Route::post('settings/setRewardSystem', 'SettingsController@setRewardPreference');
        Route::get('settings/viewRewardSystem', 'SettingsController@getRewardPreference');
        Route::post('settings/setPaymentPreference', 'SettingsController@setPaymentPreference');
        Route::get('settings/viewPaymentPreference', 'SettingsController@getPaymentPreference');
        Route::post('settings/setBookingPreference', 'SettingsController@setBookingPreference');
        Route::get('settings/viewBookingPreference', 'SettingsController@getBookingPreference');
        Route::post('settings/setAvailability', 'SettingsController@setAvailability');
        Route::get('settings/viewAvailability', 'SettingsController@getAvailability');
        Route::post('settings/setBookingLimit', 'SettingsController@setBookingLimit');
        Route::get('settings/viewBookingLimit', 'SettingsController@getBookingLimit');
        Route::post('settings/setStorePreferences', 'SettingsController@setStorePreferences');
        Route::get('settings/viewStorePreferences', 'SettingsController@getStorePreferences');
        Route::post('settings/uploadPortfolio', 'UserController@uploadWorkImages');
        Route::get('settings/viewPortfolio', 'UserController@getWorkImages');
        Route::delete('settings/deletePortfolio', 'UserController@deleteWorkImages');
        Route::post('settings/updateBio', 'UserController@createBio');
        Route::post('settings/updateCoverPhoto', 'UserController@updateCoverPhoto');
        Route::get('settings/viewSetupProgress', 'UserController@getSetupProgress');
        Route::get('settings/genProfileLink', 'SettingsController@generateProfileLink');
        Route::post('settings/editProfileLink', 'SettingsController@editProfileLink');
        Route::post('settings/generate-store-link', 'SettingsController@generateStoreLink');
        Route::post('settings/update-store-link', 'SettingsController@updateStoreLink');
        Route::post('settings/setBankDetails', 'SettingsController@setBankDetials');
        Route::get('settings/getBankDetails', 'SettingsController@getBankDetails');
        Route::get('settings/earnings', 'SettingsController@getStoreEarnings');
        Route::get('settings/transactions', 'TransactionController@index');

        //2FA
        Route::post('2fa/generateSecret', 'TwoFactorAuthController@generateSecret');
        Route::post('2fa/generateOTP', 'TwoFactorAuthController@generateOTP');
        Route::post('2fa/enable2fa', 'TwoFactorAuthController@enable2fa');
        Route::post('2fa/enableSms2Fa', 'TwoFactorAuthController@enableSms2Fa');
        Route::post('2fa/disable2fa', 'TwoFactorAuthController@disable2fa');
        Route::post('2fa/disableSms2Fa', 'TwoFactorAuthController@disableSms2Fa');
        Route::post('2fa/authenticate', 'TwoFactorAuthController@authenticate');
        Route::post('2fa/validateOTP', 'TwoFactorAuthController@validateOTP');
        Route::post('2fa/resendOTP', 'TwoFactorAuthController@resendOTP');

        //Invoice routes
        Route::get('billing/invoices', 'InvoiceController@getBillingInvoices');
        Route::get('billingInvoice/download', 'InvoiceController@downloadInvoice');
        Route::get('billingInvoice/downloadAll', 'InvoiceController@downloadAllInvoice');

        Route::post('invoice/placeInvoicePayment', 'InvoiceController@placeInvoicePayment');
        Route::get('invoice/handleCallback', 'InvoiceController@handleGatewayCallback');

        //Customer routes
        Route::get('myCustomers', 'CustomerController@myCustomers');
        Route::get('customer/details', 'CustomerController@getCustomerDetails');

        //Payment routes
        Route::get('funds/recentDeposits', 'PaymentController@recentDeposits');
        //Route::get('search/payments', 'PaymentController@searchTransaction');
        Route::get('funds/awaitingDelivery', 'PaymentController@appointmentsAwaitingDelivery');
        Route::get('funds/withdrawalHistory', 'PaymentController@withdrawalHistory');
        Route::get('funds/balance', 'PaymentController@balance');
        Route::post('funds/withdraw', 'PaymentController@withdraw');
        Route::post('funds/createWallet', 'PaymentController@createWallet');

        //User Account routes
        Route::get('userAccount/getProfileInfo', 'AccountController@getProfileInfo');
        Route::post('userAccount/modifyProfileInfo', 'AccountController@modifyProfileInfo');
        Route::patch('userAccount/updateProfileInfo', 'AccountController@updateProfileInfo');
        Route::post('userAccount/changeAccountType', 'AccountController@changeAccountType');
        Route::post('userAccount/addBankAccount', 'AccountController@addBankAccount');
        Route::post('userAccount/addSocialMediaLink', 'AccountController@addSocialMediaLink');
        Route::post('userAccount/removeSocialMediaLink', 'AccountController@removeSocialMediaLink');
        Route::post('userAccount/changeAPIsetting', 'AccountController@changeAPIsetting');
        Route::get('userAccount/getReferralStats', 'AccountController@getReferralStats');
        Route::get('userAccount/testBridgePay', 'AccountController@testBridgePay');
        //Route::get('userAccount/setReferralCode', 'AccountController@setReferralCode');
        Route::post('userAccount/addUserInterests', 'AccountController@addUserInterests');
        Route::post('userAccount/deleteUserInterests', 'AccountController@deleteUserInterests');
        Route::get('userAccount/getUserInterests', 'AccountController@getUserInterests');
        Route::get('userAccount/getInterests', 'AccountController@getInterests');

        // Client Routes
        Route::post('client/review', 'UserController@leaveAReview');

        // Notification Routes
        Route::get('notifications', 'NotificationController@index')->name('notifications.index');
        Route::get('notifications/{id}/view', 'NotificationController@show')->name('notifications.show');

        // StoreServiceType

        Route::post('/store-service-type', 'StoreServiceTypeController@store');
        Route::post('/store-service-type-update', 'StoreServiceTypeController@update');
        Route::get('/store-service-types', 'StoreServiceTypeController@index');
        Route::post('/store-service-type-delete', 'StoreServiceTypeController@destroy');

        //UserStoreController;

        Route::post('/user-store/add-user', 'UserStoreController@store');
        Route::delete('/user-store/remove-user', 'UserStoreController@removeUser');
        Route::post('/user-store/leave', 'UserStoreController@leaveStore');
        Route::post('/store/generate-code', 'UserStoreController@generateStoreCode');
        Route::post('/store/set-booth-rent', 'UserStoreController@setUpBoothRent');
        Route::post('/store/send-booth-rent-reminder', 'UserStoreController@sendBoothRentReminder');
        Route::get('store/view-staffs', 'UserStoreController@getStoreUsers');
        Route::post('store/switch-store', 'UserStoreController@switchStores');
        // Book Controller
        Route::post('book-appointment', 'BookingController@bookAppointment');
        Route::get('appointment/payment/verify', 'BookingController@verifyAppointmentBooking');
        Route::get('myAppointments', 'BookingController@myAppointments');
        Route::get('myBookings', 'BookingController@myBookings');
        Route::get('viewAppointment', 'BookingController@viewAppointment');
        Route::post('appointment/cancel', 'BookingController@cancelAppointment');
        Route::post('appointment/accept', 'BookingController@acceptAppointment');
        Route::post('appointment/complete', 'BookingController@completeAppointment');
        Route::post('appointment/reschedule', 'BookingController@rescheduleAppointment');
        Route::get('retrieveAppointmentsByFilters', 'BookingController@retrieveAppointmentsByFilters');

        // Booth endpoints
        Route::post('set-booth-rental', 'BoothController@setUpBoothRent');
        Route::post('update-booth-rental', 'BoothController@updateBoothRent');
        Route::get('booth-rentals', 'BoothController@viewBoothRents');
        Route::get('booth-rental', 'BoothController@showBoothRent');
        Route::post('delete-booth-rental', 'BoothController@deleteBoothRent');
        Route::post('booth-payment/mark-as-paid', 'BoothController@markAsPaid');
        Route::get('booth-payment/status', 'BoothController@boothUsersPayment');
        Route::get('findBooth', 'BoothController@findBooth');

        // Favourite Stylist
        Route::post('stylist/add-favorite', 'FavoriteStylistController@addFavoriteStylist');
        Route::post('stylist/remove-favorite', 'FavoriteStylistController@removeFavoriteStylist');
        Route::get('stylist/get-favorites', 'FavoriteStylistController@getFavoriteStylists');
        Route::post('stylist/clear-all-favorites', 'FavoriteStylistController@clearFavoriteStylists');
        Route::get('suggest-favorites', 'FavoriteStylistController@suggestStylistsForFavorites');
        Route::get('popular-artist', 'FavoriteStylistController@getPopularStylists');

        // Overview
        Route::get('overview', 'ServiceProviderStatController@overview');

        // Voucher Routes
        Route::post('voucher/issue', 'VoucherController@issueVoucher');
        Route::get('voucher/user', 'VoucherController@getUserVouchers');
        Route::post('voucher/redeem', 'VoucherController@redeemVoucher');
        Route::post('voucher/validate', 'VoucherController@validateVoucher');

        //wallet routes
        Route::post('wallet/withdraw', 'WalletController@withdraw');
        Route::post('wallet/openDispute', 'WalletController@openWalletDispute');
        Route::post('wallet/createWallet', 'WalletController@createWallet');
        Route::get('wallet/getWallet', 'WalletController@getWallet');
        Route::get('wallet/getTransactionsHistory', 'WalletController@getTransactionsHistory');
        Route::get('wallet/getTransactionFee', 'WalletController@getTransactionFee');
        Route::post('wallet/setPin', 'WalletController@setPin');
        Route::post('wallet/validatePin', 'WalletController@validatePin');

        Route::get('user/getNotifications', 'AccountController@getNotifications');
        Route::get('user/markNotificationsAsRead', 'AccountController@markNotificationsAsRead');
        Route::post('appointment', 'AppointmentController@store');

    });
    Route::post('notifications/create', 'NotificationController@store');
    Route::post('vfd/webhook', 'WebhookController@VFDWebhook');
});