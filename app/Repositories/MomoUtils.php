<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Log as Logger;
use Carbon\Carbon;
use App\Models\MomoSetting;

class MomoUtils
{


  public function __construct()
  {

    //$this->url = "https://sandbox.momodeveloper.mtn.com";
    $this->url = "https://proxy.momoapi.mtn.com";
    //$this->sub_url = "https://sandbox.momodeveloper.mtn.com";
    //"https://proxy.momoapi.mtn.com/collection/v1_0/bc-authorize"

    $this->sub_url = "http://10.156.42.160:3111/GDEService/v1";
    //$this->sub_url = "http://10.156.42.7:3111/GDEService/v1/getSubscription";
  }

  public function gen_uuid()
  {
    return sprintf(
      '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
      // 32 bits for "time_low"
      mt_rand(0, 0xffff),
      mt_rand(0, 0xffff),

      // 16 bits for "time_mid"
      mt_rand(0, 0xffff),

      // 16 bits for "time_hi_and_version",
      // four most significant bits holds version number 4
      mt_rand(0, 0x0fff) | 0x4000,

      // 16 bits, 8 bits for "clk_seq_hi_res",
      // 8 bits for "clk_seq_low",
      // two most significant bits holds zero and one for variant DCE1.1
      mt_rand(0, 0x3fff) | 0x8000,

      // 48 bits for "node"
      mt_rand(0, 0xffff),
      mt_rand(0, 0xffff),
      mt_rand(0, 0xffff)
    );
  }

  public function getToken()
  {

    $tokenObj = MomoSetting::whereNotNull('id')->first();
    $date_time = Carbon::now();
    if (!is_null($tokenObj) && $tokenObj->token_expires_at > $date_time) {
      return ['error' => 0, 'statusCode' => 201, 'responseMessage' => 'Valid Token', 'token' => $tokenObj->token];
    }

    //$basic_auth_token =  "MWQ4NGIwMWYtODNjMy00MWYyLTk4NmItMjVjYzk0NmYwMjBmOjliOGJlN2E4NzNkODRmZGVhODNiNmYwZWU5OGQ5Y2Y0";
    $basic_auth_token =  "M2JiNjA2MmItMGRjMi00ZDZiLWJhMWItN2Q2NDBlNmEwNDlkOmNkY2VkM2IzNjZiZTRhZDJhOGUwZjBhMjhmNmY4MDMz";
    //$collection_sub_key = "b082007a526d4925a7eca6c6b6b7c163";
    $collection_sub_key = "a885417088324bd187e5a48779b7c573";
    $curl = curl_init();
    $url = $this->url . '/collection/token/';
    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_SSL_VERIFYPEER => 0,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "Authorization: Basic $basic_auth_token",
        "Ocp-Apim-Subscription-Key: $collection_sub_key",
        "Content-length: 0" 
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

    //$response = json_decode($response);
    return $response;
    if ($status_code != 200) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => $response];
    }

    if (!is_null($tokenObj)) {
      $tokenObj->update([
        'token' => $response->access_token,
        'token_expires_at' => $date_time->addHours(1)
      ]);
    } else {
      MomoSetting::create([
        'token' => $response->access_token,
        'token_expires_at' => $date_time->addHours(1)
      ]);
    }

    return ['error' => 0, 'statusCode' => 201, 'responseMessage' => "Successful", 'token' => $response->access_token];


    // {
    //   "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSMjU2In0.eyJjbGllbnRJZCI6IjFkODRiMDFmLTgzYzMtNDFmMi05ODZiLTI1Y2M5NDZmMDIwZiIsImV4cGlyZXMiOiIyMDIyLTA3LTAyVDA5OjAzOjM5LjA2OSIsInNlc3Npb25JZCI6ImMyNzdmNjE3LWZjZDgtNDliYS1hOTNiLWM3ZmJhNTNkZGI2OCJ9.UE5aoaQHE96HActqn2iL7XDWIPV6-fyo22kQ8T7QlY07KnkzkEMQnWbZgNNFMzCqlwFpsgTuLYFwcE7z1WsUf-9V19REluU14dCYmaCw6t2Pmfs37oD4ZQ7yYwwvkubQO9cu5Ao56umUq0ARKJjYL7BZQx_F_yP3bmL6waTGr_KRnxt4Mf8jbEgzkZKzrppNEqk4sKQ3RY_esv_AK0qFFqqfFNkzr4Rwey-qCfhaAdpwGDB-RFPijtR9QoPIQapxjMof9NEIyZKndRw-vyURJn20IQUZVtv-jsDHzo_SM-IcOzw4wboW_Ja-YtmHptfMzqO5yomSfzP7qGE6nQGx1Q",
    //   "token_type": "access_token",
    //   "expires_in": 3600
    // }
  }


 public function getAccountHolderInfoMsisdn($msisdn)
  {

    $login_response = $this->getToken();
    if ($login_response['error'] == 1) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => "token was not generated"];
      //return "token was not generated";
    }
    $token = $login_response['token'];
    $collection_sub_key = "b082007a526d4925a7eca6c6b6b7c163";
    $target_env = "sandbox";
    $ch = curl_init();
    $url = $this->url . "/collection/v1_0/accountholder/msisdn/$msisdn/basicuserinfo";
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      "Content-Type: application/json",
      "Authorization: Bearer $token",
      "X-Target-Environment: $target_env",
      "Ocp-Apim-Subscription-Key: $collection_sub_key"

    ]);

    $response = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_errno($ch);

    if ($err) {
      $error_msg = curl_error($ch);
      curl_close($ch);
      return ['error' => 1, 'statusCode' => 500, 'responseMessage' => $error_msg];
    }
    curl_close($ch);

    $response = json_decode($response);
    //return $keyResponse;
    if (is_null($response)) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => 'Null response!'];
    } elseif ($status_code != 200) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => $response->message];
    }

    return ['error' => 0, 'statusCode' => 201, 'responseMessage' => 'Successful', 'accountDetails' => $response];

    // {
    //   "given_name": "string",
    //   "family_name": "string",
    //   "birthdate": "string",
    //   "locale": "string",
    //   "gender": "string",
    //   "status": "string"
    // }

  }

  public function checkCustomerStatus($msisdn)
  {

    $login_response = $this->getToken();
    if ($login_response['error'] == 1) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => "token was not generated"];
      //return "token was not generated";
    }
    $token = $login_response['token'];
    $collection_sub_key = "b082007a526d4925a7eca6c6b6b7c163";
    $target_env = "sandbox";
    $ch = curl_init();
    $url = $this->url . "/collection/v1_0/accountholder/msisdn/$msisdn/active";
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      "Content-Type: application/json",
      "Authorization: Bearer $token",
      "X-Target-Environment: $target_env",
      "Ocp-Apim-Subscription-Key: $collection_sub_key"

    ]);

    $response = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_errno($ch);

    if ($err) {
      $error_msg = curl_error($ch);
      curl_close($ch);
      return ['error' => 1, 'statusCode' => 500, 'responseMessage' => $error_msg];
    }
    curl_close($ch);

    $response = json_decode($response);
    //return $keyResponse;
    if (is_null($response)) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => 'Null response!'];
    } elseif ($status_code != 200) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => $response->message];
    }

    return ['error' => 0, 'statusCode' => 201, 'responseMessage' => 'Successful', 'accountActive' => $response->result];

    // {
    //   "result": false
    // }

  }


  public function requestToPay($amount, $currency, $ref, $payerMsg = '', $payeeNote = '', $msisdn_or_id, $type = "msisdn")
  {

    $login_response = $this->getToken();
    if ($login_response['error'] == 1) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => "token was not generated"];
      //return "token was not generated";
    }
    $token = $login_response['token'];
    $collection_sub_key = "b082007a526d4925a7eca6c6b6b7c163";
    $target_env = "sandbox";
    $req_reference = $this->gen_uuid(); //"44ea9f7b-60e3-417a-aa6b-aa20fb157831";
    $payload = [
      "amount" => $amount,
      "currency" => $currency,
      "externalId" => $ref,
      "payer" => [
        "partyIdType" => strtoupper($type),
        "partyId" => $msisdn_or_id
      ],
      "payerMessage" => $payerMsg,
      "payeeNote" => $payeeNote
    ];
    $ch = curl_init();
    $url = $this->url . "/collection/v1_0/requesttopay";
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, true));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      "Content-Type: application/json",
      "Authorization: Bearer $token",
      "X-Target-Environment: $target_env",
      "Ocp-Apim-Subscription-Key: $collection_sub_key",
      "X-Reference-Id: $req_reference"

    ]);

    $response = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_errno($ch);

    if ($err) {
      $error_msg = curl_error($ch);
      curl_close($ch);
      return ['error' => 1, 'statusCode' => 500, 'responseMessage' => $error_msg];
    }
    curl_close($ch);

    $response = json_decode($response);
    //return $keyResponse;
    if ($status_code != 202) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => $response->message];
    }

    return ['error' => 0, 'statusCode' => 201, 'responseMessage' => 'Successful', 'referenceId' => $req_reference];

    //request payload
    // {
    //   "amount": "1000",
    //   "currency": "EUR",
    //   "externalId": "mom-12332",
    //   "payer": {
    //     "partyIdType": "MSISDN",
    //     "partyId": "2348039688395"
    //   },
    //   "payerMessage": "Testing testing",
    //   "payeeNote": "Test"
    // }



  }


  public function requestToPayDetails($req_reference)
  {

    $login_response = $this->getToken();
    if ($login_response['error'] == 1) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => "token was not generated"];
      //return "token was not generated";
    }
    $token = $login_response['token'];
    $collection_sub_key = env("MOMO_COLLECTION_KEY"); //"b082007a526d4925a7eca6c6b6b7c163";
    $target_env = env("MOMO_ENV"); //"sandbox"
    $ch = curl_init();
    $url = $this->url . "/collection/v1_0/requesttopay/$req_reference";
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      "Content-Type: application/json",
      "Authorization: Bearer $token",
      "X-Target-Environment: $target_env",
      "Ocp-Apim-Subscription-Key: $collection_sub_key"

    ]);

    $response = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_errno($ch);

    if ($err) {
      $error_msg = curl_error($ch);
      curl_close($ch);
      return ['error' => 1, 'statusCode' => 500, 'responseMessage' => $error_msg];
    }
    curl_close($ch);

    $response = json_decode($response);
    //return $keyResponse;
    if (is_null($response)) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => 'Null response!'];
    } elseif ($status_code != 200) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => $response->message];
    }

    return ['error' => 0, 'statusCode' => 201, 'responseMessage' => 'Successful', 'transactionDetails' => $response];

    // {
    //   "financialTransactionId": "1249035174",
    //   "externalId": "mom-12332",
    //   "amount": "1000",
    //   "currency": "EUR",
    //   "payer": {
    //     "partyIdType": "MSISDN",
    //     "partyId": "2348039688395"
    //   },
    //   "payerMessage": "Testing testing",
    //   "payeeNote": "Test",
    //   "status": "SUCCESSFUL"
    // }

  }


  public function sendDeliveryNotification($referenceId, $NotificationMsg)
  {

    $login_response = $this->getToken();
    if ($login_response['error'] == 1) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => "token was not generated"];
      //return "token was not generated";
    }
    $token = $login_response['token'];
    $collection_sub_key = "b082007a526d4925a7eca6c6b6b7c163";
    $target_env = "sandbox";
    $req_reference = $this->gen_uuid(); //"44ea9f7b-60e3-417a-aa6b-aa20fb157831";
    $payload = ["notificationMessage" => $NotificationMsg];
    $ch = curl_init();
    $url = $this->url . "/collection/v1_0/requesttopay/$referenceId/deliverynotification";
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, true));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      "Content-Type: application/json",
      "Authorization: Bearer $token",
      "X-Target-Environment: $target_env",
      "Ocp-Apim-Subscription-Key: $collection_sub_key",
      "X-notificationMessage: $NotificationMsg",
      //"Language: En"

    ]);

    $response = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_errno($ch);

    if ($err) {
      $error_msg = curl_error($ch);
      curl_close($ch);
      return ['error' => 1, 'statusCode' => 500, 'responseMessage' => $error_msg];
    }
    curl_close($ch);

    $response = json_decode($response);
    //return $keyResponse;
    if ($status_code != 200) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => $response->message];
    }

    return ['error' => 0, 'statusCode' => 201, 'responseMessage' => 'Successful'];
  }


  public function getAccountBalance($currency = null)
  {

    $login_response = $this->getToken();
    if ($login_response['error'] == 1) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => "token was not generated"];
      //return "token was not generated";
    }
    $token = $login_response['token'];
    $collection_sub_key = env("MOMO_COLLECTION_KEY"); //"b082007a526d4925a7eca6c6b6b7c163";
    $target_env = env("MOMO_ENV"); //"sandbox"

    if (!is_null($currency)) {
      $url = $this->url . "/collection/v1_0/account/balance/$currency";
    } else {
      $url = $this->url . "/collection/v1_0/account/balance";
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      "Content-Type: application/json",
      "Authorization: Bearer $token",
      "X-Target-Environment: $target_env",
      "Ocp-Apim-Subscription-Key: $collection_sub_key"

    ]);

    $response = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_errno($ch);

    if ($err) {
      $error_msg = curl_error($ch);
      curl_close($ch);
      return ['error' => 1, 'statusCode' => 500, 'responseMessage' => $error_msg];
    }
    curl_close($ch);

    $response = json_decode($response);
    //return $keyResponse;
    if (is_null($response)) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => 'Null response!'];
    } elseif ($status_code != 200) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => $response->message];
    }

    return ['error' => 0, 'statusCode' => 201, 'responseMessage' => 'Successful', 'accountBalance' => $response];

    // {
    //   "availableBalance": "string",
    //   "currency": "string"
    // }

  }


  public function depositOrDisburse($amount, $currency, $ref, $payerMsg, $payeeNote, $msisdn_or_id, $type = "MSISDN")
  {

    $login_response = $this->getToken();
    if ($login_response['error'] == 1) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => "token was not generated"];
      //return "token was not generated";
    }
    $token = $login_response['token'];
    $disbursement_sub_key = "b082007a526d4925a7eca6c6b6b7c163";
    $target_env = "sandbox";
    $req_reference = $this->gen_uuid(); //"44ea9f7b-60e3-417a-aa6b-aa20fb157831";
    $payload = [
      "amount" => $amount,
      "currency" => $currency,
      "externalId" => $ref,
      "payer" => [
        "partyIdType" => strtoupper($type),
        "partyId" => $msisdn_or_id
      ],
      "payerMessage" => $payerMsg,
      "payeeNote" => $payeeNote
    ];
    $ch = curl_init();
    $url = $this->url . "/disbursement/v1_0/deposit";
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, true));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      "Content-Type: application/json",
      "Authorization: Bearer $token",
      "X-Target-Environment: $target_env",
      "Ocp-Apim-Subscription-Key: $disbursement_sub_key",
      "X-Reference-Id: $req_reference"

    ]);

    $response = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_errno($ch);

    if ($err) {
      $error_msg = curl_error($ch);
      curl_close($ch);
      return ['error' => 1, 'statusCode' => 500, 'responseMessage' => $error_msg];
    }
    curl_close($ch);

    $response = json_decode($response);
    //return $keyResponse;
    if ($status_code != 202) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => $response->message];
    }

    return ['error' => 0, 'statusCode' => 201, 'responseMessage' => 'Successful', 'referenceId' => $req_reference];

    //request payload
    // {
    //   "amount": "1000",
    //   "currency": "EUR",
    //   "externalId": "mom-12332",
    //   "payer": {
    //     "partyIdType": "MSISDN",
    //     "partyId": "2348039688395"
    //   },
    //   "payerMessage": "Testing testing",
    //   "payeeNote": "Test"
    // }



  }


  public function getDepositDetails($req_reference)
  {

    $login_response = $this->getToken();
    if ($login_response['error'] == 1) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => "token was not generated"];
      //return "token was not generated";
    }
    $token = $login_response['token'];
    $disbursement_sub_key = env("MOMO_DISBURSEMENT_KEY"); //"b082007a526d4925a7eca6c6b6b7c163";
    $target_env = env("MOMO_ENV"); //"sandbox"
    $ch = curl_init();
    $url = $this->url . "/disbursement/v1_0/deposit/$req_reference";
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      "Content-Type: application/json",
      "Authorization: Bearer $token",
      "X-Target-Environment: $target_env",
      "Ocp-Apim-Subscription-Key: $disbursement_sub_key"

    ]);

    $response = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_errno($ch);

    if ($err) {
      $error_msg = curl_error($ch);
      curl_close($ch);
      return ['error' => 1, 'statusCode' => 500, 'responseMessage' => $error_msg];
    }
    curl_close($ch);

    $response = json_decode($response);
    //return $keyResponse;
    if (is_null($response)) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => 'Null response!'];
    } elseif ($status_code != 200) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => $response->message];
    }

    return ['error' => 0, 'statusCode' => 201, 'responseMessage' => 'Successful', 'transactionDetails' => $response];

    // {
    //   "financialTransactionId": "1249035174",
    //   "externalId": "mom-12332",
    //   "amount": "1000",
    //   "currency": "EUR",
    //   "payer": {
    //     "partyIdType": "MSISDN",
    //     "partyId": "2348039688395"
    //   },
    //   "payerMessage": "Testing testing",
    //   "payeeNote": "Test",
    //   "status": "SUCCESSFUL"
    // }

  }


  public function refundDeposit($amount, $currency, $internalRef, $payerMsg, $payeeNote, $depositRef)
  {

    $login_response = $this->getToken();
    if ($login_response['error'] == 1) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => "token was not generated"];
      //return "token was not generated";
    }
    $token = $login_response['token'];
    $disbursement_sub_key = "b082007a526d4925a7eca6c6b6b7c163";
    $target_env = "sandbox";
    $req_reference = $this->gen_uuid(); //"44ea9f7b-60e3-417a-aa6b-aa20fb157831";
    $payload = [
      "amount" => $amount,
      "currency" => $currency,
      "externalId" => $internalRef,
      "payerMessage" => $payerMsg,
      "payeeNote" => $payeeNote,
      "referenceIdToRefund" => $depositRef
    ];
    $ch = curl_init();
    $url = $this->url . "/disbursement/v1_0/refund";
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, true));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      "Content-Type: application/json",
      "Authorization: Bearer $token",
      "X-Target-Environment: $target_env",
      "Ocp-Apim-Subscription-Key: $disbursement_sub_key",
      "X-Reference-Id: $req_reference"

    ]);

    $response = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_errno($ch);

    if ($err) {
      $error_msg = curl_error($ch);
      curl_close($ch);
      return ['error' => 1, 'statusCode' => 500, 'responseMessage' => $error_msg];
    }
    curl_close($ch);

    $response = json_decode($response);
    //return $keyResponse;
    if ($status_code != 202) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => $response->message];
    }

    return ['error' => 0, 'statusCode' => 201, 'responseMessage' => 'Successful', 'referenceId' => $req_reference];

    //request payload
    // {
    //   "amount": "1000",
    //   "currency": "EUR",
    //   "externalId": "mom-12332",
    //   "payer": {
    //     "partyIdType": "MSISDN",
    //     "partyId": "2348039688395"
    //   },
    //   "payerMessage": "Testing testing",
    //   "payeeNote": "Test"
    // }



  }


  public function getRefundDetails($req_reference)
  {

    $login_response = $this->getToken();
    if ($login_response['error'] == 1) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => "token was not generated"];
      //return "token was not generated";
    }
    $token = $login_response['token'];
    $disbursement_sub_key = env("MOMO_DISBURSEMENT_KEY"); //"b082007a526d4925a7eca6c6b6b7c163";
    $target_env = env("MOMO_ENV"); //"sandbox"
    $ch = curl_init();
    $url = $this->url . "/disbursement/v1_0/deposit/$req_reference";
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      "Content-Type: application/json",
      "Authorization: Bearer $token",
      "X-Target-Environment: $target_env",
      "Ocp-Apim-Subscription-Key: $disbursement_sub_key"

    ]);

    $response = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_errno($ch);

    if ($err) {
      $error_msg = curl_error($ch);
      curl_close($ch);
      return ['error' => 1, 'statusCode' => 500, 'responseMessage' => $error_msg];
    }
    curl_close($ch);

    $response = json_decode($response);
    //return $keyResponse;
    if (is_null($response)) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => 'Null response!'];
    } elseif ($status_code != 200) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => $response->message];
    }

    return ['error' => 0, 'statusCode' => 201, 'responseMessage' => 'Successful', 'transactionDetails' => $response];

    // {
    //   "financialTransactionId": "352270854",
    //   "externalId": "mud-12355",
    //   "amount": "1000",
    //   "currency": "EUR",
    //   "payee": {
    //     "partyIdType": "PARTY_CODE",
    //     "partyId": "1d84b01f-83c3-41f2-986b-25cc946f020f"
    //   },
    //   "payerMessage": "Test refund",
    //   "payeeNote": "string",
    //   "status": "SUCCESSFUL"
    // }

  }


  public function transferOrDisburse($amount, $currency, $ref, $payerMsg, $payeeNote, $msisdn_or_id, $type = "MSISDN")
  {

    $login_response = $this->getToken();
    if ($login_response['error'] == 1) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => "token was not generated"];
      //return "token was not generated";
    }
    $token = $login_response['token'];
    $disbursement_sub_key = "b082007a526d4925a7eca6c6b6b7c163";
    $target_env = "sandbox";
    $req_reference = $this->gen_uuid(); //"44ea9f7b-60e3-417a-aa6b-aa20fb157831";
    $payload = [
      "amount" => $amount,
      "currency" => $currency,
      "externalId" => $ref,
      "payer" => [
        "partyIdType" => strtoupper($type),
        "partyId" => $msisdn_or_id
      ],
      "payerMessage" => $payerMsg,
      "payeeNote" => $payeeNote
    ];
    $ch = curl_init();
    $url = $this->url . "/disbursement/v1_0/transfer";
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, true));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      "Content-Type: application/json",
      "Authorization: Bearer $token",
      "X-Target-Environment: $target_env",
      "Ocp-Apim-Subscription-Key: $disbursement_sub_key",
      "X-Reference-Id: $req_reference"

    ]);

    $response = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_errno($ch);

    if ($err) {
      $error_msg = curl_error($ch);
      curl_close($ch);
      return ['error' => 1, 'statusCode' => 500, 'responseMessage' => $error_msg];
    }
    curl_close($ch);

    $response = json_decode($response);
    //return $keyResponse;
    if ($status_code != 202) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => $response->message];
    }

    return ['error' => 0, 'statusCode' => 201, 'responseMessage' => 'Successful', 'referenceId' => $req_reference];

    //request payload
    // {
    //   "amount": "1000",
    //   "currency": "EUR",
    //   "externalId": "mom-12332",
    //   "payer": {
    //     "partyIdType": "MSISDN",
    //     "partyId": "2348039688395"
    //   },
    //   "payerMessage": "Testing testing",
    //   "payeeNote": "Test"
    // }



  }


  public function getTransferDetails($req_reference)
  {

    $login_response = $this->getToken();
    if ($login_response['error'] == 1) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => "token was not generated"];
      //return "token was not generated";
    }
    $token = $login_response['token'];
    $disbursement_sub_key = env("MOMO_DISBURSEMENT_KEY"); //"b082007a526d4925a7eca6c6b6b7c163";
    $target_env = env("MOMO_ENV"); //"sandbox"
    $ch = curl_init();
    $url = $this->url . "/disbursement/v1_0/transfer/$req_reference";
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      "Content-Type: application/json",
      "Authorization: Bearer $token",
      "X-Target-Environment: $target_env",
      "Ocp-Apim-Subscription-Key: $disbursement_sub_key"

    ]);

    $response = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_errno($ch);

    if ($err) {
      $error_msg = curl_error($ch);
      curl_close($ch);
      return ['error' => 1, 'statusCode' => 500, 'responseMessage' => $error_msg];
    }
    curl_close($ch);

    $response = json_decode($response);
    //return $keyResponse;
    if (is_null($response)) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => 'Null response!'];
    } elseif ($status_code != 200) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => $response->message];
    }

    return ['error' => 0, 'statusCode' => 201, 'responseMessage' => 'Successful', 'transactionDetails' => $response];

    // {
    //   "amount": 100,
    //   "currency": "GBP",
    //   "financialTransactionId": 363440463,
    //   "externalId": 83453,
    //   "payee": {
    //     "partyIdType": "MSISDN",
    //     "partyId": 4609274685.0
    //   },
    //   "status": "SUCCESSFUL"
    // }

  }

  
  public function subscribe_json($msisdn, $service_code, $transaction_id)
  {
    $ip = "41.206.4.162";
    $port = "8310";
    $pwd = "Huawei123";
    $ts = @date('Ymdhis');
    //$ts = '20200313010452';
    //$password = md5($spid . $pwd . $ts);

    //<! – Subscription request -->
    $xml = "<?xml version='1.0'?>
            <subscribeServiceRequest>
                <msisdn>$msisdn</msisdn>
                <serviceCode>$service_code</serviceCode>
                <requestTime>$ts</requestTime>
                <externalTransactionId>$transaction_id</externalTransactionId>
            </subscribeServiceRequest>";

    $headers = array(
      "Content-type: application/json",
      "Authorization: Basic dhrucf&%$===wr"
    );

    // echo things out to see what you are sending
    //Logger::info("Subscribe XML Request - ", [$xml]);

    /*    echo "====Request XML Start====" . PHP_EOL;
            echo $xml . PHP_EOL;
            echo "====Request XML End====" . PHP_EOL;
        */
    // Set to one provided by MTN

    $url = $this->sub_url . "/subscribe";
    $soap_do = curl_init();
    curl_setopt($soap_do, CURLOPT_URL, $url);
    curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($soap_do, CURLOPT_POST, true);
    curl_setopt($soap_do, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($soap_do, CURLOPT_POSTFIELDS, json_encode([
          "msisdn" => $msisdn,
          "serviceCode" => $service_code,
          "requestTime" => $ts,
          "externalTransactionId" => $transaction_id
        ]));

    // the result of the soap call. You can log it and examine it

    $result = curl_exec($soap_do);

    // log($result);

    $err = curl_error($soap_do);
    if ($err) {
      $error_msg = curl_error($soap_do);
      curl_close($soap_do);
      return ['error' => 1, 'statusCode' => 500, 'responseMessage' => $error_msg];
    }
    curl_close($soap_do);
    //$transaction = json_decode($result);

    //print_r($result); exit;

    // echo out to see what you got

    //    echo "======Response XML Start======" . PHP_EOL;
    //print_r($result);
    //  echo "======Response XML  End======" . PHP_EOL;

    //<! -- Acknowledgement response -->
    $res = "<?xml version='1.0' encoding='UTF-8'?>
         <subscribeServiceResponse>
           <resultCode>300</resultCode>
           <resultDesc>Subscription pending</resultDesc>
           <transactionId>175093464</transactionId>
            <externalTransactionId>2813131655572699</externalTransactionId>
           <status>PENDING</status>
         </subscribeServiceResponse>";

    // $string = subscribe($conn, $num, $spid, $product_id, $service_id);
    // $string = str_replace("ns1:", "", "$string");
    // $string = str_replace("env:", "", "$string");
    // $xml = simplexml_load_string($string);
    // $desc = $xml->Body->subscribeProductResponse->subscribeProductRsp->resultDescription;
    // $response = $xml->Body->subscribeProductResponse->subscribeProductRsp->result;
    // $data = array("responseCode" => "$response", "description" => "$desc");
    // echo $result = json_encode($data);
    Logger::info("Subscribe XML Response - ", [$result]);

    // $xml = simplexml_load_string($result);
    // $resultCode = $xml->Body->subscribeServiceResponse->resultCode;
    // $resultDesc = $xml->Body->subscribeServiceResponse->resultDesc;
    // $trans_id = $xml->Body->subscribeServiceResponse->externalTransactionId;
    // $status = $xml->Body->subscribeServiceResponse->status;
    
    //return ['error' => 0, 'statusCode' => 201, 'responseMessage' => $resultDesc, 'status' => $status, 'trans_id' => $trans_id, 'responseCode' => $resultCode];
    return json_decode($result);
  }

  public function subscribe($msisdn, $service_code, $transaction_id, $auto_renew = false)
  {
    $ip = "41.206.4.162";
    $port = "8310";
    $pwd = "Huawei123";
    $ts = @date('Ymdhis');
    //$ts = '20200313010452';
    //$password = md5($spid . $pwd . $ts);

    //<! – Subscription request -->
    $xml = "<?xml version='1.0'?>
            <subscribeServiceRequest>
                <msisdn>$msisdn</msisdn>
                <serviceCode>$service_code</serviceCode>
                <requestTime>$ts</requestTime>
                <externalTransactionId>$transaction_id</externalTransactionId>
                <autoRenewal>false</autoRenewal>
                <paymentOption>MOMO</paymentOption>
            </subscribeServiceRequest>";

    $headers = array(
      "Content-Type: text/xml",
      //"Content-length: " . strlen($xml),
      "user: Pepperest",
      "pass:  mudaala@4245",
      //"user: PEPPEREST",
      //"pass:  P@ssword",
      //"Authorization: Basic dhrucf&%$===wr"
    );

    // echo things out to see what you are sending
    Logger::info("Subscribe XML Request - ", [$xml]);

    /*    echo "====Request XML Start====" . PHP_EOL;
            echo $xml . PHP_EOL;
            echo "====Request XML End====" . PHP_EOL;
        */
    // Set to one provided by MTN

    $url = $this->sub_url . "/subscribe";
    $soap_do = curl_init();
    curl_setopt($soap_do, CURLOPT_URL, $url);
    curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($soap_do, CURLOPT_POST, true);
    curl_setopt($soap_do, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($soap_do, CURLOPT_POSTFIELDS, $xml);

    // the result of the soap call. You can log it and examine it

    $result = curl_exec($soap_do);

    // log($result);

    $err = curl_error($soap_do);
    if ($err) {
      $error_msg = curl_error($soap_do);
      curl_close($soap_do);
      return ['error' => 1, 'statusCode' => 500, 'responseMessage' => $error_msg];
    }
    curl_close($soap_do);

    //print_r($result); exit;

    // echo out to see what you got

    //    echo "======Response XML Start======" . PHP_EOL;
    //print_r($result);
    //  echo "======Response XML  End======" . PHP_EOL;

    //<! -- Acknowledgement response -->
    $res = "<?xml version='1.0' encoding='UTF-8'?>
         <subscribeServiceResponse>
           <resultCode>300</resultCode>
           <resultDesc>Subscription pending</resultDesc>
           <transactionId>175093464</transactionId>
            <externalTransactionId>2813131655572699</externalTransactionId>
           <status>PENDING</status>
         </subscribeServiceResponse>";

    // $string = subscribe($conn, $num, $spid, $product_id, $service_id);
    // $string = str_replace("ns1:", "", "$string");
    // $string = str_replace("env:", "", "$string");
    // $xml = simplexml_load_string($string);
    // $desc = $xml->Body->subscribeProductResponse->subscribeProductRsp->resultDescription;
    // $response = $xml->Body->subscribeProductResponse->subscribeProductRsp->result;
    // $data = array("responseCode" => "$response", "description" => "$desc");
    // echo $result = json_encode($data);
    Logger::info("Subscribe XML Response - ", [$result]);

    $xml = simplexml_load_string($result);
    // $resultCode = $xml->Body->subscribeServiceResponse->resultCode;
    // $resultDesc = $xml->Body->subscribeServiceResponse->resultDesc;
    // $trans_id = $xml->Body->subscribeServiceResponse->externalTransactionId;
    // $status = $xml->Body->subscribeServiceResponse->status;

    // return ['error' => 0, 'statusCode' => 201, 'responseMessage' => $resultDesc, 'status' => $status, 'trans_id' => $trans_id, 'responseCode' => $resultCode];
    if (!is_null($xml)) {
        $resultCode = ((array)$xml->resultCode)[0];
        $resultDesc = ((array)$xml->resultDesc)[0];
        $status = ((array)$xml->status)[0];
        if ($resultCode >= 300) {
          return ['error' => 1, 'statusCode' => 400, 'responseMessage' => $resultDesc, 'resultCode' => $resultCode, 'status' => $status];
        }
        $trans_id = ((array)$xml->transactionId)[0];
        //$our_trans_id = ((array)$xml->externalTransactionId)[0];

        return ['error' => 0, 'statusCode' => 201, 'responseMessage' => $resultDesc, 'resultCode' => $resultCode, 'ext_trans_id' => $trans_id, 'internal_trans_id' => $transaction_id, 'status' => $status];
        //Logger::info("Subscription Callback Response - ", $response);
      }
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => 'Empty response'];
  }

  public function subscribe_soap($msisdn, $service_code, $transaction_id){
        $url = $this->sub_url . "/subscribe";
        
        ini_set('soap.wsdl_cache_enabled',0);
        ini_set('soap.wsdl_cache_ttl',0);
        
        $ServiceName1 =   "PRST";
        $ServiceKey1  =   "B98B90P2727373738384848JKSO08890B8";
        $headers = array();
        try{
        
            $client = new SoapClient($url);
            
            $ns = "http://tempuri.org/";
            $headerbody = array('servicename'=> $ServiceName1,
                                          'passkey'=>$ServiceKey1); 
            $headers = new SoapHeader($ns, 'xHeader', $headerbody); 

            $client->__setSoapHeaders($headers);
          
            $body = array('AccountNo'=> $acctNo, 'BankCode'=> $bankCode);
            //$body = array('AccountNo'=> '3057031223', 'BankCode'=> '011');

            //{"NameEnquiryResult":{"AccountName":"Test Account","ResponseCode":"000","ResponseMessage":"[2] Name enquiry successful"}}
            
            $response = $client->NameEnquiry(array('request' => $body));

            $res = $response->NameEnquiryResult;
            //$status = $res->Status;
            
            if ($res->ResponseCode == "000") {
              return ['error' => 0, 'statusCode' => $res->ResponseCode, 'responseMessage' => $res->ResponseMessage, 'data' => $res];
            }else{
              return ['error' => 1, 'statusCode' => $res->ResponseCode, 'responseMessage' => $res->ResponseMessage, 'data' => $res];
            }
           
           //echo json_encode($response);

        }catch(Exception $e){
          return ['error' => 1, 'statusCode' => $e->getStatusCode(), 'responseMessage' => $e->getMessage()];
        }
    }



  public function check_sub_status($msisdn)
  {
    $ip = "41.206.4.162";
    $port = "8310";
    $pwd = "Huawei123";
    $ts = @date('Ymdhis');
    //$ts = '20200313010452';
    $password = md5($spid . $pwd . $ts);

    //<! – Subscription request -->
    $xml = "<?xml version='1.0' encoding='UTF-8' standalone='yes'?>
            <getSubscriptionStatusRequest>
                  <msisdn>$msisdn</msisdn>
                  <requestTime>$ts</requestTime>
            </getSubscriptionStatusRequest>";


    $headers = array(
      "POST  HTTP/1.1",
      "Content-type: application/soap+xml; charset=\"utf-8\"",
      "SOAPAction: \"\"",
      "Content-length: " . strlen($xml)
    );

    // echo things out to see what you are sending
    Logger::info("Check Subscription XML Request - ", [$xml]);

    /*    echo "====Request XML Start====" . PHP_EOL;
            echo $xml . PHP_EOL;
            echo "====Request XML End====" . PHP_EOL;
        */
    // Set to one provided by MTN

    $url = $this->sub_url . "/getSubscriptionstatus";
    $soap_do = curl_init();
    curl_setopt($soap_do, CURLOPT_URL, $url);
    curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($soap_do, CURLOPT_POST, true);
    curl_setopt($soap_do, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($soap_do, CURLOPT_POSTFIELDS, $xml);

    // the result of the soap call. You can log it and examine it

    $result = curl_exec($soap_do);

    // log($result);

    $err = curl_error($soap_do);
    if ($err) {
      $error_msg = curl_error($ch);
      curl_close($ch);
      return ['error' => 1, 'statusCode' => 500, 'responseMessage' => $error_msg];
    }
    curl_close($ch);

    // echo out to see what you got
    Logger::info("Check Subscription XML Response - ", [$result]);

    //    echo "======Response XML Start======" . PHP_EOL;
    //print_r($result);
    //  echo "======Response XML  End======" . PHP_EOL;

    //<! -- SUCCESS RESPONSE status code 200 -->
    $res = "<?xml version='1.0' encoding='UTF-8' standalone='yes'?>
                <getSubscriptionStatusResponse>
                    <resultCode>0</resultCode>
                    <resultDesc>Active Subscription</resultDesc>
                    <ServiceCode>Weekly-2500</ServiceCode>
                    <ExpiryDate>XX:XX:XXXX</ExpiryDate>
                </getSubscriptionStatusResponse>";

    $xml = simplexml_load_string($result);
    $resultCode = $xml->Body->getSubscriptionStatusResponse->resultCode;
    $resultDesc = $xml->Body->getSubscriptionStatusResponse->resultDesc;
    $service_code = $xml->Body->getSubscriptionStatusResponse->ServiceCode;
    $expiry_date = $xml->Body->getSubscriptionStatusResponse->ExpiryDate;

    return ['error' => 0, 'statusCode' => 201, 'responseMessage' => $resultDesc, 'serviceCode' => $service_code, 'expiryDate' => $expiry_date];
  }


  public function sub_callback()
  {

    $data = file_get_contents('php://input');

    // echo out to see what you got

    //    echo "======Response XML Start======" . PHP_EOL;
    //print_r($result);
    //  echo "======Response XML  End======" . PHP_EOL;

    //<! -- SUCCESS RESPONSE status code 200 -->
    $res = "<?xml version='1.0' encoding='UTF-8'?>
                   <status>0K</status>";
    echo $res;
    Logger::info("Subscription Callback XML Response - ", [$data]);

    $req = "<?xml version='1.0' encoding='UTF-8' standalone='yes'?>
                <subscribeServiceResponse>
                <resultCode>0</resultCode>
                <resultDesc>Subscription Successful</resultDesc>
                <TransactionId>23176701875xxxxxxx</externalTransactionId>
                <externalTransactionId>2317670187537675235</externalTransactionId>
                </subscribeServiceResponse>
                ";


    $xml = simplexml_load_string($data);
    $resultCode = $xml->Body->subscribeServiceResponse->resultCode;
    $resultDesc = $xml->Body->subscribeServiceResponse->resultDesc;
    $trans_id = $xml->Body->subscribeServiceResponse->TransactionId;
    $our_trans_id = $xml->Body->subscribeServiceResponse->externalTransactionId;

    $response = ['error' => 0, 'statusCode' => 201, 'responseMessage' => $resultDesc, 'resultCode' => $resultCode, 'ext_trans_id' => $trans_id, 'internal_trans_id' => $our_trans_id];
    Logger::info("Subscription Callback Response - ", [$response]);

    return $res;
  }


  public function getCustDetails($acctNo, $bankCode)
  {
    if ($bankCode != '011') {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => 'Only first bank accounts are allowed'];
    }
    $url = $this->cust_detail_url;

    ini_set('soap.wsdl_cache_enabled', 0);
    ini_set('soap.wsdl_cache_ttl', 0);

    $ServiceName =   'PRST';
    $ServiceKey  =   '8890B8';

    $headers = array();


    try {
      //$client = new SoapClient($url, array('soap_version' => SOAP_1_2,'trace' => 1,'exceptions'=>0));
      $client = new SoapClient($url, array('trace' => 1));
      $headers[] = new SoapHeader(
        'http://tempuri.org/',
        'servicename',
        $ServiceName
      );

      $headers[] = new SoapHeader(
        'http://tempuri.org/',
        'passkey',
        $ServiceKey
      );

      $client->__setSoapHeaders($headers);



      $response = $client->FetchCustDetails2(array('AccountNumber' => $acctNo));

      $res = $response->FetchCustDetails2Result->AccountDetails2;
      $status = $res->Response;

      if ($status->ResponseCode == "00") {
        return ['error' => 0, 'statusCode' => $status->ResponseCode, 'responseMessage' => $status->ResponseMessage, 'data' => $res];
      } else {
        return ['error' => 1, 'statusCode' => $status->ResponseCode, 'responseMessage' => $status->ResponseMessage, 'data' => $res];
      }
    } catch (Exception $e) {
      //echo json_encode(['code' => 201, 'desc' => $e->getMessage()]); 
      return ['error' => 1, 'statusCode' => $e->getStatusCode(), 'responseMessage' => $e->getMessage()];
    }
  }
}
