<?php

namespace App\Repositories;

use Exception;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log as Logger;

class Sms
{
    static public function sendWithTermii($to, $msg, $channels = ["generic"])
    {
        try {
            if (!is_null($to) && !is_null($msg)) {
                $from = env('SMS_TERMII_NAME');
                $apI_key = env('SMS_TERMII_API_KEY');
                $url = env('SMS_TERMII_HOST');
                foreach ($channels as $channel) {
                    $payload = [
                        "api_key" => $apI_key,
                        "from" => $from,
                        "to" => $to,
                        "sms" => $msg,
                        "type" => "plain",
                        "channel" => $channel
                    ];
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, true));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        "Content-Type: application/json"
                    ]);

                    $response = curl_exec($ch);
                    Logger::info('SMS Provider Host - TERMII', [$url, $payload]);
                    Logger::info('SMS Raw Response - TERMII', [$response]);
                    curl_close($ch);
                    $response = json_decode($response);
                    Logger::info('SMS Response - TERMII', [$response]);
                }

                // if ($response->code == "ok") {
                //     return "sent";
                // }
                return $response;
                //if (stristr($response,$to) !== FALSE) return true; else return false;
            }
            return false;
        } catch (Exception $e) {
            Logger::info('SMS Error', [$e->getMessage() . ' - ' . $e->__toString()]);
            return false;
        }
    }


    static public function sendWithSleengShort($to,$msg){
        try{
            if (!is_null($to) && !is_null($msg)) {
                $from = env('SMS_SLEENGSHORT_NAME');
                $app_key = env('SMS_SLEENGSHORT_APP_KEY');
                $url = env('SMS_SLEENGSHORT_HOST');
                $payload = [
                        "sender_id" => $from,
                        "recipients" => $to,
                        "msg" => $msg,
                        "type" => 1
                    ];
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, true));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Content-Type: application/json",
                    "Accept: application/json",
                    "X-Api-Key: $app_key"
                  ]);
                
                $response = curl_exec($ch);
                curl_close($ch);
                $response = json_decode($response);
                Logger::info('SMS Response - sleengshort', [$response]);
                if ($response->status == "ok") {
                    return "sent";
                }
                return $response;
                //if (stristr($response,$to) !== FALSE) return true; else return false;
            }
            return false;
        }

        catch(Exception $e) {
          Logger::info('SMS Error', [$e->getMessage().' - '. $e->__toString()]); 
          return false;
        }
        
    }

    static public function sendWithAT($to, $msg)
    {
        try {
            if (!is_null($to) && !is_null($msg)) {
                $from = env('SMS_SENDER_NAME1');
                $username = env('SMS_SENDER_USERNAME1');
                $pass = env('SMS_SENDER_PASSWORD1');
                $url = env('SMS_SENDER_HOST1');
                $token = env('SMS_SENDER_TOKEN1');

                $curlPost = 'username=' . $username . '&to=' . $to . '&from=' . $from . '&message=' . $msg;
                $arr = [
                    'username' => $username,
                    'to' => $to,
                    'from' => $from,
                    'message' => $msg
                ];
                $curlPost = http_build_query($arr);
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Content-Type: application/x-www-form-urlencoded",
                    "Accept: application/json",
                    "apiKey: $token"
                ]);


                $response = curl_exec($ch);
                $err = curl_errno($ch);

                if ($err) {
                    $error_msg = curl_error($ch);
                    curl_close($ch);
                    return ['error' => 1, 'statusCode' => 500, 'responseMessage' => $error_msg, 'Detail' => $error_msg];
                }
                $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                $response = json_decode($response);
                Logger::info('SMS Response - AT', [$response]);
                if ($httpcode >= 500) {
                    return ['error' => 1, 'statusCode' => $httpcode, 'responseMessage' => $httpcode, 'Detail' => $response];
                }
                if ($httpcode == 201 && !is_null($response->SMSMessageData->Message)) {
                    return 'sent';
                }

                return [$response, $httpcode, $curlPost, $token];
                //if (stristr($response,$to) !== FALSE) return true; else return false;
                // {
                //     "SMSMessageData": {
                //         "Message": "Sent to 1/1 Total Cost: KES 0.8000",
                //         "Recipients": [{
                //             "statusCode": 101,
                //             "number": "+254711XXXYYY",
                //             "status": "Success",
                //             "cost": "KES 0.8000",
                //             "messageId": "ATPid_SampleTxnId123"
                //         }]
                //     }
                // }
            }
            return false;
        } catch (Exception $e) {
            Logger::info('SMS Error', [$e->getMessage() . ' - ' . $e->__toString()]);
            return $e->__toString();
        }
    }

    static public function sendWithNuobject($to, $msg)
    {
        try {
            if (!is_null($to) && !is_null($msg)) {
                $from = env('SMS_SENDER_NAME');
                $username = env('SMS_SENDER_USERNAME');
                $pass = env('SMS_SENDER_PASSWORD');
                $url = env('SMS_SENDER_HOST');

                $curlPost = 'user=' . $username . '&pass=' . $pass . '&to=' . $to . '&from=' . $from . '&msg=' . $msg;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                $response = curl_exec($ch);
                curl_close($ch);
                Logger::info('SMS Response - nuobject', [$response]);
                return $response;
                //if (stristr($response,$to) !== FALSE) return true; else return false;
            }
            return false;
        } catch (Exception $e) {
            Logger::info('SMS Error', [$e->getMessage() . ' - ' . $e->__toString()]);
            return false;
        }
    }

    static public function sendWithTwilio($to, $msg)
    {
        try {
            if (!is_null($to) && !is_null($msg)) {
                $sid = env("TWILIO_SID"); // Your Account SID from www.twilio.com/console
                $token = env("TWILIO_AUTH_TOKEN"); // Your Auth Token from www.twilio.com/console

                $client = new Client($sid, $token);
                $response = $client->messages->create(
                    $to, // Text this number
                    array(
                        'from' => env('VALID_TWILLO_NUMBER'), // From a valid Twilio number
                        'body' => $msg
                    )
                );

                $response = json_decode($response);
                Logger::info('SMS Response - Twilio', [$response]);

                return $response;
            }
            return false;
        } catch (Exception $e) {
            Logger::info('SMS Error', [$e->getMessage() . ' - ' . $e->__toString()]);
            return false;
        }
    }

    static public function sendWithNexmo($to, $msg)
    {
        try {
            if (!is_null($to) && !is_null($msg)) {
                $api_key = env("NEXMO_KEY");
                $api_secret = env("NEXMO_SECRET");
                $from = env('APP_NAME');
                $params = [
                    "api_key" => $api_key,
                    "api_secret" => $api_secret,
                    "from" => $from,
                    "text" => $msg,
                    "to" => $to
                ];

                $url = "https://rest.nexmo.com/sms/json";
                $params = json_encode($params);

                $ch = curl_init(); // Initialize cURL
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($params),
                    'accept:application/json'
                ));
                $response = curl_exec($ch);
                curl_close($ch);

                $response = json_decode($response);
                Logger::info('SMS Response - Nexmo', [$response]);

                return $response;
            }
            return false;
        } catch (Exception $e) {
            Logger::info('SMS Error', [$e->getMessage() . ' - ' . $e->__toString()]);
            return false;
        }
    }
}
