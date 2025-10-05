<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\SocialLink;
use App\Models\SocialProvider;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Rappasoft\LaravelAuthenticationLog\Traits\AuthenticationLoggable;

class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    use Notifiable;
    use HasFactory;
    use AuthenticationLoggable;

    protected $fillable = [
        'name',
        'firstName',
        'lastName',
        'email',
        'password',
        'accountstatus',
        'account_type',
        'user_type',
        'specialization',
        // 'google2fa_enabled',
        // 'google2fa_secret',
        'profile_image_link',
        'cover_image_link',
        'phone',
        //'sms2fa_enabled',
        'sms_otp',
        'merchant_code',
        'referral_code',
        'bank',
        'bankcode',
        'accountno',
        'accountname',
        'wallet_id',
        'token',
        'login_otp',
        'login_otp_expires_at',
        'stripe_account_id',
        'bio',
        'availability',
        'booking_preferences',
        'payment_preferences',
        'rewards',
        'booking_limits',
        'withdrawal_schedule'

    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        //'google2fa_secret',
        'sms_otp',
        'token',
        'login_otp'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        // 'google2fa_enabled' => 'boolean',
        // 'sms2fa_enabled' => 'boolean',
        'email_verified' => 'boolean',
        'availability' => 'array',
        'booking_preferences' => 'array',
        'payment_preferences' => 'array',
        'rewards' => 'array',
        'booking_limits' => 'array'
    ];


    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }


     public function scopeGuests($query)
    {
        return $query->where('user_type', 'guest');
    }

    public function scopeRegular($query)
    {
        return $query->where('user_type', 'regular');
    }
    

    function socialProviders()
    {
        return $this->hasMany(SocialProvider::class);
    }

    public function markEmailAsVerified()
    {
        return $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
            'email_verified' => 1,
        ])->save();
    }
    public function hasVerifiedEmail()
    {
        return !is_null($this->email_verified_at) && $this->email_verified;
    }

    /**
     * Encrypt the user's 2fa secret.
     *
     * @param  string  $value
     * @return string
     */
    public function setGoogle2faSecretAttribute($value)
    {
        $this->attributes['google2fa_secret'] = !is_null($value) ? encrypt($value) : NULL;
    }

    /**
     * Decrypt the user's 2fa secret.
     *
     * @param  string  $value
     * @return string
     */
    public function getGoogle2faSecretAttribute($value)
    {
        return decrypt($value);
    }

    function interests()
    {
        return $this->belongsToMany(Interest::class, 'user_interests');
    }

    function userInterests()
    {
        return $this->hasMany(UserInterest::class, 'user_id', 'id');
    }

    function subscriptions()
    {
        return $this->belongsToMany(Subscription::class, 'user_subscriptions', 'user_id', 'subscription_id')
            ->as('subscription')
            ->withPivot('active', 'expires_at')
            ->withTimestamps();
    }

    function activeSubscriptions()
    {
        return $this->belongsToMany(Subscription::class, 'user_subscriptions', 'user_id', 'subscription_id')
            ->as('subscription')
            ->withPivot('active', 'expires_at')
            ->withTimestamps()
            ->wherePivot('active', 1)
            ->wherePivot('expires_at', '>', now());
    }

    function subscriptionDueIn1Day()
    {
        return $this->belongsToMany(Subscription::class, 'user_subscriptions', 'user_id', 'subscription_id')
            ->as('subscription')
            ->withPivot('active', 'expires_at')
            ->withTimestamps()
            ->wherePivot('active', 1)
            ->wherePivot('expires_at', '>=', Carbon::now()->addDays(1)->startOfDay())
            ->wherePivot('expires_at', '<=', Carbon::now()->addDays(1)->endOfDay());
    }

    function subscriptionDueIn2Days()
    {
        return $this->belongsToMany(Subscription::class, 'user_subscriptions', 'user_id', 'subscription_id')
            ->as('subscription')
            ->withPivot('active', 'expires_at')
            ->withTimestamps()
            ->wherePivot('active', 1)
            ->wherePivot('expires_at', '>=', Carbon::now()->addDays(2)->startOfDay())
            ->wherePivot('expires_at', '<=', Carbon::now()->addDays(2)->endOfDay());
    }

    function subscriptionDueIn3Days()
    {
        return $this->belongsToMany(Subscription::class, 'user_subscriptions', 'user_id', 'subscription_id')
            ->as('subscription')
            ->withPivot('active', 'expires_at')
            ->withTimestamps()
            ->wherePivot('active', 1)
            ->wherePivot('expires_at', '>=', Carbon::now()->addDays(3)->startOfDay())
            ->wherePivot('expires_at', '<=', Carbon::now()->addDays(3)->endOfDay());
    }

    function hasActiveSubscription()
    {
        return $this->activeSubscriptions()->count() > 0;
    }

    function expiredSubscriptions()
    {
        return $this->belongsToMany(Subscription::class, 'user_subscriptions', 'user_id', 'subscription_id')
            ->as('subscription')
            ->withPivot('active', 'expires_at')
            ->withTimestamps()
            ->wherePivot('active', 0)
            ->wherePivot('expires_at', '<=', now());
    }

    function hasActiveSubscriptionAndOnAutoRenewal()
    {
        return $this->belongsToMany(Subscription::class, 'user_subscriptions', 'user_id', 'subscription_id')
            ->as('subscription')
            ->withPivot('active', 'expires_at', 'auto_renew')
            ->withTimestamps()
            ->wherePivot('active', 1)
            ->wherePivot('auto_renew', 1)
            ->wherePivot('expires_at', '>', now())
            ->count() > 0;
    }

    function hasUsedFreeTrial()
    {
        return $this->belongsToMany(Subscription::class, 'user_subscriptions', 'user_id', 'subscription_id')
            ->where('price', 0)
            ->count() > 0;
    }

    function subscriptionStatus()
    {
        if ($this->paidSubscriptions()->count() > 0) {
            $status = 'Paid Subscription';
        } elseif ($this->freeTrialSubscriptions()->count() > 0) {
            $status = 'Free Trial';
        } else {
            $status = 'No Active Subscription';
        }
        return $status;
    }

    function paidSubscriptions()
    {
        return $this->belongsToMany(Subscription::class, 'user_subscriptions', 'user_id', 'subscription_id')
            ->as('subscription')
            ->where('price', '>', 0)
            ->withPivot('active', 'expires_at')
            ->withTimestamps()
            ->wherePivot('active', 1)
            ->wherePivot('expires_at', '>', now());
    }

    function freeTrialSubscriptions()
    {
        return $this->belongsToMany(Subscription::class, 'user_subscriptions', 'user_id', 'subscription_id')
            ->as('subscription')
            ->where('price', 0)
            ->withPivot('active', 'expires_at')
            ->withTimestamps()
            ->wherePivot('active', 1)
            ->wherePivot('expires_at', '>', now());
    }

    public function sales()
    {
        return $this->hasMany(Order::class, 'merchant_id')->where('payment_status', 1);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'merchant_id');
    }

    public function purchases()
    {
        return $this->hasMany(Order::class, 'buyer_id')->where('payment_status', 1);
    }

    public function pickup_address()
    {
        return $this->hasOne(PickupAddress::class, 'merchant_id', 'id');
    }

    public function store_address()
    {
        return $this->hasOne(StoreAddress::class, 'merchant_id', 'id');
    }

    public function deliveryAddress()
    {
        return $this->hasMany(Address::class, 'recipient', 'id')->oldest();
    }

    public function notificationSetting()
    {
        return $this->hasOne(NotificationSetting::class, 'user_id', 'id')
            ->select([
                "notify_new_order_via_email",
                "notify_new_order_via_sms",
                "notify_new_order_via_push_notification"
            ])
            ->withDefault([
                "notify_new_order_via_email" => true,
                "notify_new_order_via_sms" => false,
                "notify_new_order_via_push_notification" => false
            ]);
    }


    public function store()
    {
        return $this->hasOne(Store::class, 'merchant_id')->latest();
    }

    public function userStores()
    {
        return $this->hasMany(UserStore::class, 'user_id');
    }

    public function billingInvoices()
    {
        return $this->hasMany(BillingInvoice::class, 'merchant_id')->latest('billing_date');
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function products()
    {
        return $this->hasManyThrough(Product::class, Store::class, 'merchant_id', 'store_id', 'id', 'id');
    }

    public function hasStore()
    {
        return $this->store()->count() > 0;
    }

    public function hasProduct()
    {
        return $this->products()->count() > 0;
    }

    public function formatted_login_otp_expires_at()
    {
        return Carbon::parse($this->login_otp_expires_at)->format("D jS \of M Y h:i:s A");
    }
    // public function setPhoneAttribute($value)
    // {
    //     if (starts_with($value, "0")) {
    //         $phone = substr_replace($value, "256", 0, 1);
    //         $this->attributes['phone'] = $phone;
    //     } else {
    //         $this->attributes['phone'] = $value;
    //     }
    // }

    public function hasUsedCoupon($couponId)
    {
        return CouponUsage::where(['user_id' => $this->id, 'coupon_id' => $couponId])->count() > 0;
    }

    public function purchaseTrnx()
    {
        return $this->hasManyThrough(OrderItem::class, Order::class, 'buyer_id', 'order_id')->where('payment_status', 1);
    }

    public function billingHistory()
    {
        return $this->hasMany(BillingHistory::class, 'merchant_id')->latest('billing_date');
    }

    public function orderTrnx()
    {
        return $this->hasManyThrough(OrderItem::class, Order::class, 'merchant_id', 'order_id')->where('payment_status', 1);
    }

    public function customers()
    {
        return $this->hasManyThrough(
            User::class,      // Final model we want (User)
            Order::class,     // Intermediate model (Order)
            'merchant_id',    // Foreign key on Order table for Merchant
            'id',             // Local key on User table (assuming User primary key is 'id')
            'id',             // Local key on Merchant table (assuming Merchant primary key is 'id')
            'buyer_id'        // Foreign key on Order table that links to User
        )->distinct(); // Ensures we get unique customers
    }

    public function reviews()
    {
        return $this->hasManyThrough(
            ProductRating::class, // The final model we want (ProductRating)
            Product::class,       // The intermediate model (Product)
            'merchant_id',        // Foreign key on Product table for the Merchant
            'product_id',         // Foreign key on ProductRating table that links to Product
            'id',                 // Local key on Merchant (User) table (assuming primary key is 'id')
            'id'                  // Local key on Product table (assuming primary key is 'id')
        );
    }

    public function reviewsMade()
    {
        return $this->hasMany(Review::class, 'user_id');
    }


    public function reviewsReceived()
    {
        return $this->hasMany(Review::class, 'merchant_id');
    }

    public function bank_details()
    {
        return $this->hasOne(UserBankDetails::class, 'user_id', 'id');
    }

    public function services()
    {
        return $this->hasMany(Service::class, 'merchant_id');
    }


    public function lowestPricedService()
    {
        return $this->services()->orderBy('price', 'asc')->limit(1);
    }

    public function rentedStores()
    {
        return $this->hasMany(UserStore::class, 'user_id');
    }


    public function getCurrentStore()
    {
        $userStore = $this->userStores()->where('current', true)->first();
        return $userStore ? $userStore->store : null;
    }

    public function rentedBooths()
    {
        return $this->hasMany(BoothRental::class, 'user_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'customer_id')->where("payment_status", 1);
    }

    public function bookings()
    {
        return $this->hasMany(Appointment::class, 'merchant_id')->where("payment_status", 1);
    }

    public function receivedVouchers()
    {
        return $this->hasMany(Voucher::class, 'user_id');
    }

    public function issuedVouchers()
    {
        return $this->hasMany(Voucher::class, 'stylist_id');
    }

    public function favoriteStylists()
    {
        return $this->hasMany(FavoriteStylist::class, 'user_id');
    }

    public function favoritedByUsers()
    {
        return $this->hasMany(FavoriteStylist::class, 'stylist_id');
    }

    public function workdoneImages()
    {
        return $this->hasMany(StoreWorkdoneImage::class, 'user_id');
    }



}