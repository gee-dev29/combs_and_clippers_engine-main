<?php

namespace App\Repositories;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Log as Logger;

class Util
{
    static public function sendWaitlistToEngage($user)
    {
        try {
            $engage_public_key = cc('engage_public_key');
            $engage_private_key = cc('engage_private_key');
            $engage = new \Engage\EngageClient($engage_public_key, $engage_private_key);
            $engage->users->identify([
                'id'      => $user->id,
                'name'    => $user->name,
                'email'   => $user->email,
                'phone'   => $user->phone,
                'referral_code' => $user->referral_code,
            ]);
            return true;
        } catch (Exception $e) {
            Logger::info('Engage.io Error', [$e->getMessage() . ' - ' . $e->__toString()]);
            return false;
        }
    }

    static public function sendEventToEngage($user, $data)
    {
        try {
            $engage_public_key = cc('engage_public_key');
            $engage_private_key = cc('engage_private_key');
            $engage = new \Engage\EngageClient($engage_public_key, $engage_private_key);
            $engageResponse = $engage->users->track($user->id, $data);
            return $engageResponse;
        } catch (Exception $e) {
            Logger::info('Engage.io Event Error', [$e->getMessage() . ' - ' . $e->__toString()]);
            return false;
        }
    }

    static public function validateAddress($user, $address)
    {
        try {
            $url = "https://api.shipbubble.com/v1/shipping/address/validate";
            //$token = env("SHIPBUBBLE_TOKEN");
            $token = cc('shipbubble_token');
            $client = new Client();
            $headers = [
                "Authorization" => "Bearer {$token}",
                "Content-Type" => "application/json"
            ];
            $payload = [
                "name" => $user->name,
                "email" => $user->email,
                "phone" => $user->phone,
                "address" => $address
            ];
            Logger::info('Validate address from Shipbubble - Request', $payload);
            $body = json_encode($payload, JSON_PRETTY_PRINT);
            $request = new Request('POST', $url, $headers, $body);
            $res = $client->sendAsync($request)->wait();
            $response = json_decode($res->getBody(), true);

            Logger::info('Validate address from Shipbubble - Response', [$response]);

            if (empty($response)) {
                return ['error' => 1, 'statusCode' => 400, 'responseMessage' => 'No response!', 'errorMessage' => 'No response!'];
            } elseif ($response['status'] != "success") {
                return ['error' => 1, 'statusCode' => 400, 'responseMessage' => $response['message'], 'errorMessage' => implode(', ', $response['errors'])];
            }

            return ['error' => 0, 'statusCode' => 200, 'responseMessage' => $response['message'], 'addressDetails' => $response['data']];
        } catch (ClientException $e) {
            Logger::info('shipbubble Error - Exception', [$e->__toString()]);
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            $responseBody = json_decode($responseBodyAsString);
            Logger::info('shipbubble responseBodyAsString Error', [$responseBody->message]);
            return ['error' => 1, 'statusCode' => 400, 'responseMessage' => $responseBody->message, 'errorMessage' => $responseBody->message];
        }
    }

    static public function validateAddressWithGoogle($user, $address)
    {
        try {
            $payload = [
                "name" => $user->name,
                "email" => $user->email,
                "phone" => $user->phone,
                "address" => $address
            ];

            Logger::info('Validate address with Google - Request', $payload);

            $result = app('geocoder')->geocode($address)->get()->first();

            Logger::info('Validate address with Google - Response', [$result]);

            if (!is_null($result)) {
                $data = [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'address' => $address,
                    'formatted_address' => $result->getFormattedAddress(),
                    'street_no' => $result->getStreetNumber(),
                    'street' => $result->getStreetName(),
                    'country' => $result->getCountry()->getName(),
                    'country_code' => $result->getCountry()->getCode(),
                    'city' => $result->getAdminLevels()->get(2)->getName(),
                    'city_code' => $result->getAdminLevels()->get(2)->getCode(),
                    'state' => $result->getAdminLevels()->first()->getName(),
                    'state_code' => $result->getAdminLevels()->first()->getCode(),
                    'longitude' => $result->getCoordinates()->getLongitude(),
                    'latitude' => $result->getCoordinates()->getLatitude(),
                    'bounds'  => [
                        'south' => $result->getBounds()->getSouth(),
                        'west' => $result->getBounds()->getWest(),
                        'north' => $result->getBounds()->getNorth(),
                        'east' => $result->getBounds()->getEast(),
                    ],
                    'timezone' => $result->getTimezone(),
                    'postal_code' => $result->getPostalCode(),
                    'location_type' => $result->getLocationType(),
                    'result_type' => $result->getResultType()[0]
                ];

                return ['error' => 0, 'statusCode' => 200, 'responseMessage' => "Address information retrieved successfully", 'addressDetails' => $data];
            }

            return ['error' => 1, 'statusCode' => 400, 'responseMessage' => 'Address information could not be retrieved', 'addressDetails' => $result];
        } catch (Exception $e) {
            return ['error' => 1, 'statusCode' => 500, 'responseMessage' => $e->getMessage(), 'errorMessage' => $e->getMessage()];
        }
    }
}
