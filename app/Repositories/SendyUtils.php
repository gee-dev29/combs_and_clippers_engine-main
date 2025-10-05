<?php

namespace App\Repositories;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Log as Logger;

class SendyUtils
{

  public function __construct()
  {
    $this->url = "https://fulfillment-api.sendyit.com/v1";
    $this->api_username = "B-SFEG-9768";
    $this->api_key = "Mjc.UmeJWE0qwUlMBc46SFXn90F0hNY1oT4UalCRPbmbu_1yAhlPrLQD5ZEey7jL";
    $this->channel_id = "SC-PJQW-2571";
    $this->participant_id = $this->api_username;
    $this->participant_type = "SELLER";
  }

  public function addProduct($product)
  {
    try {
      $client = new Client();
      $headers = [
        "Authorization" => "Bearer {$this->api_key}",
        "Content-Type" => "application/json"
      ];


      $variants = [];
      $variants['product_variant_description'] = $product->description;
      $variants['product_variant_currency'] = $product->currency;
      $variants['product_variant_unit_price'] = $product->price;
      $variants['product_variant_quantity'] = $product->quantity;
      $variants['product_variant_quantity_type'] = 'KILOGRAM';
      $variants['product_variant_image_link'] = $product->image_url;

      $product_variants = [$variants];


      $payload = [
        "product_name" => $product->productname,
        "product_description" => $product->description,
        "product_variants" => $product_variants,
      ];

      $body = json_encode($payload, JSON_PRETTY_PRINT);
      $url = $this->url . '/products';
      $request = new Request('POST', $url, $headers, $body);
      $res = $client->sendAsync($request)->wait();
      $response = json_decode($res->getBody(), true);

      if (is_null($response)) {
        return ['error' => 1, 'statusCode' => 401, 'responseMessage' => 'Null response!'];
      } elseif (!$response['data']) {
        return ['error' => 1, 'statusCode' => 401, 'responseMessage' => $response['message']];
      }
      return ['error' => 0, 'statusCode' => 200, 'responseMessage' => $response['message'], 'product' => $response['data']['product']];
    } catch (ClientException $e) {
      $response = $e->getResponse();
      $responseBodyAsString = $response->getBody()->getContents();
      Logger::info('Sendy addProduct Exception', [$responseBodyAsString]);
      $responseBody = json_decode($responseBodyAsString);
      return ['error' => 1, 'statusCode' => 400, 'responseMessage' => $responseBody->message, 'errorMessage' => $responseBody->message];
    }
  }

  public function getShipmentCost($items)
  {
    try {
      $client = new Client();
      $headers = [
        "Authorization" => "Bearer {$this->api_key}",
        "Content-Type" => "application/json"
      ];

      $order_items = [];

      foreach ($items as $item) {
        $product = [];
        $product['product_id'] = $item->productInfo->sendy_product_id;
        $product['product_variant_id'] = $item->productInfo->sendy_variant_id;
        $product['quantity'] = $item->quantity;
        $product['currency'] = $item->currency;
        $product['unit_price'] = $item->price;
        $order_items[] = $product;
      }

      $payload = [
        "products" => $order_items
      ];

      $body = json_encode($payload, JSON_PRETTY_PRINT);
      $url = $this->url . '/orders/pricing';
      $request = new Request('POST', $url, $headers, $body);
      $res = $client->sendAsync($request)->wait();
      $response = json_decode($res->getBody(), true);

      if (is_null($response) || !$response['data']) {
        return ['error' => 1, 'statusCode' => 401, 'responseMessage' => 'No data!'];
      }
      return ['error' => 0, 'statusCode' => 200, 'cost_of_goods' => $response['data']['costOfGoods'], 'fulfilment_fee' => $response['data']['fulfilmentFee'], 'currency' => $response['data']['currency']];
    } catch (ClientException $e) {
      $response = $e->getResponse();
      $responseBodyAsString = $response->getBody()->getContents();
      Logger::info('Sendy getShipmentCost Exception', [$responseBodyAsString]);
      $responseBody = json_decode($responseBodyAsString);
      return ['error' => 1, 'statusCode' => 400, 'responseMessage' => $responseBody->message, 'errorMessage' => $responseBody->message];
    }
  }

  public function requestDelivery($items, $address, $delivery_note = 'No notes')
  {
    try {
      $client = new Client();
      $headers = [
        "Authorization" => "Bearer {$this->api_key}",
        "Content-Type" => "application/json"
      ];

      $order_items = [];

      foreach ($items as $item) {
        $product = [];
        $product['product_id'] = $item->productInfo->sendy_product_id;
        $product['product_variant_id'] = $item->productInfo->sendy_variant_id;
        $product['quantity'] = $item->quantity;
        $product['currency'] = $item->productInfo->currency;
        $product['unit_price'] = $item->price;
        $order_items[] = $product;
      }

      $payload = [
        "collect_payment" => false,
        "means_of_payment" => [
          "means_of_payment_type" => "SELLER_WALLET",
          "means_of_payment_id" => "string",
          "participant_type" => $this->participant_type,
          "participant_id" => $this->participant_id,
        ],
        "products" => $order_items,
        "destination" => [
          "name" => $address->name,
          "phone_number" => $address->phone,
          "delivery_location" => [
            "description" => $address->street,
            "longitude" => $address->longitude,
            "latitude" => $address->latitude,
          ],
          "house_location" => $address->address,
          "delivery_instructions" => $delivery_note,
        ]
      ];

      $body = json_encode($payload, JSON_PRETTY_PRINT);
      $url = $this->url . '/orders';
      $request = new Request('POST', $url, $headers, $body);
      $res = $client->sendAsync($request)->wait();
      $response = json_decode($res->getBody(), true);

      if (is_null($response)) {
        return ['error' => 1, 'statusCode' => 401, 'responseMessage' => 'No data!'];
      }
      return ['error' => 0, 'statusCode' => 200, 'responseMessage' => $response['message'], 'fulfilment_requests' => $response['fulfilmentRequests']];
    } catch (ClientException $e) {
      $response = $e->getResponse();
      $responseBodyAsString = $response->getBody()->getContents();
      Logger::info('Sendy requestDelivery Exception', [$responseBodyAsString]);
      $responseBody = json_decode($responseBodyAsString);
      return ['error' => 1, 'statusCode' => 400, 'responseMessage' => $responseBody->message, 'errorMessage' => $responseBody->message];
    }
  }


  public function requestPickup($items, $address, $delivery_note = 'Please call me for my availability')
  {
    try {
      $client = new Client();
      $headers = [
        "Authorization" => "Bearer {$this->api_key}",
        "Content-Type" => "application/json"
      ];

      $order_items = [];

      foreach ($items as $item) {
        $product = [];
        $product['product_id'] = $item->productInfo->sendy_product_id;
        $product['product_variant_id'] = $item->productInfo->sendy_variant_id;
        $product['quantity_to_request'] = $item->quantity;
        $order_items[] = $product;
      }

      $payload = [
        "products" => $order_items,
        "destination" => [
          "name" => $address->name,
          "phone_number" => $address->phone,
          "delivery_location" => [
            "description" => $address->street,
            "longitude" => $address->longitude,
            "latitude" => $address->latitude,
          ],
          "house_location" => $address->address,
          "delivery_instructions" => $delivery_note,
        ]
      ];

      $body = json_encode($payload, JSON_PRETTY_PRINT);
      $url = $this->url . '/orders/pickup';
      $request = new Request('POST', $url, $headers, $body);
      $res = $client->sendAsync($request)->wait();
      $response = json_decode($res->getBody(), true);

      if (is_null($response)) {
        return ['error' => 1, 'statusCode' => 401, 'responseMessage' => 'Null response!'];
      } elseif (!$response['data']) {
        return ['error' => 1, 'statusCode' => 401, 'responseMessage' => $response['message']];
      }
      return ['error' => 0, 'statusCode' => 200, 'responseMessage' => $response['message'], 'data' => $response['data']];
    } catch (ClientException $e) {
      $response = $e->getResponse();
      $responseBodyAsString = $response->getBody()->getContents();
      Logger::info('Sendy requestPickup Exception', [$responseBodyAsString]);
      $responseBody = json_decode($responseBodyAsString);
      return ['error' => 1, 'statusCode' => 400, 'responseMessage' => $responseBody->message, 'errorMessage' => $responseBody->message];
    }
  }


  public function trackOrder($order_id)
  {
    try {
      $client = new Client();
      $headers = [
        "Authorization" => "Bearer {$this->api_key}",
        "Content-Type" => "application/json"
      ];

      $url = $this->url . "/orders/{$order_id}/track";
      $request = new Request('GET', $url, $headers);
      $res = $client->sendAsync($request)->wait();
      $response = json_decode($res->getBody(), true);

      if (is_null($response)) {
        return ['error' => 1, 'statusCode' => 401, 'responseMessage' => 'Null response!'];
      } elseif (!$response['data']) {
        return ['error' => 1, 'statusCode' => 401, 'responseMessage' => $response['message']];
      }
      return ['error' => 0, 'statusCode' => 200, 'responseMessage' => $response['message'], 'status' => $response['data']['status']];
    } catch (ClientException $e) {
      $response = $e->getResponse();
      $responseBodyAsString = $response->getBody()->getContents();
      Logger::info('Sendy trackOrder Exception', [$responseBodyAsString]);
      $responseBody = json_decode($responseBodyAsString);
      return ['error' => 1, 'statusCode' => 400, 'responseMessage' => $responseBody->message, 'errorMessage' => $responseBody->message];
    }
  }

  public function cancelOrder($order_id, $reason)
  {
    try {
      $client = new Client();
      $headers = [
        "Authorization" => "Bearer {$this->api_key}",
        "Content-Type" => "application/json"
      ];
      $payload = [
        "cancellation_reason" => $reason,
      ];

      $body = json_encode($payload, JSON_PRETTY_PRINT);
      $url = $this->url . "/orders/{$order_id}/cancel";
      $request = new Request('PATCH', $url, $headers, $body);
      $res = $client->sendAsync($request)->wait();
      $response = json_decode($res->getBody(), true);

      if (is_null($response)) {
        return ['error' => 1, 'statusCode' => 401, 'responseMessage' => 'Null response!'];
      } elseif (!$response['data']) {
        return ['error' => 1, 'statusCode' => 401, 'responseMessage' => $response['message']];
      }
      return ['error' => 0, 'statusCode' => 200, 'responseMessage' => $response['message'], 'status' => $response['data']['fulfilment_request']['fulfilment_request_status']];
    } catch (ClientException $e) {
      $response = $e->getResponse();
      $responseBodyAsString = $response->getBody()->getContents();
      Logger::info('Sendy cancelOrder Exception', [$responseBodyAsString]);
      $responseBody = json_decode($responseBodyAsString);
      return ['error' => 1, 'statusCode' => 400, 'responseMessage' => $responseBody->message, 'errorMessage' => $responseBody->message];
    }
  }

  public function getOrders($offset = 0, $max = 100)
  {
    try {
      $client = new Client();
      $headers = [
        "Authorization" => "Bearer {$this->api_key}",
        "Content-Type" => "application/json"
      ];

      $url = $this->url . "/orders?offset={$offset}&max={$max}";
      $request = new Request('GET', $url, $headers);
      $res = $client->sendAsync($request)->wait();
      $response = json_decode($res->getBody(), true);

      if (is_null($response)) {
        return ['error' => 1, 'statusCode' => 401, 'responseMessage' => 'Null response!'];
      } elseif (!$response['data']) {
        return ['error' => 1, 'statusCode' => 401, 'responseMessage' => $response['message']];
      }
      return ['error' => 0, 'statusCode' => 200, 'responseMessage' => $response['message'], 'fulfilment_requests' => $response['data']['fulfilment_requests'], 'pagination' => $response['data']['pagination']];
    } catch (ClientException $e) {
      $response = $e->getResponse();
      $responseBodyAsString = $response->getBody()->getContents();
      Logger::info('Sendy getOrders Exception', [$responseBodyAsString]);
      $responseBody = json_decode($responseBodyAsString);
      return ['error' => 1, 'statusCode' => 400, 'responseMessage' => $responseBody->message, 'errorMessage' => $responseBody->message];
    }
  }

  public function requestPickupForInventory()
  {
    try {
      $client = new Client();
      $headers = [
        "Authorization" => "Bearer {$this->api_key}",
        "Content-Type" => "application/json"
      ];

      $url = $this->url . '/orders/pickup';
      $request = new Request('GET', $url, $headers);
      $res = $client->sendAsync($request)->wait();
      $response = json_decode($res->getBody(), true);

      if (is_null($response)) {
        return ['error' => 1, 'statusCode' => 401, 'responseMessage' => 'Null response!'];
      } elseif (!$response['data']) {
        return ['error' => 1, 'statusCode' => 401, 'responseMessage' => $response['message']];
      }
      return ['error' => 0, 'statusCode' => 200, 'responseMessage' => $response['message'], 'data' => $response['data']];
    } catch (ClientException $e) {
      $response = $e->getResponse();
      $responseBodyAsString = $response->getBody()->getContents();
      Logger::info('Sendy requestPickupForInventory Exception', [$responseBodyAsString]);
      $responseBody = json_decode($responseBodyAsString);
      return ['error' => 1, 'statusCode' => 400, 'responseMessage' => $responseBody->message, 'errorMessage' => $responseBody->message];
    }
  }

  public function getStockRequest()
  {
    try {
      $client = new Client();
      $headers = [
        "Authorization" => "Bearer {$this->api_key}",
        "Content-Type" => "application/json"
      ];

      $url = $this->url . '/stock-requests';
      $request = new Request('GET', $url, $headers);
      $res = $client->sendAsync($request)->wait();
      $response = json_decode($res->getBody(), true);

      if (is_null($response)) {
        return ['error' => 1, 'statusCode' => 401, 'responseMessage' => 'Null response!'];
      } elseif (!$response['data']) {
        return ['error' => 1, 'statusCode' => 401, 'responseMessage' => $response['message']];
      }
      return ['error' => 0, 'statusCode' => 200, 'responseMessage' => $response['message'], 'stockrequests' => $response['data']['stockrequests'], 'pagination' => $response['data']['pagination']];
    } catch (ClientException $e) {
      $response = $e->getResponse();
      $responseBodyAsString = $response->getBody()->getContents();
      Logger::info('Sendy getStockRequest Exception', [$responseBodyAsString]);
      $responseBody = json_decode($responseBodyAsString);
      return ['error' => 1, 'statusCode' => 400, 'responseMessage' => $responseBody->message, 'errorMessage' => $responseBody->message];
    }
  }

  public function getStockRequestSummary()
  {
    try {
      $client = new Client();
      $headers = [
        "Authorization" => "Bearer {$this->api_key}",
        "Content-Type" => "application/json"
      ];

      $url = $this->url . '/stock-requests/summary';
      $request = new Request('GET', $url, $headers);
      $res = $client->sendAsync($request)->wait();
      $response = json_decode($res->getBody(), true);

      if (is_null($response)) {
        return ['error' => 1, 'statusCode' => 401, 'responseMessage' => 'Null response!'];
      } elseif (!$response['data']) {
        return ['error' => 1, 'statusCode' => 401, 'responseMessage' => $response['message']];
      }
      return ['error' => 0, 'statusCode' => 200, 'responseMessage' => $response['message'], 'stockRequestSummary' => $response['data']['stockRequestSummary']];
    } catch (ClientException $e) {
      $response = $e->getResponse();
      $responseBodyAsString = $response->getBody()->getContents();
      Logger::info('Sendy getStockRequestSummary Exception', [$responseBodyAsString]);
      $responseBody = json_decode($responseBodyAsString);
      return ['error' => 1, 'statusCode' => 400, 'responseMessage' => $responseBody->message, 'errorMessage' => $responseBody->message];
    }
  }
}
