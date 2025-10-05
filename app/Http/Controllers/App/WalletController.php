<?php

namespace App\Http\Controllers\App;

use Exception;
use App\Models\Wallet;
use Illuminate\Http\Request;
use App\Models\WalletDispute;
use App\Services\WalletService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\WalletResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log as Logger;
use App\Http\Resources\WalletTransactionResource;



class WalletController extends Controller
{
    public function withdraw(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bank_name' => 'required|string',
            'bank_code' => 'required|numeric',
            'account_no' => 'required|numeric',
            'account_name' => 'required|string',
            'wallet_id' => 'required|integer',
            'narration' => 'nullable|string',
            'amount' => 'required|numeric|min:50'
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        try {
            $user = $this->getAuthUser($request);
            if (!$user) {
                return $this->errorResponse('User not found!', 404);
            }

            //get the absolute amount
            $amount = abs($request->amount);
            $withdrawal_fee = cc('wallet_withdrawal_fee');
            $total_amount = $amount + $withdrawal_fee;
            //get wallet
            $wallet = Wallet::where(['user_id' => $user->id, 'id' => $request->wallet_id])->first();
            if (!$wallet) {
                return $this->errorResponse('Wallet not found!', 404);
            }
            //get wallet balance
            $balance = $wallet->amount;
            if ($total_amount <= $balance) {
                //process the withdrawal
                $walletService = new WalletService;
                $walletService->initiateWithdrawal($request, $user, $wallet, $amount);
                return $this->successResponse("Your withdrawal of {$amount} has been initiated successfully", 201);
            }
            return $this->errorResponse('Sorry! You have insufficient balance.', 403);
        } catch (Exception $e) {
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage() . ' - ' . $e->__toString(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function openWalletDispute(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'wallet_id' => 'required|integer',
            'transaction_reference' => 'required|string',
            'dispute_description' => 'required|string',
            'dispute_proof' => 'nullable|mimes:jpeg,jpg,png,pdf,gif,bmp|max:1024',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        try {
            $user = $this->getAuthUser($request);
            if (!$user) {
                return $this->errorResponse('User not found.', 404);
            }

            //get wallet
            $wallet = Wallet::where(['user_id' => $user->id, 'id' => $request->wallet_id])->first();
            if (!$wallet) {
                return $this->errorResponse('Wallet not found.', 404);
            }

            //find transaction
            //$walletTransaction = WalletTransaction::where(['wallet_id' => $wallet->id, 'transaction_ref' => $request->transaction_reference])->first();
            $walletTransaction = DB::table('wallet_transactions AS wt')->select('wt.*')
                ->join('withdrawals AS w', 'w.id', '=', 'wt.withdrawal_id')
                ->where(function ($query) use ($request) {
                    $query->where('wt.transaction_ref', $request->transaction_reference)
                        ->orWhere('w.transferRef', $request->transaction_reference);
                })->where('wt.wallet_id', $wallet->id)
                ->first();
            if (!$walletTransaction) {
                return $this->errorResponse('Transaction not found.', 404);
            }

            $walletDispute = WalletDispute::create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'wallet_transaction_id' => $walletTransaction->id,
                'transaction_reference' => $request->input('transaction_reference'),
                'dispute_description' => $request->input('dispute_description'),
                'status' => WalletDispute::OPEN
            ]);


            if ($request->hasFile('dispute_proof') && !is_null($walletDispute)) {
                $linkArray = $this->imageUtil->saveDocument($request->file('dispute_proof'), '/dispute_proof/', $walletDispute->id);
                if (!is_null($linkArray)) {
                    $dispute_proof = array_shift($linkArray);
                    $walletDispute->update(['dispute_proof' => $dispute_proof]);
                }
            }

            //send email to admin
            $this->peppUtil->send_wallet_dispute_email($walletDispute);

            return response()->json(compact('walletDispute'), 201);
        } catch (Exception $e) {
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage() . ' - ' . $e->__toString(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function createWallet(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'provider'      => 'required',
            'bvn'           => 'required|numeric',
            'date_of_birth' => 'required|date'
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        try {
            $user = $this->getAuthUser($request);
            if (!$user) {
                return $this->errorResponse('User not found.', 404);
            }
            $provider = $request->provider;
            $bvn = $request->bvn;
            $dob = $request->date_of_birth;
            //create wallet
            $walletService = new WalletService;
            $wallet = $walletService->createVFDAccount($user, $dob, $bvn);
            if (is_array($wallet) && $wallet['error'] == 1) {
                return $this->errorResponse($wallet['responseMessage'], 500);
            }

            if (is_null($wallet)) {
                return $this->errorResponse('Wallet could not be created. Please, try again later.', 500);
            }

            $wallet = new WalletResource($wallet);
            return response()->json(compact('wallet'), 200);
        } catch (Exception $e) {
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage() . ' - ' . $e->__toString(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function getWallet(Request $request)
    {
        try {
            $user = $this->getAuthUser($request);
            if (!$user) {
                return $this->errorResponse('User not found.', 404);
            }
            //get wallet
            $walletService = new WalletService;
            $wallet = $walletService->getWallet($user);
            $wallet = new WalletResource($wallet);
            return response()->json(compact('wallet'), 200);
        } catch (Exception $e) {
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage() . ' - ' . $e->__toString(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function getTransactionsHistory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:all,credit,debit',
            'wallet_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        try {
            $user = $this->getAuthUser($request);
            if (!$user) {
                return $this->errorResponse('User not found.', 404);
            }
            $type = $request->type;
            $wallet_id = $request->wallet_id;
            $wallet = Wallet::where(['id' => $wallet_id, 'user_id' => $user->id])->first();
            if (is_null($wallet)) {
                return $this->errorResponse('Wallet not found.', 404);
            }

            //get wallet history for the selected wallet
            $walletService = new WalletService;
            $walletHistory = $walletService->getTransactionsHistory($user, $type, $wallet_id);
            $walletHistory = $this->addMeta(WalletTransactionResource::collection($walletHistory));
            return response()->json(compact('walletHistory'), 200);
        } catch (Exception $e) {
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage() . ' - ' . $e->__toString(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function getTransactionFee(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        try {
            $user = $this->getAuthUser($request);
            if (!$user) {
                return $this->errorResponse('User not found.', 404);
            }

            return response()->json(['amount' => $request->amount, 'fee' => cc('wallet_withdrawal_fee')], 200);
        } catch (Exception $e) {
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage() . ' - ' . $e->__toString(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function setPin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pin' => 'required|numeric|digits:4'
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        try {
            $user = $this->getAuthUser($request);
            if (!$user) {
                return $this->errorResponse('User not found.', 404);
            }

            $user->update(['transaction_pin' => Hash::make($request->input('pin'))]);

            return $this->successResponse("Your wallet transaction pin has been set successfully", 201);
        } catch (Exception $e) {
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage() . ' - ' . $e->__toString(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function validatePin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pin' => 'required|numeric|digits:4'
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        try {
            $user = $this->getAuthUser($request);
            if (!$user) {
                return $this->errorResponse('User not found.', 404);
            }

            $plainPin = $request->pin;
            $hashedPin = $user->transaction_pin;
            if (is_null($hashedPin)) {
                return $this->errorResponse('No Transaction PIN, please set your PIN.', 404);
            }

            if (Hash::check($plainPin, $hashedPin)) {
                return $this->successResponse("Pin validated successfully", 200);
            }
            return $this->errorResponse('Invalid pin supplied.', 401);
        } catch (Exception $e) {
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage() . ' - ' . $e->__toString(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }
}
