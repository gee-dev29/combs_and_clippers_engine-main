<?php

namespace App\Repositories;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Log as Logger;
use GuzzleHttp\Exception\ClientException;

class LetaUtils
{
    private $url;
    private $token;

    public function __construct()
    {
        $this->url = env('LETA_BASE_URL');
        $this->token = env('LETA_API_TOKEN');
    }

    public function getShipmentRates($fromAddress, $toAddress)
    {
        try {
            $client = new Client();
            $headers = [
                "Authorization" => "{$this->token}",
                "Content-Type" => "application/json"
            ];

            $payload = [
                "origin" => [
                    "latitude" => (float) $fromAddress->latitude,
                    "longitude" =>(float) $fromAddress->longitude
                ],
                "destination" => [
                    "latitude" => (float) $toAddress->latitude,
                    "longitude" => (float) $toAddress->longitude
                ]
            ];

            Logger::info('Leta getShipmentRates payload', [json_encode($payload)]);

            $body = json_encode($payload, JSON_PRETTY_PRINT);
            $url = $this->url . '/shipping/rates/calculate';
            $request = new Request('POST', $url, $headers, $body);
            $res = $client->sendAsync($request)->wait();
            $response = json_decode($res->getBody());

            Logger::info('Leta getShipmentRates Response', ["payload" => $body, "response" => $response]);

            if ($response->status_code !== 200) {
                return ['error' => 1, 'statusCode' => $response->status_code, 'responseMessage' => $response->detail];
            }
            return ['error' => 0, 'statusCode' => $response->status_code, 'responseMessage' => "Rates calculated successfully", 'rates' => $response->detail];
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            Logger::info('Leta getShipmentRates Exception', [$responseBodyAsString]);
            $responseBody = json_decode($responseBodyAsString);
            return ['error' => 1, 'statusCode' => $responseBody->status_code, 'responseMessage' => $responseBody->detail, 'Detail' => $responseBody->detail];
        }
    }

    public function bookShipment($user, $orderRef, $items, $fromAddress, $toAddress, $delivery_note = 'Please, handle with care.', $description = 'customer order')
    {
        try {
            $client = new Client();
            $headers = [
                "Authorization" => "{$this->token}",
                "Content-Type" => "application/json"
            ];

            $order_items = [];
            $total_weight = 0;
            $total_price = 0;

            foreach ($items as $item) {
                $product = [];
                $product['code'] = $item->productInfo->product_slug;
                $product['quantity'] = $item->quantity;
                $product['price'] = $item->price;
                $total_weight += $item->productInfo->weight;
                $total_price += $item->price;
                $order_items[] = $product;
            }

            $payload = [
                "reference" => $orderRef,
                "special_instruction" => $delivery_note,
                "cargo_description" => $description,
                "payment_method" => "prepaid",
                //"depot_code" => "some-good-code",
                //"supplier_code" => "some-good-code",
                "customer" => [
                    "phone_number" => $user->phone,
                    "email" => $user->email,
                    "name" => $user->name
                ],
                "products" => $order_items,
                // "total_weight" => $total_weight,
                // "total_price" => $total_price,
                //"dropoff_address" => $toAddress->formatted_address,
                "dropoff" => [
                    "name" => $toAddress->street,
                    "longitude" => (string) $toAddress->longitude,
                    "latitude" =>  (string) $toAddress->latitude,
                ],
                "pickup" => [
                    "name" => $fromAddress->street,
                    "longitude" => (string) $fromAddress->longitude,
                    "latitude" =>  (string) $fromAddress->latitude,
                ],
            ];

            $body = json_encode($payload, JSON_PRETTY_PRINT);
            $url = $this->url . '/orders/add';
            $request = new Request('POST', $url, $headers, $body);
            $res = $client->sendAsync($request)->wait();
            $response = json_decode($res->getBody());

            Logger::info('Leta bookShipment Response', ["payload" => $body, "response" => $response]);

            if ($response->status_code !== 200) {
                return ['error' => 1, 'statusCode' => $response->status_code, 'responseMessage' => $response->detail];
            }
            return ['error' => 0, 'statusCode' => $response->status_code, 'responseMessage' => $response->detail];
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            Logger::info('Leta bookShipment Exception', [$responseBodyAsString]);
            $responseBody = json_decode($responseBodyAsString);
            return ['error' => 1, 'statusCode' => $responseBody->status_code, 'responseMessage' => $responseBody->detail, 'Detail' => $responseBody->detail];
        }
    }
}
