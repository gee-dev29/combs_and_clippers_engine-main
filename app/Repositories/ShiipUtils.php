<?php

namespace App\Repositories;

use Carbon\Carbon;
use GuzzleHttp\Client;
use App\Models\ShiipToken;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log as Logger;

class ShiipUtils
{

  public function __construct()
  {
    $this->url = "https://delivery.apiideraos.com/api/v2";
    //$this->url = "https://delivery-staging.apiideraos.com/api/v2";
    //$this->url = "https://stoplight.io/mocks/ideraos/shiip/10884705";
  }

  public function shiipLogin_test()
  {
    $email = env('SHIIP_EMAIL');
    $password = env('SHIIP_PASSWORD');
    $curl = curl_init();
    $url = $this->url . '/auth/login';
    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_SSL_VERIFYPEER => 0,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => json_encode([
        "email_phone" => $email,
        "password" => $password
      ]),
      CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "cache-control: no-cache",
      ],
    ));

    $response = curl_exec($curl);
    $err = curl_errno($curl);

    if ($err) {
      $error_msg = curl_error($curl);
      curl_close($curl);
      return ['error' => 1, 'statusCode' => 500, 'responseMessage' => $error_msg, 'response' => $response];
    }
    curl_close($curl);

    $response = json_decode($response);
    //return $response;
    if (!$response->status) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => $response->message];
    }

    return ['error' => 0, 'statusCode' => 201, 'responseMessage' => $response->message, 'userDetails' => $response->data->user, 'token' => $response->data->token];
  }

  public function shiipLogin()
  {

    $tokenObj = ShiipToken::whereNotNull('id')->first();
    $date_time = Carbon::now();
    if (!is_null($tokenObj) && $tokenObj->token_expires_at > $date_time) {
      return ['error' => 0, 'statusCode' => 201, 'responseMessage' => 'Valid Token', 'token' => $tokenObj->token, 'user_id' => $tokenObj->user_id];
    }
    $email = env('SHIIP_EMAIL');
    $password = env('SHIIP_PASSWORD');
    $curl = curl_init();
    $url = $this->url . '/auth/login';
    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_SSL_VERIFYPEER => 0,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => json_encode([
        "email_phone" => $email,
        "password" => $password
      ]),
      CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "cache-control: no-cache",
      ],
    ));

    $response = curl_exec($curl);
    $err = curl_errno($curl);

    if ($err) {
      $error_msg = curl_error($curl);
      curl_close($curl);
      return ['error' => 1, 'statusCode' => 500, 'responseMessage' => $error_msg, 'response' => $response];
    }
    curl_close($curl);

    $response = json_decode($response);
    //return $response;
    if (!$response->status) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => $response->message];
    }

    if (!is_null($tokenObj)) {
      $tokenObj->update([
        'token' => $response->data->token,
        'token_expires_at' => $date_time->addDays(1),
        'user_id' => $response->data->user->id
      ]);
    } else {
      ShiipToken::create([
        'token' => $response->data->token,
        'token_expires_at' => $date_time->addDays(1),
        'user_id' => $response->data->user->id
      ]);
    }

    return ['error' => 0, 'statusCode' => 201, 'responseMessage' => $response->message, 'userDetails' => $response->data->user, 'token' => $response->data->token, 'user_id' => $response->data->user->id];
  }


  public function validateAddress($address)
  {
    $login_response = $this->shiipLogin();
    if ($login_response['error'] == 1) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => "token was not generated"];
      //return "token was not generated";
    }
    $token = $login_response['token'];

    $client = new Client();
    $headers = [
      "Authorization" => "Bearer $token"
    ];
    $url = $this->url . "/geoaddress/$address";
    $request = new Request('GET', $url, $headers);
    $res = $client->sendAsync($request)->wait();
    $response = json_decode($res->getBody());
    if (is_null($response)) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => 'Null response!'];
    } elseif (!$response->status) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => $response->message];
    }

    return ['error' => 0, 'statusCode' => 201, 'responseMessage' => $response->message, 'addressDetails' => $response->data];
  }

  public function getShipmentRates($items, $fromAddress, $toAddress, $shipmentType = 'local')
  {
    $login_response = $this->shiipLogin();
    if ($login_response['error'] == 1) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => "token was not generated"];
      //return "token was not generated";
    }
    $token = $login_response['token'];

    $client = new Client();
    $headers = [
      "Authorization" => "Bearer $token",
      "Content-Type" => "application/json"
    ];

    $parcels = [];
    $parcels['weight'] = 0;
    $parcels['height'] = 0;
    $parcels['length'] = 0;
    $parcels['width'] = 0;
    $parcels['date'] = Carbon::now()->format('Y-m-d H:i');
    $pickup_date = Carbon::now()->addDays(1)->format('Y-m-d H:i');

    $sent_items = [];

    foreach ($items as $item) {
      $parcels['weight'] += $item->productInfo->weight;
      $parcels['height'] =  $parcels['height'] < $item->productInfo->height ? $item->productInfo->height : $parcels['height'];
      $parcels['length'] =  $parcels['length'] < $item->productInfo->length ? $item->productInfo->length : $parcels['length'];
      $parcels['width'] =  $parcels['width'] < $item->productInfo->width ? $item->productInfo->width : $parcels['width'];
      $s_item = [];
      $s_item['category'] = $item->description;
      $s_item['name'] = $item->productname;
      $s_item['quantity'] = $item->quantity;
      $s_item['weight'] = $item->productInfo->weight;
      $s_item['height'] = $item->productInfo->height;
      $s_item['width'] = $item->productInfo->width;
      $s_item['length'] = $item->productInfo->length;
      $s_item['amount'] = $item->price;
      $s_item['description'] = $item->description;
      $s_item['pickup_date'] = $pickup_date;
      $sent_items[] = $s_item;
    }

    $toAddress = $toAddress->toArray();
    $fromAddress = $fromAddress->toArray();

    unset($toAddress['created_at'], $toAddress['updated_at'], $fromAddress['created_at'], $fromAddress['updated_at']);

    $payload = [
      "type" => $shipmentType,
      "items" => $sent_items,
      "parcels" => $parcels,
      "fromAddress" => $fromAddress,
      "toAddress" => $toAddress
    ];

    $body = json_encode($payload, JSON_PRETTY_PRINT);
    $url = $this->url . '/tariffs/allprice';
    $request = new Request('POST', $url, $headers, $body);
    $res = $client->sendAsync($request)->wait();
    $response = json_decode($res->getBody());

    if (is_null($response)) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => 'Null response!'];
    } elseif (!$response->status) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => $response->message];
    }
    return ['error' => 0, 'statusCode' => 201, 'responseMessage' => $response->message, 'rates' => $response->data->rates, 'get_rates_key' => $response->data->get_rates_key, 'redis_key' => $response->data->redis_key, 'kwik_key' => $response->data->kwik_key];

    // $shipments = Http::withToken($token)->post(
    //   $url,
    //   $payload
    // )->throw()->json();

    // return $shipments;
  }

  public function bookShipment($redis_key, $rate_id, $platform = "web2")
  {
    $login_response = $this->shiipLogin();
    if ($login_response['error'] == 1) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => "token was not generated"];
      //return "token was not generated";
    }
    $token = $login_response['token'];
    $user_id = $login_response['user_id'];


    $client = new Client();
    $headers = [
      "Authorization" => "Bearer $token",
      "Content-Type" => "application/json"
    ];

    $payload = [
      "redis_key" => $redis_key,
      "user_id" => $user_id,
      "rate_id" => $rate_id,
      "platform" => $platform
    ];

    $body = json_encode($payload, JSON_PRETTY_PRINT);
    $url = $this->url . '/shipments';
    $request = new Request('POST', $url, $headers, $body);
    $res = $client->sendAsync($request)->wait();
    $response = json_decode($res->getBody());

    if (!$response->status) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => $response->message];
    }

    return ['error' => 0, 'statusCode' => 201, 'responseMessage' => $response->message, 'bookingStatusCode' => $response->data->code, 'bookingStatus' => $response->data->message];
  }

  public function trackShipment($reference)
  {
    $login_response = $this->shiipLogin();
    if ($login_response['error'] == 1) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => "token was not generated"];
      //return "token was not generated";
    }
    $token = $login_response['token'];

    $client = new Client();
    $headers = [
      "Authorization" => "Bearer $token"
    ];
    $url = $this->url . "/shipments/track?reference=$reference";
    $request = new Request('GET', $url, $headers);
    $res = $client->sendAsync($request)->wait();
    $response = json_decode($res->getBody());

    if (!$response->status) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => $response->message];
    }

    return ['error' => 0, 'statusCode' => 201, 'responseMessage' => $response->message, 'shipmentDetails' => $response->data];
  }

  public function getAllBookedShipment()
  {
    $login_response = $this->shiipLogin();
    if ($login_response['error'] == 1) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => "token was not generated"];
      //return "token was not generated";
    }
    $token = $login_response['token'];

    $client = new Client();
    $headers = [
      "Authorization" => "Bearer $token"
    ];
    $url = $this->url . "/all/orders";
    $request = new Request('GET', $url, $headers);
    $res = $client->sendAsync($request)->wait();
    $response = json_decode($res->getBody());

    if (!$response->status) {
      return ['error' => 1, 'statusCode' => 401, 'responseMessage' => $response->message];
    }

    return ['error' => 0, 'statusCode' => 201, 'responseMessage' => $response->message, 'allShipments' => $response->data];
  }
}
