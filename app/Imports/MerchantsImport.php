<?php

namespace App\Imports;

use Exception;
use App\Models\User;
use App\Models\Store;
use App\Models\Wallet;
use Illuminate\Support\Str;
use App\Repositories\Mailer;
use App\Models\StoreCategory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use App\Services\UgandanPhoneValidator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Bmatovu\MtnMomo\Products\Collection as MomoCollection;

class MerchantsImport implements ToCollection, WithChunkReading, ShouldQueue, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        $collection = new MomoCollection();
        foreach ($rows as $row) {
            //if (!UgandanPhoneValidator::validate($row['phone'])) continue;
            $phone = $row['phone'];
            if (!$collection->isActive($phone)) continue;
            $password = generatePassword();
            $name = $row['full_name'];
            $email = $row['email'];
            $name_arr = explode(" ", $name);

            //find the user with this email
            $user = User::where('email', $email)->first();
            //user not found?
            if (is_null($user)) {
                //create user
                $user = User::create([
                    'name' => $name,
                    'firstName' =>  isset($name_arr[0]) ? $name_arr[0] : null,
                    'lastName' =>  isset($name_arr[1]) ? $name_arr[1] : null,
                    'email' => $email,
                    'phone' => $phone,
                    'password' => Hash::make($password),
                    'referral_code' => generateReferralCode(),
                    'account_type' => 'Merchant',
                    'accountstatus' => 1,
                    'token' => Str::random(64)
                ]);

                $user->update(['merchant_code' => generateShortUniqueID($user->email, $user->id)]);

                //create wallet
                $wallet = new Wallet;
                $wallet->amount = 0;
                $wallet->save();

                $user->update(['wallet_id' => $wallet->id]);

                //create store
                $store_name = $row['store_name'];
                $categoryId = StoreCategory::where('categoryname', $row['store_category'])->value('id');

                if(is_null($categoryId)){
                    $category = StoreCategory::create(['categoryname' => $row['store_category']]);
                    $categoryId = $category->id;
                }

                $store = new Store;
                $store->merchant_id = $user->id;
                $store->store_name = $store_name;
                $store->store_category = $categoryId;
                $store->save();

                //send verification and password email
                $mailer = new Mailer;
                $mailer->sendVerificationEmail($user);
                $mailer->sendPasswordEmail($user, $password);
            }
        }
    }

    public function chunkSize(): int
    {
        return 200;
    }
}
