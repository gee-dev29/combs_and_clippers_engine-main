<?php
namespace App\Repositories;
use PDF;
use \Exception;
use Carbon\Carbon;
use App\Mail\OrderCanceled;
use App\Mail\TransactionMail;
use App\Mail\FXOrderCancelled;
use App\Mail\BookingMail_Buyer;
use App\Mail\EmailVerification;
use App\Mail\PasswordResetMail;
use App\Mail\BookingMail_Seller;
use App\Mail\NewOrderMail_Buyer;
use App\Mail\FXEmailVerification;
use App\Mail\NewOrderMail_Seller;
use App\Mail\NewFXOrderMail_Buyer;
use App\Mail\NewInvoiceMail_Buyer;
use App\Mail\OrderConfirmTestMail;
use App\Mail\NewFXOrderMail_Seller;
use App\Mail\NewInvoiceMail_Seller;
use App\Mail\OrderStatusChangeMail;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceStatusChangeMail;
use App\Mail\PasswordResetMail_token;
use App\Mail\OrderTranxStatusChangeMail;
use App\Mail\OrderStatusChangeMail_Seller;
use App\Mail\OrderStatusChangeMailWithOTP;
use App\Mail\OrderDeliveryStatusChangeMail;
use Illuminate\Support\Facades\Log as Logger;
use App\Mail\OrderTranxStatusChangeMail_Seller;
use App\Mail\OrderStatusChangeMailWithOTP_Seller;
use App\Mail\OrderDeliveryStatusChangeMail_Seller;

class PepperestUtils 
{
    

    public function __construct()
    {
        
    }

    public function send_test()
    {
        try{
            
            //[$cur, $ActualAmount, $description, $TxnRef]
            $data['name'] = 'Emmanuel Obute';
            $email = 'emmanuel6.obute@gmail.com';
            $data['ActualAmount'] = 50000.45;
            $data['description'] = 'Payment for enso water pack';
            $data['TxnRef'] = '747748383';
            $data['cur'] = 'N';
            Mail::to($email)
            ->send(new TransactionMail($data));
                    
            return true;
        }

        //catch exception
        catch(Exception $e) {
          Logger::info('Email Error', [$e->getMessage().' - '. $e->__toString()]); 
          return false;
        }
       
    }

    public function sendSMS($to,$msg){
        try{
            if (!is_null($to) && !is_null($msg)) {
                
                //$gloSubString = ['0705','0805','0807','0815','0811','0905', '0915'];
                // $mtnSubString = ['0803','0806','0703','0706','0813','0816', '0810', '0814', '0903', '0906', '0913', '0916', '07025', '07026', '0704'];
                //$subString = phoneNoSubString($to);
                // if (in_array($subString, $mtnSubString)) {
                //     $to = formatPhoneNo($to);
                //     $sms_response = $this->sendSMS_sleengShortID($to,$msg);
                //     //$sms_response = $this->sendSMS_AT($to,$msg);
                //     return $sms_response;
                // }
                $to = formatPhoneNo($to);
                $sms_response = $this->sendSMS_sleengShort($to,$msg);
                if ($sms_response == 'sent') {
                    return $sms_response;
                }else{
                    $to = formatPhoneNo($to);
                    $sms_response = $this->sendSMS_AT($to,$msg);
                    //Logger::info('AT SMS Response', [$sms_response]); 
                    if ($sms_response != 'sent') {
                        $sms_response = $this->sendSMS_nuobject($to,$msg);
                    } 
                }
                
                return $sms_response;
            }
            return false;
        }

        catch(Exception $e) {
          Logger::info('SMS Error', [$e->getMessage().' - '. $e->__toString()]); 
          return false;
        }
        
    }

    public function sendSMS_nuobject($to,$msg){
        try{
            if (!is_null($to) && !is_null($msg)) {
                $from = env('SMS_SENDER_NAME');
                $username = env('SMS_SENDER_USERNAME');
                $pass = env('SMS_SENDER_PASSWORD');
                $url = env('SMS_SENDER_HOST');

                $curlPost = 'user='.$username.'&pass='.$pass.'&to='.$to.'&from='.$from.'&msg='.$msg;
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
        }

        catch(Exception $e) {
          Logger::info('SMS Error', [$e->getMessage().' - '. $e->__toString()]); 
          return false;
        }
        
    }

    public function sendSMS_termii($to,$msg, $channels=["generic"]){
        try{
            
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
                        "Content-Type: application/json",
                        "Accept: application/json"
                      ]);
                    
                    $response = curl_exec($ch);
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
        }

        catch(Exception $e) {
          Logger::info('SMS Error', [$e->getMessage().' - '. $e->__toString()]); 
          return false;
        }
        
    }

    public function sendSMS_AT($to,$msg){
        try{
            if (!is_null($to) && !is_null($msg)) {
                $from = env('SMS_SENDER_NAME1');
                $username = env('SMS_SENDER_USERNAME1');
                $pass = env('SMS_SENDER_PASSWORD1');
                $url = env('SMS_SENDER_HOST1');
                $token = env('SMS_SENDER_TOKEN1');

                $curlPost = 'username='.$username.'&to='.$to.'&from='.$from.'&message='.$msg;
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
        
                if($err){
                  $error_msg = curl_error($ch);
                  curl_close($ch);
                  return ['error' => 1, 'statusCode' => 500, 'responseMessage' => $error_msg, 'Detail' => $error_msg];
                }
                $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                $response = json_decode($response);
                Logger::info('SMS Response - AT', [$response]); 
                if($httpcode >= 500){
                  return ['error' => 1, 'statusCode' => $httpcode, 'responseMessage' => $httpcode, 'Detail' => $response];
                }
                if ($httpcode==201 && !is_null($response->SMSMessageData->Message)) {
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
        }

        catch(Exception $e) {
          Logger::info('SMS Error', [$e->getMessage().' - '. $e->__toString()]); 
          return $e->__toString();
        }
        
    }

    public function sendSMS_sleengShort($to,$msg){
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

    public function sendSMS_sleengShortID($to,$msg){
        try{
            if (!is_null($to) && !is_null($msg)) {
                $from = 'FinAdvisor';
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


    public function send_test_email($email_type='signup')
    {
        try{
            
            $data['name'] = 'Emma';
            //$data['email'] = 'emma@pepperest.com';
            $data['email'] = 'emmanuel6.obute@gmail.com';
            $buyer_mail = false;
            if ($email_type == 'signup') {
                $buyer_mail = Mail::send('mails.signupWelcome_buyer', $data, function($message) use($data) {
                        $message->to($data['email'])
                                ->from(cc('mail_from'))
                                ->subject("Welcome to Pepperest");
               });
            }elseif ($email_type == 'verification') {
                $buyer_mail = Mail::send('mails.email_new.update-email-verification', $data, function($message) use($data) {
                        $message->to($data['email'])
                                ->from(cc('mail_from'))
                                ->subject("Welcome to Pepperest");
               });
            }elseif ($email_type == 'order') {
                $buyer_mail = Mail::send('mails.email_new.update-order', $data, function($message) use($data) {
                        $message->to($data['email'])
                                ->from(cc('mail_from'))
                                ->subject("Welcome to Pepperest");
               });
            }elseif ($email_type == 'reset') {
                $buyer_mail = Mail::send('mails.email_new.update-reset-password', $data, function($message) use($data) {
                        $message->to($data['email'])
                                ->from(cc('mail_from'))
                                ->subject("Welcome to Pepperest");
               });
            }elseif ($email_type == 'welcome') {
                $buyer_mail = Mail::send('mails.email_new.update-welcome', $data, function($message) use($data) {
                        $message->to($data['email'])
                                ->from(cc('mail_from'))
                                ->subject("Welcome to Pepperest");
               });
            }
            
            return $buyer_mail;
            if (Mail::to($data['email'])->send(new OrderConfirmTestMail($data))) {
                return true;
            }
            return false;
            // Mail::send('mails.orderConfirmTest', $data, function($message) use($data) {
            //     $message->to($data['email'])
            //             ->from(cc('mail_from'))
            //             ->subject("Welcome to Pepperest Escrow");
            //             //->attachData($pdf->output(), "TermsAndConditions.pdf");
            // });
               
            //return true;
            
        }

        catch(Exception $e) {
          Logger::info('Email Error', [$e->getMessage().' - '. $e->__toString()]); 
          return false;
        }
       
    }



    //Send user to Engageso
    public function send_userDetails_to_engage($user)
    {
        try{
            
            $engage = new \Engage\EngageClient(env('ENGAGE_PUB_KEY'), env('ENGAGE_PRIV_KEY'));
            if (is_null($user->email)) {
                $engage->users->identify([
                  'id' => $user->id,
                  'first_name' => $user->firstName,
                  'last_name' => $user->lastName,
                  //'number' => $user->userInfo->phone,
                  //'created_at' => '2020-05-30T09:30:10Z'
                ]);
            }else{
               $engage->users->identify([
                  'id' => $user->id,
                  'email' => $user->email,
                  'first_name' => $user->firstName,
                  'last_name' => $user->lastName,
                  //'number' => $user->userInfo->phone,
                  //'created_at' => '2020-05-30T09:30:10Z'
                ]); 
            }
            

            return true;
        }

        //catch exception
        catch(Exception $e) {
          Logger::info('Engage.io Error', [$e->getMessage().' - '. $e->__toString()]); 
          return false;
        }
       
    }

    public function send_waitlistUser_to_engage($user)
    {
        try {
            $engage = new \Engage\EngageClient(env('ENGAGE_PUB_KEY'), env('ENGAGE_PRIV_KEY'));

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

    //track($uid, $data)
    //Send event to engage
    public function send_userEvent_to_engage($user, $data)
    {
        try{
            
            $engage = new \Engage\EngageClient(env('ENGAGE_PUB_KEY'), env('ENGAGE_PRIV_KEY'));
            
            $engageResponse = $engage->users->track($user->id, $data); 
               // {
               //    "event": "Add to cart",
               //    "timestamp": "2020-05-30T09:30:10Z",
               //    "properties": {
               //      "product": "T123",
               //      "currency": "USD",
               //      "amount": 12.99
               //     }


               //  }
               //  or
               //  {
               //    "event": "Paid",
               //    "value": 49.99
               //  }
          
            return $engageResponse;
        }

        //catch exception
        catch(Exception $e) {
          Logger::info('Engage.io Event Error', [$e->getMessage().' - '. $e->__toString()]); 
          return false;
        }
       
    }

    public function send_welcome_email($user)
    {
        try{
            
            //[$cur, $ActualAmount, $description, $TxnRef]
            $data['name'] = $user->name;
            $data['email'] = $user->email;
            $data['link'] = cc('frontend_base_url');
            $email = $user->email;
            if (!is_null($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                // Mail::to($email)
                // ->send(new SignupWelcomeMail($data));
                //$pdf = PDF::loadView('mails.termsAndConditions', $data);
                if ($user->userInfo->usertype == 'Buyer') {
                    $buyer_mail = Mail::send('mails.signupWelcome_buyer', $data, function($message) use($data) {
                        $message->to($data['email'])
                                ->from(cc('mail_from'))
                                ->subject("Welcome to Peppa");
                                //->attachData($pdf->output(), "TermsAndConditions.pdf");
                    });
                }else{
                    $seller_mail = Mail::send('mails.signupWelcome_seller', $data, function($message) use($data) {
                        $message->to($data['email'])
                                ->from(cc('mail_from'))
                                ->subject("Welcome to Peppa");
                                //->attachData($pdf->output(), "TermsAndConditions.pdf");
                    });  
                }
               
                

                //termsAndConditions
                return true;
            }
            
                    
            return false;
        }

        catch(Exception $e) {
          Logger::info('Email Error', [$e->getMessage().' - '. $e->__toString()]); 
          return false;
        }
       
    }

    //userInfo
    public function send_new_account_admin_email($user)
    {
        try{
            
            //[$cur, $ActualAmount, $description, $TxnRef]
            $data['name'] = $user->name;
            $data['email'] = $user->email;
            $data['account_type'] = $user->userInfo->usertype;
            $data['platform_country'] = $user->userInfo->country;
            $buyer_mail = Mail::send('mails.peppa.signupNotification', $data, function($message) use($data) {
                        $message->to(cc('admin_email_address'))
                                ->from(cc('mail_from'))
                                ->subject("Welcome to Peppa");
                });
            return true;
        }

        catch(Exception $e) {
          Logger::info('Email Error', [$e->getMessage().' - '. $e->__toString()]); 
          return false;
        }
       
    }

    public function send_welcome_email_bk($user)
    {
        try{
            
            //[$cur, $ActualAmount, $description, $TxnRef]
            $data['name'] = $user->name;
            $data['email'] = $user->email;
            $data['link'] = cc('frontend_base_url');
            $email = $user->email;
            if (!is_null($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                // Mail::to($email)
                // ->send(new SignupWelcomeMail($data));
                //$pdf = PDF::loadView('mails.termsAndConditions', $data);
                if ($user->userInfo->usertype == 'Buyer') {
                    $buyer_mail = Mail::send('mails.signupWelcome_bk1', $data, function($message) use($data) {
                        $message->to($data['email'])
                                ->from(cc('mail_from'))
                                ->subject("Welcome to Peppa Escrow");
                                //->attachData($pdf->output(), "TermsAndConditions.pdf");
                    });
                }else{
                    $seller_mail = Mail::send('mails.signupWelcome_bk1', $data, function($message) use($data) {
                        $message->to($data['email'])
                                ->from(cc('mail_from'))
                                ->subject("Welcome to Pepperest");
                                //->attachData($pdf->output(), "TermsAndConditions.pdf");
                    });  
                }
               
                

                //termsAndConditions
                return true;
            }
            
                    
            return false;
        }

        catch(Exception $e) {
          Logger::info('Email Error', [$e->getMessage().' - '. $e->__toString()]); 
          return false;
        }
       
    }



    public function send_welcome_email_with_password($user, $password)
    {
        try{
            
            //[$cur, $ActualAmount, $description, $TxnRef]
            $data['name'] = $user->name;
            $data['email'] = $user->email;
            $data['link'] = env('frontend_base_url');
            $data['password'] = $password;
            $email = $user->email;
            if (!is_null($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                // Mail::to($email)
                // ->send(new SignupWelcomeMail($data));
                //$pdf = PDF::loadView('mails.termsAndConditions', $data);
               
                $buyer_mail = Mail::send('mails.signupWelcomeWithPasword', $data, function($message) use($data) {
                    $message->to($data['email'])
                            ->from(cc('mail_from'))
                            ->subject("Welcome to Peppa");
                            //->attachData($pdf->output(), "TermsAndConditions.pdf");
                });

                //termsAndConditions
                return true;
            }
            
                    
            return false;
        }

        //catch exception
        catch(Exception $e) {
          Logger::info('Email Error', [$e->getMessage().' - '. $e->__toString()]); 
          return false;
        }
       
    }

    public function send_welcome_email_with_otp($user, $otp)
    {
        try{
            
            //[$cur, $ActualAmount, $description, $TxnRef]
            $data['name'] = $user->firstName;
            $data['email'] = $user->email;
            $data['link'] = env('frontend_base_url');
            $data['otp'] = $otp;
            $email = $user->email;
            if (!is_null($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                // Mail::to($email)
                // ->send(new SignupWelcomeMail($data));
                //$pdf = PDF::loadView('mails.termsAndConditions', $data);
               
                $buyer_mail = Mail::send('mails.peppa.signupWelcomeWithOTP', $data, function($message) use($data) {
                    $message->to($data['email'])
                            ->from(cc('mail_from'))
                            ->subject("Welcome to Peppa");
                            //->attachData($pdf->output(), "TermsAndConditions.pdf");
                });

                //termsAndConditions
                return true;
            }
            
                    
            return false;
        }

        //catch exception
        catch(Exception $e) {
          Logger::info('Email Error', [$e->getMessage().' - '. $e->__toString()]); 
          return false;
        }
       
    }


    public function send_account_activation_email_otp($user, $otp)
    {
        try{
            
            //[$cur, $ActualAmount, $description, $TxnRef]
            $data['name'] = $user->firstName;
            $data['email'] = $user->email;
            $data['link'] = env('frontend_base_url');
            $data['otp'] = $otp;
            $email = $user->email;
            if (!is_null($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                // Mail::to($email)
                // ->send(new SignupWelcomeMail($data));
                //$pdf = PDF::loadView('mails.termsAndConditions', $data);
               
                $buyer_mail = Mail::send('mails.peppa.ActivateAccountWithOTP', $data, function($message) use($data) {
                    $message->to($data['email'])
                            ->from(cc('mail_from'))
                            ->subject("Account Activation on Peppa");
                            //->attachData($pdf->output(), "TermsAndConditions.pdf");
                });

                //termsAndConditions
                return true;
            }
            
                    
            return false;
        }

        //catch exception
        catch(Exception $e) {
          Logger::info('Email Error', [$e->getMessage().' - '. $e->__toString()]); 
          return false;
        }
       
    }

    
    public function send_password_reset_email_otp($user, $otp)
    {
        try{
            
            //[$cur, $ActualAmount, $description, $TxnRef]
            $data['name'] = $user->firstName;
            $data['email'] = $user->email;
            $data['link'] = env('frontend_base_url');
            $data['otp'] = $otp;
            $email = $user->email;
            if (!is_null($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                // Mail::to($email)
                // ->send(new SignupWelcomeMail($data));
                //$pdf = PDF::loadView('mails.termsAndConditions', $data);
               
                $buyer_mail = Mail::send('mails.peppa.PasswordResetWithOTP', $data, function($message) use($data) {
                    $message->to($data['email'])
                            ->from(cc('mail_from'))
                            ->subject("Account Password Reset on Peppa");
                            //->attachData($pdf->output(), "TermsAndConditions.pdf");
                });

                //termsAndConditions
                return true;
            }
            
                    
            return false;
        }

        //catch exception
        catch(Exception $e) {
          Logger::info('Email Error', [$e->getMessage().' - '. $e->__toString()]); 
          return false;
        }
       
    }


    public function send_password_reset($userObj, $token)
    {
      
        $data['name'] = $userObj->name;
        $email = $userObj->email;
        
        $data['reset_link'] = env('frontend_base_url') . "reset_password/token?token=$token&email=$email";
    
        if (Mail::to($email)->send(new PasswordResetMail($data))) {
            return true;
        }
        return false;
       
    }

    public function send_fx_password_reset($userObj, $token)
    {

        $data['name'] = $userObj->name;
        $email = $userObj->email;

        $data['reset_link'] = env('fx_frontend_base_url') . "reset-password?token=$token";

        if (Mail::to($email)->send(new PasswordResetMail($data))) {
            return true;
        }
        return false;
    }

    public function send_password_reset_token($userObj, $token)
    {
      
        $data['name'] = $userObj->name;
        $email = $userObj->email;
        $data['token'] = $token;
    
        if (Mail::to($email)->send(new PasswordResetMail_token($data))) {
            return true;
        }
        return false;
       
    }

    
    public function send_create_order_email($order, $buyer){
      try{  
        $data['description'] = $order->description;
        $data['cost'] = number_format($order->cost,2);
        $data['currency'] = $order->currency;
        $data['orderRef'] = $order->orderRef;
        $data['seller_email'] = $order->seller_email;
        $data['buyer_email'] = $buyer->email;
        $data['order_link'] = cc('frontend_base_url') .'app/orders?id='. $order->id;
        $data['seller_name'] = $order->sellerDetails->name;
        $data['buyer_name'] = $order->buyerDetails->name;
        
        $buyer_mail = false;
        $seller_mail = false;
        if (filter_var($data['seller_email'], FILTER_VALIDATE_EMAIL)) {
          $seller_mail = Mail::to($data['seller_email'])->send(new NewOrderMail_Seller($data));
        }

        if (filter_var($data['buyer_email'], FILTER_VALIDATE_EMAIL)) {
          $buyer_mail = Mail::to($data['buyer_email'])->send(new NewOrderMail_Buyer($data));
        }
        

        if ($seller_mail && $buyer_mail) {
            return true;
        }


        return false;

      } catch (Exception $e) {
        return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'],500);
      }

    }

    
    public function send_create_order_tranx_email($order){
      try{  
        $data['description'] = $order->description;
        $data['cost'] = number_format($order->cost,2);
        $data['currency'] = $order->currency;
        $data['orderRef'] = $order->orderRef;
        $orderRef = $order->orderRef;
        $data['seller_email'] = $order->seller_email;
        $data['seller_phone'] = $order->seller_phone;
        $data['buyer_email'] = !is_null($order->buyerDetails) ? $order->buyerDetails->email : "";
        $data['order_link'] = cc('frontend_base_url') .'vendor/incoming-payment?orderRef='. $order->orderRef;
        $data['seller_name'] = !is_null($order->sellerDetails) ? $order->sellerDetails->name : "";
        $data['seller_fname'] = !is_null($order->sellerDetails) ? $order->sellerDetails->firstName : "";
        $data['buyer_name'] = !is_null($order->buyerDetails) ? $order->buyerDetails->name : "";
        $data['buyer_fname'] =!is_null($order->buyerDetails) ?  $order->buyerDetails->firstName : "";
        $data['buyer_phone'] = !is_null($order->buyerDetails) ? $order->buyerDetails->phoneNo : "";
        $data['order_date'] = Carbon::parse($order->created_at)->format('d/m/Y H:i');
        if ($order->transaction_type == "Invoice") {
            $data['order_link'] = cc('frontend_base_url') .'dashboard/invoices/detail/'. $order->id;
        }
        
        $buyer_mail = false;
        $seller_mail = false;
        if (filter_var($data['seller_email'], FILTER_VALIDATE_EMAIL)) {
            $seller_mail = Mail::send('mails.peppa.newOrderTranx_Seller', $data, function($message) use($data) {
                    $orderRef = $data['orderRef'];
                    $buyer_name = $data['buyer_name'];
                    $message->to($data['seller_email'])
                            ->from(cc('mail_from'))
                            ->subject("$buyer_name just paid you using Peppa");
                            //->attachData($pdf->output(), "TermsAndConditions.pdf");
                });
        }

        if (filter_var($data['buyer_email'], FILTER_VALIDATE_EMAIL)) {
          $buyer_mail = Mail::send('mails.peppa.newOrderTranx_Buyer', $data, function($message) use($data) {
                    $orderRef = $data['orderRef'];
                    $seller_name = $data['seller_name'];
                    $message->to($data['buyer_email'])
                            ->from(cc('mail_from'))
                            ->subject("You just paid $seller_name using Peppa");
                            //->attachData($pdf->output(), "TermsAndConditions.pdf");
                });
        }
        

        if ($seller_mail && $buyer_mail) {
            return true;
        }


        return false;

      } catch (Exception $e) {
        Logger::info('Email Error', [$e->getMessage().' - '. $e->__toString()]); 
        return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'],500);
      }

    }

     public function send_order_sales_contract_email($order, $initiator){
      try{
       $data['description'] = $order->description;
        $data['cost'] = number_format($order->cost,2);
        $data['currency'] = $order->currency;
        $data['orderRef'] = $order->orderRef;
        $data['seller_email'] = $order->seller_email;
        $data['buyer_email'] = $order->buyer->email;
        $tranxStatusArray = cc('transaction.status');
        $data['tranxStatus'] = isset($tranxStatusArray[$order->status]) ? $tranxStatusArray[$order->status] : 'Undefined';
        $data['initiator_name'] = $initiator->name;
        $data['initiator_email'] = $initiator->email;
        $data['order'] = $order;
        $data['date_time'] = Carbon::now()->format('M d, Y');
        //$data['order_link'] = cc('frontend_base_url') .'/app/orders?id='. $order->id;
        
        $pdf = PDF::loadView('mails.salesContractPDF', $data);
        // return $pdf->download('invoice.pdf');
        $buyer_mail = false;
        if (filter_var($data['buyer_email'], FILTER_VALIDATE_EMAIL)) {
          $buyer_mail = Mail::send('mails.salesContract', $data, function($message) use($data, $pdf) {
            $message->to([$data['seller_email'],$data['buyer_email']])
                    ->from(cc('mail_from'))
                    ->subject("Order Sales Contract on Peppa - ".$data['orderRef'])
                    ->attachData($pdf->output(), "Sales_Contract.pdf");
           });
        }
        
        // $seller_mail = Mail::to($data['seller_email'])->send(new OrderStatusChangeMail($data));
        // $buyer_mail = Mail::to($data['buyer_email'])->send(new OrderStatusChangeMail($data));

        if ($buyer_mail) {
            return true;
        }


        return false;
      } catch (Exception $e) {
        return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'],500);
      }
    }

    

    public function send_new_placed_order_email($order){
        try{
            
            $data['orderAddress'] = $order->orderAddress;
            $data['pickupAddress'] = $order->pickupAddress;
            $data['deliveryCompany'] = !is_null($order->orderLogistics) ? $order->orderLogistics->courier_id : "";
            $data['deliveryStatus'] = !is_null($order->orderLogistics) ? $order->orderLogistics->delivery_status : "";
            $data['trackingUrl'] = !is_null($order->orderLogistics) ? $order->orderLogistics->tracking_url : "";
            $data['description'] = $order->orderRef;
            $data['cost'] = number_format($order->total,2);
            $data['totalprice'] = number_format($order->totalprice,2);
            $data['shipping'] = number_format($order->shipping,2);
            $data['pepfee'] = number_format($order->pepperestfees,2);
            $data['sum_total'] = number_format($order->pepperestfees + $order->totalprice + $order->shipping,2);
            $data['currency'] = $order->currency;
            $data['orderRef'] = $order->orderRef;
            $data['delivery_type'] = $order->delivery_type;
            $data['orderItems'] = $order->orderItems;
            $data['estimated_days'] = !is_null($order->orderLogistics) ? $order->orderLogistics->estimated_days : null;
            $data['seller_email'] = !is_null($order->seller) ? $order->seller->email : null;
            $data['buyer_email'] = !is_null($order->buyer) ? $order->buyer->email : null;
            $tranxStatusArray = cc('transaction.status');
            $data['tranxStatus'] = isset($tranxStatusArray[$order->status]) ? $tranxStatusArray[$order->status] : 'Undefined';
            $data['order_link'] = env('frontend_base_url') .'orderDetails?orderRef='. $order->orderRef;
            $data['seller_name'] = !is_null($order->seller) ? $order->seller->name : null;
            $buyer_obj = $order->buyer;
            $data['buyer_name'] = !is_null($buyer_obj) ? $buyer_obj->name : null;
            $recipient_addr = $order->orderAddress;
            if (!is_null($recipient_addr)) {
                $data['recipient_address'] = $recipient_addr->street_1.', '.$recipient_addr->city.', '.$recipient_addr->state.', '.$recipient_addr->country;
                $data['recipient_phone'] = $recipient_addr->phone;
                $data['recipient_name'] = $recipient_addr->name;
            }else{
                $data['recipient_address'] = '';
                $data['recipient_phone'] = $buyer_obj->phone;
                $data['recipient_name'] = $buyer_obj->name;
            }
            
            $buyer_mail = false;
            $seller_mail = false;
            if (filter_var($data['seller_email'], FILTER_VALIDATE_EMAIL)) {
              $seller_mail = Mail::to($data['seller_email'])
              ->cc(cc('admin_email_address'))
              ->send(new NewOrderMail_Seller($data));
            }

            if (filter_var($data['buyer_email'], FILTER_VALIDATE_EMAIL)) {
              $buyer_mail = Mail::to($data['buyer_email'])->send(new NewOrderMail_Buyer($data));
            }

            $seller_msg = "A new order has been placed from your Peppa store, Ref: " . $order->orderRef;
            if (!is_null($order->seller->phone)) {
               $this->sendSMS($order->seller->phone, $seller_msg);
            }

            if ($seller_mail && $buyer_mail) {
                return true;
            }


            return false;
        }

        catch(Exception $e) {
          Logger::info('Email Error', [$e->getMessage().' - '. $e->__toString()]); 
          return false;
        }

    }

    public function send_placed_order_status_change_email($order, $initiator){
        try{
            $data['orderAddress'] = $order->orderAddress;
            $data['deliveryCompany'] = !is_null($order->orderLogistics) ? $order->orderLogistics->courier_id : "";
            $data['deliveryStatus'] = !is_null($order->orderLogistics) ? $order->orderLogistics->delivery_status : "";
            $data['trackingUrl'] = !is_null($order->orderLogistics) ? $order->orderLogistics->tracking_url : "";
            $data['description'] = $order->orderRef;
            $data['cost'] = number_format($order->total,2);
            $data['totalprice'] = number_format($order->totalprice,2);
            $data['shipping'] = number_format($order->shipping,2);
            $data['pepfee'] = number_format($order->pepperestfees,2);
            $data['sum_total'] = number_format($order->pepperestfees + $order->totalprice + $order->shipping,2);
            
            $data['currency'] = $order->currency;
            $data['orderRef'] = $order->orderRef;
            $data['orderItems'] = $order->orderItems;
            $data['estimated_days'] = !is_null($order->orderLogistics) ? $order->orderLogistics->estimated_days : null;
            $data['seller_email'] = !is_null($order->seller) ? $order->seller->email : null;
            $data['buyer_email'] = !is_null($order->buyer) ? $order->buyer->email : null;
            $tranxStatusArray = cc('transaction.status');
            $data['tranxStatus'] = isset($tranxStatusArray[$order->status]) ? $tranxStatusArray[$order->status] : 'Undefined';
            $data['initiator_name'] = $initiator->name;
            $data['initiator_email'] = $initiator->email;
            $data['order_link'] = env('frontend_base_url') .'orderDetails?orderRef='. $order->orderRef;
            $data['seller_name'] = !is_null($order->seller) ? $order->seller->name : null;
            $buyer_obj = $order->buyer;
            $data['buyer_name'] = !is_null($buyer_obj) ? $buyer_obj->name : null;
            $recipient_addr = $order->orderAddress;
            if (!is_null($recipient_addr)) {
                $data['recipient_address'] = $recipient_addr->street_1.', '.$recipient_addr->city.', '.$recipient_addr->state.', '.$recipient_addr->country;
                $data['recipient_phone'] = $recipient_addr->phone;
                $data['recipient_name'] = $recipient_addr->name;
            }else{
                $data['recipient_address'] = '';
                $data['recipient_phone'] = $buyer_obj->phone;
                $data['recipient_name'] = $buyer_obj->name;
            }
            
            $buyer_mail = false;
            $seller_mail = false;
            if (filter_var($data['seller_email'], FILTER_VALIDATE_EMAIL)) {
              $seller_mail = Mail::to($data['seller_email'])
              ->cc(cc('admin_email_address'))
              ->send(new OrderStatusChangeMail_Seller($data));
            }

            if (filter_var($data['buyer_email'], FILTER_VALIDATE_EMAIL)) {
              $buyer_mail = Mail::to($data['buyer_email'])->send(new OrderStatusChangeMail($data));
            }

            $msg = "The status of your order with ref: "  . $order->orderRef ." has changed to " . $data['tranxStatus'] . " on Peppa store.";
            if (!is_null($order->seller->phone)) {
               $this->sendSMS($order->seller->phone, $msg);
            }

            if (!is_null($order->buyer->phone)) {
               $this->sendSMS($order->buyer->phone, $msg);
            }

            if ($seller_mail && $buyer_mail) {
                return true;
            }


            return false;
        }

        catch(Exception $e) {
          Logger::info('Email Error', [$e->getMessage().' - '. $e->__toString()]); 
          return false;
        }

    }

    public function send_placed_order_status_change_email_with_otp($order, $initiator){
        try{
            $data['description'] = $order->orderRef;
            $data['cost'] = number_format($order->total,2);
            $data['pepfee'] = number_format($order->pepperestfees,2);
            $data['currency'] = $order->currency;
            $data['orderRef'] = $order->orderRef;
            $data['orderItems'] = $order->orderItems;
            $data['estimated_days'] = !is_null($order->orderLogistics) ? $order->orderLogistics->estimated_days : null;
            $data['seller_email'] = !is_null($order->seller) ? $order->seller->email : null;
            $data['buyer_email'] = !is_null($order->buyer) ? $order->buyer->email : null;
            $data['orderOTP'] = $order->delivery_confirmation_pin;
            $tranxStatusArray = cc('transaction.status');
            $data['tranxStatus'] = isset($tranxStatusArray[$order->status]) ? $tranxStatusArray[$order->status] : 'Undefined';
            $data['initiator_name'] = $initiator->name;
            $data['initiator_email'] = $initiator->email;
            $data['order_link'] = env('frontend_base_url') .'orderDetails?orderRef='. $order->orderRef;
            $data['seller_name'] = !is_null($order->seller) ? $order->seller->name : null;
            $buyer_obj = $order->buyer;
            $data['buyer_name'] = !is_null($buyer_obj) ? $buyer_obj->name : null;
            $recipient_addr = $order->orderAddress;
            if (!is_null($recipient_addr)) {
                $data['recipient_address'] = $recipient_addr->street_1.', '.$recipient_addr->city.', '.$recipient_addr->state.', '.$recipient_addr->country;
                $data['recipient_phone'] = $recipient_addr->phone;
                $data['recipient_name'] = $recipient_addr->name;
            }else{
                $data['recipient_address'] = '';
                $data['recipient_phone'] = $buyer_obj->phone;
                $data['recipient_name'] = $buyer_obj->name;
            }
            
            $buyer_mail = false;
            $seller_mail = false;
            if (filter_var($data['seller_email'], FILTER_VALIDATE_EMAIL)) {
              $seller_mail = Mail::to($data['seller_email'])->send(new OrderStatusChangeMailWithOTP_Seller($data));
            }

            if (filter_var($data['buyer_email'], FILTER_VALIDATE_EMAIL)) {
              $buyer_mail = Mail::to($data['buyer_email'])->send(new OrderStatusChangeMailWithOTP($data));
            }

            $msg = "The status of your order with ref: "  . $order->orderRef ." has changed to " . $data['tranxStatus'] . " on Peppa store.";
            if (!is_null($order->seller->phone)) {
               $this->sendSMS($order->seller->phone, $msg);
            }

            if (!is_null($order->buyer->phone)) {
               $this->sendSMS($order->buyer->phone, $msg);
            }

            if ($seller_mail && $buyer_mail) {
                return true;
            }


            return false;
        }

        catch(Exception $e) {
          Logger::info('Email Error', [$e->getMessage().' - '. $e->__toString()]); 
          return false;
        }

    }


    public function send_order_delivery_status_change_email($order){
        try{
            $data['orderAddress'] = $order->orderAddress;
            $data['deliveryCompany'] = !is_null($order->orderLogistics) ? $order->orderLogistics->courier_id : "";
            $data['deliveryStatus'] = !is_null($order->orderLogistics) ? $order->orderLogistics->delivery_status : "";
            $data['trackingUrl'] = !is_null($order->orderLogistics) ? $order->orderLogistics->tracking_url : "";
            $data['description'] = $order->orderRef;
            $data['cost'] = number_format($order->total,2);
            $data['totalprice'] = number_format($order->totalprice,2);
            $data['shipping'] = number_format($order->shipping,2);
            $data['pepfee'] = number_format($order->pepperestfees,2);
            $data['sum_total'] = number_format($order->pepperestfees + $order->totalprice + $order->shipping,2);
            $data['currency'] = $order->currency;
            $data['orderRef'] = $order->orderRef;
            $data['orderItems'] = $order->orderItems;
            $data['estimated_days'] = !is_null($order->orderLogistics) ? $order->orderLogistics->estimated_days : null;
            $data['seller_email'] = !is_null($order->seller) ? $order->seller->email : null;
            $data['buyer_email'] = !is_null($order->buyer) ? $order->buyer->email : null;
            $tranxStatusArray = cc('transaction.status');
            $data['tranxStatus'] = isset($tranxStatusArray[$order->status]) ? $tranxStatusArray[$order->status] : 'Undefined';
            $data['initiator_name'] = 'ShipBubble';
            $data['initiator_email'] = 'ShipBubble';
            $data['order_link'] = env('frontend_base_url') .'orderDetails?orderRef='. $order->orderRef;
            $data['seller_name'] = !is_null($order->seller) ? $order->seller->name : null;
            $buyer_obj = $order->buyer;
            $data['buyer_name'] = !is_null($buyer_obj) ? $buyer_obj->name : null;
            $recipient_addr = $order->orderAddress;
            if (!is_null($recipient_addr)) {
                $data['recipient_address'] = $recipient_addr->street_1.', '.$recipient_addr->city.', '.$recipient_addr->state.', '.$recipient_addr->country;
                $data['recipient_phone'] = $recipient_addr->phone;
                $data['recipient_name'] = $recipient_addr->name;
            }else{
                $data['recipient_address'] = '';
                $data['recipient_phone'] = $buyer_obj->phone;
                $data['recipient_name'] = $buyer_obj->name;
            }
            
            $buyer_mail = false;
            $seller_mail = false;
            if (filter_var($data['seller_email'], FILTER_VALIDATE_EMAIL)) {
              $seller_mail = Mail::to($data['seller_email'])
              ->cc(cc('admin_email_address'))
              ->send(new OrderDeliveryStatusChangeMail_Seller($data));
            }

            if (filter_var($data['buyer_email'], FILTER_VALIDATE_EMAIL)) {
              $buyer_mail = Mail::to($data['buyer_email'])->send(new OrderDeliveryStatusChangeMail($data));
            }

            $msg = "The delivery status of your order with ref: "  . $order->orderRef ." has changed to " . $data['deliveryStatus'] . " on Peppa store.";
            if (!is_null($order->seller->phone)) {
               $this->sendSMS($order->seller->phone, $msg);
            }

            if (!is_null($order->buyer->phone)) {
               $this->sendSMS($order->buyer->phone, $msg);
            }

            if ($seller_mail && $buyer_mail) {
                return true;
            }


            return false;
        }

        catch(Exception $e) {
          Logger::info('Email Error', [$e->getMessage().' - '. $e->__toString()]); 
          return false;
        }

    }


    public function send_order_tranx_status_change_email($order, $initiator){
        try{
            $data['description'] = $order->description;
            $data['cost'] = number_format($order->cost,2);
            $data['currency'] = $order->currency;
            $data['orderRef'] = $order->orderRef;
            $data['seller_email'] = $order->seller_email;
            $data['buyer_email'] = $order->buyer->email;
            $tranxStatusArray = cc('transaction.status');
            $data['tranxStatus'] = isset($tranxStatusArray[$order->status]) ? $tranxStatusArray[$order->status] : 'Undefined';
            $data['initiator_name'] = $initiator->name;
            $data['initiator_email'] = $initiator->email;
            $data['order_link'] = cc('frontend_base_url') .'vendor/incoming-payment?orderRef='. $order->orderRef;
            $data['seller_name'] = $order->sellerDetails->name;
            $data['buyer_name'] = $order->buyerDetails->name;
            $data['seller_phone'] = $order->seller_phone;
            $data['buyer_phone'] = !is_null($order->buyerDetails) ? $order->buyerDetails->phoneNo : "";
            $data['order_date'] = Carbon::parse($order->created_at)->format('d/m/Y H:i');

            $buyer_mail = false;
            $seller_mail = false;
            if (filter_var($data['seller_email'], FILTER_VALIDATE_EMAIL)) {
              $send_mail_seller = Mail::to($data['seller_email'])->send(new OrderTranxStatusChangeMail_Seller($data));
            }

            if (filter_var($data['buyer_email'], FILTER_VALIDATE_EMAIL)) {
              $buyer_mail = Mail::to($data['buyer_email'])->send(new OrderTranxStatusChangeMail($data));
            }

            if ($seller_mail && $buyer_mail) {
                return true;
            }


            return false;
        }

        catch(Exception $e) {
          Logger::info('Email Error', [$e->getMessage().' - '. $e->__toString()]); 
          return false;
        }

    }



    public function send_create_invoice_email($invoice, $initiator){
        
        $data['description'] = 'Peppa Invoice';
        $data['cost'] = $invoice->totalcost;
        $data['currency'] = !is_null($invoice->currency) ? $invoice->currency : 'NGN';
        $data['orderRef'] = $invoice->invoiceRef;
        $data['seller_email'] = $invoice->merchantEmail;
        $data['buyer_email'] = $invoice->customerEmail;
        $data['invoice_link'] = cc('frontend_base_url') .'confirm-invoice/'. $invoice->id;
        //$data['token'] = $token;
        
        $seller_mail = Mail::to($data['seller_email'])->send(new NewInvoiceMail_Seller($data));
        $buyer_mail = Mail::to($data['buyer_email'])->send(new NewInvoiceMail_Buyer($data));

        if ($seller_mail && $buyer_mail) {
            return true;
        }


        return false;

    }


    public function send_invoice_status_change_email($invoice, $initiator){
       

        $data['description'] = 'Peppa Invoice';
        $data['cost'] = $invoice->totalcost;
        $data['currency'] = !is_null($invoice->currency) ? $invoice->currency : 'NGN';
        $data['orderRef'] = $invoice->invoiceRef;
        $data['seller_email'] = $invoice->merchantEmail;
        $data['buyer_email'] = $invoice->customerEmail;
        $tranxStatusArray = cc('transaction.status');
        $data['tranxStatus'] = isset($tranxStatusArray[$invoice->status]) ? $tranxStatusArray[$invoice->status] : 'Undefined';
        $data['initiator_name'] = $initiator->name;
        $data['initiator_email'] = $initiator->email;
        $data['invoice_link'] = cc('frontend_base_url') .'confirm-invoice/'. $invoice->id;
        //$data['token'] = $token;
        
        $seller_mail = Mail::to($data['seller_email'])->send(new InvoiceStatusChangeMail($data));
        $buyer_mail = Mail::to($data['buyer_email'])->send(new InvoiceStatusChangeMail($data));

        if ($seller_mail && $buyer_mail) {
            return true;
        }


        return false;

    }


    public function audit_log($log_json, $initiator, $subject)
    {
        return ;
    }

    public function sendVerificationEmail($user)
    {
        $data['user'] = $user;
        Mail::to($user->email)->send(new EmailVerification($data));
    }

    public function sendFXVerificationEmail($user)
    {
        $data['user'] = $user;
        Mail::to($user->email)->send(new FXEmailVerification($data));
    }

    public function sendAppNotification($user, $subject, $message)
    {
        $payload = [
            "to" => $user->fcm_token,
            "collapse_key" => "New Message",
            "priority" => "high",
            "notification" => [
                "title" => $subject,
                "body" => $message
            ]
        ];
        $key = env("fcm_key"); 
        $url = env("fcm_base_url");
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => json_encode($payload, true),
          CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Accept: application/json",
            "Authorization: key=$key"
          ],
        ));

        $response = curl_exec($curl);
        $err = curl_errno($curl);
        
        if($err){
          $error_msg = curl_error($curl);
          curl_close($curl);
          return ['error' => 1, 'statusCode' => 500, 'responseMessage' => $error_msg, 'Detail' => $error_msg];
        }
        curl_close($curl);

        $transaction = json_decode($response);
        Logger::info('FCM_TOKEN Notification Response', [$transaction]);
        return [$response, $transaction];
        // if ($transaction->responseCode == "00") {
        //   return ['error' => 0, 'statusCode' => $transaction->responseCode, 'responseMessage' => $transaction->responseMessage, 'responseDetails' => $transaction, 'bankCode' => 101];
        // }else{
        //   return ['error' => 1, 'statusCode' => $transaction->responseCode, 'responseMessage' => $transaction->responseMessage];
        // }
    }

    public function send_request_payment_email($tranx)
    {
        try {
            $data['description'] = $tranx->description;
            $data['cost'] = number_format($tranx->cost, 2);
            $data['currency'] = $tranx->currency;
            $data['orderRef'] = $tranx->orderRef;
            $data['seller_email'] = $tranx->seller_email;
            $data['seller_phone'] = $tranx->seller_phone;
            $data['buyer_email'] = $tranx->buyerDetails->email;
            $data['dispute_link'] = cc('frontend_base_url') . '/open-dispute?orderRef=' . $tranx->orderRef;
            $data['seller_name'] = $tranx->sellerDetails->name;
            $data['seller_fname'] = $tranx->sellerDetails->firstName;
            $data['buyer_name'] = $tranx->buyerDetails->name;
            $data['buyer_fname'] = $tranx->buyerDetails->firstName;
            $data['buyer_phone'] = $tranx->buyerDetails->phoneNo;
            $data['order_date'] = Carbon::parse($tranx->created_at)->format('d/m/Y H:i');

            $buyer_mail = false;
            $seller_mail = false;
            if (filter_var($data['seller_email'], FILTER_VALIDATE_EMAIL)) {
                $seller_mail = Mail::send('mails.peppa.requestPaymentSeller', $data, function ($message) use ($data) {
                    $orderRef = $data['orderRef'];
                    $buyer_name = $data['buyer_name'];
                    $message->to($data['seller_email'])
                    ->from(cc('mail_from'))
                    ->subject("You just requested payment from $buyer_name using Peppa");
                });
            }

            if (filter_var($data['buyer_email'], FILTER_VALIDATE_EMAIL)) {
                $buyer_mail = Mail::send('mails.peppa.requestPaymentBuyer', $data, function ($message) use ($data) {
                    $orderRef = $data['orderRef'];
                    $seller_name = $data['seller_name'];
                    $message->to($data['buyer_email'])
                    ->from(cc('mail_from'))
                    ->subject("You just received a payment request from $seller_name using Peppa");
                });
            }

            if ($seller_mail && $buyer_mail) {
                return true;
            }

            return false;
        } catch (Exception $e) {
            Logger::info('requestPayment Email Error', [$e->getMessage() . ' - ' . $e->__toString()]);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function send_refund_request_email($refund)
    {
        try {
            $data['refundID'] = $refund->id;
            $data['buyer_name'] = $refund->buyer->name;
            $data['merchant_name'] = $refund->merchant->name;
            $data['refund_link'] = cc('admin_base_url') . '/refundRequests';

            $admins = explode(',', env('admin_emails'));
            // Loop through each admin and send the email
            foreach ($admins as $admin) {
                if (filter_var($admin, FILTER_VALIDATE_EMAIL)) {
                    $admin_mail = Mail::send('mails.peppa.refundRequestAdmin', $data, function ($message) use ($data, $admin) {
                        $buyer_name = $data['buyer_name'];
                        $message->to($admin)
                            ->from(cc('mail_from'))
                            ->subject("$buyer_name just requested for a refund");
                    });
                }
            }

            return true;
        } catch (Exception $e) {
            Logger::info('refundRequest Email Error', [$e->getMessage() . ' - ' . $e->__toString()]);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function send_withdrawal_email($withdrawal)
    {
        try {
            $data['withdrawalID'] = $withdrawal->id;
            $data['withdrawer_fname'] = $withdrawal->user->firstName;
            $data['account_number'] = $withdrawal->account_number;
            $data['account_name'] = $withdrawal->account_name;
            $data['bank_name'] = $withdrawal->bank_name;
            $data['withdrawer_email'] = $withdrawal->user->email;
            $data['amount_requested'] = $withdrawal->amount_requested;
            $data['amount_paid'] = $withdrawal->amount;
            $data['withdrawal_fee'] = $withdrawal->fee;
            $data['wallet_balance'] = $withdrawal->wallet->amount;
            $data['wallet_currency'] = $withdrawal->wallet->currency;
            $data['tranxRef'] = $withdrawal->transferRef;
            $data['withdrawal_date'] = Carbon::parse($withdrawal->created_at)->format('d/m/Y H:i');

            $owner_mail = false;
            if (filter_var($data['withdrawer_email'], FILTER_VALIDATE_EMAIL)) {
                $owner_mail = Mail::send('mails.peppa.walletWithdrawal', $data, function ($message) use ($data) {
                    $amount = $data['wallet_currency'] . $data['amount_requested'];
                    $message->to($data['withdrawer_email'])
                    ->from(cc('mail_from'))
                    ->subject("Your wallet has just been debited with $amount");
                });
            }

            if ($owner_mail) {
                return true;
            }

            return false;
        } catch (Exception $e) {
            Logger::info('withdrawal Email Error', [$e->getMessage() . ' - ' . $e->__toString()]);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }


    public function send_wallet_credit_email($walletTranx, $wallet)
    {
        try {
            
            $data['amount_paid'] = $walletTranx->amount;
            $data['owner_mail'] = $wallet->user->email;
            $data['owner_fname'] = $wallet->user->firstName;
            $data['wallet_balance'] = $wallet->amount;
            $data['wallet_currency'] = $wallet->currency;
            $data['tranxRef'] = $walletTranx->transaction_ref;
            $data['from'] = $walletTranx->from_account_name;
            $data['narration'] = $walletTranx->narration;
            $data['credit_date'] = Carbon::parse($walletTranx->created_at)->format('d/m/Y H:i');

            $owner_mail = false;
            if (filter_var($data['owner_mail'], FILTER_VALIDATE_EMAIL)) {
                $owner_mail = Mail::send('mails.peppa.walletCredit', $data, function ($message) use ($data) {
                    $amount = $data['wallet_currency'] . $data['amount_paid'];
                    $message->to($data['owner_mail'])
                    ->from(cc('mail_from'))
                    ->subject("Your wallet has just been credited with $amount");
                });
            }

            if ($owner_mail) {
                return true;
            }

            return false;
        } catch (Exception $e) {
            Logger::info('Credit Email Error', [$e->getMessage() . ' - ' . $e->__toString()]);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function send_vpd_contact_us_email($name, $email, $subject, $content)
    {
        try {
            $data['name'] = $name;
            $data['subject'] = $subject;
            $data['content'] = $content;
            $data['email'] = $email;

            $support_mail = env('vpd_support_mail');

            if (filter_var($support_mail, FILTER_VALIDATE_EMAIL)) {
                $mail = Mail::send('mails.peppa.vpdContactUs', $data, function ($message) use ($data, $support_mail) {
                    $message->to($support_mail)
                        ->from(cc('mail_from'))
                        ->subject("Customer support request - " . $data['subject']);
                });

                return true;
            }
            return false;
        } catch (Exception $e) {
            Logger::info('VPD Contact Us Email Error', [$e->getMessage() . ' - ' . $e->__toString()]);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function send_fx_contact_us_email($merchant, $name, $email, $subject, $content)
    {
        try {
            $data['name'] = $name;
            $data['subject'] = $subject;
            $data['content'] = $content;
            $data['email'] = $email;

            $recipient = $merchant->email;

            if (filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                $mail = Mail::send('mails.peppa.FXContactUs', $data, function ($message) use ($data, $recipient) {
                    $message->to($recipient)
                        ->from(cc('mail_from'))
                        ->subject("Customer support request - " . $data['subject']);
                });

                return true;
            }
            return false;
        } catch (Exception $e) {
            Logger::info('FX Contact Us Email Error', [$e->getMessage() . ' - ' . $e->__toString()]);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function send_wallet_charge_email($walletTranx, $wallet)
    {
        try {
            $data['amount_charged'] = $walletTranx->amount;
            $data['owner_mail'] = $wallet->user->email;
            $data['owner_fname'] = $wallet->user->firstName;
            $data['wallet_balance'] = $wallet->amount;
            $data['wallet_currency'] = $wallet->currency;
            $data['tranxRef'] = $walletTranx->transaction_ref;
            $data['narration'] = $walletTranx->narration;
            $data['charge_date'] = Carbon::parse($walletTranx->created_at)->format('d/m/Y H:i');

            $owner_mail = false;
            if (filter_var($data['owner_mail'], FILTER_VALIDATE_EMAIL)) {
                $owner_mail = Mail::send('mails.peppa.walletCharge', $data, function ($message) use ($data) {
                    $amount = $data['wallet_currency'] . $data['amount_charged'];
                    $message->to($data['owner_mail'])
                    ->from(cc('mail_from'))
                    ->subject("Your wallet has just been debited with $amount");
                });
            }

            if ($owner_mail) {
                return true;
            }

            return false;
        } catch (Exception $e) {
            Logger::info('Wallet Charge Email Error', [$e->getMessage() . ' - ' . $e->__toString()]);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function send_fx_connect_domain_email($customer)
    {
        try {
            $data['merchant_name'] = $customer->name;
            $data['merchant_email'] = $customer->email;
            $data['store_name'] = $customer->businessname;
            $data['domain'] = $customer->custom_url;

            $recipient = env('integration_email');

            if (filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                $mail = Mail::send('mails.peppa.FXConnectDomain', $data, function ($message) use ($recipient) {
                    $message->to($recipient)
                        ->from(cc('mail_from'))
                        ->subject("Domain Connection Request");
                });

                return true;
            }
            return false;
        } catch (Exception $e) {
            Logger::info('FX Connect Domain Email Error', [$e->getMessage() . ' - ' . $e->__toString()]);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function send_order_canceled_email($order, $initiator)
    {
        try {
            $data['description'] = $order->orderRef;
            $data['cost'] = number_format($order->total, 2);
            $data['totalprice'] = number_format($order->totalprice, 2);
            $data['shipping'] = number_format($order->shipping, 2);
            $data['pepfee'] = number_format($order->pepperestfees, 2);
            $data['sum_total'] = number_format($order->pepperestfees + $order->totalprice + $order->shipping, 2);
            $data['currency'] = $order->currency;
            $data['orderRef'] = $order->orderRef;
            $data['orderItems'] = $order->orderItems;
            $data['seller_name'] = !is_null($order->seller) ? $order->seller->name : null;
            $data['seller_email'] = !is_null($order->seller) ? $order->seller->email : null;
            $data['buyer_name'] = !is_null($order->buyer) ? $order->buyer->name : null;
            $data['buyer_email'] = !is_null($order->buyer) ? $order->buyer->email : null;
            $data['canceledBy'] = $initiator->userInfo->id == $order->buyer_id ? 'buyer' : 'vendor';
            $data['initiator_name'] = $initiator->name;
            $data['initiator_email'] = $initiator->email;
            $data['order_link'] = env('frontend_base_url') . 'orderDetails?orderRef=' . $order->orderRef;

            $buyer_mail = false;
            $seller_mail = false;
            if (filter_var($data['seller_email'], FILTER_VALIDATE_EMAIL)) {
                $data['name'] = $data['seller_name'];
                $seller_mail = Mail::to($data['seller_email'])->cc(cc('admin_email_address'))->send(new OrderCanceled($data));
            }

            if (filter_var($data['buyer_email'], FILTER_VALIDATE_EMAIL)) {
                $data['name'] = $data['buyer_name'];
                $buyer_mail = Mail::to($data['buyer_email'])->send(new OrderCanceled($data));
            }

            $msg = "Your order with ref: "  . $order->orderRef . " has been canceled on Peppa store.";
            if (!is_null($order->seller->phone)) {
                $this->sendSMS($order->seller->phone, $msg);
            }

            if (!is_null($order->buyer->phone)) {
                $this->sendSMS($order->buyer->phone, $msg);
            }

            if ($seller_mail && $buyer_mail) {
                return true;
            }

            return false;
        } catch (Exception $e) {
            Logger::info('Order Cancel Email Error', [$e->getMessage() . ' - ' . $e->__toString()]);
            return false;
        }
    }

    public function send_wallet_otp($user, $otp)
    {
        try {
            $data['name'] = $user->firstName;
            $data['email'] = $user->email;
            $data['link'] = env('frontend_base_url');
            $data['otp'] = $otp;
            $email = $user->email;
            if (!is_null($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $otp_mail = Mail::send('mails.peppa.WalletOTP', $data, function ($message) use ($data) {
                    $message->to($data['email'])
                    ->from(cc('mail_from'))
                    ->subject("Wallet OTP Authentication on Peppa");
                });
                return true;
            }
            return false;
        } catch (Exception $e) {
            Logger::info('Wallet OTP Email Error', [$e->getMessage() . ' - ' . $e->__toString()]);
            return false;
        }
    }

    public function send_booking_confirmation_email($booking)
    {
        try {

            $data['customerAddress'] = $booking->customerAddress;
            $data['vendorAddress'] = $booking->vendorAddress;
            $data['service'] = $booking->service;
            $data['cost'] = number_format($booking->total, 2);
            $data['booking_price'] = number_format($booking->booking_price, 2);
            $data['home_service_fee'] = number_format($booking->home_service_fee, 2);
            $data['pepperest_fee'] = number_format($booking->pepperest_fee, 2);
            $data['total'] = number_format($booking->pepperest_fee + $booking->booking_price + $booking->home_service_fee, 2);
            $data['currency'] = $booking->currency;
            $data['bookingRef'] = $booking->booking_ref;
            $data['delivery_type'] = $booking->delivery_type;
            $data['payment_type'] = $booking->payment_type;
            $data['booked_date'] = $booking->booked_date;
            $data['booked_time'] = $booking->booked_time;
            $data['booked_at'] = Carbon::parse($booking->created_at)->format('d/m/Y H:i');
            $data['seller_name'] = !is_null($booking->seller) ? $booking->seller->name : null;
            $data['seller_email'] = !is_null($booking->seller) ? $booking->seller->email : null;
            $data['seller_phone'] = !is_null($booking->seller) ? $booking->seller->phone : null;
            $data['buyer_email'] = !is_null($booking->buyer) ? $booking->buyer->email : null;
            $tranxStatusArray = cc('transaction.status');
            $data['payment_status'] = isset($tranxStatusArray[$booking->status]) ? $tranxStatusArray[$booking->status] : 'Undefined';
            $data['booking_link'] = env('frontend_base_url') . 'bookingDetails?bookingRef=' . $booking->booking_ref;
            $buyer_obj = $booking->buyer;
            $data['buyer_name'] = !is_null($buyer_obj) ? $buyer_obj->name : null;
            $customer_addr = $booking->customerAddress;
            if (!is_null($customer_addr)) {
                $data['customer_address'] = $customer_addr->street_1 . ', ' . $customer_addr->city . ', ' . $customer_addr->state . ', ' . $customer_addr->country;
                $data['customer_phone'] = $customer_addr->phone;
                $data['customer_name'] = $customer_addr->name;
            } else {
                $data['customer_address'] = '';
                $data['customer_phone'] = $buyer_obj->phone;
                $data['customer_name'] = $buyer_obj->name;
            }

            $buyer_mail = false;
            $seller_mail = false;
            if (filter_var($data['seller_email'], FILTER_VALIDATE_EMAIL)) {
                $seller_mail = Mail::to($data['seller_email'])->cc(cc('admin_email_address'))->send(new BookingMail_Seller($data));
            }

            if (filter_var($data['buyer_email'], FILTER_VALIDATE_EMAIL)) {
                $buyer_mail = Mail::to($data['buyer_email'])->send(new BookingMail_Buyer($data));
            }

            $seller_msg = "A new booking has been placed from your Peppa store, Ref: " . $booking->booking_ref;
            if (!is_null($booking->seller->phone)) {
                $this->sendSMS($booking->seller->phone, $seller_msg);
            }

            if ($seller_mail && $buyer_mail) {
                return true;
            }

            return false;
        } catch (Exception $e) {
            Logger::info('Booking Confirmation Email Error', [$e->getMessage() . ' - ' . $e->__toString()]);
            return false;
        }
    }

    public function send_fx_new_placed_order_email($order)
    {
        try {

            $data['orderAddress'] = $order->orderAddress;
            $data['pickupAddress'] = $order->pickupAddress;
            $data['deliveryCompany'] = !is_null($order->orderLogistics) ? $order->orderLogistics->courier_id : "";
            $data['deliveryStatus'] = !is_null($order->orderLogistics) ? $order->orderLogistics->delivery_status : "";
            $data['trackingUrl'] = !is_null($order->orderLogistics) ? $order->orderLogistics->tracking_url : "";
            $data['description'] = $order->orderRef;
            $data['cost'] = number_format($order->total, 2);
            $data['totalprice'] = number_format($order->totalprice, 2);
            $data['shipping'] = number_format($order->shipping, 2);
            $data['pepfee'] = number_format($order->pepperestfees, 2);
            $data['sum_total'] = number_format($order->pepperestfees + $order->totalprice + $order->shipping, 2);
            $data['currency'] = $order->currency;
            $data['orderRef'] = $order->orderRef;
            $data['delivery_type'] = $order->delivery_type;
            $data['orderItems'] = $order->orderItems;
            $data['estimated_days'] = !is_null($order->orderLogistics) ? $order->orderLogistics->estimated_days : null;
            $data['seller_email'] = !is_null($order->seller) ? $order->seller->email : null;
            $data['buyer_email'] = !is_null($order->buyer) ? $order->buyer->email : null;
            $tranxStatusArray = cc('transaction.status');
            $data['tranxStatus'] = isset($tranxStatusArray[$order->status]) ? $tranxStatusArray[$order->status] : 'Undefined';
            $data['order_link'] = env('fx_frontend_base_url') . 'orderDetails?orderRef=' . $order->orderRef;
            $data['seller_name'] = !is_null($order->seller) ? $order->seller->name : null;
            $buyer_obj = $order->buyer;
            $data['buyer_name'] = !is_null($buyer_obj) ? $buyer_obj->name : null;
            $recipient_addr = $order->orderAddress;
            if (!is_null($recipient_addr)) {
                $data['recipient_address'] = $recipient_addr->street_1 . ', ' . $recipient_addr->city . ', ' . $recipient_addr->state . ', ' . $recipient_addr->country;
                $data['recipient_phone'] = $recipient_addr->phone;
                $data['recipient_name'] = $recipient_addr->name;
            } else {
                $data['recipient_address'] = '';
                $data['recipient_phone'] = $buyer_obj->phone;
                $data['recipient_name'] = $buyer_obj->name;
            }

            $buyer_mail = false;
            $seller_mail = false;
            if (filter_var($data['seller_email'], FILTER_VALIDATE_EMAIL)) {
                $seller_mail = Mail::to($data['seller_email'])
                ->cc(cc('admin_email_address'))
                ->send(new NewFXOrderMail_Seller($data));
            }

            if (filter_var($data['buyer_email'], FILTER_VALIDATE_EMAIL)) {
                $buyer_mail = Mail::to($data['buyer_email'])->send(new NewFXOrderMail_Buyer($data));
            }

            $seller_msg = "A new order has been placed from your Peppa store, Ref: " . $order->orderRef;
            if (!is_null($order->seller->phone)) {
                $this->sendSMS($order->seller->phone, $seller_msg);
            }

            if ($seller_mail && $buyer_mail) {
                return true;
            }

            return false;
        } catch (Exception $e) {
            Logger::info('New FX Order Email Error', [$e->getMessage() . ' - ' . $e->__toString()]);
            return false;
        }
    }

    public function send_wallet_dispute_email($walletDispute)
    {
        try {
            $data['disputeID'] = $walletDispute->id;
            $data['sender_name'] = $walletDispute->transaction->from_account_name;
            $data['receiver_name'] = $walletDispute->transaction->to_account_name;
            $data['transaction_reference'] = $walletDispute->transaction_reference;
            $data['dispute_description'] = $walletDispute->dispute_description;
            $data['dispute_link'] = cc('admin_base_url') . '/wallet/disputes';

            $admins = explode(',', env('admin_emails'));
            // Loop through each admin and send the email
            foreach ($admins as $admin) {
                if (filter_var($admin, FILTER_VALIDATE_EMAIL)) {
                    $admin_mail = Mail::send('mails.peppa.walletDisputeAdmin', $data, function ($message) use ($data, $admin) {
                        $sender_name = $data['sender_name'];
                        $message->to($admin)
                            ->from(cc('mail_from'))
                            ->subject("$sender_name just raised a wallet dispute");
                    });
                }
            }
            return true;
        } catch (Exception $e) {
            Logger::info('Wallet Dispute Email Error', [$e->getMessage() . ' - ' . $e->__toString()]);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function send_fx_order_canceled_email($order, $initiator)
    {
        try {
            $data['description'] = $order->orderRef;
            $data['cost'] = number_format($order->total, 2);
            $data['totalprice'] = number_format($order->totalprice, 2);
            $data['shipping'] = number_format($order->shipping, 2);
            $data['pepfee'] = number_format($order->pepperestfees, 2);
            $data['sum_total'] = number_format($order->pepperestfees + $order->totalprice + $order->shipping, 2);
            $data['currency'] = $order->currency;
            $data['orderRef'] = $order->orderRef;
            $data['orderItems'] = $order->orderItems;
            $data['seller_name'] = !is_null($order->seller) ? $order->seller->name : null;
            $data['seller_email'] = !is_null($order->seller) ? $order->seller->email : null;
            $data['buyer_name'] = !is_null($order->buyer) ? $order->buyer->name : null;
            $data['buyer_email'] = !is_null($order->buyer) ? $order->buyer->email : null;
            $data['canceledBy'] = $initiator->userInfo->id == $order->buyer_id ? 'buyer' : 'vendor';
            $data['initiator_name'] = $initiator->name;
            $data['initiator_email'] = $initiator->email;
            $data['order_link'] = env('fx_frontend_base_url') . 'orderDetails?orderRef=' . $order->orderRef;

            $buyer_mail = false;
            $seller_mail = false;
            if (filter_var($data['seller_email'], FILTER_VALIDATE_EMAIL)) {
                $data['name'] = $data['seller_name'];
                $seller_mail = Mail::to($data['seller_email'])->cc(cc('admin_email_address'))->send(new FXOrderCancelled($data));
            }

            if (filter_var($data['buyer_email'], FILTER_VALIDATE_EMAIL)) {
                $data['name'] = $data['buyer_name'];
                $buyer_mail = Mail::to($data['buyer_email'])->send(new FXOrderCancelled($data));
            }

            $msg = "Your order with ref: "  . $order->orderRef . " has been canceled on Peppa store.";
            if (!is_null($order->seller->phone)) {
                $this->sendSMS($order->seller->phone, $msg);
            }

            if (!is_null($order->buyer->phone)) {
                $this->sendSMS($order->buyer->phone, $msg);
            }

            if ($seller_mail && $buyer_mail) {
                return true;
            }

            return false;
        } catch (Exception $e) {
            Logger::info('FX Order Cancel Email Error', [$e->getMessage() . ' - ' . $e->__toString()]);
            return false;
        }
    }
}