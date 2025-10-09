<?php

namespace App\Http\Controllers;

use DateTime;
use Exception;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\Store;
use Ramsey\Uuid\Uuid;
use App\Models\Refund;
use App\Models\Wallet;
use App\Models\Product;
use App\Models\Activity;
use App\Repositories\Sms;
use App\Models\Withdrawal;
use App\Models\CouponUsage;
use App\Models\Transaction;
use Illuminate\Support\Str;
use App\Repositories\Mailer;
use Illuminate\Http\Request;
use App\Models\BillingInvoice;
use App\Models\PendingPayment;
use App\Repositories\MomoUtils;
use App\Models\UserSubscription;
use App\Repositories\ImageUtils;
use App\Repositories\SendyUtils;
use App\Repositories\ShiipUtils;
use App\Repositories\NotificationUtils;
use App\Models\UserBoothProgress;
use App\Repositories\StripeUtils;
use App\Jobs\RequestOrderDelivery;
use App\Jobs\RequestProductPickup;
use App\Models\TransactionHistory;
use App\Repositories\PawaPayUtils;
use App\Repositories\PaymentUtils;
use App\Repositories\VFDUtils;
use App\Services\MomoDisbursement;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\InternalTransaction;
use App\Repositories\PaystackUtils;
use App\Models\UserGrowServiceProgress;
use App\Repositories\WhatsappMessenger;
use Tymon\JWTAuth\Exceptions\JWTException;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    // Deprecated traits removed - they're now built into the base controller in Laravel 11

    /**
     * The SendyUtils instance.
     *
     * @var SendyUtils
     */
    protected $Sendy;

    /**
     * The WhatsappMessenger instance.
     *
     * @var WhatsappMessenger
     */
    protected $WhatsappMessenger;

    /**
     * The MomoUtils instance.
     *
     * @var MomoUtils
     */
    protected $Momo;

    /**
     * The Mailer instance.
     *
     * @var Mailer
     */
    protected $Mailer;

    /**
     * The ImageUtils instance.
     *
     * @var ImageUtils
     */
    protected $imageUtil;

    /**
     * The VFDUtils instance.
     *
     * @var VFDUtils
     */
    protected $vfdUtil;

    /**
     * The ShiipUtils instance.
     *
     * @var ShiipUtils
     */
    protected $shiipUtils;

    /**
     * The StripeUtils instance.
     *
     * @var StripeUtils
     */
    protected $stripeUtils;

    /**
     * The PaystackUtils instance.
     *
     * @var PaystackUtils
     */
    protected $paystackUtils;

    /**
     * The NotificationUtils instance.
     *
     * @var NotificationUtils
     */
    protected $notifyUtils;

    /**
     * The pagination per page limit.
     *
     * @var int
     */
    protected $perPage;

    /**
     * The payment provider.
     *
     * @var string
     */
    protected $payment_provider;

    /**
     * The country code.
     *
     * @var string
     */
    protected $country;

    /**
     * The currency code.
     *
     * @var string
     */
    protected $currency;

    public function __construct(ImageUtils $imageUtil, VFDUtils $vfdUtil, ShiipUtils $shiipUtils, WhatsappMessenger $messenger, MomoUtils $momoUtils, Mailer $mailer, SendyUtils $sendyUtils, StripeUtils $stripeUtils, PaystackUtils $paystackUtils, NotificationUtils $notifyUtils)
    {
        $this->perPage = 12;
        $this->imageUtil = $imageUtil;
        $this->vfdUtil = $vfdUtil;
        $this->shiipUtils = $shiipUtils;
        $this->Sendy = $sendyUtils;
        $this->WhatsappMessenger = $messenger;
        $this->Momo = $momoUtils;
        $this->payment_provider = "stripe";
        $this->country = "Nigeria";
        $this->currency = "NGN";
        $this->Mailer = $mailer;
        $this->stripeUtils = $stripeUtils;
        $this->paystackUtils = $paystackUtils;
        $this->notifyUtils = $notifyUtils;
    }

    protected function addMeta($object)
    {
        return $object->response()->getData(true);
    }

    protected function startsWith($haystack, $needle)
    {
        $length = strlen($needle);
        return substr($haystack, 0, $length) === $needle;
    }

    protected function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if (!$length) {
            return true;
        }
        return substr($haystack, -$length) === $needle;
    }

    protected function getAuthUser(Request $request)
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(["ResponseStatus" => "Unsuccessful", 'Detail' => 'User not found', "ResponseCode" => 400], 400);
            }

            return $user;
        } catch (TokenExpiredException $e) {
            return response()->json(["ResponseStatus" => "Unsuccessful", 'Detail' => 'Expired Token', "ResponseCode" => $e->getStatusCode()], $e->getStatusCode());
        } catch (TokenInvalidException $e) {
            return response()->json(["ResponseStatus" => "Unsuccessful", 'Detail' => 'Invalid token', "ResponseCode" => $e->getStatusCode()], $e->getStatusCode());
        } catch (JWTException $e) {
            return response()->json(["ResponseStatus" => "Unsuccessful", 'Detail' => 'Token not provided', "ResponseCode" => $e->getStatusCode()], $e->getStatusCode());
        }
        return $user;
    }

    protected function getAuthID(Request $request)
    {
        $user = $this->getAuthUser($request);
        $user_id = $user->id;
        return $user_id;
    }

    protected function getAuthEmail(Request $request)
    {
        $user = $this->getAuthUser($request);
        $user_email = $user->email;
        return $user_email;
    }

    protected function saveActivity($model, $model_uid, $merchant_id, $buyer_id, $description, $beforeAction = NULL, $afterAction = NULL)
    {
        try {
            if (!$beforeAction) {
                $beforeAction = [];
            }

            if (!$afterAction) {
                $afterAction = [];
            }

            $routeArray = app('request')->route()->getAction();
            $controllerAction = class_basename($routeArray['controller']);
            list($controller, $action) = explode('@', $controllerAction);

            $data = [
                'model' => $model,
                'model_uid' => $model_uid,
                'merchant_id' => $merchant_id,
                'buyer_id' => $buyer_id,
                'description' => $description,
                'controller' => $controller,
                'action' => $action,
                'params' => (app('request')->query()) ? json_encode(app('request')->query()) : '',
                'before_action' => json_encode($beforeAction, JSON_PRETTY_PRINT),
                'after_action' => json_encode($afterAction, JSON_PRETTY_PRINT),
            ];

            $activity = Activity::create($data);
        } catch (Exception $e) {
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    protected function respondWithToken($token)
    {

        $expiry = auth('api')->factory()->getTTL();
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $expiry * 60
        ];
    }

    protected function creditWallet($wallet_id, $amount)
    {
        $wallet = Wallet::find($wallet_id);
        if (!is_null($wallet)) {
            $balance = $wallet->amount;
            //update wallet amount
            $wallet->update(['amount' => ($balance + $amount)]);
        }
    }

    protected function createInternalTransaction($order)
    {
        $transaction = InternalTransaction::create([
            'merchant_id' => $order->merchant_id,
            'customer_id' => $order->buyer_id,
            'order_id' => $order->id,
            'type' => 'credit',
            'transaction_ref' => $order->orderRef,
            'narration' => "Payment for order with reference {$order->orderRef}",
            'currency' => $order->currency,
            'amount' => $order->totalprice,
            'payment_status' => Transaction::SUCCESSFUL,
        ]);
    }


    protected function reportExceptionOnBugsnag(Exception $e)
    {
        Bugsnag::notifyException($e);
    }

    protected function reportErrorOnBugsnag($type, $message)
    {
        Bugsnag::notifyError($type, $message);
    }

    protected function initiateRefund($order)
    {
        $paymentProvider = $this->payment_provider;
        $app_name = env("APP_NAME");
        if ($paymentProvider == 'momo') {
            $refund = new MomoDisbursement();
            $transactionId = Uuid::uuid4()->toString();
            $amount = $order->totalprice + $order->shipping;
            $refundId = $refund->refund($order->paymentRef, $transactionId, $amount, "Refund of your order {$order->orderRef} on {$app_name}", "Refund of your order {$order->orderRef} on {$app_name}");

            //record the refund
            $orderRefund = Refund::create([
                'order_id' => $order->id,
                'transaction_ref' => $refundId,
                'currency' => $this->currency,
                'amount' => $amount,
                'status' => Transaction::PENDING,
            ]);
        } else if ($paymentProvider == 'pawapay') {
            $transactionId = Uuid::uuid4()->toString();
            $amount = $order->totalprice + $order->shipping;
            $pawapay = new PawaPayUtils();
            $refund = $pawapay->requestRefund($order->paymentRef, $transactionId);
            if ($refund['error'] == 0 && $refund['response']->status == 'ACCEPTED') {
                $refundId = $refund['response']->refundId;
                //record the refund
                $orderRefund = Refund::create([
                    'order_id' => $order->id,
                    'transaction_ref' => $refundId,
                    'currency' => $this->currency,
                    'amount' => $amount,
                    'status' => Transaction::PENDING,
                ]);
            }
        }
    }

    protected function initiateTransfer($merchant, $amount)
    {
        $paymentProvider = $this->payment_provider;
        if ($paymentProvider == 'momo') {
            $transfer = new MomoDisbursement();
            $transactionId = Uuid::uuid4()->toString();
            $msisdn = $merchant->phone;
            $transferId = $transfer->transfer($transactionId, $msisdn, $amount, 'Combs and Clippers Merchant Fund Withdrawal', 'Combs and Clippers Merchant Fund Withdrawal');
            //record the transaction
            $transaction = InternalTransaction::create([
                'merchant_id' => $merchant->id,
                'type' => 'debit',
                'transaction_ref' => $transferId,
                'narration' => 'Merchant withdrawal',
                'currency' => $this->currency,
                'amount' => $amount,
                'payment_status' => Transaction::PENDING,
            ]);
            //record the withdrawal
            $withdrawal = Withdrawal::create([
                'merchant_id' => $merchant->id,
                'transaction_ref' => $transferId,
                'currency' => $this->currency,
                'amount' => $amount,
                'status' => Transaction::PENDING,
            ]);

            $wallet = $merchant->wallet;
            $balance = $wallet->amount;
            //update wallet amount
            $wallet->update(['amount' => ($balance - $amount)]);
        } else if ($paymentProvider == 'pawapay') {
            $pawapay = new PawaPayUtils();
            $transactionId = Uuid::uuid4()->toString();
            $msisdn = $merchant->phone;
            $transfer = $pawapay->requestPayout($msisdn, $amount, "GBP", $transactionId, 'Combs and Clippers payout');
            if ($transfer['error'] == 0 && in_array($transfer['response']->status, ['ACCEPTED', 'ENQUEUED'])) {
                $transferId = $transfer['response']->payoutId;
                //record the transaction
                $transaction = InternalTransaction::create([
                    'merchant_id' => $merchant->id,
                    'type' => 'debit',
                    'transaction_ref' => $transferId,
                    'narration' => 'Merchant withdrawal',
                    'currency' => $this->currency,
                    'amount' => $amount,
                    'payment_status' => Transaction::PENDING,
                ]);
                //record the withdrawal
                $withdrawal = Withdrawal::create([
                    'merchant_id' => $merchant->id,
                    'transaction_ref' => $transferId,
                    'currency' => $this->currency,
                    'amount' => $amount,
                    'status' => Transaction::PENDING,
                ]);

                $wallet = $merchant->wallet;
                $balance = $wallet->amount;
                //update wallet amount
                $wallet->update(['amount' => ($balance - $amount)]);
            }
        }
    }

    protected function validationError($validator)
    {
        return response()->json(["ResponseCode" => 422, "ResponseStatus" => "Unsuccessful", "ResponseMessage" => implode(', ', $validator->messages()->all()), 'Detail' => $validator->errors()], 422);
    }

    protected function errorResponse($message, $code)
    {
        return response()->json(["ResponseCode" => $code, "ResponseStatus" => "Unsuccessful", "ResponseMessage" => $message, 'Detail' => $message], $code);
    }


    protected function successResponse($message, $code, ...$extraData)
    {
        // Convert the passed arguments into an associative array
        $extraDataArray = [];
        foreach ($extraData as $data) {
            if (is_array($data)) {
                $extraDataArray = array_merge($extraDataArray, $data);
            }
        }

        return response()->json(array_merge([
            "ResponseCode" => $code,
            "ResponseStatus" => "Successful",
            "ResponseMessage" => $message,
            "Detail" => $message,
        ], $extraDataArray), $code);
    }


    protected function sendShipment($order_id)
    {
        $order = Order::where('id', $order_id)->first();
        $merchant = $order->seller;
        if (!is_null($order) && $merchant->hasActiveSubscription()) {
            //request for order pickup from the Merchant by Sendy
            //RequestProductPickup::dispatchIf(cc('environment') == 'production', $order);

            //request for order delivery to the Buyer by Sendy
            //RequestOrderDelivery::dispatchIf(cc('environment') == 'production', $order);
        }
    }

    protected function updateProductQuantity($orderItems)
    {
        try {
            foreach ($orderItems as $item) {
                $product = Product::find($item->productID);
                $productQty = $product->quantity;
                $product->update([
                    'quantity' => $productQty - $item->quantity
                ]);
            }
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    protected function createTransaction($order, $customer, $merchant, $fulfill_days, $paymentRef)
    {
        $tranx = Transaction::create([
            'posting_date' => Carbon::now(),
            'transcode' => $paymentRef,
            'customer_email' => $customer->email,
            'merchant_email' => $merchant->email,
            'merchant_code' => $merchant->id,
            'description' => 'Payment for Order ID: ' . $order->id,
            'order_id' => $order->id,
            'amount' => $order->totalprice,
            'currency' => $this->currency,
            'startdate' => $order->created_at,
            'enddate' => $order->maxdeliverydate,
            'fulfill_days' => $fulfill_days . ' days',
            'trans_status' => 'Payment Initiated',
        ]);

        if ($tranx) {
            //create transaction History
            $trans_hist = TransactionHistory::create([
                'transcode' => $paymentRef,
                'customer_email' => $customer->email,
                'merchant_email' => $merchant->email,
                'trans_status' => 'Payment Initiated',
                'status_update_date' => Carbon::now(),
                'updatedby' => 'Customer'
            ]);
        }
    }

    protected function updateTransaction($paymentRef, $paymentStatus)
    {
        $tranx = Transaction::where('transcode', $paymentRef)->first();
        if (!is_null($tranx)) {
            $tranx->update([
                'payment_date' => Carbon::now(),
                'payment_status' => $paymentStatus,
                'trans_status' => $paymentStatus
            ]);

            //Update transaction History
            $trans_hist = TransactionHistory::create([
                'transcode' => $paymentRef,
                'customer_email' => $tranx->customer_email,
                'merchant_email' => $tranx->merchant_email,
                'trans_status' => 'Payment ' . $paymentStatus,
                'status_update_date' => Carbon::now(),
                'updatedby' => 'Customer'
            ]);
        }
    }

    protected function createPendingPayment($payment_type, $amount, $currency, $paymentRef, $initiated_by_id, $payment_gateway)
    {
        $tranx = PendingPayment::create([
            'payment_type' => $payment_type,
            'amount' => $amount,
            'reference' => $paymentRef,
            'initiated_by' => $initiated_by_id,
            'currency' => $currency,
            'payment_gateway' => $payment_gateway
        ]);
    }

    protected function updatePendingPayment($paymentRef, $paymentStatus)
    {
        $tranx = PendingPayment::where('reference', $paymentRef)->first();
        if (!is_null($tranx)) {
            $tranx->update([
                'payment_date' => Carbon::now(),
                'payment_gateway_status' => $paymentStatus,
                'payment_status' => 1
            ]);
        }
        return $tranx;
    }

    protected function processOrder($order)
    {
        $paymentStatus = Transaction::SUCCESSFUL;
        $paymentRef = $order->paymentRef;

        $this->updatePendingPayment($paymentRef, $paymentStatus);
        $this->updateTransaction($paymentRef, $paymentStatus);
        //----------------------------------------------------------------
        $count = count($order->orderItems);
        $order->update(['status' => Order::PAID, 'payment_status' => ORDER::PAYMENT_SUCCESSFUL]);
        if ($order->delivery_type == ORDER::TYPE_DELIVERY) {
            $this->sendShipment($order->id);
        } else {
            $this->Mailer->sendPickupReadyEmail($order);
        }

        $this->Mailer->sendOrderConfirmationEmail($order);
        $this->Mailer->sendNewOrderEmail($order);
        //send sms
        $app_name = env("APP_NAME");
        $buyerMessage = "Dear {$order->buyer->name}, the payment for your order {$order->orderRef} has been received. Thank you for choosing {$app_name}!";
        $sellerMessage = "Dear {$order->seller->name}, We come with great news!, An order with reference ID: {$order->orderRef} has just been placed on your {$app_name} store.";

        $this->updateProductQuantity($order->orderItems);
    }

    protected function processCancelation($order, $reason)
    {
        $this->initiateRefund($order);
        $order->update([
            'status' => Order::CANCELED,
            'cancellation_reason' => $reason,
        ]);
        //send email
        $this->Mailer->sendOrderCanceledEmail($order);
    }

    protected function recordCouponUsage($userId, $couponId)
    {
        CouponUsage::firstOrCreate(['user_id' => $userId, 'coupon_id' => $couponId], ['user_id' => $userId, 'coupon_id' => $couponId]);
    }

    protected function createSubscription($subscription, $merchant, $extTransID, $intTransID, $status, $active = 0)
    {
        $expires_at = new DateTime('+' . $subscription->invoice_period . ' ' . $subscription->invoice_interval);
        $merchant_sub = UserSubscription::create(
            [
                'user_id' => $merchant->id,
                'subscription_id' => $subscription->id,
                'ext_trans_id' => $extTransID,
                'internal_trans_id' => $intTransID,
                'status' => $status,
                'active' => $active,
                'expires_at' => $expires_at,
            ]
        );
        $invoice = BillingInvoice::create([
            'merchant_id' => $merchant->id,
            'invoice_number' => substr(Str::uuid(), 0, 20),
            'billing_date' => Carbon::now(),
            'status' => $active,
            'currency' => $subscription->currency,
            'amount' => $subscription->price,
            'plan' => $subscription->plan,
            'next_billing_date' => $merchant_sub->expires_at,
        ]);
        return $merchant_sub;
    }

    public function sendOTP($to, $msg)
    {
        $sms_provider = env("SMS_PROVIDER");
        if ($sms_provider == "termii") {
            return Sms::sendWithTermii($to, $msg);
        } elseif ($sms_provider == "sleengshort") {
            return Sms::sendWithSleengShort($to, $msg);
        } elseif ($sms_provider == "nuobjects") {
            return Sms::sendWithNuobject($to, $msg);
        } else {
            return Sms::sendWithTermii($to, $msg);
        }

    }

    // protected function calculateBoothProgress($userId, $storeId)
    // {

    //     $progress = UserBoothProgress::where('user_id', $userId)->where('store_id', $storeId)->first();

    //     if ($progress) {

    //         $fields = [
    //             'add_schedule_location',
    //             'setup_my_service',
    //             'setup_portfolio',
    //             'create_bio',
    //             'accept_payment',
    //         ];


    //         $completedSteps = 0;
    //         foreach ($fields as $field) {
    //             if ($progress->{$field} == 1) {
    //                 $completedSteps++;
    //             }
    //         }

    //         $totalSteps = count($fields);
    //         $progressPercentage = ($completedSteps / $totalSteps) * 100;

    //         return $progressPercentage;
    //     }

    //     return 0;
    // }

    // protected function calculateGrowProgress($userId, $storeId)
    // {

    //     $progress = UserGrowServiceProgress::where('user_id', $userId)->where('store_id', $storeId)->first();

    //     if ($progress) {

    //         $fields = [
    //             'create_profile_link',
    //             'setup_referal_reward',
    //             'setup_loyalty_reward',
    //             'schedule_protection',
    //         ];


    //         $completedSteps = 0;
    //         foreach ($fields as $field) {
    //             if ($progress->{$field} == 1) {
    //                 $completedSteps++;
    //             }
    //         }


    //         $totalSteps = count($fields);
    //         $progressPercentage = ($completedSteps / $totalSteps) * 100;

    //         return $progressPercentage;
    //     }

    //     return 0;
    // }



    // protected function updateBoothProgress($userId, $storeId, $column)
    // {
    //     $progress = UserBoothProgress::updateOrCreate(
    //         [
    //             "user_id" => $userId,
    //             "store_id" => $storeId
    //         ],
    //         [
    //             $column => (string) 1
    //         ]
    //     );

    //     return $progress;

    // }

    // protected function updateGrowServiceProgress($userId, $storeId, $column)
    // {

    //     $progress = UserGrowServiceProgress::updateOrCreate(
    //         [
    //             "user_id" => $userId,
    //             "store_id" => $storeId
    //         ],
    //         [
    //             $column => (string) 1
    //         ]
    //     );

    //     return $progress;

    // }


    protected function calculateBoothProgress($userId, $storeId = null)
    {
        $query = UserBoothProgress::where('user_id', $userId);

        if ($storeId !== null) {
            $query->where('store_id', $storeId);
        }

        $progress = $query->first();

        if ($progress) {
            $fields = [
                'add_schedule_location',
                'setup_my_service',
                'setup_portfolio',
                'create_bio',
                'accept_payment',
            ];

            $completedSteps = 0;
            foreach ($fields as $field) {
                if ($progress->{$field} == 1) {
                    $completedSteps++;
                }
            }

            $totalSteps = count($fields);
            return ($completedSteps / $totalSteps) * 100;
        }

        return 0;
    }

    protected function calculateGrowProgress($userId, $storeId = null)
    {
        $query = UserGrowServiceProgress::where('user_id', $userId);

        if ($storeId !== null) {
            $query->where('store_id', $storeId);
        }

        $progress = $query->first();

        if ($progress) {
            $fields = [
                'create_profile_link',
                'setup_referal_reward',
                'setup_loyalty_reward',
                'schedule_protection',
            ];

            $completedSteps = 0;
            foreach ($fields as $field) {
                if ($progress->{$field} == 1) {
                    $completedSteps++;
                }
            }

            $totalSteps = count($fields);
            return ($completedSteps / $totalSteps) * 100;
        }

        return 0;
    }

    protected function updateBoothProgress($userId, $storeId = null, $column)
    {
        $progress = UserBoothProgress::updateOrCreate(
            [
                "user_id" => $userId,
                "store_id" => $storeId,
            ],
            [
                $column => (string) 1
            ]
        );

        return $progress;
    }

    protected function updateGrowServiceProgress($userId, $storeId = null, $column)
    {
        $progress = UserGrowServiceProgress::updateOrCreate(
            [
                "user_id" => $userId,
                "store_id" => $storeId,
            ],
            [
                $column => (string) 1
            ]
        );

        return $progress;
    }



    protected function generateBoothCode($storeId, $boothId)
    {

        $randomString = strtoupper(Str::random(6));
        $data = $storeId . '-' . $boothId . '-' . $randomString;
        $encryptedCode = base64_encode($data);

        return $encryptedCode;
    }

    protected function decryptBoothCode($code)
    {

        $decodedData = base64_decode($code);
        [$storeId, $boothId, $randomPart] = explode('-', $decodedData);
        return ['store_id' => $storeId, 'booth_id' => $boothId];
    }




    protected function errorResponseWithData($message, $code, ...$extraData)
    {
        // Convert the passed arguments into an associative array
        $extraDataArray = [];
        foreach ($extraData as $data) {
            if (is_array($data)) {
                $extraDataArray = array_merge($extraDataArray, $data);
            }
        }

        return response()->json(array_merge([
            "ResponseCode" => $code,
            "ResponseStatus" => "Unsuccessful",
            "ResponseMessage" => $message,
            "Detail" => $message,
        ], $extraDataArray), $code);

    }

    protected function calculateNextPaymentDate($boothRental, $paymentDate)
    {
        $paymentDate = Carbon::parse($paymentDate);

        switch ($boothRental->payment_timeline) {
            case 'weekly':
                return $paymentDate->addWeek();
            case 'every two weeks':
                return $paymentDate->addWeeks(2);
            case 'twice a month':
                return $paymentDate->addDays(15);
            case 'monthly':
                return $paymentDate->addMonth();
            default:
                throw new Exception('Unsupported payment timeline');
        }
    }
}