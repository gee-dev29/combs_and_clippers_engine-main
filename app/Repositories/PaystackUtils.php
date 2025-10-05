<?php

namespace App\Repositories;

use \Exception;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Log as Logger;

class PaystackUtils
{
    private $secret_key;
    private $public_key;
    private $payment_url;

    public function __construct()
     {
    $this->secret_key = config('services.paystack.secret_key');
    $this->public_key = config('services.paystack.public_key');
    $this->payment_url = config('services.paystack.payment_url');


    //  Logger::info('Env debug', [
    //     'secret_from_env' => env("PAYSTACK_SECRET_KEY"),
    //     'url_from_env' => env("PAYSTACK_PAYMENT_URL"),
    //     'app_env' => env('APP_ENV'),
    //     'config_cached' => file_exists(base_path('bootstrap/cache/config.php'))
    // ]);
    }

public function generatePaymentLink($customer, $amount, $currency = "NGN", $extra = 0, $callback = null)
{
    $amount = $amount + $extra;

    $customerInfo = [
        'name' => $customer->name,
        'email' => $customer->email,
        'phone' => $customer->phone,
    ];

    $reference = 'paystack-' . paystack()->genTranxRef();
    $paymentID = "PMT" . unique_random_string();

    $data = array(
        "amount" => $amount * 100,
        "reference" => $reference,
        "email" => $customer->email,
        "phone" => $customer->phone,
        "currency" => $currency,
        "orderID" => $paymentID,
        "description" => "Payment for service",
        "metadata" => json_encode($customerInfo),
        "callback_url" => $callback
    );

    try {
        $url = $this->payment_url . "/transaction/initialize";
        $ch = curl_init();
        $fields_string = json_encode($data);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer $this->secret_key",
            "Content-Type: application/json",
            "Accept: application/json",
            "Cache-Control: no-cache",
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);

      
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        $errno = curl_errno($ch);



        curl_close($ch);

        if ($err) {
            Logger::error('Paystack generatePaymentLink error - ', [$err]);
            return ['error' => 1, "responseStatus" => "Unsuccessful", "statusCode" => 500, 'Detail' => $err, "responseMessage" => 'Something went wrong'];
        }

        $paymentDetails = json_decode($response, true);

        return ['error' => 0, 'responseStatus' => 'Successful', 'paymentRef' => $reference, 'paymentUrl' => $paymentDetails['data']['authorization_url']];
    } catch (Exception $e) {
        Logger::error('Paystack generatePaymentLink exception - ', [$e->getMessage() . ' - ' . $e->__toString()]);
        return ['error' => 1, "responseStatus" => "Unsuccessful", "statusCode" => 500, 'Detail' => $e->getMessage(), "responseMessage" => 'Something went wrong'];
    }
}
    public function verifyPayment($tranx_ref)
    {
        try {
            $url = $this->payment_url . "/transaction/verify/{$tranx_ref}";
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "Authorization: Bearer $this->secret_key",
                    "Cache-Control: no-cache",
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                return ['error' => 1, "responseStatus" => "Unsuccessful", "statusCode" => 500, 'Detail' => $err, "responseMessage" => 'Something went wrong', 'reference' => $tranx_ref];
            }

            $paymentDetails = json_decode($response, true);
            Logger::info('Paystack verify payment response', [$paymentDetails]);
            if (!empty($paymentDetails) && $paymentDetails['status']) {
                return ['error' => 0, "responseStatus" => "Successful", 'statusCode' => "00", 'responseMessage' => $paymentDetails['message'], 'paymentDetails' => $paymentDetails, 'reference' => $tranx_ref];
            } elseif (!empty($paymentDetails) && !$paymentDetails['status']) {
                return ['error' => 1, "responseStatus" => "Unsuccessful", 'statusCode' => "01", 'responseMessage' => $paymentDetails['message'], 'paymentDetails' => $paymentDetails, 'reference' => $tranx_ref];
            } else {
                return ['error' => 1, "responseStatus" => "Unsuccessful", 'statusCode' => "01", 'responseMessage' => "Failed", 'reference' => $tranx_ref];
            }
        } catch (Exception $e) {
            Logger::error('Paystack verifyPayment exception - ', [$e->getMessage() . ' - ' . $e->__toString()]);
            return ['error' => 1, "responseStatus" => "Unsuccessful", "statusCode" => 500, 'Detail' => $e->getMessage(), "responseMessage" => 'Something went wrong'];
        }
    }
}
