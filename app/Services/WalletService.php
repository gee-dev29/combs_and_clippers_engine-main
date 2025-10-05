<?php

namespace App\Services;

use stdClass;
use Exception;
use Carbon\Carbon;
use App\Models\Bank;
use App\Models\Wallet;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use App\Repositories\VFDUtils;
use App\Models\WalletTransaction;
use App\Supports\Traits\SmsTrait;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log as Logger;


class WalletService
{
    use SmsTrait;
    protected $perPage = 12;

    public function creditWallet($user, $wallet, $amount, $currency = 'NGN', $payload)
    {
        $wallet = Wallet::find($wallet->id);
        if (!is_null($wallet)) {
            $balance = $wallet->amount;
            $wallet->update(['amount' => $balance + $amount]);
            //create wallet transaction

            $walletTransaction = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'credit',
                'transaction_ref' => $payload->ref,
                'narration' => $payload->narration,
                'currency' => $currency,
                'amount' => $amount,
                'from_account_no' => $payload->originatoraccountnumber,
                'from_account_name' => $payload->originatorname,
                'from_bank_name' => $payload->bankname,
                'from_bank_code' => $payload->bankcode,
                'to_account_no' => $payload->craccount,
                'to_account_name' => $payload->craccountname,
                'to_bank_name' => "Wema",
                'to_bank_code' => $wallet->bank_code,
                'status' => WalletTransaction::SUCCESSFUL
            ]);
            //Send Credit email
            // $this->send_wallet_credit_email($walletTransaction, $wallet);
            // $narration = $payload->narration;
            // $ref = $payload->paymentreference;
            // $from = $payload->originatorname;
            // $name = $user->name;
            // $balance = $wallet->amount;
            // $msg = "Combs_and_clippers: Hi $name! \nYour wallet has been credited: \nCR: $amount \nBal: $balance \nRef: $ref \nfrom: $from \n$narration";
            // $this->sendSMS($user->phoneNo, $msg);
            return true;
        }

        return false;
    }

    public function creditWalletUnclaimed($user, $wallet, $amount, $currency = 'NGN', $payload)
    {
        $wallet = Wallet::find($wallet->id);
        if (!is_null($wallet)) {
            $balance = $wallet->unclaimed_amount;
            $wallet->update(['unclaimed_amount' => $balance + $amount]);
            //create wallet transaction

            $walletTransaction = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'credit_unclaimed',
                'transaction_ref' => $payload->ref,
                'narration' => $payload->narration,
                'currency' => $currency,
                'amount' => $amount,
                'from_account_no' => $payload->originatoraccountnumber,
                'from_account_name' => $payload->originatorname,
                'from_bank_name' => $payload->bankname,
                'from_bank_code' => $payload->bankcode,
                'to_account_no' => $payload->craccount,
                'to_account_name' => $payload->craccountname,
                'to_bank_name' => "Wema",
                'to_bank_code' => $wallet->bank_code,
                'status' => WalletTransaction::SUCCESSFUL
            ]);
            //Send Credit email
            // $this->send_wallet_credit_email($walletTransaction, $wallet);
            // $narration = $payload->narration;
            // $ref = $payload->paymentreference;
            // $from = $payload->originatorname;
            // $name = $user->name;
            // $balance = $wallet->amount;
            // $balance_unclaimed = $wallet->unclaimed_amount;
            // $msg = "Combs_and_clippers: Hi $name! \nYour wallet has been credited: \nCR: $amount \nBal: $balance \nBal_unclaimed: $balance_unclaimed \nRef: $ref \nfrom: $from \n$narration";
            // $this->sendSMS($user->phoneNo, $msg);
            return true;
        }

        return false;
    }

    public function logWalletTransaction($user, $wallet, $amount, $currency = 'NGN', $payload, $status = NULL)
    {
        $wallet = Wallet::find($wallet->id);
        if (!is_null($wallet)) {
            //create wallet transaction
            $walletTransaction = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'credit',
                'transaction_ref' => $payload->ref,
                'narration' => $payload->narration,
                'currency' => $currency,
                'amount' => $amount,
                'from_account_no' => $payload->originatoraccountnumber,
                'from_account_name' => $payload->originatorname,
                'from_bank_name' => $payload->bankname,
                'from_bank_code' => $payload->bankcode,
                'to_account_no' => $payload->craccount,
                'to_account_name' => $payload->craccountname,
                'to_bank_name' => $payload->bankname,
                'to_bank_code' => $wallet->bank_code,
                'status' => is_null($status) ? WalletTransaction::PENDING : $status
            ]);
            return true;
        }

        return false;
    }
    public function refundWallet($user, $wallet, $amount, $ref = NULL, $currency = 'NGN', $narration = 'Combs_and_clippers wallet refund')
    {
        $wallet = Wallet::find($wallet->id);
        if (!is_null($wallet)) {
            $balance = $wallet->amount;
            $wallet->update(['amount' => $balance + $amount]);
            //create wallet transaction

            $walletTransaction = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'credit',
                'transaction_ref' => $ref,
                'narration' => $narration,
                'currency' => $currency,
                'amount' => $amount,
                'from_account_name' => "Combs_and_clippers",
                'status' => WalletTransaction::SUCCESSFUL
            ]);
            //Send Credit email
            $this->send_wallet_credit_email($walletTransaction, $wallet);
            $from = "Combs_and_clippers";
            $name = $user->name;
            $balance = $wallet->amount;
            $msg = "Combs_and_clippers: Hi $name! \nYour wallet has been credited: \nCR: $amount \nBal: $balance \nRef: $ref \nfrom: $from \n$narration";
            $this->sendSMS($user->phoneNo, $msg);
            return true;
        }

        return false;
    }

    public function initiateWithdrawal(Request $request, $user, $wallet, $amount, $currency = 'NGN', $narration = 'Combs_and_clippers wallet withdrawal')
    {
        $is_internal = 0;
        if ($request->bank_code == Bank::WEMA) {
            $recipientWallet = $this->getWalletByaccountNo($request->account_no);
            if (!is_null($recipientWallet)) {
                $is_internal = 1;
            }
        }
        //record the withdrawal
        $withdrawal_fee = cc('wallet_withdrawal_fee');
        $withdrawal = Withdrawal::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'amount' => $amount,
            'amount_requested' => $amount,
            'fee' => $withdrawal_fee,
            'narration' => $request->narration,
            'account_number' => $request->account_no,
            'account_name' => $request->account_name,
            'bank_name' => $request->bank_name,
            'bank_code' => $request->bank_code,
            'withdrawal_status' => Withdrawal::PENDING,
            'is_internal' => $is_internal
        ]);
        //create wallet transaction
        $walletTransaction = WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'withdrawal_id' => $withdrawal->id,
            'type' => 'debit',
            'currency' => $currency,
            'narration' => !is_null($request->narration) ? $request->narration : $narration,
            'amount' => $amount + $withdrawal_fee,
            'from_account_no' => $wallet->account_number,
            'from_account_name' => $user->name,
            'from_bank_name' => 'Wema',
            'from_bank_code' => $wallet->bank_code,
            'to_account_no' => $request->account_no,
            'to_account_name' => $request->account_name,
            'to_bank_name' => $request->bank_name,
            'to_bank_code' => $request->bank_code,
            'status' => WalletTransaction::PENDING
        ]);

        //deduct amount from wallet
        $balance = $wallet->amount;
        $wallet->update(['amount' => ($balance - ($amount + $withdrawal_fee))]);

        //now credit peppa wallet
        if ($is_internal) {
            $peppaWallet = $this->getWalletById(env('PEPPA_WALLET_ID'));
            if (!is_null($peppaWallet) && !is_null($peppaWallet->user)) {
                $payload = new stdClass;
                $reference = "WALL-{$wallet->id}-" . time();
                $payload->ref = $reference;
                $payload->paymentreference = $reference;
                $payload->narration = !is_null($withdrawal->narration) ? $withdrawal->narration : ":Combs_and_clippers:wallet:{$withdrawal->user->name}";
                $payload->originatoraccountnumber = $withdrawal->wallet->account_number;
                $payload->originatorname = $withdrawal->user->name;
                $payload->bankname = 'Wema';
                $payload->bankcode = $withdrawal->wallet->bank_code;
                $payload->craccount = $withdrawal->account_number;
                $payload->craccountname = $withdrawal->account_name;
                $this->creditWallet($peppaWallet->user, $peppaWallet, $amount + $withdrawal_fee, 'NGN', $payload);
            }
        }
        return true;
    }

    public function chargeWallet($user, $wallet, $amount, $ref = NULL, $currency = 'NGN', $narration = 'Combs_and_clippers wallet payment')
    {
        //create wallet transaction
        $walletTransaction = WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'type' => 'debit',
            'transaction_ref' => $ref,
            'narration' => $narration,
            'currency' => $currency,
            'amount' => $amount,
            'from_account_no' => $wallet->account_number,
            'from_account_name' => $user->name,
            'from_bank_name' => 'Wema',
            'from_bank_code' => $wallet->bank_code,
            'status' => WalletTransaction::SUCCESSFUL
        ]);

        //deduct amount from wallet
        $balance = $wallet->amount;
        $wallet->update(['amount' => ($balance - $amount)]);

        //notify user of charge
        $this->send_wallet_charge_email($walletTransaction, $wallet);
        $name = $user->name;
        $balance = $wallet->amount;
        //$ref = $ref;
        $msg = "Combs_and_clippers: Hi $name! \nYour wallet has just been debited: \nDR: $amount \nBal: $balance \nReference: $ref \nNarration: $narration";
        $this->sendSMS($user->phoneNo, $msg);

        return true;
    }

    public function debitInternalWallet($amount, $currency = 'NGN', $payload)
    {
        $peppaWallet = $this->getWalletById(env('PEPPA_WALLET_ID'));
        if (!is_null($peppaWallet) && !is_null($peppaWallet->user)) {
            //create wallet transaction
            $walletTransaction = WalletTransaction::create([
                'wallet_id' => $peppaWallet->id,
                'type' => 'debit',
                'transaction_ref' => $payload->ref,
                'narration' => $payload->narration,
                'currency' => $currency,
                'amount' => $amount,
                'from_account_no' => $payload->originatoraccountnumber,
                'from_account_name' => $payload->originatorname,
                'from_bank_name' => $payload->bankname,
                'from_bank_code' => $payload->bankcode,
                'to_account_no' => $payload->craccount,
                'to_account_name' => $payload->craccountname,
                'to_bank_name' => "Wema",
                'to_bank_code' => $peppaWallet->bank_code,
                'status' => WalletTransaction::SUCCESSFUL
            ]);
            //deduct amount from wallet
            $balance = $peppaWallet->amount;
            $peppaWallet->update(['amount' => ($balance - $amount)]);

            //notify user of charge
            // $this->send_wallet_charge_email($walletTransaction, $peppaWallet);
            // $name = $peppaWallet->user->name;
            // $balance = $peppaWallet->amount;
            // $ref = $payload->paymentreference;
            // $msg = "Combs_and_clippers: Hi $name! \nYour wallet has just been debited: \nDR: $amount \nBal: $balance \nReference: $ref \nNarration: $payload->narration";
            // $this->sendSMS($peppaWallet->user->phoneNo, $msg);

            return true;
        }
    }

    public function getWallet($user, $currency = 'NGN')
    {
        $wallet = Wallet::where(['user_id' => $user->id, 'currency' => $currency])->first();

        return $wallet;
    }

    public function createVFDAccount($user, $dateOfBirth, $bvn, $currency = 'NGN')
    {
        $wallet = Wallet::where(['user_id' => $user->id, 'currency' => $currency, 'bank_code' => '566'])->first();
        //create wallet if no wallet found
        if (is_null($wallet) && $user->account_type != 'Client') {
            $vfd = new VFDUtils();
            $dob = Carbon::parse($dateOfBirth)->format('d-M-Y'); // Outputs: 08-Mar-1995
            $account = $vfd->createPermAccount($dob, $bvn);
            if ($account['error'] == 0) {
                $wallet = Wallet::create([
                    'user_id' => $user->id,
                    'wallet_number' => '0000',
                    'currency' => $currency,
                    'bank_code' => $account['bankCode']
                ]);

                $length = 4 - strlen((string)$wallet->id);
                $prefix = str_repeat("0", $length);
                $wallet_number = $prefix . (string)$wallet->id;
                $account_number = $account['account']->accountNo;
                $first_name = $account['account']->firstname;
                $last_name = $account['account']->lastname;
                $middle_name = $account['account']->middlename;
                $phone = $account['account']->phone;
                $wallet->update([
                    'wallet_number' => $wallet_number,
                    'account_number' => $account_number,
                    'amount' => 0
                ]);

                return $wallet;
            }
            return $account;
        }
        return $wallet;
    }

    public function getWalletByaccountNo($account_number, $currency = NULL)
    {
        if (!is_null($currency)) {
            $wallet = Wallet::where(['account_number' => $account_number, 'currency' => $currency])->first();
        } else {
            $wallet = Wallet::where('account_number', $account_number)->first();
        }

        return $wallet;
    }

    public function getWalletById($id, $currency = NULL)
    {
        if (!is_null($currency)) {
            $wallet = Wallet::where(['id' => $id, 'currency' => $currency])->first();
        } else {
            $wallet = Wallet::where('id', $id)->first();
        }

        return $wallet;
    }

    public function getTransactionsHistory($user, $type, $wallet_id, $currency = 'NGN')
    {
        $wallet = Wallet::where(['id' => $wallet_id, 'user_id' => $user->id, 'currency' => $currency])->first();
        if (!is_null($wallet)) {
            switch ($type) {
                case 'all':
                    $history = WalletTransaction::where(['wallet_id' => $wallet->id])->orderBy('id', 'DESC')->paginate($this->perPage);
                    break;
                case 'credit':
                    $history = WalletTransaction::where(['wallet_id' => $wallet->id, 'type' => 'credit'])->orderBy('id', 'DESC')->paginate($this->perPage);
                    break;
                case 'debit':
                    $history = WalletTransaction::where(['wallet_id' => $wallet->id, 'type' => 'debit'])->orderBy('id', 'DESC')->paginate($this->perPage);
                    break;
            }
            return $history;
        }
        return [];
    }

    private function send_wallet_credit_email($walletTranx, $wallet)
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

    private function send_wallet_charge_email($walletTranx, $wallet)
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
}
