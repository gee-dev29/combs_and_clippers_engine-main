<?php

namespace App\Repositories;

use Carbon\Carbon;
use App\Mail\Support;
use App\Mail\Welcome;
use App\Mail\NewOrder;
use App\Mail\LoginCode;
use App\Mail\PickupReady;
use App\Mail\BoothRentDue;
use App\Mail\JoinWaitlist;
use App\Mail\SendPassword;
use App\Mail\AdminNewOrder;
use App\Mail\DisputeRaised;
use App\Mail\PasswordReset;
use App\Mail\AccountCreated;
use App\Mail\OrderCancelled;
use App\Mail\ProductRequest;
use App\Mail\AccountCreation;
use App\Mail\DisputeAccepted;
use App\Mail\SubscriptionDue;
use App\Mail\InviteToWaitlist;
use App\Mail\EmailVerification;
use App\Mail\OrderConfirmation;
use App\Mail\OrderStatusChange;
use App\Mail\PhoneVerification;
use App\Mail\SendAdminPassword;
use App\Mail\AdminAccountUpdate;
use App\Mail\BuyerRaisedDispute;
use App\Mail\SubscriptionActive;
use App\Mail\SellerAcceptedDispute;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendDisputeAcceptedToAdmin;

class Mailer
{
    public function sendOrderConfirmationEmail($order)
    {
        Mail::to($order->buyer->email)->queue(new OrderConfirmation($order));
    }

    public function sendPickupReadyEmail($order)
    {
        $now = Carbon::now();
        $maxdeliverydate = Carbon::parse($order->maxdeliverydate);
        $diff = $now->diffInDays($maxdeliverydate);
        $days = $diff == 0 ? 1 : $diff;
        Mail::to($order->buyer->email)->later(now()->addDays($days), new PickupReady($order));
    }

    public function sendOrderStatusChangeEmail($order, $status)
    {
        Mail::to($order->buyer->email)->queue(new OrderStatusChange($order, $status));
    }

    public function sendNewOrderEmail($order)
    {
        Mail::to($order->seller->email)->queue(new NewOrder($order));
        if (appIsOnProduction()) {
            Mail::to(['Mukisa@mudala.ug', 'jaliah@mudala.ug', 'kenneth@mudala.ug'])->queue(new AdminNewOrder($order));
        }
    }

    public function sendOrderCanceledEmail($order)
    {
        Mail::to($order->seller->email)->queue(new OrderCancelled($order->seller->name, $order->seller->email, $order));
        Mail::to($order->buyer->email)->queue(new OrderCancelled($order->buyer->name, $order->buyer->email, $order));
    }

    public function sendDisputeEmail($order, $dispute)
    {
        Mail::to($order->seller->email)->queue(new BuyerRaisedDispute($order, $dispute));
        Mail::to($order->buyer->email)->queue(new DisputeRaised($order, $dispute));
    }

    public function sendDisputeAcceptedEmail($order, $dispute)
    {
        $support_mail = cc('support_mail');
        Mail::to($support_mail)->queue(new SendDisputeAcceptedToAdmin($order, $dispute));
        Mail::to($order->seller->email)->queue(new DisputeAccepted($order, $dispute));
        Mail::to($order->buyer->email)->queue(new SellerAcceptedDispute($order, $dispute));
    }

    public function sendAccountCreationEmail($user)
    {
        Mail::to($user)->queue(new AccountCreation($user));
    }

    public function sendAccountCreatedEmail($user)
    {
        $support_mail = cc('support_mail');
        if (appIsOnProduction()) {
            Mail::to($support_mail)->queue(new AccountCreated($user));
        }
    }

    public function sendVerificationEmail($user)
    {
        Mail::to($user)->queue(new EmailVerification($user));
    }

    public function sendPasswordEmail($user, $password)
    {
        Mail::to($user)->queue(new SendPassword($user, $password));
    }

    public function sendAdminPasswordEmail($admin, $password)
    {
        Mail::to($admin)->queue(new SendAdminPassword($admin, $password));
    }

    public function sendSubscriptionActiveEmail($user, $invoice)
    {
        Mail::to($user)->queue(new SubscriptionActive($user, $invoice));
    }

    public function sendSubscriptionDueEmail($user, $days)
    {
        Mail::to($user)->queue(new SubscriptionDue($user, $days));
    }

    public function sendBoothRentReminder($user, $days)
    {
        Mail::to($user)->queue(new BoothRentDue($user, $days));
    }

    public function sendWelcomeEmail($user)
    {
        Mail::to($user)->later(now()->addHours(2), new Welcome($user));
    }

    public function sendLoginCodeEmail($user)
    {
        Mail::to($user)->queue(new LoginCode($user));
    }

    public function sendProductRequestEmail($user, $productRequest)
    {
        Mail::to($user)->queue(new ProductRequest($user, $productRequest));
    }

    public function sendSupportEmail($name, $email, $subject, $message)
    {
        $support_mail = cc('support_mail');
        Mail::to($support_mail)->queue(new Support($name, $email, $subject, $message));
    }

    public function sendJoinWaitlistEmail($user)
    {
        Mail::to($user)->queue(new JoinWaitlist($user));
    }

    public function sendInviteToJoinWaitlistEmail($email, $referrer, $referral_code)
    {
        Mail::to($email)->queue(new InviteToWaitlist($email, $referrer, $referral_code));
    }

    public function sendPasswordResetEmail($user, $token)
    {
        Mail::to($user)->queue(new PasswordReset($user, $token));
    }

    public function sendPhoneVerificationOTP($user, $otp)
    {
        Mail::to($user->email)->queue(new PhoneVerification($user, $otp));
    }

    public function sendAdminAccountUpdateEmail($user, $changes, $updateType)
    {
        Mail::to($user)->queue(new AdminAccountUpdate($user, $changes, $updateType));
    }
}