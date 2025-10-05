<?php

namespace App\Imports;

use Exception;
use App\Models\User;
use App\Models\Referral;
use App\Models\Customers;
use App\Models\kustomers;
use App\Repositories\Mailer;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Facades\Notification;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Notifications\sendPasswordNotification;

class CustomersImport implements ToModel, WithHeadingRow
{

    // public function __construct(Mailer $mailer)
    // {
    //     $this->Mailer = $mailer;
    // }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $name = $row['name'];
        $name_arr = explode(" ", $name);

        try {
            //find the user with this email
            $user = User::where('email', $row['email'])->first();
            //user not found?
            if (is_null($user)) {
                //then create the user
                $user = User::create([
                    'name' => $name,
                    'firstName' =>  isset($name_arr[0]) ? $name_arr[0] : null,
                    'lastName' =>  isset($name_arr[1]) ? $name_arr[1] : null,
                    'email' => $row['email'],
                    'phone' => $row['phone'],
                    'password' => Hash::make($row['password']),
                    'referral_code' => generateReferralCode(),
                    'account_type' => $row['account_type'],
                    'accountstatus' => 1
                ]);
                
                if ($row['account_type'] == 'Merchant') {
                    $user->update(['merchant_code' => generateShortUniqueID($user->email, $user->id)]);
                }

                if (!is_null($user)) {
                    // $this->Mailer->sendPasswordEmail($user, $row['password']);
                    //sendToEngage($user);
                    return $user;
                } else {
                    return NULL;
                }
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
