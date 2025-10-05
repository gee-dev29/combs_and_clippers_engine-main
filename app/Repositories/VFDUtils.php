<?php
namespace App\Repositories;
use Paystack;
use \Exception;
use Notification;
use Carbon\Carbon;
use App\Notifications\TransferResponse;
use Illuminate\Support\Facades\Log as Logger;

class VFDUtils 
{
    
    public function __construct()
    {
      $this->base_url = env("VFD_BASE_URL");
      $this->access_token = env("VFD_ACCESS_TOKEN");
      $this->wallet_credentials = env("VFD_WALLET_CREDENTIALS");
     
    }

    function encryptV2($encryption_key,$data)
    {
        //TripleDES.encrypt("{accountNumber};{bankCBNCode}",secretKey)
        $method = "des-ede3-cbc";
        $source = mb_convert_encoding($encryption_key, 'UTF-16LE', 'UTF-8');
        $encryption_key = md5($source, true);
        $encryption_key .= substr($encryption_key, 0, 16);
        $iv =  "\0\0\0\0\0\0\0\0";
        $encData = openssl_encrypt($data,$method, $encryption_key, $options=OPENSSL_RAW_DATA, $iv);
        return base64_encode($encData);
    }


    public function nameEnquiry($accountNo, $bankCode, $tranfer_type="inter")
    {
      try{

        $wallet_credentials = $this->wallet_credentials;
        $access_token = $this->access_token;
        if($bankCode == '999999'){
          $tranfer_type="intra";
        }

        // transfer_type
        // accountNo
        // bank
        // wallet-credentials
        // {
        //   "status": "00",
        //   "message": "Account Found",
        //   "data": {
        //     "name": "OGBA, CHRISTOPHER CHINONYE",
        //     "clientId": "NaN",
        //     "bvn": "22222222223",
        //     "account": {
        //       "id": "999116190411110815131298994293",
        //       "number": "0001744830"
        //     },
        //     "status": "2",
        //     "currency": "NGN",
        //     "bank": "GTBank"
        //   }
        // }
        
        //$url = $this->base_url ."/transfer/recipient?transfer_type=$tranfer_type&accountNo=$accountNo&bank=$bankCode&wallet-credentials=$wallet_credentials";
        $url = $this->base_url ."/transfer/recipient?transfer_type=$tranfer_type&accountNo=$accountNo&bank=$bankCode";
        Logger::info('VFD Name Enquiry Request', [$url]);
        
        $curl = curl_init(); 

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);                    
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
                "accept: application/json",
                "AccessToken: $access_token",
                "Authorization: Bearer $access_token"
              ]);

        $response = curl_exec($curl);
        $err = curl_errno($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
        if($err){
          $error_msg = curl_error($curl);
          curl_close($curl);
          //return $error_msg;
          return ['error' => 1, 'statusCode' => 500, 'responseMessage' => $error_msg];
        }
        curl_close($curl);

        

        $transaction = json_decode($response);
        Logger::info('VFD Name Enquiry Response', [$transaction]);
        //return ['error' => 1, $transaction, $url];
        if($httpcode >= 500){
          return ['error' => 1, 'statusCode' => 500, 'trans' => $transaction, 'responseMessage' => $err];
        }
        if (is_null($transaction)) {
          return ['error' => 1, 'statusCode' => 500, 'responseMessage' => 'Null response', 'trans' => $transaction, 'url' => $url];
        }
        if ($transaction->status == "00") {
          return ['error' => 0, 'statusCode' => $transaction->status, 'responseMessage' => $transaction->message, 'accountInfo' => $transaction->data];
        }else{
          return ['error' => 1, 'statusCode' => $transaction->status, 'responseMessage' => $transaction->message, 'trans' => $transaction, 'url' => $url];
        }
       
      } catch (Exception $e) {
        Logger::info('VFD Error', [$e->getMessage().' - '. $e->__toString()]); 
        return ['error' => 1, 'statusCode' => 500, 'Detail' => $e->getMessage(), 'responseMessage' => 'Something went wrong'];
      }
       
    }

    
    public function transactionStatusQuery($reference)
    {
      try{
        $access_token = $this->access_token;
        $wallet_credentials = $this->wallet_credentials;
        
        //$url = $this->base_url ."/transactions?reference=$reference&wallet-credentials=$wallet_credentials";
        $url = $this->base_url ."/transactions?reference=$reference";
        Logger::info('VFD Name Enquiry Request', [$url]);
        
        $curl = curl_init(); 

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);                    
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
                "accept: application/json",
                "AccessToken: $access_token",
                "Authorization: Bearer $access_token"
              ]);

        $response = curl_exec($curl);
        $err = curl_errno($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
        if($err){
          $error_msg = curl_error($curl);
          curl_close($curl);
          //return $error_msg;
          return ['error' => 1, 'statusCode' => 500, 'responseMessage' => $error_msg];
        }
        curl_close($curl);

        

        $transaction = json_decode($response);
        Logger::info('VFD Transaction Status Query Response', [$transaction]);
        //return ['error' => 1, $transaction, $url];
        if($httpcode >= 500){
          return ['error' => 1, 'statusCode' => 500, 'trans' => $transaction, 'responseMessage' => $err, 'reference' => $reference];
        }
        if (is_null($transaction)) {
          return ['error' => 1, 'statusCode' => 500, 'responseMessage' => 'Null response', 'trans' => $transaction, 'url' => $url, 'reference' => $reference];
        }
        if ($transaction->status == "00") {
          return ['error' => 0, 'statusCode' => $transaction->status, 'responseMessage' => $transaction->message, 'transferInfo' => $transaction->data, 'reference' => $reference];
        }else{
          return ['error' => 1, 'statusCode' => $transaction->status, 'responseMessage' => $transaction->message, 'trans' => $transaction, 'url' => $url, 'reference' => $reference];
        }

        // {
        //   "status": "00",
        //   "message": "Successful Transaction Retrieval",
        //   "data": {
        //       "TxnId": "TestWallet-1019101910190993",
        //       "amount": "500000.00",
        //       "accountNo": "1000058012",
        //       "fromAccountNo": "1000075901",
        //       "transactionStatus": "99",
        //       "transactionDate": "2023-01-11 08:05:25.0",
        //       "toBank": "999999",
        //       "fromBank": "999999",
        //       "sessionId": "",
        //       "bankTransactionId": "",
        //       "transactionType": "OUTFLOW"
        //   }
        // }
       
      } catch (Exception $e) {
        Logger::info('VFD Error', [$e->getMessage().' - '. $e->__toString()]); 
        return ['error' => 1, 'statusCode' => 500, 'Detail' => $e->getMessage(), 'responseMessage' => 'Something went wrong'];
      }
       
    }


    public function getBankList()
    {
      try{

        $access_token = $this->access_token;

        $url = $this->base_url ."/bank";
        
        $curl = curl_init(); 

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);                    
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
                "accept: application/json",
                "AccessToken: $access_token",
                "Authorization: Bearer $access_token"
              ]);

        $response = curl_exec($curl);
        $err = curl_errno($curl);
        
        
        if($err){
          $error_msg = curl_error($curl);
          curl_close($curl);
          //return $error_msg;
          return ['error' => 1, 'statusCode' => 500, 'responseMessage' => $error_msg];
        }
        curl_close($curl);

        $transaction = json_decode($response);
        //return ['error' => 1, $transaction, $url];
        if ($transaction->status == "00") {
          return ['error' => 0, 'statusCode' => $transaction->status, 'responseMessage' => $transaction->message, 'banks' => $transaction->data->bank];
        }else{
          return ['error' => 1, 'statusCode' => $transaction->status, 'responseMessage' => $transaction->message, 'trans' => $transaction, 'url' => $url];
        }
       
      } catch (Exception $e) {
        Logger::info('VFD Error', [$e->getMessage().' - '. $e->__toString()]);
        return ['error' => 1, 'statusCode' => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'];
      }
       
    }


    public function transferFundTest($toAccount, $toBank, $narration, $amount)
    {
      $txref = "PEP-ref-" . time(); // ensure you generate unique references per   
      try{
        $fromAccountNo = env('VFD_ACCOUNT_NO');
        $accountId = env('VFD_ACCOUNT_ID');
        $client = env('VFD_ACCOUNT_NAME');
        $clientId = env('VFD_CLIENT_ID');
        $fromBvn = env('VFD_ACCOUNT_BVN');
        
        $nameEnq = $this->nameEnquiry($toAccount, $toBank);
        
        if ($nameEnq['error'] == 0 && $nameEnq['statusCode'] == "00") {
            $acctInfo = $nameEnq['accountInfo'];
            $toSession = $acctInfo->account->id;
            $toBvn =  $acctInfo->bvn;
            $toClient =  $acctInfo->name;
            $toClientId =  ($acctInfo->clientId != "NaN") ? $acctInfo->clientId : "";
            $signature = hash("sha512", $fromAccountNo . $toAccount);
            $access_token = $this->access_token;

            $payload = ["fromSavingsId" => $accountId, 
                      "fromBvn" => $fromBvn,
                      "fromAccount" => $fromAccountNo,
                      "fromClientId" => $clientId,
                      "fromClient" => $client,
                      "amount" => $amount,
                      "toAccount"  => $toAccount,
                      "toBvn" => $toBvn,
                      "toClientId" => $toClientId,
                      "toClient" => $toClient,
                      "toKyc" => "99",
                      "toBank" => $toBank,
                      "toSavingsId" => "",
                      "toSession" => $toSession,
                      "signature" => $signature,
                      "remark" => $narration,
                      "reference" => $txref,
                      "transferType" => "inter",
                     ];
            $url = $this->base_url ."/transfer";
            $curl = curl_init();
            curl_setopt_array($curl, array(
              CURLOPT_URL => $url,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_CUSTOMREQUEST => "POST",
              CURLOPT_POSTFIELDS => json_encode($payload, true),
              CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "accept: application/json",
                "Authorization: Bearer $access_token",
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
            Logger::info('VFD Transfer Response', [$transaction]);
            //return [$response, $signature1];
            if ($transaction->status == "00") {
              return ['error' => 0, 'statusCode' => $transaction->status, 'responseMessage' => $transaction->message, 'transferInfo' => $transaction->data];
            }else{
              return ['error' => 1, 'statusCode' => $transaction->status, 'responseMessage' => $transaction->message];
            }
            //return $nameEnq;
            // {
            //   "status": "00",
            //   "message": "Simulated Successful Response From Nibss",
            //   "data": {
            //     "txnId": "admin-12910hewr43999",
            //     "sessionId": "999116220325070115177619115956",
            //     "reference": "24781648191675853"
            //   }
            // }

        }else{
           return $nameEnq;
        }
        
      } catch (Exception $e) {
        Logger::info('VFD Error', [$e->getMessage().' - '. $e->__toString()]);
        return ['error' => 1, 'statusCode' => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong', 'reference' => $txref];
      }
       
    }


    public function transferFund($toAccount, $toBankCode, $amount, $narration)
    {
      $txref = "PEP-ref-" . time(); // ensure you generate unique references per 
      try{
        $fromAccountNo = env('VFD_ACCOUNT_NO');
        $accountId = env('VFD_ACCOUNT_ID');
        $client = env('VFD_ACCOUNT_NAME');
        $clientId = env('VFD_CLIENT_ID');
        $fromBvn = env('VFD_ACCOUNT_BVN');
        $nameEnq = $this->nameEnquiry($toAccount, $toBankCode);
        
        if ($nameEnq['error'] == 0 && $nameEnq['statusCode'] == "00") {
            $acctInfo = $nameEnq['accountInfo'];
            $toSession = $acctInfo->account->id;
            $toBvn =  $acctInfo->bvn;
            $toClient =  $acctInfo->name;
            $toClientId =  ($acctInfo->clientId != "NaN") ? $acctInfo->clientId : "";
            $signature = hash("sha512", $fromAccountNo . $toAccount);
            $access_token = $this->access_token;
            $wallet_credentials = $this->wallet_credentials;

            $payload = ["fromSavingsId" => $accountId, 
                      "fromBvn" => $fromBvn,
                      "fromAccount" => $fromAccountNo,
                      "fromClientId" => $clientId,
                      "fromClient" => $client,
                      "amount" => $amount,
                      "toAccount"  => $toAccount,
                      "toBvn" => $toBvn,
                      "toClientId" => $toClientId,
                      "toClient" => $toClient,
                      "toKyc" => "99",
                      "toBank" => $toBankCode,
                      "toSavingsId" => "",
                      "toSession" => $toSession,
                      "signature" => $signature,
                      "remark" => $narration,
                      "reference" => $txref,
                      "transferType" => "inter",
                     ];
            $url = $this->base_url ."/transfer?wallet-credentials=$wallet_credentials";
            //$url = $this->base_url ."/transfer";
            $curl = curl_init();
            curl_setopt_array($curl, array(
              CURLOPT_URL => $url,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_CUSTOMREQUEST => "POST",
              CURLOPT_POSTFIELDS => json_encode($payload, true),
              CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "accept: application/json",
                "AccessToken: $access_token",
                "Authorization: Bearer $access_token",
              ],
            ));

            $response = curl_exec($curl);
            $err = curl_errno($curl);
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            
            if($err){
              $error_msg = curl_error($curl);
              curl_close($curl);
              //return $error_msg;
              return ['error' => 1, 'statusCode' => 500, 'responseMessage' => $error_msg, 'reference' => $txref];
            }
            curl_close($curl);
            Logger::info('VFD Transfer Fund Response', [$response]);
            $transaction = json_decode($response);

            $transfer = [];
            $transfer["details"] = "\nAcct No: $toAccount \n BankCode: $toBankCode \n amount: $amount \nnarration: $narration \nResponse: $response";

            // Notification::route('slack', env('SLACK_HOOK_TRANSFER_URL'))
            //               ->notify(new TransferResponse($transfer));
            //return [$response, $signature1];
            if (is_null($transaction)) {
              return ['error' => 1, 'statusCode' => 500, 'responseMessage' => 'Null response', 'trans' => $transaction, 'url' => $url, 'reference' => $txref];
            }
            if($httpcode >= 500){
              return ['error' => 1, 'statusCode' => $transaction->status, 'responseMessage' => $transaction->message, 'reference' => $txref];
            }
            if ($transaction->status == "00") {
              return ['error' => 0, 'statusCode' => $transaction->status, 'responseMessage' => $transaction->message, 'transferInfo' => $transaction->data, 'reference' => $txref];
            }else{
              return ['error' => 1, 'statusCode' => $transaction->status, 'responseMessage' => $transaction->message, 'response' => $transaction, 'payload' => $payload, 'reference' => $txref];
            }
              
        }else{
          $response = json_encode($nameEnq);
          $transfer = [];
          $transfer["details"] = "\nAcct No: $toAccount \n BankCode: $toBankCode \n amount: $amount \nnarration: $narration \nResponse: $response";

          // Notification::route('slack', env('SLACK_HOOK_TRANSFER_URL'))
          //                 ->notify(new TransferResponse($transfer));
          return $nameEnq;

           //return ['error' => 1, 'statusCode' => $transaction->status, 'responseMessage' => $transaction->message, 'trans' => $transaction, 'url' => $url];
        }
        
      } catch (Exception $e) {
        Logger::info('VFD Error', [$e->getMessage().' - '. $e->__toString()]);
        return ['error' => 1, 'statusCode' => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong', 'reference' => $txref];
      }
       
    }


    public function transferFundIntraTest()
    {
      $txref = "PEP-ref-" . time(); // ensure you generate unique references per 
      try{
        
        $fromAccountNo = "1000060468";
        $accountId = "6046";
        $client = "Pepperest Escrow";
        $clientId = "5924";
        //$toAccount = $customerObj->accountno;
        //$toBank = $customerObj->bankcode;
        //10000604680001744830
        $toAccount = "1000063012";
        $toBank = "999999";
        $tranfer_type = "intra";
        $nameEnq = $this->nameEnquiry($toAccount, $toBank, $tranfer_type);
        
        if ($nameEnq['error'] == 0 && $nameEnq['statusCode'] == "00") {
            $acctInfo = $nameEnq['accountInfo'];
            $toSession = $acctInfo->account->id;
            $toBvn =  $acctInfo->bvn;
            $toClient =  $acctInfo->name;
            $toClientId =  ($acctInfo->clientId != "NaN") ? $acctInfo->clientId : "";
            $signature = hash("sha512", $fromAccountNo . $toAccount);
            //$signature = "ff2d844cb4a29937d146b73fc116f74ac3871a6f2259e356146aa1ec0e17bc4bda7688e89dadd1df5a1678437009ff53fc447a045b5c728203b9d6a0c1e36d54";
            $access_token = $this->access_token;

            $payload = ["fromSavingsId" => $accountId, 
                      "fromBvn" => "1000000000",
                      "fromAccount" => $fromAccountNo,
                      "fromClientId" => $clientId,
                      "fromClient" => $client,
                      "amount" => "1000",
                      "toAccount"  => $toAccount,
                      "toBvn" => $toBvn,
                      "toClientId" => $toClientId,
                      "toClient" => $toClient,
                      "toKyc" => "99",
                      "toBank" => $toBank,
                      "toSavingsId" => $toSession,
                      "toSession" => $toSession,
                      "signature" => $signature,
                      "remark" => "narration",
                      "reference" => $txref,
                      "transferType" => "intra",
                     ];
            $url = $this->base_url ."/transfer";
            $curl = curl_init();
            curl_setopt_array($curl, array(
              CURLOPT_URL => $url,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_CUSTOMREQUEST => "POST",
              CURLOPT_POSTFIELDS => json_encode($payload, true),
              CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "accept: application/json",
                "Authorization: Bearer $access_token",
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
            return [$transaction, $url, json_encode($payload, true)];
            if ($transaction->status == "00") {
              return ['error' => 0, 'statusCode' => $transaction->status, 'responseMessage' => $transaction->message, 'transferInfo' => $transaction->data];
            }else{
              return ['error' => 1, 'statusCode' => $transaction->status, 'responseMessage' => $transaction->message];
            }
            //return $nameEnq;
            // {
            //   "status": "00",
            //   "message": "Simulated Successful Response From Nibss",
            //   "data": {
            //     "txnId": "admin-12910hewr43999",
            //     "sessionId": "999116220325070115177619115956",
            //     "reference": "24781648191675853"
            //   }
            // }

        }else{
           return $nameEnq;
        }
        
      } catch (Exception $e) {
        Logger::info('VFD Error', [$e->getMessage().' - '. $e->__toString()]);
        return ['error' => 1, 'statusCode' => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong', 'reference' => $txref];
      }
       
    }

    //($seller_acctno, $seller_bankcode, $tranx->totalprice, $narration)
    public function transferFundIntra($toAccount, $toBankCode, $amount, $narration)
    {
      $txref = "PEP-ref-" . time(); // ensure you generate unique references per 
      try{
        
        $fromAccountNo = env('VFD_ACCOUNT_NO');
        $accountId = env('VFD_ACCOUNT_ID');
        $client = env('VFD_ACCOUNT_NAME');
        $clientId = env('VFD_CLIENT_ID');
        
        $tranfer_type = "intra";
        $nameEnq = $this->nameEnquiry($toAccount, $toBankCode, $tranfer_type);
        
        if ($nameEnq['error'] == 0 && $nameEnq['statusCode'] == "00") {
            $acctInfo = $nameEnq['accountInfo'];
            $toSession = $acctInfo->account->id;
            $toBvn =  $acctInfo->bvn;
            $toClient =  $acctInfo->name;
            $toClientId =  ($acctInfo->clientId != "NaN") ? $acctInfo->clientId : "";
            $signature = hash("sha512", $fromAccountNo . $toAccount);
            //$signature = "ff2d844cb4a29937d146b73fc116f74ac3871a6f2259e356146aa1ec0e17bc4bda7688e89dadd1df5a1678437009ff53fc447a045b5c728203b9d6a0c1e36d54";
            $access_token = $this->access_token;
            $wallet_credentials = $this->wallet_credentials;

            $payload = ["fromSavingsId" => $accountId, 
                      "fromBvn" => "1000000000",
                      "fromAccount" => $fromAccountNo,
                      "fromClientId" => $clientId,
                      "fromClient" => $client,
                      "amount" => $amount,
                      "toAccount"  => $toAccount,
                      "toBvn" => $toBvn,
                      "toClientId" => $toClientId,
                      "toClient" => $toClient,
                      "toKyc" => "99",
                      "toBank" => $toBankCode,
                      "toSavingsId" => $toSession,
                      "toSession" => $toSession,
                      "signature" => $signature,
                      "remark" => $narration,
                      "reference" => $txref,
                      "transferType" => "intra",
                     ];
            $url = $this->base_url ."/transfer?wallet-credentials=$wallet_credentials";
            //$url = $this->base_url ."/transfer";
            $curl = curl_init();
            curl_setopt_array($curl, array(
              CURLOPT_URL => $url,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_CUSTOMREQUEST => "POST",
              CURLOPT_POSTFIELDS => json_encode($payload, true),
              CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "accept: application/json",
                "AccessToken: $access_token",
                "Authorization: Bearer $access_token",
              ],
            ));

            $response = curl_exec($curl);
            $err = curl_errno($curl);
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            
            if($err){
              $error_msg = curl_error($curl);
              curl_close($curl);
              //return $error_msg;
              return ['error' => 1, 'statusCode' => 500, 'responseMessage' => $error_msg, 'reference' => $txref];
            }
            curl_close($curl);
            Logger::info('VFD Transfer Fund Intra Response', [$response]);
            $transaction = json_decode($response);
            $transfer = [];
            $transfer["details"] = "\nAcct No: $toAccount \n BankCode: $toBankCode \n amount: $amount \nnarration: $narration \nResponse: $response";

            // Notification::route('slack', env('SLACK_HOOK_TRANSFER_URL'))
            //               ->notify(new TransferResponse($transfer));
            //return [$transaction, $url, json_encode($payload, true)];
            if (is_null($transaction)) {
              return ['error' => 1, 'statusCode' => 500, 'responseMessage' => 'Null response', 'trans' => $transaction, 'url' => $url, 'reference' => $txref];
            }
            if($httpcode >= 500){
              return ['error' => 1, 'statusCode' => $transaction->status, 'responseMessage' => $transaction->message, 'reference' => $txref];
            }
            if ($transaction->status == "00") {
              return ['error' => 0, 'statusCode' => $transaction->status, 'responseMessage' => $transaction->message, 'transferInfo' => $transaction->data, 'reference' => $txref];
            }else{
              return ['error' => 1, 'statusCode' => $transaction->status, 'responseMessage' => $transaction->message, 'reference' => $txref];
            }
            //return $nameEnq;
            // {
            //   "status": "00",
            //   "message": "Simulated Successful Response From Nibss",
            //   "data": {
            //     "txnId": "admin-12910hewr43999",
            //     "sessionId": "999116220325070115177619115956",
            //     "reference": "24781648191675853"
            //   }
            // }

        }else{
          $response = json_encode($nameEnq);
          $transfer = [];
          $transfer["details"] = "\nAcct No: $toAccount \n BankCode: $toBankCode \n amount: $amount \nnarration: $narration \nResponse: $response";

          // Notification::route('slack', env('SLACK_HOOK_TRANSFER_URL'))
          //                 ->notify(new TransferResponse($transfer));
          return $nameEnq;
        }
        
      } catch (Exception $e) {
        Logger::info('VFD Error', [$e->getMessage().' - '. $e->__toString()]);
        return ['error' => 1, 'statusCode' => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong', 'reference' => $txref];
      }
       
    }


    public function createTempAccount($merchant_name, $merchant_id, $amount, $validity = 120)
    {
      try{ 
        $txref = "PEP-ref-" . time(); // ensure you generate unique references per 
        $wallet_credentials = $this->wallet_credentials;
        $access_token = $this->access_token;
        $payload = ["amount" => $amount, 
                  "merchantName" => $merchant_name,
                  "merchantId" => time(),
                  "reference" => $txref,
                  "validityTime" => $validity,
                 ];
        //$url = $this->base_url ."/virtualaccount";
        $url = $this->base_url ."/virtualaccount?wallet-credentials=$wallet_credentials";

        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => json_encode($payload, true),
          CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "accept: application/json",
            "AccessToken: $access_token",
            "Authorization: Bearer $access_token",
          ],
        ));

        $response = curl_exec($curl);
        $err = curl_errno($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
        if($err){
          $error_msg = curl_error($curl);
          curl_close($curl);
          //return $error_msg;
          return ['error' => 1, 'statusCode' => 500, 'responseMessage' => $error_msg, 'reference' => $txref];
        }
        curl_close($curl);
        Logger::info('VFD Virtual Account Request', [json_encode($payload, true)]);
        Logger::info('VFD Virtual Account Response', [$response]);
        $transaction = json_decode($response);
        //return [$transaction, $url, json_encode($payload, true)];
        // {
        //     "status": "00",
        //     "message": "Successful",
        //     "accountNumber": "4600070017",
        //     "reference": "212272727"
        // }

        if (is_null($transaction)) {
          return ['error' => 1, 'statusCode' => 500, 'responseMessage' => 'Null response', 'trans' => $transaction, 'url' => $url];
        }
        if($httpcode >= 500){
          return ['error' => 1, 'statusCode' => $transaction->status, 'responseMessage' => $transaction->message];
        }
        if ($transaction->status == "00") {
          $transaction->accountName = $merchant_name;
          return ['error' => 0, 'responseMessage' => $transaction->message, 'statusCode' => $transaction->status, 'responseDetails' => $transaction, 'bankCode' => 566, 'initiationTranRef' => $txref];
        }else{
          return ['error' => 1, 'statusCode' => $transaction->status, 'responseMessage' => $transaction->message];
        }
        
        
      } catch (Exception $e) {
        Logger::error('VFD Virtual Account error', [$e->getMessage().' - '. $e->__toString()]); 
        return ['error' => 1, 'statusCode' => 500, 'responseMessage' => $e->getMessage()];
      }
       
    }

  public function createPermAccount($dob, $bvn)
  {
    try {
      $wallet_credentials = $this->wallet_credentials;
      $access_token = $this->access_token;
      $url = $this->base_url . "/client/create?wallet-credentials=$wallet_credentials&dateOfBirth=$dob&bvn=$bvn";

      $curl = curl_init();
      curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_HTTPHEADER => [
          "Content-Type: application/json",
          "accept: application/json",
          "AccessToken: $access_token",
          "Authorization: Bearer $access_token",
        ],
      ));

      $response = curl_exec($curl);
      $err = curl_errno($curl);
      $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

      if ($err) {
        $error_msg = curl_error($curl);
        curl_close($curl);
        //return $error_msg;
        return ['error' => 1, 'statusCode' => 500, 'responseMessage' => $error_msg];
      }
      curl_close($curl);
      Logger::info("VFD Create Permanent Account", ["Request" => $url, "Response" => $response]);
      $account = json_decode($response);
      // {
      //   "status": "00",
      //   "message": "Successful Creation",
      //   "data": {
      //       "firstname": "MARIUS",
      //       "middlename": "DOE",
      //       "lastname": "PETERSON",
      //       "bvn": "22222222223",
      //       "phone": "09022222222",
      //       "dob": "08-Mar-1995",
      //       "accountNo": "1001563612"
      //   }
      // }

    
      if ($httpcode >= 500) {
        return ['error' => 1, 'statusCode' => $account->status, 'responseMessage' => $account->message];
      }
      
      if(env("APP_ENV") == "local"){
        $account = array(
          "status" => "00",
          "message" => "Successful Creation",
          "data" => [
              "firstname" => "MARIUS",
              "middlename" => "DOE",
              "lastname" => "PETERSON",
              "bvn" => "22222222223",
              "phone" => "09022222222",
              "dob" => "08-Mar-1995",
              "accountNo" => "1001563612"
          ]);
        $account = json_encode($account, true);
        $account = json_decode($account);
      }
      if (is_null($account)) {
        return ['error' => 1, 'statusCode' => 500, 'responseMessage' => 'Null response', 'url' => $url];
      }
      
      if ($account->status == "00") {
        return ['error' => 0, 'statusCode' => $account->status, 'responseMessage' => $account->message,  'account' => $account->data, 'bankCode' => 566];
      } else {
        return ['error' => 1, 'statusCode' => $account->status, 'responseMessage' => $account->message];
      }
    } catch (Exception $e) {
      Logger::error('VFD Create Permanent Account error', [$e->getMessage() . ' - ' . $e->__toString()]);
      return ['error' => 1, 'statusCode' => 500, 'responseMessage' => $e->getMessage()];
    }
  }
}