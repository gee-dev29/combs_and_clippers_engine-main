<?php

namespace App\Http\Controllers\App;

use stdClass;
use Exception;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\User;
use App\Models\Wallet;
use App\Models\VfdWebhook;
use App\Models\Appointment;
use Illuminate\Http\Request;
use App\Services\WalletService;
use App\Models\TransactionAccount;
use App\Models\BoothRentPaymentHistory;
use App\Models\BoothRentalPayment;
use App\Models\WalletTransaction;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log as Logger;



class WebhookController extends Controller
{
    public function VFDWebhook(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
            'reference' => 'nullable|string',
            'account_number' => 'nullable|string',
            'originator_account_number' => 'nullable|string',
            'originator_account_name' => 'nullable|string',
            'originator_bank' => 'nullable|string',
            'originator_narration' => 'nullable|string',
            'session_id' => 'nullable|string',
            'amount' => 'nullable|numeric',
            'timestamp' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(["ResponseStatus" => "Unsuccessful", 'Detail' =>  $validator->errors(), "ResponseCode" => 401, "ResponseMessage" => implode(', ', $validator->messages()->all()), "message" => implode(', ', $validator->messages()->all())], 401);
        }
            
            //$App_key = apache_request_headers()['App_key'];
            Logger::info('VFD webhook event', [$request->all()]);
            Logger::info('VFD webhook App_key', [$request->header('App_Key')]);

            if (!is_null($request->input('account_number'))) {
                $virtualAcct = TransactionAccount::where(['account_number' => $request->input('account_number'), 'status' => 0])->orderBy('id', 'DESC')->first();
                if (!is_null($virtualAcct)) {
                    VfdWebhook::create([
                        'reference' => $request->input('reference'),
                        'account_no' => $request->input('account_number'),
                        'from_account_no' => $request->input('originator_account_number'),
                        'from_account_name' => $request->input('originator_account_name'),
                        'from_bankcode' => $request->input('originator_bank'),
                        'narration' => $request->input('originator_narration'),
                        'session_id' => $request->input('session_id'),
                        'amount' => $request->input('amount'),
                        'trans_date' => $request->input('timestamp'),
                    ]);
                }else{
                    return response()->json([
                        "requestSuccessful" => true,
                        "responseMessage" => "success",
                        "responseCode" => "00"
                    ], 200);
                }
                $accountNo = $request->input('account_number');
                $amount = (float)$request->input('amount');
                $currency = "NGN";
                $walletService = new WalletService;
                $wallet = $walletService->getWalletByaccountNo($accountNo);
                if (!is_null($wallet) && !is_null($wallet->user)) {
                    $payload = new stdClass;
                    //$reference = "WALL-{$wallet->id}-" . time();
                    //$payload->ref = $reference;
                    $payload->paymentreference = $request->input('reference');
                    $payload->narration = $request->input('originator_narration');
                    $payload->originatoraccountnumber = $request->input('originator_account_number');
                    $payload->originatorname = $request->input('originator_account_name');
                    $payload->bankname = 'VFD';
                    $payload->bankcode = $virtualAcct->bank_code;
                    $payload->craccount = $accountNo;
                    $payload->craccountname = $virtualAcct->account_name;
                }

                if (!is_null($virtualAcct) && !is_null($virtualAcct->appointment_id)) {
                    //booth_rental_payment_id
                    $trans_id = $virtualAcct->appointment_id;
                    $appointment = Appointment::find($trans_id); //total
                    $statusArr = cc('transaction.statusArray');
                    if (!is_null($appointment) && $virtualAcct->total > $amount) {
                        $virtualAcct->update([
                            'status' => 2, 
                            'amount_paid' => $amount
                        ]);
                    }else{
                        $virtualAcct->update([
                            'status' => 1, 
                            'amount_paid' => $amount
                        ]);
                    }
                    $cost = $appointment->total_amount + $appointment->processing_fee; 
                    if (!is_null($appointment) && $cost <= $amount) {
                        $appointment->update([
                            "status" => 'Paid',
                            "payment_status" => 1
                        ]);

                        //Credit merchant wallet unclaimed balance
                        //$appointment->total_amount
                        //creditWalletUnclaimed
                        $merchant = User::find($appointment->merchant_id);
                        $credit_amount = $appointment->total_amount;
                        if (!is_null($merchant) && !is_null($merchant->wallet)) {
                            $payload->ref = $appointment->payment_ref;
                            $walletService->creditWalletUnclaimed($merchant, $merchant->wallet, $credit_amount, 'NGN', $payload);
                            $walletService->logWalletTransaction($merchant, $merchant->wallet, $credit_amount, 'NGN', $payload);
                        }
                        $users = [$appointment->serviceProvider, $appointment->customer];
                        $subject = $currency.$appointment->total_amount." Appointment Payment Confirmed - ref: ". $appointment->appointment_ref; 
                        $this->notifyUtils->appointmentNotificationToUsers($users, $appointment, $subject);
                        
                    }
                    
                }elseif (!is_null($virtualAcct) && !is_null($virtualAcct->booth_rental_payment_id)) {
                    //
                    $trans_id = $virtualAcct->booth_rental_payment_id;
                    $booth_rent = BoothRentalPayment::find($trans_id); //total
                    if (!is_null($booth_rent) && $virtualAcct->total > $request->input('amount')) {
                        $virtualAcct->update([
                            'status' => 2, 
                            'amount_paid' => $amount
                        ]);
                    }else{
                        $virtualAcct->update([
                            'status' => 1, 
                            'amount_paid' => $amount
                        ]);
                    }
                    $cost = $booth_rent->amount + $booth_rent->processing_fee; 
                    if (!is_null($booth_rent) && $cost <= $amount) {
                        $booth_rent->update([
                            "payment_status" => 1
                        ]);
                        $payment_date = Carbon::now();
                        BoothRentPaymentHistory::create([
                            "booth_rent_payment_id" => $booth_rent->id,
                            "amount_paid" => $booth_rent->amount,
                            "payment_date" => $payment_date
                        ]);

                        //Credit merchant wallet balance
                        $merchant = $booth_rent->userStore->store->owner;
                        $credit_amount = $booth_rent->amount;
                        if (!is_null($merchant) && !is_null($merchant->wallet)) {
                            $payload->ref = "booth_rental_id: ". $booth_rent->booth_rental_id;
                            $walletService->logWalletTransaction($merchant, $merchant->wallet, $credit_amount, 'NGN', $payload, WalletTransaction::SUCCESSFUL);
                            $walletService->creditWallet($merchant, $merchant->wallet, $credit_amount, 'NGN', $payload);
                        }

                        $users = [$booth_rent->userStore->user, $merchant];
                        $subject = $currency.$booth_rent->amount." Booth Rent Payment Confirmed."; 
                        $this->notifyUtils->boothRentPaymentNotificationToUsers($users, $booth_rent, $subject);
                    }
                    
                }elseif (!is_null($virtualAcct) && !is_null($virtualAcct->order_id)) {
                    $order_id = $virtualAcct->order_id;
                    $order = Order::find($order_id);
                    $statusArr = cc('transaction.statusArray');
                    $total_cost = $order->total + $order->shipping;
                    if (!is_null($order) && $total_cost > $request->input('amount')) {
                        $virtualAcct->update(['status' => 2, 
                            'amount_paid' => $request->input('amount'),
                            'payment_gateway' => "VFD"
                        ]);
                    }else{
                        $virtualAcct->update(['status' => 1, 
                            'amount_paid' => $request->input('amount')
                        ]);
                    }
                       
                    if (!is_null($order) && $total_cost <= $request->input('amount')) {
                        if ($order->payment_status != 1) {
                            $order->update([
                                "status" => $statusArr['Paid'],
                                "payment_status" => 1
                            ]);
                        }
                        
                    }

                }else {
                    //payment is normal wallet transfer
                    $wallet = $walletService->getWalletByaccountNo($accountNo);
                    if (!is_null($wallet) && !is_null($wallet->user)) {
                        $reference = "WALL-{$wallet->id}-" . time();
                        $payload->ref = $reference;
                        $walletService->creditWallet($wallet->user, $wallet, $amount, 'NGN', $payload);
                    }
                }
                //header("HTTP/1.1 200 OK");
                return response()->json([
                    "requestSuccessful" => true,
                    "responseMessage" => "success",
                    "responseCode" => "00"
                ], 200);
            }
            return response()->json([
                    "requestSuccessful" => true,
                    "responseMessage" => "success",
                    "responseCode" => "00"
                ], 200);
            
        } catch (Exception $e) {
            Logger::error('VFD webhook error - ', [$e->getMessage() . ' - ' . $e->__toString()]);
            return response()->json([
                "requestSuccessful" => true,
                "responseMessage" => "success",
                "responseCode" => "00"
            ], 200);
        }
    }
    // public function VFDWebhook(Request $request)
    // {
    //     try {
    //         $validator = Validator::make($request->all(), [
    //             'reference' => 'nullable|string',
    //             'account_number' => 'nullable|string',
    //             'originator_account_number' => 'nullable|string',
    //             'originator_account_name' => 'nullable|string',
    //             'originator_bank' => 'nullable|string',
    //             'originator_narration' => 'nullable|string',
    //             'session_id' => 'nullable|string',
    //             'amount' => 'nullable|numeric',
    //             'timestamp' => 'nullable|string',
    //         ]);

    //         if ($validator->fails()) {
    //             return $this->validationError($validator);
    //         }

    //         Logger::info('VFD webhook event', [$request->all()]);

    //         if (!is_null($request->input('account_number'))) {
    //             VfdWebhook::create([
    //                 'reference' => $request->input('reference'),
    //                 'account_no' => $request->input('account_number'),
    //                 'from_account_no' => $request->input('originator_account_number'),
    //                 'from_account_name' => $request->input('originator_account_name'),
    //                 'from_bankcode' => $request->input('originator_bank'),
    //                 'narration' => $request->input('originator_narration'),
    //                 'session_id' => $request->input('session_id'),
    //                 'amount' => $request->input('amount'),
    //                 'trans_date' => $request->input('timestamp'),
    //             ]);
    //             $accountNo = $request->input('account_number');
    //             $amount = (float)$request->input('amount');
    //             $walletService = new WalletService;

    //             $virtualAcct = TransactionAccount::where(['account_number' => $accountNo, 'status' => 0])->orderBy('id', 'DESC')->first();
    //             //if payment is for an appointment
    //             if (!is_null($virtualAcct) && !is_null($virtualAcct->appointment_id)) {
    //                 $appointment_id = $virtualAcct->appointment_id;
    //                 $appointment = Appointment::find($appointment_id);
    //                 if (!is_null($appointment) && $appointment->total_amount == $amount) {
    //                     $appointment->update(["status" => "Paid", "payment_status" => 1]);
    //                     //send appointment confirmation
    //                     //$this->peppUtil->send_booking_confirmation_email($appointment);
    //                     //find and log wallet transaction
    //                     $wallet = Wallet::where(['user_id' => $appointment->merchant_id])->first();
    //                     if (!is_null($wallet) && !is_null($wallet->user)) {
    //                         $payload = new stdClass;
    //                         $payload->ref = $appointment->payment_ref;
    //                         $payload->paymentreference = $request->input('reference');
    //                         $payload->narration = $request->input('originator_narration');
    //                         $payload->originatoraccountnumber = $request->input('originator_account_number');
    //                         $payload->originatorname = $request->input('originator_account_name');
    //                         $payload->bankname = 'VFD';
    //                         $payload->bankcode = $virtualAcct->bank_code;
    //                         $payload->craccount = $accountNo;
    //                         $payload->craccountname = $virtualAcct->account_name;
    //                         $walletService->logWalletTransaction($wallet->user, $wallet, $amount, 'NGN', $payload);
    //                     }
    //                 }
    //             } elseif (!is_null($virtualAcct) && !is_null($virtualAcct->order_id)) {
    //                 //if payment is for an order
    //                 $order_id = $virtualAcct->order_id;
    //                 $order = Order::find($order_id);
    //                 $statusArr = cc('transaction.statusArray');
    //                 $total_cost = $order->total + $order->shipping;

    //                 if (!is_null($order) && $total_cost == $amount) {
    //                     if ($order->payment_status != 1) {
    //                         $order->update([
    //                             "status" => $statusArr['Paid'],
    //                             "payment_status" => 1
    //                         ]);
    //                     }
    //                 }
    //             } else {
    //                 //payment is normal wallet transfer
    //                 $wallet = $walletService->getWalletByaccountNo($accountNo);
    //                 if (!is_null($wallet) && !is_null($wallet->user)) {
    //                     $payload = new stdClass;
    //                     $reference = "WALL-{$wallet->id}-" . time();
    //                     $payload->ref = $reference;
    //                     $payload->paymentreference = $request->input('reference');
    //                     $payload->narration = $request->input('originator_narration');
    //                     $payload->originatoraccountnumber = $request->input('originator_account_number');
    //                     $payload->originatorname = $request->input('originator_account_name');
    //                     $payload->bankname = 'VFD';
    //                     $payload->bankcode = $wallet->bank_code;
    //                     $payload->craccount = $accountNo;
    //                     $payload->craccountname = $wallet->user->name;
    //                     $walletService->creditWallet($wallet->user, $wallet, $amount, 'NGN', $payload);
    //                 }
    //             }
    //         }
    //         return response()->json([
    //             "requestSuccessful" => true,
    //             "responseMessage" => "success",
    //             "responseCode" => "00"
    //         ], 200);
    //     } catch (Exception $e) {
    //         Logger::error('VFD webhook error - ', [$e->getMessage() . ' - ' . $e->__toString()]);
    //         return response()->json([
    //             "requestSuccessful" => true,
    //             "responseMessage" => "success",
    //             "responseCode" => "00"
    //         ], 200);
    //     }
    // }
}
