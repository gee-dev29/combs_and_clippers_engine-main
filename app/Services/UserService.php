<?php
namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserService 
{
    /**
     * Function to create user 
     *
     * @param  array $userData
     * @return User
     */
    public function createUser(array $data): User
    {

        $name_arr = explode(" ", $data['name']);
        return User::create([
          'name' => $data['name'],
          'firstName' => $name_arr[0],
          'lastName' => isset($name_arr[1]) ? $name_arr[1] : $name_arr[0],
          'email' => $data['email'],
          'password' => Hash::make(Str::random(8)),
          'referral_code' => generateReferralCode(),
          'account_type' => 'Client',
          'specialization' => '',
          'accountstatus' => 1,
          'token' => Str::random(64)
        ]);
    }
}