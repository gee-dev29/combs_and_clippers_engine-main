<?php

namespace App\Repositories;

use \Exception;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Log as Logger;

class StripeUtils
{
    private $secret_key;
    private $public_key;

    public function __construct()
    {
        $this->secret_key = env("STRIPE_SECRET");
        $this->public_key = env("STRIPE_KEY");
    }

    public function createAccount($user, $type = 'standard', $country = 'GB')
    {
        try {
            $stripe = new \Stripe\StripeClient($this->secret_key);
            $data = [
                'type' => $type,
                'country' => $country,
                'email' => $user->email,
                'capabilities' => [
                    'card_payments' => ['requested' => true],
                    'transfers' => ['requested' => true],
                ],
                'business_type' => 'individual',
                'business_profile' => [
                    //'url' => 'https://example.com',
                    'name' => $user->store->store_name,
                ],
                'individual' => [
                    'email' => $user->email,
                    'first_name' => $user->firstName,
                    'last_name' => $user->lastName,
                    'phone' => $user->phone,
                ],
            ];
            Logger::info('Stripe create account payload', ["payload" => json_encode($data)]);
            $account = $stripe->accounts->create($data);
            return ['error' => 0, 'responseMessage' => 'Successful', 'responseDetails' => $account];
        } catch (Exception $e) {
            Logger::error('Stripe createAccount error - ', [$e->getMessage() . ' - ' . $e->__toString()]);
            return ['error' => 1, "responseStatus" => "Unsuccessful", "statusCode" => 500, 'Detail' => $e->getMessage(), "responseMessage" => 'Something went wrong'];
        }
    }

    public function createAccountLink($account_id, $refreshURL, $returnURL)
    {
        try {
            // Set your secret key. Remember to switch to your live secret key in production.
            // See your keys here: https://dashboard.stripe.com/apikeys
            $stripe = new \Stripe\StripeClient($this->secret_key);
            $data = [
                'account' => $account_id,
                'refresh_url' => $refreshURL,
                'return_url' => $returnURL,
                'type' => 'account_onboarding',
            ];
            Logger::info('Stripe create account link payload', ["payload" => json_encode($data)]);
            $link = $stripe->accountLinks->create($data);
            return ['error' => 0, 'responseMessage' => 'Successful', 'responseDetails' => $link];
        } catch (Exception $e) {
            Logger::error('Stripe createAccountLink error - ', [$e->getMessage() . ' - ' . $e->__toString()]);
            return ['error' => 1, "responseStatus" => "Unsuccessful", "statusCode" => 500, 'Detail' => $e->getMessage(), "responseMessage" => 'Something went wrong'];
        }
    }


    public function getAccountById($account_id)
    {
        try {
            $stripe = new \Stripe\StripeClient($this->secret_key);

            $account = $stripe->accounts->retrieve($account_id, []);
            return ['error' => 0, 'responseMessage' => 'Successful', 'responseDetails' => $account];
        } catch (Exception $e) {
            Logger::error('Stripe getAccountById error - ', [$e->getMessage() . ' - ' . $e->__toString()]);
            return ['error' => 1, "responseStatus" => "Unsuccessful", "statusCode" => 500, 'Detail' => $e->getMessage(), "responseMessage" => 'Something went wrong'];
        }
    }

    public function deleteAccount($account_id)
    {
        try {
            $stripe = new \Stripe\StripeClient($this->secret_key);

            $account = $stripe->accounts->delete($account_id, []);
            return ['error' => 0, 'responseMessage' => 'Successful', 'responseDetails' => $account];
        } catch (Exception $e) {
            Logger::error('Stripe deleteAccount error - ', [$e->getMessage() . ' - ' . $e->__toString()]);
            return ['error' => 1, "responseStatus" => "Unsuccessful", "statusCode" => 500, 'Detail' => $e->getMessage(), "responseMessage" => 'Something went wrong'];
        }
    }


    public function rejectAccount($account_id)
    {
        try {
            $stripe = new \Stripe\StripeClient($this->secret_key);

            $account = $stripe->accounts->reject($account_id, ['reason' => 'fraud']);
            return ['error' => 0, 'responseMessage' => 'Successful', 'responseDetails' => $account];
        } catch (Exception $e) {
            Logger::error('Stripe rejectAccount error - ', [$e->getMessage() . ' - ' . $e->__toString()]);
            return ['error' => 1, "responseStatus" => "Unsuccessful", "statusCode" => 500, 'Detail' => $e->getMessage(), "responseMessage" => 'Something went wrong'];
        }
    }

    public function generatePayLink($user, $items, $success_url, $cancel_url, $shipping = 0, $tax = 0)
    {
        try {
            $stripe = new \Stripe\StripeClient($this->secret_key);

            $line_items = [];

            foreach ($items as $item) {
                $l_item = [];
                $l_item['price_data']['product_data']['name'] = $item->productname ?? $item->product_name;
                $l_item['price_data']['product_data']['description'] = strip_tags($item->description);
                $l_item['price_data']['product_data']['images'][] = $item->image_url;
                $l_item['price_data']['currency'] = $item->productInfo->currency;
                $l_item['price_data']['unit_amount'] = $item->price * 100;
                $l_item['quantity'] = $item->quantity;

                $line_items[] = $l_item;
            }

            if ($tax > 0) {
                $l_item = [];
                $l_item['price_data']['product_data']['name'] = "Tax";
                $l_item['price_data']['product_data']['description'] = "Goods and services tax";
                $l_item['price_data']['currency'] = $items[0]->productInfo->currency;
                $l_item['price_data']['unit_amount'] = $tax * 100;
                $l_item['quantity'] = 1;
                $line_items[] = $l_item;
            }

            $paymentRef = "ORD" . unique_random_string();
            $checkout = $stripe->checkout->sessions->create([
                'client_reference_id' => $paymentRef,
                'customer_email' => $user->email,
                // 'submit_type' => 'donate',
                // 'billing_address_collection' => 'required',
                // 'shipping_address_collection' => [
                //     'allowed_countries' => ['US', 'CA'],
                // ],
                // 'line_items' => [
                //     [
                //         'price_data' => [
                //             'unit_amount' => 2000,
                //             'product_data' => ['name' => 'T-shirt'],
                //             'currency' => $items[0]->productInfo->currency,
                //         ],
                //         'quantity' => 1,
                //     ],
                // ],
                // 'automatic_tax' => [
                //     'enabled' => true,
                // ],
                'shipping_options' => [
                    [
                        'shipping_rate_data' => [
                            'type' => 'fixed_amount',
                            'fixed_amount' => [
                                'amount' => $shipping * 100,
                                'currency' => $items[0]->productInfo->currency,
                            ],
                            'display_name' => 'Standard shipping',
                            'delivery_estimate' => [
                                'minimum' => [
                                    'unit' => 'business_day',
                                    'value' => 2,
                                ],
                                'maximum' => [
                                    'unit' => 'business_day',
                                    'value' => 7,
                                ],
                            ],
                        ],
                    ]
                ],
                'line_items' => $line_items,
                'mode' => 'payment',
                'success_url' => $success_url . '?success=true&session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $cancel_url . '?canceled=true',
            ]);

            return ['error' => 0, 'responseMessage' => 'Successful', 'paymentRef' => $paymentRef, 'paymentUrl' => $checkout->url, 'session_id' => $checkout->id];
        } catch (Exception $e) {
            Logger::error('Stripe generatePayLink error - ', [$e->getMessage() . ' - ' . $e->__toString()]);
            return ['error' => 1, "responseStatus" => "Unsuccessful", "statusCode" => 500, 'Detail' => $e->getMessage(), "responseMessage" => 'Something went wrong'];
        }
    }

    public function generateMerchantPayLink($account_id, $user, $items, $success_url, $cancel_url, $shipping = 0, $tax = 0)
    {
        try {
            $stripe = new \Stripe\StripeClient($this->secret_key);

            $line_items = [];

            foreach ($items as $item) {
                $l_item = [];
                $l_item['price_data']['product_data']['name'] = $item->productname ?? $item->product_name;
                $l_item['price_data']['product_data']['description'] = strip_tags($item->description);
                $l_item['price_data']['product_data']['images'][] = $item->image_url;
                $l_item['price_data']['currency'] = $item->productInfo->currency;
                $l_item['price_data']['unit_amount'] = $item->price * 100;
                $l_item['quantity'] = $item->quantity;

                $line_items[] = $l_item;
            }

            if ($tax > 0) {
                $l_item = [];
                $l_item['price_data']['product_data']['name'] = "Tax";
                $l_item['price_data']['product_data']['description'] = "Goods and services tax";
                $l_item['price_data']['currency'] = $items[0]->productInfo->currency;
                $l_item['price_data']['unit_amount'] = $tax * 100;
                $l_item['quantity'] = 1;
                $line_items[] = $l_item;
            }

            $paymentRef = "ORD" . unique_random_string();
            $checkout = $stripe->checkout->sessions->create([
                'client_reference_id' => $paymentRef,
                'customer_email' => $user->email,
                // 'submit_type' => 'donate',
                // 'billing_address_collection' => 'required',
                // 'shipping_address_collection' => [
                //     'allowed_countries' => ['US', 'CA'],
                // ],
                // 'line_items' => [
                //     [
                //         'price_data' => [
                //             'unit_amount' => 2000,
                //             'product_data' => ['name' => 'T-shirt'],
                //             'currency' => $items[0]->productInfo->currency,
                //         ],
                //         'quantity' => 1,
                //     ],
                // ],
                // 'automatic_tax' => [
                //     'enabled' => true,
                // ],
                'shipping_options' => [
                    [
                        'shipping_rate_data' => [
                            'type' => 'fixed_amount',
                            'fixed_amount' => [
                                'amount' => $shipping * 100,
                                'currency' => $items[0]->productInfo->currency,
                            ],
                            'display_name' => 'Standard shipping',
                            'delivery_estimate' => [
                                'minimum' => [
                                    'unit' => 'business_day',
                                    'value' => 2,
                                ],
                                'maximum' => [
                                    'unit' => 'business_day',
                                    'value' => 7,
                                ],
                            ],
                        ],
                    ]
                ],
                'line_items' => $line_items,
                'mode' => 'payment',
                'success_url' => $success_url . '?success=true&session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $cancel_url . '?canceled=true',
                //'payment_intent_data' => ['application_fee_amount' => 123]

            ], ['stripe_account' => $account_id]);

            return ['error' => 0, 'responseMessage' => 'Successful', 'paymentRef' => $paymentRef, 'paymentUrl' => $checkout->url, 'session_id' => $checkout->id];
        } catch (Exception $e) {
            Logger::error('Stripe generateMerchantPayLink error - ', [$e->getMessage() . ' - ' . $e->__toString()]);
            return ['error' => 1, "responseStatus" => "Unsuccessful", "statusCode" => 500, 'Detail' => $e->getMessage(), "responseMessage" => 'Something went wrong'];
        }
    }

    public function generateSubscriptionPayLink($user, $priceID, $success_url, $cancel_url, $couponID = null)
    {
        try {
            $stripe = new \Stripe\StripeClient($this->secret_key);
            $paymentRef = "SUB" . unique_random_string();
            $payload = [
                'client_reference_id' => $paymentRef,
                'customer_email' => $user->email,
                'line_items' => [[
                    'price' => $priceID,
                    'quantity' => 1,
                ]],
                'mode' => 'subscription',
                'success_url' => $success_url . '?success=true&session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $cancel_url . '?canceled=true',
                // 'subscription_data' => [
                //     'trial_period_days' => 7,
                // ],
                // 'trial_settings' => [
                //     'end_behavior' => [
                //         'missing_payment_method' => 'pause'
                //     ]
                // ],
                // 'subscription_data' => [
                //     'billing_cycle_anchor' => 1672531200,
                // ],
                // 'automatic_tax' => [
                //     'enabled' => true,
                // ],
            ];
            if (!is_null($couponID)) {
                $payload['discounts'] = [['coupon' => $couponID]];
            }
            $checkout = $stripe->checkout->sessions->create($payload);

            return ['error' => 0, 'responseMessage' => 'Successful', 'paymentRef' => $paymentRef, 'paymentUrl' => $checkout->url, 'session_id' => $checkout->id];
        } catch (Exception $e) {
            Logger::error('Stripe generateSubscriptionPayLink error - ', [$e->getMessage() . ' - ' . $e->__toString()]);
            return ['error' => 1, "responseStatus" => "Unsuccessful", "statusCode" => 500, 'Detail' => $e->getMessage(), "responseMessage" => 'Something went wrong'];
        }
    }

    public function createCustomerPortal($sessionID, $return_url)
    {
        try {
            $stripe = new \Stripe\StripeClient($this->secret_key);
            $checkout_session = $stripe->checkout->sessions->retrieve($sessionID, []);

            $portal = $stripe->billingPortal->sessions->create([
                'customer' => $checkout_session->customer,
                'return_url' => $return_url,
            ]);

            return ['error' => 0, 'responseMessage' => 'Successful', 'portal' => $portal->url];
        } catch (Exception $e) {
            Logger::error('Stripe createCustomerPortal error - ', [$e->getMessage() . ' - ' . $e->__toString()]);
            return ['error' => 1, "responseStatus" => "Unsuccessful", "statusCode" => 500, 'Detail' => $e->getMessage(), "responseMessage" => 'Something went wrong'];
        }
    }

    public function getSessionDetails($sessionID)
    {
        try {
            $stripe = new \Stripe\StripeClient($this->secret_key);
            $session = $stripe->checkout->sessions->retrieve($sessionID, []);
            return ['error' => 0, 'responseMessage' => 'Successful', 'session' => $session];
        } catch (Exception $e) {
            Logger::error('Stripe getSessionDetails error - ', [$e->getMessage() . ' - ' . $e->__toString()]);
            return ['error' => 1, "responseStatus" => "Unsuccessful", "statusCode" => 500, 'Detail' => $e->getMessage(), "responseMessage" => 'Something went wrong'];
        }
    }

    public function getMerchantSessionDetails($account_id, $sessionID)
    {
        try {
            $stripe = new \Stripe\StripeClient($this->secret_key);
            $session = $stripe->checkout->sessions->retrieve($sessionID, [], ['stripe_account' => $account_id]);
            return ['error' => 0, 'responseMessage' => 'Successful', 'session' => $session];
        } catch (Exception $e) {
            Logger::error('Stripe getMerchantSessionDetails error - ', [$e->getMessage() . ' - ' . $e->__toString()]);
            return ['error' => 1, "responseStatus" => "Unsuccessful", "statusCode" => 500, 'Detail' => $e->getMessage(), "responseMessage" => 'Something went wrong'];
        }
    }

    public function retrieveCoupon($couponCode)
    {
        try {
            $stripe = new \Stripe\StripeClient($this->secret_key);
            $coupon = $stripe->promotionCodes->all(['code' => $couponCode]);
            return ['error' => 0, 'responseMessage' => 'Successful', 'coupon' => $coupon];
        } catch (Exception $e) {
            Logger::error('Stripe retrieveCoupon error - ', [$e->getMessage() . ' - ' . $e->__toString()]);
            return ['error' => 1, "responseStatus" => "Unsuccessful", "statusCode" => 500, 'Detail' => $e->getMessage(), "responseMessage" => 'Something went wrong'];
        }
    }

    public function retrieveCouponById($couponID)
    {
        try {
            $stripe = new \Stripe\StripeClient($this->secret_key);
            $coupon = $stripe->coupons->retrieve($couponID, []);
            return ['error' => 0, 'responseMessage' => 'Successful', 'coupon' => $coupon];
        } catch (Exception $e) {
            Logger::error('Stripe retrieveCouponById error - ', [$e->getMessage() . ' - ' . $e->__toString()]);
            return ['error' => 1, "responseStatus" => "Unsuccessful", "statusCode" => 500, 'Detail' => $e->getMessage(), "responseMessage" => 'Something went wrong'];
        }
    }

    public function generateServicePayLink($user, $item, $success_url, $cancel_url, $tax = 0)
    {
        try {
            $stripe = new \Stripe\StripeClient($this->secret_key);
            $amount = $item->price;
            if (!is_null($item->deposit)) {
                if ($item->deposit == "50%") {
                    $amount = (50 / 100) * $item->price;
                } elseif ($item->deposit == "70%") {
                    $amount = (70 / 100) * $item->price;
                }
            }
            $line_items = [];

            $l_item = [];
            $l_item['price_data']['product_data']['name'] = $item->name;
            $l_item['price_data']['product_data']['description'] = strip_tags($item->description);
            $l_item['price_data']['product_data']['images'][] = $item->image_url;
            $l_item['price_data']['currency'] = $item->currency;
            $l_item['price_data']['unit_amount'] = $amount * 100;
            $l_item['quantity'] = 1;

            $line_items[] = $l_item;

            if ($tax > 0) {
                $l_item = [];
                $l_item['price_data']['product_data']['name'] = "Tax";
                $l_item['price_data']['product_data']['description'] = "Goods and services tax";
                $l_item['price_data']['currency'] = $item->currency;
                $l_item['price_data']['unit_amount'] = $tax * 100;
                $l_item['quantity'] = 1;
                $line_items[] = $l_item;
            }

            $paymentRef = "BK" . unique_random_string();
            $checkout = $stripe->checkout->sessions->create([
                'client_reference_id' => $paymentRef,
                'customer_email' => $user->email,
                'line_items' => $line_items,
                'mode' => 'payment',
                'success_url' => $success_url . '?success=true&session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $cancel_url . '?canceled=true',
            ]);

            return ['error' => 0, 'responseMessage' => 'Successful', 'paymentRef' => $paymentRef, 'paymentUrl' => $checkout->url, 'session_id' => $checkout->id];
        } catch (Exception $e) {
            Logger::error('Stripe generateServicePayLink error - ', [$e->getMessage() . ' - ' . $e->__toString()]);
            return ['error' => 1, "responseStatus" => "Unsuccessful", "statusCode" => 500, 'Detail' => $e->getMessage(), "responseMessage" => 'Something went wrong'];
        }
    }

    public function generateMerchantServicePayLink($account_id, $user, $item, $success_url, $cancel_url, $tax = 0)
    {
        try {
            $stripe = new \Stripe\StripeClient($this->secret_key);
            $amount = $item->price;
            if (!is_null($item->deposit)) {
                if ($item->deposit == "50%") {
                    $amount = (50 / 100) * $item->price;
                } elseif ($item->deposit == "70%") {
                    $amount = (70 / 100) * $item->price;
                }
            }
            $line_items = [];
            $l_item = [];
            $l_item['price_data']['product_data']['name'] = $item->name;
            $l_item['price_data']['product_data']['description'] = strip_tags($item->description);
            $l_item['price_data']['product_data']['images'][] = $item->image_url;
            $l_item['price_data']['currency'] = $item->currency;
            $l_item['price_data']['unit_amount'] = $amount * 100;
            $l_item['quantity'] = 1;

            $line_items[] = $l_item;

            if ($tax > 0) {
                $l_item = [];
                $l_item['price_data']['product_data']['name'] = "Tax";
                $l_item['price_data']['product_data']['description'] = "Goods and services tax";
                $l_item['price_data']['currency'] = $item->currency;
                $l_item['price_data']['unit_amount'] = $tax * 100;
                $l_item['quantity'] = 1;
                $line_items[] = $l_item;
            }

            $paymentRef = "BK" . unique_random_string();
            $checkout = $stripe->checkout->sessions->create([
                'client_reference_id' => $paymentRef,
                'customer_email' => $user->email,
                'line_items' => $line_items,
                'mode' => 'payment',
                'success_url' => $success_url . '?success=true&session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $cancel_url . '?canceled=true',

            ], ['stripe_account' => $account_id]);

            return ['error' => 0, 'responseMessage' => 'Successful', 'paymentRef' => $paymentRef, 'paymentUrl' => $checkout->url, 'session_id' => $checkout->id];
        } catch (Exception $e) {
            Logger::error('Stripe generateMerchantServicePayLink error - ', [$e->getMessage() . ' - ' . $e->__toString()]);
            return ['error' => 1, "responseStatus" => "Unsuccessful", "statusCode" => 500, 'Detail' => $e->getMessage(), "responseMessage" => 'Something went wrong'];
        }
    }

    public function generateDomainPayLink($user, $currency, $amount, $description, $success_url, $cancel_url, $tax = 0)
    {
        try {
            $stripe = new \Stripe\StripeClient($this->secret_key);

            $line_items = [];

            $l_item = [];
            $l_item['price_data']['product_data']['name'] = "Domain Purchase";
            $l_item['price_data']['product_data']['description'] = $description;
            $l_item['price_data']['currency'] = $currency;
            $l_item['price_data']['unit_amount'] = $amount * 100;
            $l_item['quantity'] = 1;

            $line_items[] = $l_item;

            if ($tax > 0) {
                $l_item = [];
                $l_item['price_data']['product_data']['name'] = "Tax";
                $l_item['price_data']['product_data']['description'] = "Goods and services tax";
                $l_item['price_data']['currency'] = $currency;
                $l_item['price_data']['unit_amount'] = $tax * 100;
                $l_item['quantity'] = 1;
                $line_items[] = $l_item;
            }

            $paymentRef = "DMP" . unique_random_string();
            $checkout = $stripe->checkout->sessions->create([
                'client_reference_id' => $paymentRef,
                'customer_email' => $user->email,
                'line_items' => $line_items,
                'mode' => 'payment',
                'success_url' => $success_url . '?success=true&session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $cancel_url . '?canceled=true',
            ]);

            return ['error' => 0, 'responseMessage' => 'Successful', 'paymentRef' => $paymentRef, 'paymentUrl' => $checkout->url, 'session_id' => $checkout->id];
        } catch (Exception $e) {
            Logger::error('Stripe generateDomainPayLink error - ', [$e->getMessage() . ' - ' . $e->__toString()]);
            return ['error' => 1, "responseStatus" => "Unsuccessful", "statusCode" => 500, 'Detail' => $e->getMessage(), "responseMessage" => 'Something went wrong'];
        }
    }

    public function generatePaymentLink($merchant, $buyer, $item, $currency, $amount, $success_url, $cancel_url, $tax = 0)
    {
        try {
            $stripe = new \Stripe\StripeClient($this->secret_key);
            $line_items = [];

            $l_item = [];
            $l_item['price_data']['product_data']['name'] = $item->name;
            $l_item['price_data']['product_data']['description'] = strip_tags($item->description);
            $l_item['price_data']['product_data']['images'][] = $item->image_url;
            $l_item['price_data']['currency'] = $currency;
            $l_item['price_data']['unit_amount'] = $amount * 100;
            $l_item['quantity'] = 1;

            $line_items[] = $l_item;

            if ($tax > 0) {
                $l_item = [];
                $l_item['price_data']['product_data']['name'] = "Tax";
                $l_item['price_data']['product_data']['description'] = "Goods and services tax";
                $l_item['price_data']['currency'] = $currency;
                $l_item['price_data']['unit_amount'] = $tax * 100;
                $l_item['quantity'] = 1;
                $line_items[] = $l_item;
            }

            $paymentRef = "SUB-PROD" . unique_random_string();
            if (is_null($merchant->stripe_account_id)) {
                $checkout = $stripe->checkout->sessions->create([
                    'client_reference_id' => $paymentRef,
                    'customer_email' => $buyer->email,
                    'line_items' => $line_items,
                    'mode' => 'payment',
                    'success_url' => $success_url . '?success=true&session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => $cancel_url . '?canceled=true',
                ]);
            } else {
                $checkout = $stripe->checkout->sessions->create([
                    'client_reference_id' => $paymentRef,
                    'customer_email' => $buyer->email,
                    'line_items' => $line_items,
                    'mode' => 'payment',
                    'success_url' => $success_url . '?success=true&session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => $cancel_url . '?canceled=true',
                ], ['stripe_account' => $merchant->stripe_account_id]);
            }


            return ['error' => 0, 'responseMessage' => 'Successful', 'paymentRef' => $paymentRef, 'paymentUrl' => $checkout->url, 'session_id' => $checkout->id];
        } catch (Exception $e) {
            Logger::error('Stripe generatePaymentLink error - ', [$e->getMessage() . ' - ' . $e->__toString()]);
            return ['error' => 1, "responseStatus" => "Unsuccessful", "statusCode" => 500, 'Detail' => $e->getMessage(), "responseMessage" => 'Something went wrong'];
        }
    }

    public function getPaymentDetails($sessionID, $merchant)
    {
        try {
            $stripe = new \Stripe\StripeClient($this->secret_key);
            if (is_null($merchant->stripe_account_id)) {
                $session = $stripe->checkout->sessions->retrieve($sessionID, []);
            }else{
                $session = $stripe->checkout->sessions->retrieve($sessionID, [], ['stripe_account' => $merchant->stripe_account_id]);  
            }
            return ['error' => 0, 'responseMessage' => 'Successful', 'session' => $session];
        } catch (Exception $e) {
            Logger::error('Stripe getPaymentDetails error - ', [$e->getMessage() . ' - ' . $e->__toString()]);
            return ['error' => 1, "responseStatus" => "Unsuccessful", "statusCode" => 500, 'Detail' => $e->getMessage(), "responseMessage" => 'Something went wrong'];
        }
    }
}
