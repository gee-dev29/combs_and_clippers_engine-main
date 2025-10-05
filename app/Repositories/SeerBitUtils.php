<?php
namespace App\Repositories;
use Paystack;
use \Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log as Logger;

class SeerBitUtils 
{
    

    public function __construct()
    {
        
    }

    public function getToken()
    {

            $curl = curl_init();

            $PBFPubKey = env('SEERBIT_PUBLIC_KEY'); 
            $privateKey = env('SEERBIT_PRIVATE_KEY');
            $keyString = $privateKey . '.' . $PBFPubKey;

            curl_setopt_array($curl, array(
              CURLOPT_URL => "https://seerbitapi.com/api/v2/encrypt/keys",
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_CUSTOMREQUEST => "POST",
              CURLOPT_POSTFIELDS => json_encode([
                "key" => $keyString
              ]),
              CURLOPT_HTTPHEADER => [
                "content-type: application/json",
                "cache-control: no-cache",
              ],
            ));

            $response = curl_exec($curl);
            $err = curl_errno($curl);
            
            if($err){
              // there was an error contacting the rave API
              //die('Curl returned error: ' . $err);
              $error_msg = curl_error($curl);
              curl_close($curl);
              return false;
            }
            curl_close($curl);

            $keyResponse = json_decode($response);
            //return $keyResponse;
            if ($keyResponse->data->code != "00") {
                return false;
            }

            return $keyResponse->data->EncryptedSecKey->encryptedKey;
    }

    //public function generatePayLink($paymentProvider, $order, $user)
    public function generatePayLink($customer_email, $amount, $productId, $productDesc, $currency = "NGN")
    {
      try{
        $curl = curl_init();

        $txref = "seerbit-" . time(); // ensure you generate unique references per transaction.
        
        $PBFPubKey = env('SEERBIT_PUBLIC_KEY'); 
        $privateKey = env('SEERBIT_PRIVATE_KEY'); 
        $redirect_url = env('SEERBIT_REDIRECT', 'https://pepperrest-dev.herokuapp.com/app/verify-payment'); // Set your own redirect URL
        $callback_url = env('SEERBIT_CALLBACK', "https://pepperrest-dev.herokuapp.com/app/verify-payment");

        $stringToHash = "amount=$amount&callbackUrl=$redirect_url&country=NG&currency=$currency&email=$customer_email&paymentReference=$txref&productDescription=$productDesc&productId=$productId&publicKey=$PBFPubKey";
        $hashString = $stringToHash . $privateKey;
        $hash = hash('sha256', $hashString);
        
        $token = $this->getToken();
        if (!$token) {
            return ['error' => 1, 'statusCode' => 401, 'responseMessage' => "token was not generated", 'reference' => $txref];
            //return "token was not generated";
        }

        //return $hash . '//' .$token; 

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://seerbitapi.com/api/v2/payments",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => json_encode([
            "publicKey" => $PBFPubKey,
            "amount" => $amount,
            "currency" => $currency,
            "country" => "NG",
            "paymentReference" => $txref,
            "email" => $customer_email,
            "productId" => $productId,
            "productDescription" => $productDesc,
            "callbackUrl" => $callback_url,
            "redirect_url" => $redirect_url,
            "hash" => $hash,
            "hashType" => "sha256"
          ]),
          CURLOPT_HTTPHEADER => [
            "content-type: application/json",
            "cache-control: no-cache",
            "Authorization: Bearer $token"
          ],
        ));

        $response = curl_exec($curl);
        $err = curl_errno($curl);
        
        if($err){
          $error_msg = curl_error($curl);
          curl_close($curl);
          //return $error_msg;
          return ['error' => 1, 'statusCode' => 500, 'responseMessage' => $error_msg, 'reference' => $txref];
        }
        curl_close($curl);

        $transaction = json_decode($response);


        if (isset($transaction->error)) {
          return ['error' => 1, 'statusCode' => isset($tranx->data->code) ? $tranx->data->code : 401, 'responseMessage' => $transaction->message, 'reference' => $txref];
        }

        if ($transaction->data->code == "00" && $transaction->status == "SUCCESS") {
          return ['error' => 0, 'statusCode' => $transaction->data->code, 'responseMessage' => $transaction->status, 'paymentUrl' => $transaction->data->payments->redirectLink, 'reference' => $txref];
        }else{
          return ['error' => 1, 'statusCode' => $transaction->data->code, 'responseMessage' => $transaction->status, 'reference' => $txref];
        }

        return $transaction;
        // {#366 ▼
        //   +"message": "INVALID HASH"
        //   +"error": "PROCESSING"
        // }

        // {#366 ▼
        //     +"status": "SUCCESS"
        //     +"data": {#361 ▼
        //     +"code": "00"
        //     +"payments": {#362 ▼
        //       +"redirectLink": "https://checkout.seerbitapi.com/?mid=SBTESTPUBK_9naV6IQn7JAqK3EzQaFLPv9OBk8utGJY&paymentReference=seerbit-1617959436"
        //       +"paymentStatus": "08"
        //     }
        //     +"message": "Successful"
        //   }
        // }

        // if(!$transaction->data && !$transaction->data->link){
        //   // there was an error from the API
        //   //print_r('API returned error: ' . $transaction->message);
        //   return 'error';
        // }

        // // redirect to page so User can pay
        // return ['paymentUrl' => $transaction->data->link, 'reference' => $txref];
      } catch (Exception $e) {
        return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'],500);
      }
       
    }

    

    public function verifySeerbitTranx($tranx_ref){
      try{
        if (!is_null($tranx_ref)) {
            
            $token = $this->getToken();
            if (!$token) {
                return "token was not generated";
            }
            $ch = curl_init(); 

            curl_setopt($ch, CURLOPT_URL, "https://seerbitapi.com/api/v2/payments/query/$tranx_ref");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                    
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "content-type: application/json",
                    "cache-control: no-cache",
                    "Authorization: Bearer $token"
                  ]);

            $response = curl_exec($ch);
            $err = curl_errno($ch);
            
            if($err){
              $error_msg = curl_error($ch);
              curl_close($ch);
              //return $error_msg;
              //return 'error';
              return ['error' => 1, 'statusCode' => 500, 'responseMessage' => $error_msg, 'reference' => $tranx_ref];
            }
            curl_close($ch);

            $tranx = json_decode($response);

            //return $tranx;
            if (isset($tranx->error)) {
                //return 'error';
                return ['error' => 1, 'statusCode' => isset($tranx->data->code) ? $tranx->data->code : 401, 'responseMessage' => $tranx->message, 'reference' => $tranx_ref];
            }

            if ($tranx->data->code == "00" && $tranx->status == "SUCCESS") {
              return ['error' => 0, 'statusCode' => $tranx->data->code, 'responseMessage' => $tranx->status, 'paymentDetails' => $tranx->data->payments, 'customerDetails' => $tranx->data->customers, 'reference' => $tranx_ref];
            }else{
                //return 'error';
                return ['error' => 1, 'statusCode' => $tranx->data->code, 'responseMessage' => $tranx->status, 'reference' => $tranx_ref];
            }



        }else {
            //return 'error';
            return ['error' => 1, 'statusCode' => 401, 'responseMessage' => 'Transaction reference was not provided.', 'reference' => $tranx_ref];
        }
      } catch (Exception $e) {
        return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'],500);
      }
    }

    public function verifyFlutterTranx($tranx_ref){
      try{
        if (!is_null($tranx_ref)) {
            // $ref = $_GET['txref'];
            // $amount = 3000; //Get the correct amount of your product
            // $currency = "NGN"; //Correct Currency from Server

            $query = array(
                "SECKEY" => env('FLUTTERWAVE_SECRET_KEY'),
                "txref" => $tranx_ref
            );

            $data_string = json_encode($query);
                    
            $ch = curl_init('https://api.ravepay.co/flwv3-pug/getpaidx/api/v2/verify');                                                                      
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);                                              
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

            $response = curl_exec($ch);

            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $header = substr($response, 0, $header_size);
            $body = substr($response, $header_size);

            $err = curl_error($ch);
            curl_close($ch);

            $resp = json_decode($response, true);

            if($err){
              // there was an error contacting the rave API
              //die('Curl returned error: ' . $err);
              return 'error';
            }

            return $resp;


            $paymentStatus = $resp['data']['status'];
            $chargeResponsecode = $resp['data']['chargecode'];
            $chargeAmount = $resp['data']['amount'];
            $chargeCurrency = $resp['data']['currency'];

        }else {
          return 'error';
        }
      } catch (Exception $e) {
        return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'],500);
      }
    }


    public function initiateSeerbitTransfer($amount, $tranx_ref){

        if (!is_null($tranx_ref)) {
            
            $PBFPubKey = env('SEERBIT_PUBLIC_KEY'); 
            $privateKey = env('SEERBIT_PRIVATE_KEY'); 
            $token = base64_encode($PBFPubKey.'.'.$PBFPubKey);
            $host = env('SEERBIT_HOST');
            $url = $host. "/merchants/api/v1/pockets/profile/$PBFPubKey/transfer";
            
            $query = array(
                "bankCode" => env('FBN_ESCROW_BANK_CODE', "011"),
                "accountNumber" => env('FBN_ESCROW_BANK_ACCOUNT_NO'),
                "amount" => $amount,
                "extTransactionRef" => $tranx_ref,
                "type" => "CREDIT_BANK"
            );

            $data_string = json_encode($query);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
             curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);                   
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "content-type: application/json",
                    "cache-control: no-cache",
                    "authHeader: $token"
                  ]);

            $response = curl_exec($ch);
            $err = curl_errno($ch);
            
            if($err){
              $error_msg = curl_error($ch);
              curl_close($ch);
              //return $error_msg;
              return ['error' => 1, 'statusCode' => 500, 'responseMessage' => $error_msg, 'reference' => $tranx_ref];
            }
            curl_close($ch);

            $tranx = json_decode($response);
            // {
            //  "responseMessage": "Request processed successfully",
            //  "responseCode": "00"
            // }
            //return $tranx;
            if (isset($tranx->error)) {
              return ['error' => 1, 'statusCode' => $tranx->responseCode , 'responseMessage' => $tranx->responseMessage, 'reference' => $tranx_ref];
            }

            if ($tranx->responseCode == "00") {
              return ['error' => 0, 'statusCode' => $tranx->responseCode, 'responseMessage' => $tranx->responseMessage, 'reference' => $tranx_ref];
            }else{
              return ['error' => 1, 'statusCode' => $tranx->responseCode , 'responseMessage' => $tranx->responseMessage, 'reference' => $tranx_ref];
            }

        }else {
           return ['error' => 1, 'statusCode' => 401, 'responseMessage' => 'Transaction reference was not provided.', 'reference' => $tranx_ref];
        }

    }


    public function verifySeerbitTransfer($tranx_ref){

        if (!is_null($tranx_ref)) {
            
            $PBFPubKey = env('SEERBIT_PUBLIC_KEY'); 
            $privateKey = env('SEERBIT_PRIVATE_KEY'); 
            $token = base64_encode($PBFPubKey.'.'.$PBFPubKey);
            $host = env('SEERBIT_HOST');
            $url = $host. "/merchants/api/v1/pockets/profile/$PBFPubKey/transfer/extref/$tranx_ref";
           

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                  
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "content-type: application/json",
                    "cache-control: no-cache",
                    "authHeader: $token"
                  ]);

            $response = curl_exec($ch);
            $err = curl_errno($ch);
            
            if($err){
              $error_msg = curl_error($ch);
              curl_close($ch);
              //return $error_msg;
              return ['error' => 1, 'statusCode' => 500, 'responseMessage' => $error_msg, 'reference' => $tranx_ref];
            }
            curl_close($ch);

            $tranx = json_decode($response);
            // {
            //      "payload": 
            //      [{
            //      "businessId": "00000013",
            //      "description": "Live Transaction",
            //      "lastBalance": 650.00,
            //      "currentBalance": 500.00,
            //      "amount": 100.00,
            //      "charge": 50.00,
            //      "type": "DB",
            //      "narration": "Debit customer account!",
            //      "payout": {
            //      "charge": 50.00,
            //      "country": "NG",
            //      "currency": "NGN",
            //      "receiverAccountNumber": "0230057153",
            //      "receiverAccountName": null,
            //      "receiverDetails": "OLUWATOSIN MICHAEL OGINNI",
            //      "senderDetails": "Akintoye Kolawole",
            //      "description": "Live Transaction",
            //      "responseCode": "00",
            //      "responseMessage": " Approved or completed 
            //     successfully",
            //      "transactionReference": 
            //     "d035810c8e0845dfa6feb0bff6012032",
            //      "linkingReference": "SBT000000084298",
            //      "amount": 100.00,
            //      "requestType": "PAYOUT",
            //      "payoutType": "DIRECT_MERCHANT",
            //      "status": "SUCCESSFUL",
            //      "requestDate": null,
            //      "lastUpdatedAt": null
            //      },
            //      "status": "SUCCESSFUL"
            //      }]
            //      "responseMessage": "Transaction record loaded successfully",
            //      "responseCode": "00"
            // }
            //return $tranx;
            if (isset($tranx->error)) {
              return ['error' => 1, 'statusCode' => $tranx->responseCode , 'responseMessage' => $tranx->responseMessage, 'reference' => $tranx_ref];
            }

            if ($tranx->responseCode == "00") {
              return ['error' => 0, 'statusCode' => $tranx->responseCode, 'responseMessage' => $tranx->responseMessage, 'paymentDetails' => $tranx->payload[0], 'reference' => $tranx_ref];
            }else{
              return ['error' => 1, 'statusCode' => $tranx->responseCode , 'responseMessage' => $tranx->responseMessage, 'reference' => $tranx_ref];
            }

        }else {
           return ['error' => 1, 'statusCode' => 401, 'responseMessage' => 'Transaction reference was not provided.', 'reference' => $tranx_ref];
        }

    }

    
    public function initiateFlutterwaveTransfer($amount, $tranx_ref){

        if (!is_null($tranx_ref)) {
            
            $PBFPubKey = env('SEERBIT_PUBLIC_KEY'); 
            $privateKey = env('SEERBIT_PRIVATE_KEY');
            $PBFSecKey = env('FLUTTERWAVE_SECRET_KEY');  
            $token = base64_encode($PBFPubKey.'.'.$PBFPubKey);
            $host = env('SEERBIT_HOST');
            $url = $host. "https://api.ravepay.co/v2/gpx/transfers/create";
            //"https://ravesandboxapi.flutterwave.com/flwv3-pug/getpaidx/api/v2/hosted/pay"
            $query = array(
                "account_bank" => env('FBN_ESCROW_BANK_CODE', "011"),
                "account_number" => env('FBN_ESCROW_BANK_ACCOUNT_NO'),
                "amount" => $amount,
                "narration" => "Payment Settlement: $tranx_ref",
                "currency" => "NGN",
                "seckey" => $PBFSecKey,
                "beneficiary_name" => '',
                "reference" => $tranx_ref,
                "callback_url" => env('FLUTTERWAVE_CALLBACK'),
                "debit_currency" => "NGN"
            );

            // {
    //     "account_bank": "044",
    //     "account_number": "0690000040",
    //     "amount": 5500,
    //     "narration": "Akhlm Pstmn Trnsfr xx007",
    //     "currency": "NGN",
    //     "reference": "akhlm-pstmnpyt-rfxx007_PMCKDU_1",
    //     "callback_url": "https://webhook.site/b3e505b0-fe02-430e-a538-22bbbce8ce0d",
    //     "debit_currency": "NGN"
    // }

            $data_string = json_encode($query);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
             curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);                   
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Content-Type: application/json",
                    "Content-Type: application/json"
                    
                  ]);

            $response = curl_exec($ch);
            $err = curl_errno($ch);
            
            if($err){
              $error_msg = curl_error($ch);
              curl_close($ch);
              //return $error_msg;
              return ['error' => 1, 'statusCode' => 500, 'responseMessage' => $error_msg, 'reference' => $tranx_ref];
            }
            curl_close($ch);

            $tranx = json_decode($response);
            // {
            //  "responseMessage": "Request processed successfully",
            //  "responseCode": "00"
            // }
            //return $tranx;
            if (isset($tranx->status) && $tranx->status != "success") {
              return ['error' => 1, 'statusCode' => $tranx->responseCode , 'responseMessage' => $tranx->responseMessage, 'reference' => $tranx_ref];
            }

            if ($tranx->responseCode == "00") {
              return ['error' => 0, 'statusCode' => $tranx->responseCode, 'responseMessage' => $tranx->responseMessage, 'reference' => $tranx_ref];
            }else{
              return ['error' => 1, 'statusCode' => $tranx->responseCode , 'responseMessage' => $tranx->responseMessage, 'reference' => $tranx_ref];
            }

        }else {
           return ['error' => 1, 'statusCode' => 401, 'responseMessage' => 'Transaction reference was not provided.', 'reference' => $tranx_ref];
        }

    }
    // {
    //     "account_bank": "044",
    //     "account_number": "0690000040",
    //     "amount": 5500,
    //     "narration": "Akhlm Pstmn Trnsfr xx007",
    //     "currency": "NGN",
    //     "reference": "akhlm-pstmnpyt-rfxx007_PMCKDU_1",
    //     "callback_url": "https://webhook.site/b3e505b0-fe02-430e-a538-22bbbce8ce0d",
    //     "debit_currency": "NGN"
    // }

    // {
    //     "status": "success",
    //     "message": "Transfer Queued Successfully",
    //     "data": {
    //         "id": 36000,
    //         "account_number": "0690000040",
    //         "bank_code": "044",
    //         "full_name": "Alexis Sanchez",
    //         "created_at": "2020-04-28T13:18:15.000Z",
    //         "currency": "NGN",
    //         "debit_currency": "NGN",
    //         "amount": 5500,
    //         "fee": 26.875,
    //         "status": "NEW",
    //         "reference": "akhlm-pstmnpyt-rfxx007_PMCKDU_1",
    //         "meta": null,
    //         "narration": "Akhlm Pstmn Trnsfr xx007",
    //         "complete_message": "",
    //         "requires_approval": 0,
    //         "is_approved": 1,
    //         "bank_name": "ACCESS BANK NIGERIA"
    //     }
    // }

}