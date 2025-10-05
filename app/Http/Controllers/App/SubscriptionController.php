<?php

namespace App\Http\Controllers\App;

use DateTime;
use Exception;
use Carbon\Carbon;
use App\Models\User;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Str;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Models\BillingHistory;
use App\Models\BillingInvoice;
use App\Models\PendingPayment;
use App\Models\UserSubscription;
use App\Repositories\PawaPayUtils;
use App\Repositories\PesaPalUtils;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\SubscriptionResource;
use Illuminate\Support\Facades\Log as Logger;
use App\Http\Resources\BillingHistoryResource;
use App\Http\Resources\UserSubscriptionResource;

class SubscriptionController extends Controller
{

  protected function updatePendingPayment($paymentRef, $paymentStatus, $user = null)
  {

    $tranx = PendingPayment::where('reference', $paymentRef)->first();
    if (!is_null($tranx)) {
      $tranx->update([
        'payment_date' => Carbon::now(),
        'payment_gateway_status' => $paymentStatus,
        'payment_status' => 1
      ]);
      $tranx_type = $tranx->payment_type;
      if (!is_null($user) && $tranx_type == 'Sub_Payment') {
        $subscription = Subscription::where('price', $tranx->amount)->first();
        if (!is_null($subscription)) {
          $this->activateSubscription($subscription, $user->id);
        }
      }
      return $tranx;
    }
    return [];
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


    return;
  }

  protected function activateSubscription($subscription, $userID)
  {
    //check if subscription is free or has trial period
    if (!is_null($subscription)) {
      $expires_at = new DateTime('+' . $subscription->invoice_period . ' ' . $subscription->invoice_interval);
      $sub = UserSubscription::create(
        [
          'user_id' => $userID,
          'subscription_id' => $subscription->id,
          'active' => 1,
          'expires_at' => $expires_at,
        ]
      );
      return $sub;
    } else {
      //pay for subscription
      return false;
    }
    return false;
  }

  protected function createPendingSubscription($merchant, $subscription, $paymentReference, $amount, $currency = "GBP")
  {
    //subscription is pending
    $transactionId = @date('Ymdhis');
    $expires_at = new DateTime('+' . $subscription->invoice_period . ' ' . $subscription->invoice_interval);
    $merchant_sub = UserSubscription::create(
      [
        'user_id' => $merchant->id,
        'subscription_id' => $subscription->id,
        'ext_trans_id' => $paymentReference,
        'internal_trans_id' => $transactionId,
        'status' => 'PENDING',
        'active' => 0,
        'expires_at' => $expires_at,
      ]
    );
    $invoice = BillingHistory::create([
      'merchant_id' => $merchant->id,
      'user_subscription_id' => $merchant_sub->id,
      'invoice_number' => Str::random(12),
      'billing_date' => Carbon::now(),
      'status' => 0,
      'currency' => $currency,
      'amount' => $amount,
      'plan' => $subscription->plan,
      'next_billing_date' => $merchant_sub->expires_at,
    ]);
    return $merchant_sub;
  }

  public function subscriptions()
  {
    try {
      $subscriptions = Subscription::all();
      $subscriptions = $this->addMeta(SubscriptionResource::collection($subscriptions));
      return response()->json(compact('subscriptions'), 200);
    } catch (Exception $e) {
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
    }
  }

  public function mySubscriptions(Request $request)
  {
    $user_id = $this->getAuthID($request);
    try {
      $subscription = User::find($user_id)->activeSubscriptions;
      $subscription = $this->addMeta(UserSubscriptionResource::collection($subscription));
      return response()->json(compact('subscription'), 200);
    } catch (Exception $e) {
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
    }
  }

  public function subscribe(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'subscription_id' => 'integer|required',
      'redirectUrl' => 'url|required',
      'cancelUrl' => 'url|required',
      'payWith' => 'string|required|in:stripe',
      'coupon' => 'string|nullable|max:20',
    ]);


    if ($validator->fails()) {
      return $this->validationError($validator);
    }

    $user = $this->getAuthUser($request);

    $merchant = User::find($user->id);

    if (is_null($merchant)) {
      return $this->errorResponse('Merchant not found', 404);
    }

    if ($merchant->hasActiveSubscriptionAndOnAutoRenewal()) {
      return $this->errorResponse('Merchant already has an active subscription', 403);
    }

    try {
      $couponID = null;
      $paymentProvider = $request->input('payWith');
      $redirectUrl = $request->input('redirectUrl');
      $cancelUrl = $request->input('cancelUrl');
      $subscription = Subscription::find($request->input('subscription_id'));
      if (is_null($subscription)) {
        return $this->errorResponse('Subscription plan not found', 404);
      }

      if ($request->filled('coupon')) {
        $coupon = $request->input('coupon');
        $stripeCoupon = $this->stripeUtils->retrieveCoupon($coupon);
        if ($stripeCoupon['error'] == 0) {
          if (empty($stripeCoupon['coupon']['data'])) {
            return $this->errorResponse('Invalid coupon supplied', 400);
          } else {
            $couponID = $stripeCoupon['coupon']['data'][0]['coupon']['id'];
          }
        } else {
          return $this->errorResponse('Error validating coupon', 500);
        }
      }

      $currency = $subscription->currency;
      $amount = $subscription->price;

      if ($paymentProvider == "stripe") {
        $subscriptionPayment = $this->stripeUtils->generateSubscriptionPayLink($user, $subscription->stripe_id, $redirectUrl, $cancelUrl, $couponID);
        if ($subscriptionPayment['error'] == 0) {
          $paymentRef = $subscriptionPayment['paymentRef'];
          $sessionID = $subscriptionPayment['session_id'];
          $this->createPendingPayment('subscription', $amount, $currency, $paymentRef, $user->id, $paymentProvider);
          $pendingSub = $this->createPendingSubscription($merchant, $subscription, $paymentRef, $amount, $currency);
          if (!is_null($pendingSub)) {
            $pendingSub->update([
              'session' => $sessionID,
            ]);
          }
        }
      }
      return response()->json(compact('subscriptionPayment'), 201);
    } catch (Exception $e) {
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
    }
  }

  public function verifySubscription(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'subscription_id' => 'integer|required',
      'paymentReference' => 'string|required',
      'sessionId' => 'string|required',
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }
    $paymentReference = $request->paymentReference;
    $user = $this->getAuthUser($request);

    $merchant = User::find($user->id);

    if (is_null($merchant)) {
      return $this->errorResponse('Merchant not found', 404);
    }

    $merchantSub = UserSubscription::where(['user_id' => $merchant->id, 'ext_trans_id' => $paymentReference])->first();
    if (is_null($merchantSub)) {
      return $this->errorResponse('Subscription record not found!', 404);
    }

    // if ($merchantSub->active) {
    //   return $this->errorResponse('Sorry! Duplicate transaction!', 403);
    // }

    $subscription_id = $request->subscription_id;

    $subscription = Subscription::find($subscription_id);

    if (is_null($subscription)) {
      return $this->errorResponse('Invalid subscription!', 400);
    }

    try {
      if ($request->filled('sessionId')) {
        $sessionID = $request->sessionId;
        $session = $this->stripeUtils->getSessionDetails($sessionID);
        //return $session;
        if ($session['error'] == 0) {
          $paymentStatus = $session['session']->payment_status;
          $sessionStatus = $session['session']->status;
          $customerId = $session['session']->customer;
          $invoiceId = $session['session']->invoice;
          $subscriptionId = $session['session']->subscription;
          $ref = $session['session']->client_reference_id;
          $amount = $session['session']->amount_total;
          $merchant->update(['authorizationCode' => $customerId]);
          $paymentDetail = $this->updatePendingPayment($ref, $paymentStatus);
          if ($paymentStatus == 'paid') {
            //activate subscription
            $merchantSub->update([
              'status' => 'SUCCESSFUL_TXN',
              'active' => 1,
              'customer' => $customerId,
              'invoice' => $invoiceId,
              'subscription' => $subscriptionId,
            ]);
            $invoice = BillingHistory::where(['merchant_id' => $merchant->id, 'user_subscription_id' => $merchantSub->id])->first();
            if (!is_null($invoice)) {
              $invoice->update(['status' => 1]);
            }
            return $this->successResponse('Your subscription plan has been activated.', 201, compact('merchantSub'));
          } else {
            return $this->successResponse('Subscription payment not successful.', 400, compact('paymentStatus'));
          }
        } else {
          return $this->errorResponse('Payment verification not successful', 400);
        }
      }
    } catch (Exception $e) {
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
    }
  }

  public function getBillingHistory(Request $request)
  {
    try {
      $user = $this->getAuthUser($request);

      $merchant = User::find($user->id);

      if (is_null($merchant)) {
        return $this->errorResponse('Merchant not found', 404);
      }
      $billing_history = $merchant->billingHistory()->paginate($this->perPage);
      $billing_history = $this->addMeta(BillingHistoryResource::collection($billing_history));
      return response()->json(compact('billing_history'), 200);
    } catch (Exception $e) {
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
    }
  }
}
