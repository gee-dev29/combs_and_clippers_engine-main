<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Log as Logger;
use Carbon\Carbon;

class PawaPayUtils
{

  private $url;
  private $token;
  private $correspondent;

  public function __construct($correspondent = "MTN_MOMO_UGA")
  {
    $this->url = env("PawaPay_Url");
    //$this->url = "https://api.pawapay.cloud";
    $this->token = env("PawaPay_Token");
    $this->correspondent = $correspondent;
  }

  // {
  //   "error": {
  //       "type": "error_type",
  //       "code": "response_code",
  //       "message": "Detailed error message goes here.."
  //   }
  // }

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

  public function requestDeposit($msisdn, $amount, $currency, $ref, $note)
  {
    $token = $this->token;
    $payload = [
      "depositId" => $ref,
      "amount" => $amount,
      "currency" => $currency,
      "correspondent" => $this->correspondent,
      "payer" => [
        "type" => "MSISDN",
        "address" => [
          "value" => $msisdn
        ]
      ],
      "customerTimestamp" => Carbon::now(),
      "statementDescription" => $note
    ];
    $curl = curl_init();
    $url = $this->url . '/deposits';
    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_SSL_VERIFYPEER => 0,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => json_encode($payload, true),
      CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "Authorization: Bearer $token"

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
    if ($status_code != 200 || isset($response->errorId)) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => 'Failed', 'response' => $response];
    }

    return ['error' => 0, 'statusCode' => 201, 'responseMessage' => "Successful", 'response' => $response];
    // {
    //   "depositId": "f4401bd2-1568-4140-bf2d-eb77d2b2b639",
    //   "status": "ACCEPTED",
    //   "created": "2020-10-19T11:17:01Z"
    // }
  }

  public function getDepositStatus($req_reference)
  {
    $token = $this->token;
    $url = $this->url . "/deposits/$req_reference";
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
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
    if ($status_code != 200 || isset($response->errorId)) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => 'Failed', 'response' => $response];
    }

    return ['error' => 0, 'statusCode' => 201, 'responseMessage' => "Successful", 'response' => $response[0]];
    // [
    //   {
    //   "depositId": "8917c345-4791-4285-a416-62f24b6982db",
    //   "status": "COMPLETED",
    //   "requestedAmount": "123.00",
    //   "depositedAmount": "123.00",
    //   "currency": "ZMW",
    //   "country": "ZMB",
    //   "payer": {
    //   "type": "MSISDN",
    //   "address": {
    //   "value": "260763456789"
    //   }
    //   },
    //   "correspondent": "MTN_MOMO_ZMB",
    //   "statementDescription": "To ACME company",
    //   "customerTimestamp": "2020-10-19T08:17:00Z",
    //   "created": "2020-10-19T08:17:01Z",
    //   "respondedByPayer": "2020-10-19T08:17:02Z",
    //   "correspondentIds": {
    //   "SOME_CORRESPONDENT_ID": "12356789"
    //   }
    //   }
    // ]

  }

  public function resendDepositCallback($req_reference)
  {
    $token = $this->token;
    $payload = [
      "depositId" => $req_reference
    ];
    $curl = curl_init();
    $url = $this->url . '/deposits/resend-callback';
    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_SSL_VERIFYPEER => 0,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => json_encode($payload, true),
      CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "Authorization: Bearer $token"

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
    if ($status_code != 200 || isset($response->errorId)) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => 'Failed', 'response' => $response];
    }

    return ['error' => 0, 'statusCode' => 201, 'responseMessage' => "Successful", 'response' => $response];
    // {
    //   "depositId": "f4401bd2-1568-4140-bf2d-eb77d2b2b639",
    //   "status": "ACCEPTED",
    //   "created": "2020-10-19T11:17:01Z"
    // }
  }

  public function requestPayout($msisdn, $amount, $currency, $ref, $note)
  {
    $token = $this->token;
    $payload = [
      "payoutId" => $ref,
      "amount" => $amount,
      "currency" => $currency,
      "correspondent" => $this->correspondent,
      "recipient" => [
        "type" => "MSISDN",
        "address" => [
          "value" => $msisdn
        ]
      ],
      "customerTimestamp" => Carbon::now(),
      "statementDescription" => $note
    ];
    $curl = curl_init();
    $url = $this->url . '/payouts';
    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_SSL_VERIFYPEER => 0,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => json_encode($payload, true),
      CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "Authorization: Bearer $token"

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
    if ($status_code != 200 || isset($response->errorId)) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => 'Failed', 'response' => $response];
    }

    return ['error' => 0, 'statusCode' => 201, 'responseMessage' => "Successful", 'response' => $response];
    // {
    //   "depositId": "f4401bd2-1568-4140-bf2d-eb77d2b2b639",
    //   "status": "ACCEPTED",
    //   "created": "2020-10-19T11:17:01Z"
    // }
  }

  public function getPayoutStatus($req_reference)
  {
    $token = $this->token;
    $url = $this->url . "/payouts/$req_reference";
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
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
    if ($status_code != 200 || isset($response->errorId)) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => 'Failed', 'response' =>  $response];
    }

    return ['error' => 0, 'statusCode' => 201, 'responseMessage' => "Successful", 'response' => $response[0]];
    // [
    //   {
    //   "payoutId": "8917c345-4791-4285-a416-62f24b6982db",
    //   "status": "COMPLETED",
    //   "requestedAmount": "123.00",
    //   "depositedAmount": "123.00",
    //   "currency": "ZMW",
    //   "country": "ZMB",
    //   "payer": {
    //   "type": "MSISDN",
    //   "address": {
    //   "value": "260763456789"
    //   }
    //   },
    //   "correspondent": "MTN_MOMO_ZMB",
    //   "statementDescription": "To ACME company",
    //   "customerTimestamp": "2020-10-19T08:17:00Z",
    //   "created": "2020-10-19T08:17:01Z",
    //   "respondedByPayer": "2020-10-19T08:17:02Z",
    //   "correspondentIds": {
    //   "SOME_CORRESPONDENT_ID": "12356789"
    //   }
    //   }
    // ]

  }

  public function resendPayoutCallback($req_reference)
  {
    $token = $this->token;
    $payload = [
      "payoutId" => $req_reference
    ];
    $curl = curl_init();
    $url = $this->url . '/payouts/resend-callback';
    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_SSL_VERIFYPEER => 0,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => json_encode($payload, true),
      CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "Authorization: Bearer $token"

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
    if ($status_code != 200 || isset($response->errorId)) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => 'Failed', 'response' => $response];
    }

    return ['error' => 0, 'statusCode' => 201, 'responseMessage' => "Successful", 'response' => $response];
    // {
    //   "depositId": "f4401bd2-1568-4140-bf2d-eb77d2b2b639",
    //   "status": "ACCEPTED",
    //   "created": "2020-10-19T11:17:01Z"
    // }
  }


  public function cancelEnqueuedPayout($req_reference)
  {
    $token = $this->token;
    $curl = curl_init();
    $url = $this->url . "/payouts/fail-enqueued/$req_reference";
    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_SSL_VERIFYPEER => 0,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "Authorization: Bearer $token"

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
    if ($status_code != 200 || isset($response->errorId)) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => $response];
    }

    return ['error' => 0, 'statusCode' => 201, 'responseMessage' => "Successful", 'response' => $response];
    // {
    //   "payoutId": "f4401bd2-1568-4140-bf2d-eb77d2b2b639",
    //   "status": "ACCEPTED"
    // }
  }


  public function requestRefund($depositRef, $refundRef)
  {
    $token = $this->token;
    $payload = [
      "depositId" => $depositRef,
      "refundId" => $refundRef
    ];
    $curl = curl_init();
    $url = $this->url . '/refunds';
    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_SSL_VERIFYPEER => 0,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => json_encode($payload, true),
      CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "Authorization: Bearer $token"

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
    if ($status_code != 200 || isset($response->errorId)) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => 'Failed', 'response' => $response];
    }

    return ['error' => 0, 'statusCode' => 201, 'responseMessage' => "Successful", 'response' => $response];
    // {
    //   "depositId": "f4401bd2-1568-4140-bf2d-eb77d2b2b639",
    //   "status": "ACCEPTED",
    //   "created": "2020-10-19T11:17:01Z"
    // }
  }

  public function getRefundStatus($refundRef)
  {
    $token = $this->token;
    $url = $this->url . "/refunds/$refundRef";
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
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
    if ($status_code != 200 || isset($response->errorId)) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => 'Failed', 'response' =>  $response];
    }

    return ['error' => 0, 'statusCode' => 201, 'responseMessage' => "Successful", 'response' => $response[0]];
    // [
    //   {
    //   "refundId": "8917c345-4791-4285-a416-62f24b6982db",
    //   "status": "COMPLETED",
    //   "requestedAmount": "123.00",
    //   "depositedAmount": "123.00",
    //   "currency": "ZMW",
    //   "country": "ZMB",
    //   "payer": {
    //   "type": "MSISDN",
    //   "address": {
    //   "value": "260763456789"
    //   }
    //   },
    //   "correspondent": "MTN_MOMO_ZMB",
    //   "statementDescription": "To ACME company",
    //   "customerTimestamp": "2020-10-19T08:17:00Z",
    //   "created": "2020-10-19T08:17:01Z",
    //   "respondedByPayer": "2020-10-19T08:17:02Z",
    //   "correspondentIds": {
    //   "SOME_CORRESPONDENT_ID": "12356789"
    //   }
    //   }
    // ]

  }

  public function resendRefundCallback($refundRef)
  {
    $token = $this->token;
    $payload = [
      "refundId" => $refundRef
    ];
    $curl = curl_init();
    $url = $this->url . '/refunds/resend-callback';
    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_SSL_VERIFYPEER => 0,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => json_encode($payload, true),
      CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "Authorization: Bearer $token"

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
    if ($status_code != 200 || isset($response->errorId)) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => 'Failed', 'response' => $response];
    }

    return ['error' => 0, 'statusCode' => 201, 'responseMessage' => "Successful", 'response' => $response];
    // {
    //   "depositId": "f4401bd2-1568-4140-bf2d-eb77d2b2b639",
    //   "status": "ACCEPTED",
    //   "created": "2020-10-19T11:17:01Z"
    // }
  }

  public function predictCorrespondent($msisdn)
  {
    $token = $this->token;
    $payload = [
      "msisdn" => $msisdn
    ];
    $curl = curl_init();
    $url = $this->url . '/v1/predict-correspondent';
    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_SSL_VERIFYPEER => 0,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => json_encode($payload, true),
      CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "Authorization: Bearer $token"

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
    if ($status_code != 200 || isset($response->errorId)) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => 'Failed', 'response' => $response];
    }

    return ['error' => 0, 'statusCode' => 201, 'responseMessage' => "Successful", 'response' => $response];
    // {
    //   "country": "ZMB",
    //   "operator": "MTN",
    //   "correspondent": "MTN_MOMO_ZMB",
    //   "msisdn": "260763456789"
    // }
  }
}
