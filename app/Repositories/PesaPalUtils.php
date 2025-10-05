<?php

namespace App\Repositories;

use DateTime;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log as Logger;

class PesaPalUtils
{

  private $url;
  private $consumer_key;
  private $consumer_secret;

  public function __construct()
  {
    $this->url = env("PesaPal_URL");
    //$this->url = "https://pay.pesapal.com/v3";
    $this->consumer_key = env("PesaPal_Consumer_Key");
    $this->consumer_secret = env("PesaPal_Consumer_Secret");
  }

  // {
  //   "error": {
  //       "type": "error_type",
  //       "code": "response_code",
  //       "message": "Detailed error message goes here.."
  //   }
  // }


  public function getToken()
  {
    $payload = [
      "consumer_key" => $this->consumer_key,
      "consumer_secret" => $this->consumer_secret
    ];
    $curl = curl_init();
    $url = $this->url . '/api/Auth/RequestToken';
    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_SSL_VERIFYPEER => 0,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => json_encode($payload, true),
      CURLOPT_HTTPHEADER => [
        "Accept: application/json",
        "Content-Type: application/json"
      ],
    ));

    $response = curl_exec($curl);
    $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $err = curl_errno($curl);

    if ($err) {
      $error_msg = curl_error($curl);
      curl_close($curl);
      return ['error' => 1, 'statusCode' => 500, 'responseMessage' => $error_msg, 'response' => $response];
    }
    curl_close($curl);

    $response = json_decode($response);
    //return $response;
    if ($status_code != 200 || isset($response->error)) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => $response];
    }

    return ['error' => 0, 'statusCode' => 201, 'responseMessage' => "Successful", 'token' => $response->token];
    // {
    //     "token":"eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJodHRwOi8vc2NoZW1hcy5taWNyb3NvZnQuY29tL3dzLzIwMDgvMDYa",
    //     "expiryDate": "2021-08-26T12:29:30.5177702Z",
    //     "error": null,
    //     "status": "200",
    //     "message": "Request processed successfully"
    // }
  }

  public function registerIPN_url($url)
  {
    $login_response = $this->getToken();
    //return [$login_response, $this->consumer_key, $this->consumer_secret];
    if ($login_response['error'] == 1) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => "token was not generated"];
      //return "token was not generated";
    }
    $token = $login_response['token'];
    $payload = [
      "url" => $url,
      "ipn_notification_type" => "POST"
    ];
    $curl = curl_init();
    $url = $this->url . '/api/URLSetup/RegisterIPN';
    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_SSL_VERIFYPEER => 0,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => json_encode($payload, true),
      CURLOPT_HTTPHEADER => [
        "Accept: application/json",
        "Content-Type: application/json",
        "Authorization: Bearer $token",
      ],
    ));

    $response = curl_exec($curl);
    $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $err = curl_errno($curl);

    if ($err) {
      $error_msg = curl_error($curl);
      curl_close($curl);
      return ['error' => 1, 'statusCode' => 500, 'responseMessage' => $error_msg, 'response' => $response];
    }
    curl_close($curl);

    $response = json_decode($response);
    //return $response;
    if ($status_code != 200 || isset($response->error)) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => $response];
    }

    return ['error' => 0, 'statusCode' => 201, 'responseMessage' => "Successful", 'response' => $response];
    // {
    //   "url": "https://www.myapplication.com/ipn",
    //   "created_date": "2022-03-03T17:29:03.7208266Z",
    //   "ipn_id": "e32182ca-0983-4fa0-91bc-c3bb813ba750",
    //   "error": null,
    //   "status": "200"
    // }
  }


  public function getRegisterIPN_url()
  {
    $login_response = $this->getToken();
    if ($login_response['error'] == 1) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => "token was not generated"];
      //return "token was not generated";
    }
    $token = $login_response['token'];

    $url = $this->url . '/api/URLSetup/GetIpnList';
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
      "Accept: application/json",
      "Content-Type: application/json",
      "Authorization: Bearer $token",
    ]);

    $response = curl_exec($curl);
    $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $err = curl_errno($curl);

    if ($err) {
      $error_msg = curl_error($curl);
      curl_close($curl);
      return ['error' => 1, 'statusCode' => 500, 'responseMessage' => $error_msg, 'response' => $response];
    }
    curl_close($curl);

    $response = json_decode($response);
    //return $response;
    if ($status_code != 200 || isset($response->error)) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => $response];
    }

    return ['error' => 0, 'statusCode' => 201, 'responseMessage' => "Successful", 'response' => $response];
    // [
    //   {
    //       "url": "https://www.myapplication.com/ipn",
    //       "created_date": "2022-03-03T17:29:03.7208266Z",
    //       "ipn_id": "e32182ca-0983-4fa0-91bc-c3bb813ba750",
    //       "error": null,
    //       "status": "200"
    //   }, 
    //   ..
    // ]

  }

  public function generatePaymentLink($order, $user, $description, $callback_url, $cancellation_url)
  {
    $login_response = $this->getToken();
    if ($login_response['error'] == 1) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => "token was not generated"];
      //return "token was not generated";
    }
    $token = $login_response['token'];
    $ipn_notification_id = env("PesaPal_CHECKOUT_IPN_ID");
    $payload = [
      "id" => $order->paymentRef,
      "currency" => $order->currency,
      "amount" => $order->amount,
      "description" => $description,
      "callback_url" => $callback_url,
      "cancellation_url" => $cancellation_url,
      "redirect_mode" => "",
      "notification_id" => $ipn_notification_id,
      "branch" => "",
      "billing_address" => [
        "email_address" => $user->email,
        "phone_number" => $user->phone,
        "country_code" => "UG",
        "first_name" => $user->firstName,
        "middle_name" => "",
        "last_name" => $user->lastName,
        "line_1" => "",
        "line_2" => "",
        "city" => "",
        "state" => "",
        "postal_code" => "",
        "zip_code" => ""
      ]
    ];

    $curl = curl_init();
    $url = $this->url . '/api/Transactions/SubmitOrderRequest';
    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_SSL_VERIFYPEER => 0,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => json_encode($payload, true),
      CURLOPT_HTTPHEADER => [
        "Accept: application/json",
        "Content-Type: application/json",
        "Authorization: Bearer $token",
      ],
    ));

    $response = curl_exec($curl);
    $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $err = curl_errno($curl);

    if ($err) {
      $error_msg = curl_error($curl);
      curl_close($curl);
      return ['error' => 1, 'statusCode' => 500, 'responseMessage' => $error_msg, 'response' => $response];
    }
    curl_close($curl);

    $response = json_decode($response);
    //return $response;
    if ($status_code != 200 || isset($response->error)) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => $response];
    }

    return ['error' => 0, 'statusCode' => 201, 'responseMessage' => "Successful", 'response' => $response];
    // {
    //     "order_tracking_id": "b945e4af-80a5-4ec1-8706-e03f8332fb04",
    //     "merchant_reference": "TEST1515111119",
    //     "redirect_url": "https://cybqa.pesapal.com/pesapaliframe/PesapalIframe3/Index/?OrderTrackingId=b945e4af-80a5-4ec1-8706-e03f8332fb04",
    //     "error": null,
    //     "status": "200"
    // }

  }

  public function generateSubPaymentLink($paymentRef, $user, $subscription, $callback_url, $cancellation_url)
  {
    $login_response = $this->getToken();
    if ($login_response['error'] == 1) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => "token was not generated"];
      //return "token was not generated";
    }
    $token = $login_response['token'];
    switch ($subscription->invoice_interval) {
      case "day":
        $frequency = "DAILY";
        break;
      case "week":
        $frequency = "WEEKLY";
        break;
      case "month":
        $frequency = "MONTHLY";
        break;
      case "year":
        $frequency = "YEARLY";
        break;
    }
    $start_date = Carbon::parse(Carbon::today())->format('d-m-Y');
    $expires_at = new DateTime('+' . $subscription->invoice_period . ' ' . $subscription->invoice_interval);
    $end_date   = Carbon::parse($expires_at)->format('d-m-Y');
    $ipn_notification_id = env("PesaPal_SUB_IPN_ID");
    $payload = [
      "id" => $paymentRef,
      "currency" => $subscription->currency,
      "amount" => $subscription->price,
      "description" => $subscription->description,
      "callback_url" => $callback_url,
      "cancellation_url" => $cancellation_url,
      "redirect_mode" => "",
      "notification_id" => $ipn_notification_id,
      "branch" => "",
      "account_number" => $user->id,
      "billing_address" => [
        "email_address" => $user->email,
        "phone_number" => $user->phone,
        "country_code" => "UG",
        "first_name" => $user->firstName,
        "middle_name" => "",
        "last_name" => $user->lastName,
        "line_1" => "",
        "line_2" => "",
        "city" => "",
        "state" => "",
        "postal_code" => "",
        "zip_code" => ""
      ],
      "subscription_details" => [
        "start_date" => $start_date,
        "end_date" => $end_date,
        "frequency" => $frequency //DAILY, WEEKLY, MONTHLY or YEARLY
      ]

    ];

    $curl = curl_init();
    $url = $this->url . '/api/Transactions/SubmitOrderRequest';
    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_SSL_VERIFYPEER => 0,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => json_encode($payload, true),
      CURLOPT_HTTPHEADER => [
        "Accept: application/json",
        "Content-Type: application/json",
        "Authorization: Bearer $token",
      ],
    ));

    $response = curl_exec($curl);
    $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $err = curl_errno($curl);

    if ($err) {
      $error_msg = curl_error($curl);
      curl_close($curl);
      return ['error' => 1, 'statusCode' => 500, 'responseMessage' => $error_msg, 'response' => $response];
    }
    curl_close($curl);

    $response = json_decode($response);
    //return $response;
    if ($status_code != 200 || isset($response->error)) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => $response];
    }

    return ['error' => 0, 'statusCode' => 201, 'responseMessage' => "Successful", 'response' => $response];
    // {
    //     "order_tracking_id": "b945e4af-80a5-4ec1-8706-e03f8332fb04",
    //     "merchant_reference": "TEST1515111119",
    //     "redirect_url": "https://cybqa.pesapal.com/pesapaliframe/PesapalIframe3/Index/?OrderTrackingId=b945e4af-80a5-4ec1-8706-e03f8332fb04",
    //     "error": null,
    //     "status": "200"
    // }

  }


  public function getPaymentStatus($orderTrackingId)
  {
    $login_response = $this->getToken();
    if ($login_response['error'] == 1) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => "token was not generated"];
      //return "token was not generated";
    }
    $token = $login_response['token'];

    $url = $this->url . "/api/Transactions/GetTransactionStatus?orderTrackingId=$orderTrackingId";
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
      "Accept: application/json",
      "Content-Type: application/json",
      "Authorization: Bearer $token",
    ]);

    $response = curl_exec($curl);
    $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $err = curl_errno($curl);

    if ($err) {
      $error_msg = curl_error($curl);
      curl_close($curl);
      return ['error' => 1, 'statusCode' => $status_code, 'responseMessage' => $error_msg, 'response' => $response];
    }
    curl_close($curl);

    $response = json_decode($response);
    //return $response;
    if ($status_code != 200 || !is_null($response->error->error_type)) {
      return ['error' => 1, 'statusCode' => $status_code, 'responseMessage' => $response];
    }

    return ['error' => 0, 'statusCode' => $status_code, 'responseMessage' => "Successful", 'response' => $response];
    // {
    //   "payment_method": "Visa",
    //   "amount": 100,
    //   "created_date": "2022-04-30T07:41:09.763",
    //   "confirmation_code": "6513008693186320103009",
    //   "payment_status_description": "Failed",
    //   "description": "Unable to Authorize Transaction.Kindly contact your bank for assistance",
    //   "message": "Request processed successfully",
    //   "payment_account": "476173**0010",
    //   "call_back_url": "https://test.com/?OrderTrackingId=7e6b62d9-883e-440f-a63e-e1105bbfadc3&OrderMerchantReference=1515111111",
    //   "status_code": 2,
    //   "merchant_reference": "1515111111",
    //   "payment_status_code": "",
    //   "currency": "KES",
    //   "error": {
    //       "error_type": null,
    //       "code": null,
    //       "message": null,
    //       "call_back_url": null
    //   },
    //   "status": "200"
    // }

  }
}
