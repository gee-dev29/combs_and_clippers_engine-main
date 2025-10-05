<?php

namespace App\Http\Controllers\App;

use Exception;
use stdClass;
use App\Models\User;
use App\Models\Withdrawal;
use App\Repositories\Mailer;
use Illuminate\Http\Request;
use App\Models\InternalTransaction;
use App\Models\Appointment;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\WithdrawalResource;
use App\Http\Resources\WalletResource;
use App\Services\WalletService;
use Illuminate\Support\Facades\Log as Logger;
use App\Http\Resources\InternalTransactionResource;
use App\Http\Resources\AppointmentUndeliveredResource;

class PaymentController extends Controller
{
    // public function __construct(Mailer $mailer)
    // {
    //     $this->perPage = 7;
    //     $this->currency = "GBP";
    //     $this->Mailer = $mailer;
    // }

    public function createWallet(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bvn'           => 'required|numeric',
            'date_of_birth' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json(["ResponseStatus" => "Unsuccessful", 'Detail' => $validator->errors(), "ResponseCode" => 401, "ResponseMessage" => implode(', ', $validator->messages()->all())], 401);
        }
        try {
            $user = $this->getAuthUser($request);
            if (!$user) {
                return response()->json(["ResponseStatus" => "Unsuccessful", 'Detail' => 'User not found.', "ResponseMessage" => 'User not found.', "ResponseCode" => 404], 404);
            }
            $bvn = $request->bvn;
            $dob = $request->date_of_birth;
            //create wallet
            $walletService = new WalletService;
            $wallet = $walletService->createVFDAccount($user, $dob, $bvn);
            if(is_array($wallet) && $wallet['error'] == 1){
                return response()->json(["ResponseStatus" => "Unsuccessful", 'Detail' => $wallet['responseMessage'], "ResponseMessage" => $wallet['responseMessage'], "ResponseCode" => 401], 401);
            }
            if(is_null($wallet)){
                return response()->json(["ResponseStatus" => "Unsuccessful", 'Detail' => 'Wallet was not created.', "ResponseMessage" => 'Wallet was not created.', "ResponseCode" => 401], 401);
            }

            $wallet = new WalletResource($wallet);
            return response()->json(compact('wallet'), 200);
        } catch (Exception $e) {
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage() . ' - ' . $e->__toString(), "ResponseMessage" => $e->getMessage()], 500);
        }
    }

    public function withdrawalHistory(Request $request)
    {
        $merchantID = $this->getAuthID($request);
        $merchant = User::find($merchantID);
        if (!$merchant) {
            return $this->errorResponse('Merchant not found', 404);
        }
        try {
            $withdrawals = Withdrawal::latest()->paginate($this->perPage);
            //$withdrawals = Withdrawal::where('user_id', $merchant->id)->latest()->paginate($this->perPage);
            $withdrawals = $this->addMeta(WithdrawalResource::collection($withdrawals));
            return response()->json(compact('withdrawals'), 200);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function balance(Request $request)
    {
        try {
            $merchantID = $this->getAuthID($request);
            $wallet = User::find($merchantID)->wallet;
            // $wallet = [
            //     "id" => 1,
            //     "user_id" => 1,
            //     "wallet_number" => "0001",
            //     "currency" => "NGN",
            //     "amount" => 10000,
            //     "unclaimed_amount" => 1500,
            //     "account_number" => 1035522881,
            //     "account_name" => "Test Tech",
            //     "bank_code" => "566",
            //     "bank_name" => "VFD MICROFINANCE BANK",
            //     "provider" => "vfd"
            // ];
            if(!is_null($wallet)){
                $wallet = new WalletResource($wallet);
            }else{
                $wallet = new stdClass;
            }
            
            return response()->json(compact('wallet'), 200);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function withdraw(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        try {
            $merchantID = $this->getAuthID($request);
            $merchant = User::find($merchantID);
            if (!is_null($merchant)) {
                if(is_null($merchant->wallet)){
                    return $this->errorResponse('Merchant does not have a wallet', 404);
                }
                //get the absolute amount
                $amount = abs($request->amount);
                //get wallet balance
                $wallet = $merchant->wallet;
                $balance = $wallet->amount;
                if ($amount <= $balance) {
                    //process the withdrawal
                    $this->initiateTransfer($merchant, $amount);
                    $fee =  $this->currency . $amount;
                    return response()->json(["ResponseStatus" => "Successful", "Detail" => "Your withdrawal of {$fee} has been initiated successfully.", "message" => "Your withdrawal of {$fee} has been initiated successfully.", "ResponseCode" => 201], 201);
                }
                return $this->errorResponse('Sorry! You have insufficient fund', 403);
            }
            return $this->errorResponse('Merchant not found', 404);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function recentDeposits(Request $request)
    {
        try {
            $merchantID = $this->getAuthID($request);
            $merchant = User::find($merchantID);
            if (!is_null($merchant)) {
                $deposits = InternalTransaction::latest()->paginate($this->perPage);
                //$deposits = InternalTransaction::where(['merchant_id' => $merchant->id, 'payment_status' => Transaction::SUCCESSFUL, 'type' => 'credit'])->latest()->paginate($this->perPage);
                $deposits = $this->addMeta(InternalTransactionResource::collection($deposits));
                return response()->json(compact('deposits'), 200);
            }
            return $this->errorResponse('Merchant not found', 404);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    
    public function appointmentsAwaitingDelivery(Request $request)
    {
        try {
            $merchantID = $this->getAuthID($request);
            $merchant = User::find($merchantID);
            if (!is_null($merchant)) {
                $appointments = Appointment::where(['payment_status' => 1, "disbursement_status" => 0, "merchant_id" => $merchantID])->whereNotIn('status', ['Processing', 'Shipped', 'Delivered', 'Completed'])->paginate($this->perPage);
                $appointments_awaiting_delivery = $this->addMeta(AppointmentUndeliveredResource::collection($appointments));
                return response()->json(compact('appointments_awaiting_delivery'), 200);
            }
            return $this->errorResponse('Merchant not found', 404);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }  

}
