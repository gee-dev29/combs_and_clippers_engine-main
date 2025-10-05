<?php

namespace App\Http\Controllers\App;

use Exception;
use \Carbon\Carbon;
use App\Models\User;
use App\Events\Login;
use App\Models\Store;
use App\Models\Review;
use GuzzleHttp\Client;
use App\Models\Country;
use App\Models\Activity;
use App\Models\Referral;
use App\Models\Waitlist;
use App\Models\UserStyle;
use App\Repositories\Util;
use App\Models\Appointment;
use Illuminate\Support\Str;
use App\Models\StoreAddress;
use App\Repositories\Mailer;
use Illuminate\Http\Request;
use App\Models\SocialProvider;
use Illuminate\Validation\Rule;
use App\Models\StoreWorkdoneImage;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\ActivityResource;
use App\Http\Resources\WaitlistResource;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Propaganistas\LaravelPhone\Rules\Phone;
use Illuminate\Support\Facades\Log as Logger;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;



class UserController extends Controller
{
public function register(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z_-]+ [a-zA-Z_-]+(?: [a-zA-Z_-]+)*$/'],
        'accountType' => ['required', 'string', 'in:Client,Stylist,Owner'],
        'referral_code' => ['nullable', 'string', 'max:25'],
        'email' => ['required', 'email', 'max:100', 'unique:users'],
        'password' => ['required', 'string', 'min:8'],
        'phone' => ['nullable', 'string', 'unique:users']
    ]);

    if ($validator->fails()) {
        return $this->validationError($validator);
    }
    
    try {
        $name = $request->input('name');
        $name_arr = explode(" ", $name);
        
        $userData = [
            'name' => $name,
            'firstName' => $name_arr[0],
            'lastName' => isset($name_arr[1]) ? $name_arr[1] : $name_arr[0],
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'referral_code' => generateReferralCode(),
            'account_type' => $request->input('accountType'),
            'specialization' => $request->input('specialization'),
            'accountstatus' => 1,
            'token' => Str::random(64)
        ];

        if ($request->filled('phone')) {
            $userData['phone'] = $request->input('phone');
            $userData['phone_verified'] = false;
        }

        $user = User::create($userData);

        if ($request->accountType != 'Client') {
            $user->update(['merchant_code' => generateShortUniqueID($user->email, $user->id)]);
        }

        if ($request->filled('referral_code')) {
            $referrer = User::where('referral_code', $request->input('referral_code'))->first();
            if (!is_null($referrer)) {
                Referral::create([
                    'referrer_id' => $referrer->id,
                    'customer_id' => $user->id,
                    'customer_type' => $user->account_type
                ]);
            }
        }

        $this->Mailer->sendVerificationEmail($user);
        $this->Mailer->sendAccountCreatedEmail($user);

   if ($request->filled('phone')) {
    try {
        $otp = rand(1000, 9999);
        
      
        $mailer = new Mailer();
        $mailer->sendPhoneVerificationOTP($user, $otp);

        $beforeState = DB::selectOne('SELECT sms_otp, updated_at FROM users WHERE id = ?', [$user->id]);

        $user->sms_otp = $otp;

        $saveResult = $user->save();
        

        $afterModelSave = DB::selectOne('SELECT sms_otp, updated_at FROM users WHERE id = ?', [$user->id]);

        if (is_null($afterModelSave->sms_otp) || $afterModelSave->sms_otp != $otp) {
            \Log::warning('6. Model Save Failed - Trying Direct SQL');
            
            try {
                $sqlResult = DB::statement('UPDATE users SET sms_otp = ?, updated_at = NOW() WHERE id = ?', [$otp, $user->id]);
                

                $afterSqlUpdate = DB::selectOne('SELECT sms_otp, updated_at FROM users WHERE id = ?', [$user->id]);

                
            } catch (Exception $sqlException) {
                \Log::error('9. SQL Update Failed', [
                    'user_id' => $user->id,
                    'sql_error' => $sqlException->getMessage(),
                ]);
            }
        }
        

        
        $otpSent = true;
        
    } catch (Exception $e) {
        \Log::error('OTP Process Exception', [
            'user_id' => $user->id ?? 'unknown',
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
        $otpSent = false;
    }
} else {
    $otpSent = false;
}

        $token = $this->respondWithToken(JWTAuth::fromUser($user));
        $userInfo = new UserResource($user);
        
        $response = compact('userInfo', 'token');
        if ($request->filled('phone')) {
            $response['otp_sent'] = $otpSent;
            $response['message'] = $otpSent 
                ? 'Registration successful. OTP sent to your email address.' 
                : 'Registration successful. OTP could not be sent, but you can request it later.';
        }
        
        return response()->json($response, 201);
        
    } catch (Exception $e) {
        return response()->json([
            "ResponseStatus" => "Unsuccessful", 
            "ResponseCode" => 500, 
            'Detail' => $e->getMessage(), 
            'message' => 'Something went wrong', 
            "ResponseMessage" => 'Something went wrong'
        ], 500);
    }
}

  // public function addPhone(Request $request)
  // {
  //   $validator = Validator::make($request->all(), [
  //     'phone' => ['required', Rule::unique(User::class)->ignore($this->getAuthUser($request)->id)],
  //   ]);

  //   if ($validator->fails()) {
  //     return $this->validationError($validator);
  //   }
  //   try {
  //     $userID = $this->getAuthID($request);
  //     $user = User::find($userID);
  //     if (is_null($user)) {
  //       return $this->errorResponse('User not found!', 404);
  //     }
  //     $otp = rand(1000, 9999);
  //     $message = "Your Combs and Clippers OTP code is " . $otp;
  //     $otpSent = $this->sendOTP($request['phone'], $message);
  //     $mailer = new Mailer();
  //     $mailer->sendPhoneVerificationOTP($user, $otp);

  //     $user->update(['phone' => $request->phone]);

  //     if ($otpSent) {
  //       $user->sms_otp = $otp;
  //       $user->save();
  //       return $this->successResponse("An OTP code has been sent to your number", 200);
  //     } else {
  //       return $this->errorResponse('OTP code could not be sent, Please try again', 401);
  //     }
  //   } catch (Exception $e) {
  //     return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
  //   }
  // }

public function resendPhoneOtp(Request $request)
{
    try {
        $userID = $this->getAuthID($request);
        $user = User::find($userID);

        if (is_null($user)) {
            return $this->errorResponse('User not found!', 404);
        }

        if (empty($user->phone)) {
            return $this->errorResponse('Phone number not found. Please add your phone number first.', 400);
        }

        $otp = rand(1000, 9999);
        
       
        $mailer = new Mailer();
        $mailer->sendPhoneVerificationOTP($user, $otp);
        
        
        $user->sms_otp = $otp;
        $user->save();
        
        \Log::info('Email OTP resent and saved', [
            'user_id' => $user->id,
            'email' => $user->email,
            'phone' => $user->phone,
            'otp_saved' => $user->sms_otp
        ]);
        
        return $this->successResponse("A new OTP has been sent to your email address", 200);
        
    } catch (Exception $e) {
        \Log::error('Resend OTP failed: ' . $e->getMessage(), [
            'user_id' => $userID ?? null,
            'error' => $e->getMessage()
        ]);
        
        return response()->json([
            "ResponseStatus" => "Unsuccessful",
            "ResponseCode" => 500,
            'Detail' => $e->getMessage(),
            'message' => 'Something went wrong',
            "ResponseMessage" => 'Something went wrong'
        ], 500);
    }
}


public function validateOTP(Request $request)
{
    $validator = Validator::make($request->all(), [
        'otp' => 'required|integer',
    ]);

    if ($validator->fails()) {
        return $this->validationError($validator);
    }
    
    try {
        $userID = $this->getAuthID($request);
        $user = User::find($userID);
        
        if (is_null($user)) {
            return $this->errorResponse('User not found!', 404);
        }
        
        $savedOTP = $user->sms_otp;
        $inputOTP = $request['otp'];
        
        // \Log::info('OTP Validation Debug', [
        //     'user_id' => $userID,
        //     'saved_otp' => $savedOTP,
        //     'saved_otp_type' => gettype($savedOTP),
        //     'input_otp' => $inputOTP,
        //     'input_otp_type' => gettype($inputOTP),
        //     'strict_comparison' => $savedOTP === $inputOTP,
        //     'loose_comparison' => $savedOTP == $inputOTP,
        //     'phone_verified_before' => $user->phone_verified,
        // ]);
        
        if ($savedOTP == $inputOTP) { 
            $user->sms_otp = NULL;
            $user->phone_verified = true; 
            $user->save();
            
            // \Log::info('OTP Validated Successfully', [
            //     'user_id' => $userID,
            //     'phone_verified_after' => $user->phone_verified,
            // ]);
            
            return $this->successResponse("OTP code validated successfully", 200);
        } else {
            return $this->errorResponse('Invalid OTP Code supplied!, Please try again', 401);
        }
        
    } catch (Exception $e) {
        \Log::error('OTP Validation Error', [
            'error' => $e->getMessage(),
            'user_id' => $userID ?? null,
            'request_otp' => $request['otp'] ?? null,
        ]);
        
        return response()->json([
            "ResponseStatus" => "Unsuccessful", 
            "ResponseCode" => 500, 
            'Detail' => $e->getMessage(), 
            'message' => 'Something went wrong', 
            "ResponseMessage" => 'Something went wrong'
        ], 500);
    }
}


  public function createStore(Request $request)
  {
    $validator = Validator::make(
      $request->all(),
      [
        'store_name' => 'required|string|max:255|regex:/^[a-zA-Z]+[\w\s-]*$/',
        'store_category' => 'nullable|integer',
        'store_sub_category' => 'nullable|integer',
        'days_available' => 'required|array',
        'time_available' => 'required',
        'website' => 'nullable|string|max:255',
        'store_icon' => 'nullable|mimes:jpeg,jpg,png,gif,bmp|max:5120',
        'store_banner' => 'nullable|mimes:jpeg,jpg,png,gif,bmp|max:5120',
        'store_description' => 'nullable|string',
        'refund_allowed' => 'integer|nullable',
        'replacement_allowed' => 'integer|nullable',
        'store_state' => 'nullable|string|max:255',
        'store_city' => 'required|string|max:255',
        'store_postal_code' => 'required|string|max:255',
        'store_address' => 'required|string|max:255',
        'store_type' => 'required|string'
      ]
    );

    if ($validator->fails()) {
      return $this->validationError($validator);
    }

    $merchantID = $this->getAuthID($request);
    try {
      $merchant = User::find($merchantID);
      if (is_null($merchant)) {
        return $this->errorResponse('User not found!', 404);
      }

      $store = Store::updateOrCreate(
        ['merchant_id' => $merchant->id],
        [
          'store_name' => $request->input('store_name'),
          'store_category' => $request->input('store_category'),
          'store_sub_category' => $request->input('store_sub_category'),
          'store_type' => $request->input('store_type'),
          'website' => $request->input('website'),
          'store_description' => $request->input('store_description'),
          'days_available' => json_encode($request->input('days_available')),
          'time_available' => json_encode($request->input('time_available')),
          'approved' => 1,
          'refund_allowed' => $request->filled('refund_allowed') ? $request->input('refund_allowed') : 0,
          'replacement_allowed' => $request->filled('replacement_allowed') ? $request->input('replacement_allowed') : 0
        ]
      );
      $this->updateBoothProgress($merchantID, $store->id, "add_schedule_location");

      if ($request->hasFile('store_icon')) {
        $imageArray = $this->imageUtil->saveImgArray($request->file('store_icon'), '/merchants/stores/icons/', $store->id, []);
        if (!is_null($imageArray)) {
          $icon = array_shift($imageArray);
          $store->update(['store_icon' => $icon]);
        }
      }

      if ($request->hasFile('store_banner')) {
        $imageArray = $this->imageUtil->saveImgArray($request->file('store_banner'), '/merchants/stores/banners/', $store->id, []);
        if (!is_null($imageArray)) {
          $banner = array_shift($imageArray);
          $store->update(['store_banner' => $banner]);
        }
      }

      //$country = Country::where('id', $request->country_id)->value('country');
      $country = $this->country;
      $state = $request->filled('store_state') ? $request->store_state . ', ' : '';
      $address = $request->input('store_address') . ', ' . $request->input('store_city') . ', ' . $state . $country;
      $store_address = StoreAddress::where(['email' => $merchant->email, 'merchant_id' => $merchant->id])->first();
      if (is_null($store_address)) {
        $store_address = StoreAddress::create(
          [
            'name' => $merchant->name,
            'email' => $merchant->email,
            'phone' => $merchant->phone,
            'merchant_id' => $merchant->id,
            'street' => $request->input('store_address'),
            'city' => $request->input('store_city'),
            'state' => $request->input('store_state'),
            'postal_code' => $request->input('store_postal_code'),
            'country' => $country,
            'address' => $address
          ]
        );
      }

      if (!is_null($store_address)) {
        $address_str = $address;
        $add_info = Util::validateAddressWithGoogle($merchant, $address_str);
        //return $add_info;
        if ($add_info['error'] == 0) {
          $store_address->update([
            'longitude' => $add_info['addressDetails']['longitude'],
            'latitude' => $add_info['addressDetails']['latitude'],
            'postal_code' => $add_info['addressDetails']['postal_code'],
            'zip' => $add_info['addressDetails']['postal_code'],
            'country' => $add_info['addressDetails']['country'],
            'formatted_address' => $add_info['addressDetails']['formatted_address'],
            'address_code' => generateAddressCode($merchant),
            'city' => $add_info['addressDetails']['city'],
            'city_code' => $add_info['addressDetails']['city_code'],
            'state' => $add_info['addressDetails']['state'],
            'state_code' => $add_info['addressDetails']['state_code'],
            'country_code' => $add_info['addressDetails']['country_code'],
            'street' => $add_info['addressDetails']['street'] ?? $store_address->street,
          ]);
        } else {
          return response()->json(["ResponseStatus" => "Unsuccessful", 'Detail' => 'Address error: ' . $add_info['responseMessage'], "ResponseMessage" => 'Address error: ' . $add_info['responseMessage'], "message" => 'Address error: ' . $add_info['responseMessage'], "ResponseCode" => 400], 400);
        }
      }
      $userInfo = new UserResource($merchant);
      return $this->successResponse("Store created successfully", 200, compact('userInfo'));
    } catch (Exception $e) {
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
    }
  }

  public function removeStore(Request $request)
  {
    try {
      $merchantID = $this->getAuthID($request);
      $store = Store::where('merchant_id', $merchantID)->latest()->first();
      if (!is_null($store)) {
        $store->products()->delete();
        $store->delete();
        return response()->json(["ResponseStatus" => "successful", 'Detail' => 'Store deleted successfully.', "ResponseMessage" => 'Store deleted successfully.', "ResponseCode" => 200], 200);
      }
      return $this->errorResponse('Store not found', 404);
    } catch (Exception $e) {
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
    }
  }

  public function login(Request $request)
  {

    $validator = Validator::make($request->all(), [
      'email' => 'required|email|max:255',
      'password' => 'required|string',
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }
    try {
      $user = User::where('email', $request->input('email'))->first();

      if (is_null($user)) {
        return $this->errorResponse('No account found, please create an account', 404);
      } else {
        if ($user->accountstatus != 1) {
          return $this->errorResponse('Your account is inactive, please contact an admin', 401);
        }
      }

      $credentials = ['email' => $request->input('email'), 'password' => $request->input('password')];
      try {
        if (!$token = JWTAuth::attempt($credentials)) {
          return $this->errorResponse('Invalid Credentials!', 401);
        }
        // dd($token);

      } catch (JWTException $e) {
        return $this->errorResponse('Could not create token', 500);
      }

      $customer = User::where('email', $request->input('email'))->first();
      if (is_null($customer->merchant_code) && $customer->account_type != "Client") {
        $customer->update(['merchant_code' => generateShortUniqueID($customer->email, $customer->id)]);
      }
      $userInfo = new UserResource($customer);
      $token = $this->respondWithToken($token);
      event(new Login($user));
      return response()->json(compact('token', 'userInfo'));
    } catch (Exception $e) {
      Logger::info('Login Error', [$e->getMessage() . ' - ' . $e->__toString()]);
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
    }
  }


  public function resetPassword(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'password' => 'required|confirmed|string|min:8',
      'password_confirmation' => 'required|string|min:8',
      'token' => 'required|string',
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }

    try {
      // Validate the token
      $tokenData = DB::table('password_resets')
        ->where('token', $request->input('token'))->first();
      // Respond if the token is invalid
      if (!$tokenData) {
        return $this->errorResponse('Invalid token supplied', 401);
      }
      //find the user with the email
      $user = User::where('email', $tokenData->email)->first();
      // Respond if no user is found
      if (!$user) {
        return $this->errorResponse('User not found', 404);
      }

      //new password
      $password = $request->input('password');

      //Hash and update the new password
      $user->password = Hash::make($password);
      $user->update();

      //Delete the token
      DB::table('password_resets')->where('email', $user->email)
        ->delete();

      //if all goes well....
      return response()->json(["ResponseStatus" => "Successful", 'Detail' => 'Your password reset was successful.', 'message' => 'Your password reset was successful.', "ResponseCode" => 201], 201);
    } catch (Exception $e) {
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
    }
  }


  public function forgotPassword(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'email' => 'required|email|max:255',
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }
    try {
      $user = User::where('email', $request['email'])->first();
      if (!is_null($user)) {
        $token = Str::random(30);
        //Create Password Reset Token
        DB::table('password_resets')->insert([
          'email' => $request['email'],
          'token' => $token,
          'created_at' => Carbon::now()
        ]);

        $this->Mailer->sendPasswordResetEmail($user, $token);
        return response()->json(["ResponseStatus" => "Successful", 'Detail' => 'A reset link has been sent to your email address.', 'message' => 'A reset link has been sent to your email address.', "ResponseCode" => 201], 201);
      }
      return $this->errorResponse('User not found', 404);
    } catch (Exception $e) {
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
    }
  }

  public function forgotPasswordByPhoneNo(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'phone' => 'required|string',
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }
    try {
      $user = User::where('phone', $request['phone'])->first();
      if (!is_null($user)) {
        $otp = rand(100000, 999999);

        //Create Password Reset Token
        DB::table('password_resets')->insert([
          'email' => $request['phone'],
          'token' => $otp,
          'created_at' => Carbon::now()
        ]);

        $message = "Your Combs and Clippers OTP code is " . $otp;
        $otpSent = $this->sendOTP($request['phone'], $message);
        return response()->json(["ResponseStatus" => "Successful", 'Detail' => 'A reset OTP has been sent to your phone.', 'message' => 'A reset OTP has been sent to your phone.', "ResponseCode" => 201], 201);
      }
      return $this->errorResponse('User not found', 404);
    } catch (Exception $e) {
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
    }
  }

  public function resetPasswordByOTP(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'password' => 'required|confirmed|string|min:8',
      'password_confirmation' => 'required|string|min:8',
      'otp' => 'required|string',
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }

    try {
      // Validate the token
      $tokenData = DB::table('password_resets')
        ->where('token', $request->input('otp'))->first();
      // Respond if the token is invalid
      if (!$tokenData) {
        return $this->errorResponse('Invalid token supplied', 401);
      }
      //find the user with the email
      $user = User::where('phone', $tokenData->email)->first();
      // Respond if no user is found
      if (!$user) {
        return $this->errorResponse('User not found', 404);
      }

      //new password
      $password = $request->input('password');

      //Hash and update the new password
      $user->password = Hash::make($password);
      $user->update();

      //Delete the token
      DB::table('password_resets')->where('email', $user->phone)
        ->delete();

      //if all goes well....
      return response()->json(["ResponseStatus" => "Successful", 'Detail' => 'Your password reset was successful.', 'message' => 'Your password reset was successful.', "ResponseCode" => 201], 201);
    } catch (Exception $e) {
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
    }
  }
  /**
   * Log out
   * Invalidate the token, so user cannot use it anymore
   * They have to relogin to get a new token
   *
   * @param Request $request
   */
  public function logout(Request $request)
  {
    $validator = Validator::make($request->all(), ['token' => 'required']);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }
    $user = User::find($this->getAuthID($request));
    try {
      JWTAuth::invalidate($request->input('token'));
      event(new Logout('api', $user));
      return response()->json([
        'status' => 'success',
        'mmessage' => 'You have successfully logged out.'
      ], 201);
    } catch (JWTException $e) {
      return $this->errorResponse('Failed to logout, please try again', 400);
    }
  }


  public function refreshToken(Request $request)
  {
    try {
      $token = JWTAuth::getToken();
      if (!$token) {
        return $this->errorResponse('Token not provided', 400);
      }

      $token = JWTAuth::refresh($token);
      $token = $this->respondWithToken($token);
      return response()->json(compact('token'));
    } catch (TokenInvalidException $e) {
      $message = $e->__toString();
      return $this->errorResponse($message, 401);
    }
  }


  public function loginWithCode(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'provider' => 'required|string',
      'code' => 'required|string'

    ]);


    if ($validator->fails()) {
      return $this->validationError($validator);
    }

    $client = new Client();

    if ($request['provider'] == 'instagram') {
      $appId = config('services.instagram.client_id');
      $secret = config('services.instagram.client_secret');
      $redirectUri = config('services.instagram.redirect');
      $code = $request['code'];
      // Get access token
      $response = $client->request('POST', 'https://api.instagram.com/oauth/access_token', [
        'form_params' => [
          'app_id' => $appId,
          'app_secret' => $secret,
          'grant_type' => 'authorization_code',
          'redirect_uri' => $redirectUri,
          'code' => $code,
        ]
      ]);

      if ($response->getStatusCode() != 200) {
        return $this->errorResponse('Unauthorized access to instagram account', 401);
      }

      $content = $response->getBody()->getContents();
      $content = json_decode($content);

      $accessToken = $content->access_token;
      $userId = $content->user_id;

      // Get user info
      //$response = $client->request('GET', "https://graph.instagram.com/me?fields=id,username,account_type&access_token={$accessToken}");
      //https://graph.facebook.com/v7.0/me/accounts?access_token={access-token}
      $response = $client->request('GET', "https://graph.facebook.com/{$userId}/accounts?access_token={$accessToken}");
      if ($response->getStatusCode() != 200) {
        return $this->errorResponse('Unauthorized access', 401);
      }

      $content = $response->getBody()->getContents();
      $content = json_decode($content);
      $name = $content->name;
      $id = $content->id;
      $email = $content->email;
      $nickname = $content->nickname;
      $avatar = $content->avatar_original;
      $token = $content->token;

      $socialProvider = SocialProvider::where('provider_id', $id)->first();
      if (!$socialProvider) {
        //create a new user and provider
        $name_arr = explode(" ", $name);
        $user = User::firstOrCreate(
          ['email' => !is_null($email) ? $email : $id],
          [
            'name' => $name,
            'firstName' => isset($name_arr[0]) ? $name_arr[0] : null,
            'lastName' => isset($name_arr[1]) ? $name_arr[1] : null,
            'account_type' => 'Merchant',
            'referral_code' => generateReferralCode()
          ]
          //['phone_no' => $socialUser->getId()]

        );
        $user->update(['merchant_code' => generateShortUniqueID($user->email, $user->id)]);
        $user->socialProviders()->create(
          ['provider_id' => $id, 'provider' => $request['provider'], 'nickname' => $nickname, 'avatar' => $avatar,]
        );
      } else {
        $user = $socialProvider->user;
      }

      $customer = $user->userInfo;
      $token = $this->respondWithToken(JWTAuth::fromUser($user));

      $userInfo = new UserResource($customer);

      return response()->json(compact('userInfo', 'token'), 201);
    } elseif ($request['provider'] == 'facebook') {

      $socialUser = Socialite::driver($request['provider'])->stateless()->user();

      $access_token = $socialUser->token;
      $user_id = $socialUser->id;

      $name = $socialUser->getName();
      $id = $socialUser->getId();
      $email = $socialUser->getEmail();
      $nickname = $socialUser->getNickname();
      $avatar = $socialUser->getAvatar();
      $token = $socialUser->token;

      $socialProvider = SocialProvider::where('provider_id', $id)->first();
      if (!$socialProvider) {
        $name_arr = explode(" ", $name);

        //create a new user and provider
        $user = User::where('email', $email)->first();
        if (is_null($user)) {
          //create a new user and provider
          $user = User::create([
            'email' => !is_null($email) ? $email : $id,
            'name' => $name,
            'firstName' => isset($name_arr[0]) ? $name_arr[0] : null,
            'lastName' => isset($name_arr[1]) ? $name_arr[1] : null,
            'account_type' => 'Merchant',
            'referral_code' => generateReferralCode()
          ]);
        }
        $user->update(['merchant_code' => generateShortUniqueID($user->email, $user->id)]);
        $user->socialProviders()->create(
          ['provider_id' => $id, 'provider' => $request['provider'], 'nickname' => $nickname, 'avatar' => $avatar,]
        );


        // $this->peppUtil->send_userDetails_to_engage($user);
      } else {
        $user = $socialProvider->user;
      }

      $token = $this->respondWithToken(JWTAuth::fromUser($user));

      $userInfo = new UserResource($user);

      return response()->json(compact('userInfo', 'token'), 201);
    } elseif ($request['provider'] == 'google') {


      $socialUser = Socialite::driver($request['provider'])->stateless()->user();

      $name = $socialUser->getName();
      $id = $socialUser->getId();
      $email = $socialUser->getEmail();
      $nickname = $socialUser->getNickname();
      $avatar = $socialUser->getAvatar();
      $token = $socialUser->token;

      $socialProvider = SocialProvider::where('provider_id', $id)->first();
      if (!$socialProvider) {
        $name_arr = explode(" ", $name);
        //create a new user and provider
        $user = User::firstOrCreate(
          ['email' => !is_null($email) ? $email : $id],
          [
            'name' => $name,
            'firstName' => isset($name_arr[0]) ? $name_arr[0] : null,
            'lastName' => isset($name_arr[1]) ? $name_arr[1] : null,
            'account_type' => 'Merchant',
            'referral_code' => generateReferralCode()
          ]
          //['phone_no' => $socialUser->getId()]

        );
        $user->update(['merchant_code' => generateShortUniqueID($user->email, $user->id)]);
        $user->socialProviders()->create(
          ['provider_id' => $id, 'provider' => $request['provider'], 'nickname' => $nickname, 'avatar' => $avatar,]
        );

        //$this->peppUtil->send_userDetails_to_engage($user);
      } else {
        $user = $socialProvider->user;
      }
      $token = $this->respondWithToken(JWTAuth::fromUser($user));

      $userInfo = new UserResource($user);

      return response()->json(compact('userInfo', 'token'), 201);
    } else {
      return $this->errorResponse('Unknown provider', 400);
    }
    return $this->errorResponse('Invalid request', 400);
  }


  public function loginFromSocial(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'provider' => 'required|string',
      'email' => 'nullable|string',
      'name' => 'required|string',
      'phone' => 'nullable|numeric',
      'userProviderID' => 'required|string',
      'accountType' => 'required|string|in:Merchant,Buyer',
    ]);


    if ($validator->fails()) {
      return $this->validationError($validator);
    }


    //$client = new Client();
    $name = $request->input('name');
    $userProviderID = $request->input('userProviderID');
    $provider = $request->input('provider');
    $email = $request->input('email');
    $accountType = $request->input('accountType');
    $phone = $request->input('phone');
    $name_arr = explode(" ", $name);

    if ($provider == 'facebook') {

      $socialProvider = SocialProvider::where('provider_id', $userProviderID)->first();
      if (!$socialProvider) {

        //$name_arr = explode (" ", $name); 
        $user = User::where('email', $email)->first();
        if (is_null($user)) {
          //create a new user and provider
          $user = User::create([
            'email' => !is_null($email) ? $email : $userProviderID,
            'name' => $name,
            'firstName' => isset($name_arr[0]) ? $name_arr[0] : null,
            'lastName' => isset($name_arr[1]) ? $name_arr[1] : null,
            'account_type' => $accountType,
            'phone' => $phone,
            'referral_code' => generateReferralCode()
          ]);

          $user->update(['merchant_code' => generateShortUniqueID($user->email, $user->id)]);
        }

        $user->socialProviders()->create(
          ['provider_id' => $userProviderID, 'provider' => $provider]
        );
      } else {
        $user = $socialProvider->user;
      }


      $token = $this->respondWithToken(JWTAuth::fromUser($user));

      $userInfo = new UserResource($user);

      return response()->json(compact('userInfo', 'token'), 201);
    } elseif ($provider == 'google') {

      $socialProvider = SocialProvider::where('provider_id', $userProviderID)->first();
      if (!$socialProvider) {
        //create a new user and provider
        //$name_arr = explode (" ", $name); 
        $user = User::where('email', $email)->first();
        if (is_null($user)) {
          //create a new user and provider
          $user = User::create([
            'email' => !is_null($email) ? $email : $userProviderID,
            'name' => $name,
            'firstName' => isset($name_arr[0]) ? $name_arr[0] : null,
            'lastName' => isset($name_arr[1]) ? $name_arr[1] : null,
            'account_type' => $accountType,
            'phone' => $phone,
            'referral_code' => generateReferralCode()
          ]);

          $user->update(['merchant_code' => generateShortUniqueID($user->email, $user->id)]);
        }

        $user->socialProviders()->create(
          ['provider_id' => $userProviderID, 'provider' => $provider,]
        );

        //$this->peppUtil->send_userDetails_to_engage($user);
      } else {
        $user = $socialProvider->user;
      }



      $token = $this->respondWithToken(JWTAuth::fromUser($user));

      $userInfo = new UserResource($user);

      return response()->json(compact('userInfo', 'token'), 201);
    } else {
      return $this->errorResponse('Unknown provider', 400);
    }
    return $this->errorResponse('Invalid request', 400);
  }

  public function getMyCustomerActivities(Request $request)
  {
    $merchantID = $this->getAuthID($request);
    try {
      $merchant = User::find($merchantID);
      if (!is_null($merchant) && $merchant->account_type == 'Merchant') {
        $merchant_id = $merchant->id;
        $activities = Activity::where('merchant_id', $merchant_id)->latest('id')->paginate($this->perPage);
        $activities = $this->addMeta(ActivityResource::collection($activities));

        return response()->json(compact('activities'), 200);
      }
      return $this->errorResponse('User not found or not a merchant', 404);
    } catch (Exception $e) {
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
    }
  }

  public function joinWaitlist(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'name' => 'nullable|string|max:255',
      'email' => 'required|email|max:255|unique:waitlists',
      'phone' => 'nullable|numeric|unique:waitlists',
      'referral_code' => 'nullable|string|max:25',
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }
    try {
      $waitlist = Waitlist::create([
        'name' => $request->input('name'),
        'email' => $request->input('email'),
        'phone' => $request->input('phone'),
        'referral_code' => generateReferralCode()
      ]);


      if ($request->filled('referral_code')) {
        $referrer = Waitlist::where('referral_code', $request->input('referral_code'))->first();
        if (!is_null($referrer)) {
          $referrer_id = $referrer->id;
          $waitlist->update(['referred_by' => $referrer_id]);
        }
      }

      $this->Mailer->sendJoinWaitlistEmail($waitlist);
      Util::sendWaitlistToEngage($waitlist);
      $event = json_encode(['event' => 'Join waitlist']);
      Util::sendEventToEngage($waitlist, $event);

      $waitlist = new WaitlistResource($waitlist);

      return response()->json(compact('waitlist'), 201);
    } catch (Exception $e) {
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
    }
  }

  public function inviteToWaitlist(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'email' => 'required|email|max:255|unique:waitlists',
      'referral_code' => 'required|string|max:25',
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }
    try {
      $email = $request->email;
      $referral_code = $request->referral_code;
      $referrer = Waitlist::where('referral_code', $referral_code)->value('email');
      $this->Mailer->sendInviteToJoinWaitlistEmail($email, $referrer, $referral_code);
      return response()->json(['status' => 'success', 'message' => "An invite has been sent to {$email} successfully"], 200);
    } catch (Exception $e) {
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
    }
  }

  public function sendLoginOTP(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'email' => 'required|email',
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }
    try {
      $otp = mt_rand(10000, 99999);
      //find the user with the email
      $user = User::where('email', $request->email)->first();
      // Respond if no user is found
      if (!$user) {
        return $this->errorResponse('User not found', 404);
      }

      $user->update([
        'login_otp' => $otp,
        'login_otp_expires_at' => now()->addMinutes(10),
      ]);
      $this->Mailer->sendLoginCodeEmail($user);
      return response()->json(["ResponseStatus" => "successful", "ResponseCode" => 200, "ResponseMessage" => "An OTP login code has been sent to your email.", "message" => "An OTP login code has been sent to your email."], 200);
    } catch (Exception $e) {
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
    }
  }

  public function loginWithOTP(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'otp_code' => 'required|string',
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }
    try {
      $user = User::where('login_otp', $request->input('otp_code'))->first();

      if (is_null($user)) {
        return $this->errorResponse('Invalid OTP code supplied!', 401);
      } else {
        if ($user->accountstatus != 1) {
          return $this->errorResponse('Your account is inactive, please contact an admin', 401);
        }
      }
      if ($user->login_otp_expires_at <= now()) {
        return $this->errorResponse('OTP code has expired!', 401);
      }

      $token = $this->respondWithToken(JWTAuth::fromUser($user));
      $userInfo = new UserResource($user);
      event(new Login($user));
      return response()->json(compact('token', 'userInfo'));
    } catch (Exception $e) {
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
    }
  }


  // public function uploadWorkImages(Request $request)
  // {
  //   $validator = Validator::make($request->all(), [
  //     'work_done_images' => 'required|array',
  //     'work_done_images.*' => 'required|image|mimes:jpeg,jpg,png,gif,bmp|max:5140',
  //   ]);

  //   if ($validator->fails()) {
  //     return $this->validationError($validator);
  //   }

  //   try {
  //     $merchantId = $this->getAuthID($request);
  //     $store = Store::where('merchant_id', $merchantId)->first();

  //     if (!$store) {
  //       return response()->json([
  //         "ResponseStatus" => "Unsuccessful",
  //         "ResponseCode" => 404,
  //         "Detail" => "Store not found.",
  //         "ResponseMessage" => "We could not find the store for this Merchant.",
  //       ], 404);
  //     }

  //     $storeId = $store->id;
  //     $images = $request->file('work_done_images');
  //     $storedImages = [];


  //     DB::beginTransaction();

  //     foreach ($images as $image) {
  //       $extension = $image->getClientOriginalExtension();
  //       $imageName = 'store_' . $storeId . '_' . time() . '_' . Str::uuid() . '.' . $extension;


  //       $path = $image->storeAs('workdone_images', $imageName, 'public');


  //       $storedImage = StoreWorkdoneImage::create([
  //         'stores_id' => $storeId,
  //         'image_url' => $path,
  //       ]);

  //       $this->updateBoothProgress($merchantId, $store->id, "setup_portfolio");


  //       $storedImages[] = [
  //         'id' => $storedImage->id,
  //         'url' => asset('storage/' . $storedImage->image_url),
  //       ];
  //     }


  //     DB::commit();

  //     return response()->json([
  //       'message' => 'Images uploaded successfully.',
  //       'data' => $storedImages,
  //     ]);
  //   } catch (Exception $e) {

  //     DB::rollBack();


  //     foreach ($storedImages as $storedImage) {
  //       Storage::disk('public')->delete($storedImage['url']);
  //     }

  //     return response()->json([
  //       "ResponseStatus" => "Unsuccessful",
  //       "ResponseCode" => 500,
  //       'Detail' => $e->getMessage(),
  //       'message' => 'Something went wrong. Files were not saved.',
  //       "ResponseMessage" => 'Something went wrong.',
  //     ], 500);
  //   }
  // }

  // public function getWorkImages(Request $request)
  // {




  //   try {
  //     $merchantId = $this->getAuthID($request);
  //     $store = Store::where('merchant_id', $merchantId)->first();

  //     $storeId = $store->id;


  //     if (!$store) {
  //       return response()->json([
  //         'ResponseStatus' => 'Unsuccessful',
  //         'ResponseCode' => 404,
  //         'Detail' => 'Store not found',
  //         'ResponseMessage' => 'We could not find the store with the provided ID.',
  //       ], 404);
  //     }


  //     $images = StoreWorkdoneImage::where('stores_id', $storeId)->get();


  //     if ($images->isEmpty()) {
  //       return response()->json([
  //         'ResponseStatus' => 'Unsuccessful',
  //         'ResponseCode' => 404,
  //         'Detail' => 'No images found for the store',
  //         'ResponseMessage' => 'There are no images uploaded for this store.',
  //       ], 404);
  //     }


  //     $imageData = $images->map(function ($image) {
  //       return [
  //         'id' => $image->id,
  //         'url' => asset('storage/' . $image->image_url),
  //       ];
  //     });

  //     return response()->json([
  //       'ResponseStatus' => 'Successful',
  //       'ResponseCode' => 200,
  //       'ResponseMessage' => 'Images retrieved successfully.',
  //       'data' => $imageData,
  //     ], 200);
  //   } catch (Exception $e) {
  //     return response()->json([
  //       'ResponseStatus' => 'Unsuccessful',
  //       'ResponseCode' => 500,
  //       'Detail' => $e->getMessage(),
  //       'ResponseMessage' => 'Something went wrong.',
  //     ], 500);
  //   }
  // }

  public function uploadWorkImages(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'work_done_images' => 'required|array',
      'work_done_images.*' => 'required|image|mimes:jpeg,jpg,png,gif,bmp|max:5140',
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }

    try {
      $merchantId = $this->getAuthID($request);
      $user = User::find($merchantId);
      $store = Store::where('merchant_id', $merchantId)->first();

      if (!$user) {
        return response()->json([
          "ResponseStatus" => "Unsuccessful",
          "ResponseCode" => 404,
          "Detail" => "User not found.",
          "ResponseMessage" => "We could not find the user.",
        ], 404);
      }

      $storeId = $store->id ?? null; // Store ID if available, otherwise null
      $images = $request->file('work_done_images');
      $storedImages = [];

      DB::beginTransaction();

      foreach ($images as $image) {
        $extension = $image->getClientOriginalExtension();
        $imageName = 'user_' . $merchantId . '_store_' . ($storeId ?? 'none') . '_' . time() . '_' . Str::uuid() . '.' . $extension;
        $path = $image->storeAs('workdone_images', $imageName, 'public');

        $storedImage = StoreWorkdoneImage::create([
          'user_id' => $storeId ? null : $merchantId, // Store under user_id if no store exists
          'stores_id' => $storeId,
          'image_url' => $path,
        ]);

        // 🔥 Always update booth progress, regardless of store existence
        $this->updateBoothProgress($merchantId, $storeId, "setup_portfolio");

        $storedImages[] = [
          'id' => $storedImage->id,
          'url' => asset('storage/' . $storedImage->image_url),
        ];
      }

      DB::commit();

      return response()->json([
        'ResponseStatus' => 'Successful',
        'ResponseMessage' => 'Images uploaded successfully.',
        'data' => $storedImages,
      ]);
    } catch (Exception $e) {
      DB::rollBack();

      foreach ($storedImages as $storedImage) {
        Storage::disk('public')->delete(str_replace(asset('storage/'), '', $storedImage['url']));
      }

      return response()->json([
        "ResponseStatus" => "Unsuccessful",
        "ResponseCode" => 500,
        'Detail' => $e->getMessage(),
        'ResponseMessage' => 'Something went wrong.',
      ], 500);
    }
  }

  public function getWorkImages(Request $request)
  {
    try {
      $merchantId = $this->getAuthID($request);
      $user = User::find($merchantId);
      $store = Store::where('merchant_id', $merchantId)->first();

      if (!$user) {
        return response()->json([
          'ResponseStatus' => 'Unsuccessful',
          'ResponseCode' => 404,
          'Detail' => 'User not found.',
          'ResponseMessage' => 'We could not find the user with the provided ID.',
        ], 404);
      }

      $storeId = $store->id ?? null;

      // Retrieve images for the store or for the user if no store exists
      $images = StoreWorkdoneImage::where(function ($query) use ($storeId, $merchantId) {
        if ($storeId) {
          $query->where('stores_id', $storeId);
        } else {
          $query->where('user_id', $merchantId);
        }
      })->get();

      if ($images->isEmpty()) {
        return response()->json([
          'ResponseStatus' => 'Unsuccessful',
          'ResponseCode' => 404,
          'Detail' => 'No images found',
          'ResponseMessage' => 'There are no images uploaded for this user or store.',
        ], 404);
      }

      $imageData = $images->map(function ($image) {
        return [
          'id' => $image->id,
          'url' => asset('storage/' . $image->image_url),
        ];
      });

      return response()->json([
        'ResponseStatus' => 'Successful',
        'ResponseCode' => 200,
        'ResponseMessage' => 'Images retrieved successfully.',
        'data' => $imageData,
      ], 200);
    } catch (Exception $e) {
      return response()->json([
        'ResponseStatus' => 'Unsuccessful',
        'ResponseCode' => 500,
        'Detail' => $e->getMessage(),
        'ResponseMessage' => 'Something went wrong.',
      ], 500);
    }
  }

  public function deleteWorkImages(Request $request)
  {

    $validator = Validator::make($request->all(), [
      'image_ids' => 'required'
    ]);

    if ($validator->fails()) {
      return response()->json([
        "ResponseStatus" => "Unsuccessful",
        "ResponseCode" => 400,
        "ResponseMessage" => "Validation Error",
        "errors" => $validator->errors()
      ], 400);
    }


    $imageIds = is_array($request->image_ids) ? $request->image_ids : [$request->image_ids];


    $validator = Validator::make(['image_ids' => $imageIds], [
      'image_ids.*' => 'required|numeric|exists:store_workdone_images,id'
    ]);

    if ($validator->fails()) {
      return response()->json([
        "ResponseStatus" => "Unsuccessful",
        "ResponseCode" => 400,
        "ResponseMessage" => "Validation Error",
        "errors" => $validator->errors()
      ], 400);
    }

    try {
      $merchantId = $this->getAuthID($request);
      $user = User::find($merchantId);

      if (!$user) {
        return response()->json([
          "ResponseStatus" => "Unsuccessful",
          "ResponseCode" => 404,
          "Detail" => "User not found.",
          "ResponseMessage" => "We could not find the user.",
        ], 404);
      }

      $store = Store::where('merchant_id', $merchantId)->first();
      $storeId = $store->id ?? null;

      // Retrieve images for the store or for the user directly
      $images = StoreWorkdoneImage::whereIn('id', $imageIds)
        ->where(function ($query) use ($storeId, $merchantId) {
          $query->where(function ($q) use ($storeId) {
            if ($storeId) {
              $q->where('stores_id', $storeId);
            }
          })
            ->orWhere(function ($q) use ($merchantId) {
              $q->where('user_id', $merchantId);
            });
        })->get();

      if ($images->isEmpty()) {
        return response()->json([
          'ResponseStatus' => 'Unsuccessful',
          'ResponseCode' => 404,
          'Detail' => 'No images found for the specified ID(s).',
          'ResponseMessage' => 'The specified images do not exist or are not accessible by the user.',
        ], 404);
      }

      DB::beginTransaction();

      $imagePaths = $images->pluck('image_url')->toArray();

      StoreWorkdoneImage::whereIn('id', $images->pluck('id'))->delete();

      Storage::disk('public')->delete($imagePaths);

      $this->updateBoothProgress($merchantId, $storeId, "setup_portfolio");

      DB::commit();

      return response()->json([
        'ResponseStatus' => 'Successful',
        'ResponseCode' => 200,
        'ResponseMessage' => 'Work image(s) deleted successfully.',
      ], 200);
    } catch (Exception $e) {
      DB::rollBack();
      return response()->json([
        "ResponseStatus" => "Unsuccessful",
        "ResponseCode" => 500,
        "Detail" => $e->getMessage(),
        "ResponseMessage" => 'Something went wrong. Unable to delete work image(s).'
      ], 500);
    }
  }

  public function createBio(Request $request)
  {
    // Validate input fields
    $validate = Validator::make($request->all(), [
      'profile_pic' => 'nullable|image|mimes:jpeg,jpg,png,gif,bmp|max:5140',
      'bio' => 'nullable|string',
      'phone' => 'nullable|numeric',
    ]);

    if ($validate->fails()) {
      // Enhanced error handling with specific rejection reasons
      $errors = $validate->errors();
      $detailedErrors = [];
      
      if ($errors->has('profile_pic')) {
        $profilePicErrors = $errors->get('profile_pic');
        foreach ($profilePicErrors as $error) {
          if (strpos($error, 'max') !== false) {
            $detailedErrors[] = 'Profile picture file size exceeds 5MB limit. Please upload a smaller image.';
          } elseif (strpos($error, 'mimes') !== false) {
            $detailedErrors[] = 'Profile picture must be a valid image file (JPEG, JPG, PNG, GIF, or BMP).';
          } elseif (strpos($error, 'image') !== false) {
            $detailedErrors[] = 'Profile picture must be a valid image file.';
          } else {
            $detailedErrors[] = $error;
          }
        }
      }
      
      if ($errors->has('phone')) {
        $detailedErrors[] = 'Phone number must contain only numeric characters.';
      }
      
      if ($errors->has('bio')) {
        $detailedErrors[] = 'Bio must be a valid text string.';
      }
      
      return response()->json([
        "ResponseCode" => 422,
        "ResponseStatus" => "Unsuccessful", 
        "ResponseMessage" => "Request rejected: " . implode(' ', $detailedErrors),
        'Detail' => [
          'validation_errors' => $errors->all(),
          'specific_issues' => $detailedErrors
        ]
      ], 422);
    }

    try {

      $user = $this->getAuthUser($request);
      if (!$user) {
        return response()->json([
          'ResponseStatus' => 'Unsuccessful',
          'ResponseCode' => 404,
          'Detail' => 'User not found.',
          'ResponseMessage' => 'We could not find the user',
        ], 404);
      }


      $updateData = [];


      if ($request->has('bio')) {
        $updateData['bio'] = $request->bio;
      }

      if ($request->has('phone')) {
        $updateData['phone'] = $request->phone;
      }

      if ($request->hasFile('profile_pic')) {
        $file = $request->file('profile_pic');

        //$fileName = $user->id . '.' . $file->getClientOriginalExtension();
        $fileName = time() . '.' . $file->getClientOriginalExtension();


        $folderPath = 'profile_pics/' . $user->id;


        $path = $file->storeAs($folderPath, $fileName, 'public');


        $updateData['profile_image_link'] = $path;
      }

      if (!empty($updateData)) {
        $user->update($updateData);
      }


      $profileImageUrl = $updateData['profile_image_link']
        ? asset('storage/' . $updateData['profile_image_link'])
        : asset('storage/' . $user->profile_image_link);

      $store = Store::where('merchant_id', $user->id)->first();
      if (!is_null($store)) {
        $this->updateBoothProgress($user->id, $store->id, "create_bio");
      }
      $user = new UserResource($user);
      return response()->json([
        'ResponseStatus' => 'Successful',
        'ResponseCode' => 200,
        'ResponseMessage' => 'Bio updated successfully.',
        'Data' => [
          'user' => $user,
          'profile_image_link' => $profileImageUrl,
        ],
      ], 200);
    } catch (Exception $e) {
      return response()->json([
        'ResponseStatus' => 'Unsuccessful',
        'ResponseCode' => 500,
        'Detail' => $e->getMessage(),
        'ResponseMessage' => 'Something went wrong.',
      ], 500);
    }
  }


  public function updateCoverPhoto(Request $request)
  {
    // Validate input fields
    $validate = Validator::make($request->all(), [
      'cover_image' => 'required|image|mimes:jpeg,jpg,png,gif,bmp|max:5140'
    ]);

    if ($validate->fails()) {
      return $this->validationError($validate);
    }

    try {

      $user = $this->getAuthUser($request);


      $updateData = [];

      if ($request->hasFile('cover_image')) {
        $file = $request->file('cover_image');

        $fileName = time() . '.' . $file->getClientOriginalExtension();

        $folderPath = 'cover_images/' . $user->id;

        $path = $file->storeAs($folderPath, $fileName, 'public');


        $updateData['cover_image_link'] = $path;
      }

      if (!empty($updateData)) {
        $user->update($updateData);
      }


      $profileImageUrl = $updateData['cover_image_link']
        ? asset('storage/' . $updateData['cover_image_link'])
        : $user->cover_image_link;

      $user = new UserResource($user);
      return response()->json([
        'ResponseStatus' => 'Successful',
        'ResponseCode' => 200,
        'ResponseMessage' => 'Cover image updated successfully.',
        'Data' => [
          'user' => $user,
          'cover_image_link' => $profileImageUrl,
        ],
      ], 200);
    } catch (Exception $e) {
      return response()->json([
        'ResponseStatus' => 'Unsuccessful',
        'ResponseCode' => 500,
        'Detail' => $e->getMessage(),
        'ResponseMessage' => 'Something went wrong.',
      ], 500);
    }
  }



  // public function getSetupProgress(Request $request)
  // {
  //   try {

  //     $merchantId = $this->getAuthID($request);


  //     $store = Store::where('merchant_id', $merchantId)->first();


  //     if (!$store) {
  //       return response()->json([
  //         "ResponseStatus" => "Unsuccessful",
  //         "ResponseCode" => 404,
  //         "Detail" => "Store not found.",
  //         "ResponseMessage" => "We could not find the store for this Merchant.",
  //       ], 404);
  //     }


  //     $progress = [
  //       "grow_progress" => $this->calculateGrowProgress($merchantId, $store->id),
  //       "boot_progress" => $this->calculateBoothProgress($merchantId, $store->id)
  //     ];


  //     return response()->json([
  //       "ResponseStatus" => "successful",
  //       "ResponseCode" => 200,
  //       "Detail" => "Setup progress retrieved successfully.",
  //       "ResponseMessage" => "Setup progress have been fetched successfully.",
  //       "data" => $progress
  //     ], 200);
  //   } catch (Exception $e) {

  //     return response()->json([
  //       "ResponseStatus" => "Unsuccessful",
  //       "ResponseCode" => 500,
  //       "Detail" => $e->getMessage(),
  //       "ResponseMessage" => "Something went wrong.",
  //     ], 500);
  //   }
  // }

  public function getSetupProgress(Request $request)
  {
    try {
      $merchantId = $this->getAuthID($request);
      $user = User::find($merchantId);

      if (!$user) {
        return response()->json([
          "ResponseStatus" => "Unsuccessful",
          "ResponseCode" => 404,
          "Detail" => "User not found.",
          "ResponseMessage" => "We could not find the user.",
        ], 404);
      }

      $store = Store::where('merchant_id', $merchantId)->first();
      $storeId = $store->id ?? null;


      $progress = [
        "grow_progress" => $this->calculateGrowProgress($merchantId, $storeId) ?: 0,
        "boot_progress" => $this->calculateBoothProgress($merchantId, $storeId) ?: 0,
      ];

      return response()->json([
        "ResponseStatus" => "Successful",
        "ResponseCode" => 200,
        "Detail" => "Setup progress retrieved successfully.",
        "ResponseMessage" => "Setup progress has been fetched successfully.",
        "data" => $progress
      ], 200);
    } catch (Exception $e) {
      return response()->json([
        "ResponseStatus" => "Unsuccessful",
        "ResponseCode" => 500,
        "Detail" => $e->getMessage(),
        "ResponseMessage" => "Something went wrong.",
      ], 500);
    }
  }


  public function leaveAReview(Request $request)
  {
    $validate = Validator::make(
      $request->all(),
      [
        "reviewText" => "required|string|max:150",
        "rating" => "required|string",
        "merchantCode" => "required|string"
      ]
    );

    if ($validate->fails()) {
      return $this->validationError($validate);
    }

    try {
      $userId = $this->getAuthID($request);
      $merchant = User::where("merchant_code", $request->merchantCode)->first();
      $merchantId = $merchant->id;

      if (!$merchant) {
        return response()->json([
          "ResponseStatus" => "Unsuccessful",
          "ResponseCode" => 404,
          "Detail" => "Merchant not found",
          "ResponseMessage" => "Invalid merchant code"
        ], 404);
      }

      $newReview = Review::create([
        "user_id" => $userId,
        "merchant_id" => $merchantId,
        "review_text" => $request->reviewText,
        "rating" => $request->rating
      ]);

      if ($newReview) {
        return response()->json(["ResponseStatus" => "successful", 'Detail' => 'Review Made Succesfully.', "ResponseMessage" => 'Review has been submitted Sucessfully.', "ResponseCode" => 200], 200);
      }
    } catch (Exception $e) {
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
    }
  }

  public function uploadStyles(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'style_images' => 'required|array',
      'style_images.*' => 'required|image|mimes:jpeg,jpg,png,gif,bmp|max:5140',
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }

    try {
      $user = User::find($this->getAuthID($request));
      if (is_null($user)) {
        return $this->errorResponse('User not found', 404);
      }

      $images = $request->file('style_images');
      $styleImages = [];

      DB::beginTransaction();

      foreach ($images as $image) {
        $extension = $image->getClientOriginalExtension();
        $imageName = 'style_' . $user->id . '_' . time() . '_' . Str::uuid() . '.' . $extension;

        $path = $image->storeAs('style_images', $imageName, 'public');

        $storedImage = UserStyle::create([
          'user_id' => $user->id,
          'image_url' => $path,
        ]);

        $styleImages[] = [
          'id' => $storedImage->id,
          'url' => asset('storage/' . $storedImage->image_url),
        ];
      }

      DB::commit();
      return $this->successResponse("Styles uploaded successfully", 200, compact('styleImages'));
    } catch (Exception $e) {
      DB::rollBack();
      foreach ($styleImages as $styleImage) {
        Storage::disk('public')->delete($styleImage['url']);
      }
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong. Files were not saved.'], 500);
    }
  }

  public function getStyles(Request $request)
  {
    try {
      $user = User::find($this->getAuthID($request));
      if (is_null($user)) {
        return $this->errorResponse('User not found', 404);
      }

      $images = UserStyle::where('user_id', $user->id)->get();

      if ($images->isEmpty()) {
        $styleImages = $images;
        return $this->successResponse("Styles retrieved successfully.", 200, compact('styleImages'));
        //return $this->errorResponse('There are no styles uploaded for this user', 404);
      }

      $styleImages = $images->map(function ($image) {
        return [
          'id' => $image->id,
          'url' => asset('storage/' . $image->image_url),
        ];
      });
      return $this->successResponse("Styles retrieved successfully.", 200, compact('styleImages'));
    } catch (Exception $e) {
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong.'], 500);
    }
  }

  public function deleteStyles(Request $request)
  {

    $validator = Validator::make($request->all(), [
      'style_ids' => 'required'
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }


    $styleIds = is_array($request->style_ids) ? $request->style_ids : [$request->style_ids];


    $validator = Validator::make(['style_ids' => $styleIds], [
      'style_ids.*' => 'required|numeric|exists:user_styles,id'
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }

    try {
      $user = User::find($this->getAuthID($request));
      if (is_null($user)) {
        return $this->errorResponse('User not found', 404);
      }

      $styles = UserStyle::whereIn('id', $styleIds)
        ->where('user_id', $user->id)
        ->get();

      if ($styles->isEmpty()) {
        return $this->errorResponse('No style images found for the specified ID(s)', 404);
      }

      DB::beginTransaction();

      $imagePaths = $styles->pluck('image_url')->toArray();

      UserStyle::whereIn('id', $styles->pluck('id'))->delete();

      Storage::disk('public')->delete($imagePaths);

      DB::commit();
      return $this->successResponse("Style image(s) deleted successfully.", 200);
    } catch (Exception $e) {
      DB::rollBack();
      return response()->json([
        "ResponseStatus" => "Unsuccessful",
        "ResponseCode" => 500,
        "Detail" => $e->getMessage(),
        "ResponseMessage" => 'Something went wrong. Unable to delete style image(s).'
      ], 500);
    }
  }




  // Create and Update store for shop owners

  public function newCreateStore(Request $request)
  {
    $validator = Validator::make(
      $request->all(),
      [
        'store_name' => 'required|string|max:255|regex:/^[a-zA-Z]+[\w\s-]*$/',
        'store_category' => 'nullable|integer',
        'store_sub_category' => 'nullable|integer',
        'days_available' => 'required|array',
        'time_available' => 'required',
        'website' => 'nullable|string|max:255',
        'store_icon' => 'nullable|mimes:jpeg,jpg,png,gif,bmp|max:5120',
        'store_banner' => 'nullable|mimes:jpeg,jpg,png,gif,bmp|max:5120',
        'store_description' => 'nullable|string',
        'refund_allowed' => 'integer|nullable',
        'replacement_allowed' => 'integer|nullable',
        'store_state' => 'nullable|string|max:255',
        'store_city' => 'required|string|max:255',
        'store_postal_code' => 'required|string|max:255',
        'store_address' => 'required|string|max:255',
        'store_id' => 'nullable|integer',
        'store_type' => 'required|string'
      ]
    );

    if ($validator->fails()) {
      return $this->validationError($validator);
    }

    $merchantID = $this->getAuthID($request);
    try {
      $merchant = User::find($merchantID);
      if (is_null($merchant)) {
        return $this->errorResponse('User not found!', 404);
      }

      $store = $request->has('store_id') ? Store::find($request->input('store_id')) : null;

      if ($store) {

        $store->update([
          'store_name' => $request->input('store_name'),
          'store_type' => $request->input('store_type'),
          'store_category' => $request->input('store_category'),
          'store_sub_category' => $request->input('store_sub_category'),
          'website' => $request->input('website'),
          'store_description' => $request->input('store_description'),
          'days_available' => json_encode($request->input('days_available')),
          'time_available' => json_encode($request->input('time_available')),
          'refund_allowed' => $request->filled('refund_allowed') ? $request->input('refund_allowed') : 0,
          'replacement_allowed' => $request->filled('replacement_allowed') ? $request->input('replacement_allowed') : 0
        ]);
      } else {

        $storeCode = $this->generateUniqueStoreCode();


        $store = Store::create([
          'merchant_id' => $merchant->id,
          'store_name' => $request->input('store_name'),
          'store_type' => $request->input('store_type'),
          'store_category' => $request->input('store_category'),
          'store_sub_category' => $request->input('store_sub_category'),
          'store_code' => $storeCode,
          'website' => $request->input('website'),
          'store_description' => $request->input('store_description'),
          'days_available' => json_encode($request->input('days_available')),
          'time_available' => json_encode($request->input('time_available')),
          'approved' => 1,
          'refund_allowed' => $request->filled('refund_allowed') ? $request->input('refund_allowed') : 0,
          'replacement_allowed' => $request->filled('replacement_allowed') ? $request->input('replacement_allowed') : 0
        ]);
      }

      $this->updateBoothProgress($merchantID, $store->id, "add_schedule_location");

      if ($request->hasFile('store_icon')) {
        $imageArray = $this->imageUtil->saveImgArray($request->file('store_icon'), '/merchants/stores/icons/', $store->id, []);
        if (!is_null($imageArray)) {
          $icon = array_shift($imageArray);
          $store->update(['store_icon' => $icon]);
        }
      }

      if ($request->hasFile('store_banner')) {
        $imageArray = $this->imageUtil->saveImgArray($request->file('store_banner'), '/merchants/stores/banners/', $store->id, []);
        if (!is_null($imageArray)) {
          $banner = array_shift($imageArray);
          $store->update(['store_banner' => $banner]);
        }
      }

      //$country = Country::where('id', $request->country_id)->value('country');
      $country = $this->country;
      $state = $request->filled('store_state') ? $request->store_state . ', ' : '';
      $address = $request->input('store_address') . ', ' . $request->input('store_city') . ', ' . $state . $country;
      $store_address = StoreAddress::where(['email' => $merchant->email, 'merchant_id' => $merchant->id])->first();
      if (is_null($store_address)) {
        $store_address = StoreAddress::create(
          [
            'name' => $merchant->name,
            'email' => $merchant->email,
            'phone' => $merchant->phone,
            'merchant_id' => $merchant->id,
            'street' => $request->input('store_address'),
            'city' => $request->input('store_city'),
            'state' => $request->input('store_state'),
            'postal_code' => $request->input('store_postal_code'),
            'country' => $country,
            'address' => $address
          ]
        );
      }

      if (!is_null($store_address)) {
        $address_str = $store_address->address;
        $add_info = Util::validateAddressWithGoogle($merchant, $address_str);
        //return $add_info;
        if ($add_info['error'] == 0) {
          $store_address->update([
            'longitude' => $add_info['addressDetails']['longitude'],
            'latitude' => $add_info['addressDetails']['latitude'],
            'postal_code' => $add_info['addressDetails']['postal_code'],
            'zip' => $add_info['addressDetails']['postal_code'],
            'country' => $add_info['addressDetails']['country'],
            'formatted_address' => $add_info['addressDetails']['formatted_address'],
            'address_code' => generateAddressCode($merchant),
            'city' => $add_info['addressDetails']['city'],
            'city_code' => $add_info['addressDetails']['city_code'],
            'state' => $add_info['addressDetails']['state'],
            'state_code' => $add_info['addressDetails']['state_code'],
            'country_code' => $add_info['addressDetails']['country_code'],
            'street' => $add_info['addressDetails']['street'] ?? $store_address->street,
          ]);
        } else {
          return response()->json(["ResponseStatus" => "Unsuccessful", 'Detail' => 'Address error: ' . $add_info['responseMessage'], "ResponseMessage" => 'Address error: ' . $add_info['responseMessage'], "message" => 'Address error: ' . $add_info['responseMessage'], "ResponseCode" => 400], 400);
        }
      }
      $userInfo = new UserResource($merchant);
      return $this->successResponse("Store created successfully", 200, compact('userInfo'));
    } catch (Exception $e) {
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
    }
  }

  // public function saveStyles(Request $request)
  // {
  //   $validator = Validator::make($request->all(), [
  //     'image' => 'required|file|mimes:jpg,jpeg,png|max:2048',
  //   ]);

  //   if ($validator->fails()) {
  //     return $this->validationError($validator);
  //   }

  //   try {

  //     $path = '/styles/';
  //     $userId = $this->getAuthID($request);
  //     $imageArray = $this->saveImgArray($request->file('image'), $path, $userId);

  //     if ($imageArray === null) {
  //       return response()->json([
  //         'ResponseStatus' => 'Unsuccessful',
  //         'message' => 'Failed to save image',
  //       ], 500);
  //     }


  //     $style = UserStyle::create([
  //       'user_id' => $request->user_id,
  //       'image_url' => $imageArray[0],
  //     ]);

  //     return response()->json([
  //       'ResponseStatus' => 'Successful',
  //       'message' => 'Style saved successfully',
  //       'data' => $style,
  //     ], 201);

  //   } catch (Exception $e) {
  //     return response()->json([
  //       'ResponseStatus' => 'Unsuccessful',
  //       'Detail' => $e->getMessage(),
  //       'message' => 'Something went wrong',
  //     ], 500);
  //   }
  // }

  // public function getSavedStyles(Request $request)
  // {

  // }

  // public function removedSavedStyles(Request $request)
  // {

  // }

  // public function showAsavedStyle(Request $request)
  // {

  // }


  public function manageAccount(Request $request)
  {
    try {

      $user = $this->getAuthUser($request);


      $validator = Validator::make($request->all(), [
        'name' => 'nullable|string|max:255',
        'email_address' => 'nullable|email|max:255|unique:users,email,' . $user->id,
        'phone_number' => 'nullable|numeric|digits_between:10,15',
        'password' => 'nullable|string',
      ]);

      if ($validator->fails()) {
        return $this->validationError($validator);
      }


      if ($request->has('name')) {
        $user->name = $request->name;
      }

      if ($request->has('email_address')) {
        $user->email = $request->email_address;
      }

      if ($request->has('phone_number')) {
        $user->phone = $request->phone_number;
      }

      if ($request->has('password')) {
        $user->password = Hash::make($request->password);
      }


      $user->save();


      return response()->json([
        'ResponseStatus' => 'Successful',
        'message' => 'Account details updated successfully.',
        'data' => [
          'name' => $user->name,
          'email' => $user->email,
          'phone_number' => $user->phone,
        ],
      ], 200);
    } catch (Exception $e) {

      return response()->json([
        'ResponseStatus' => 'Unsuccessful',
        'Detail' => $e->getMessage(),
        'message' => 'Something went wrong',
      ], 500);
    }
  }

  public function setNoShowPolicy(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'no_show_policy_enabled' => 'required|boolean',
      'no_show_fee_percentage' => 'nullable|numeric|min:0|max:100',
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }

    try {
      $user = $this->getAuthUser($request);

      $user->no_show_policy_enabled = $request->no_show_policy_enabled;
      $user->no_show_fee_percentage = $request->no_show_policy_enabled
        ? $request->no_show_fee_percentage
        : null;

      $user->save();

      return response()->json([
        'status' => 'success',
        'message' => 'No-Show Policy updated successfully.',
        'data' => [
          'no_show_policy_enabled' => $user->no_show_policy_enabled,
          'no_show_fee_percentage' => $user->no_show_fee_percentage,
        ],
      ]);
    } catch (Exception $e) {
      return response()->json([
        'status' => 'error',
        'message' => 'Something went wrong.',
        'error' => $e->getMessage(),
      ], 500);
    }
  }

  public function getNoShowPolicy(Request $request)
  {
    try {

      $user = $this->getAuthUser($request);

      return response()->json([
        'status' => 'success',
        'message' => 'No-Show Policy retrieved successfully.',
        'data' => [
          'no_show_policy_enabled' => $user->no_show_policy_enabled,
          'no_show_fee_percentage' => $user->no_show_fee_percentage,
        ],
      ], 200);
    } catch (Exception $e) {
      return response()->json([
        'status' => 'error',
        'message' => 'Something went wrong.',
        'error' => $e->getMessage(),
      ], 500);
    }
  }


  public function calculateNoShowFee(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'appointment_id' => 'required|exists:appointments,id',
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }


    try {

      $appointmentId = $request->appointment_id;
      $appointment = Appointment::findOrFail($appointmentId);


      $serviceProvider = $appointment->serviceProvider;


      if (!$serviceProvider->no_show_policy_enabled || !$serviceProvider->no_show_fee_percentage) {
        return response()->json([
          'status' => 'success',
          'no_show_fee' => 0,
          'message' => 'No-Show Fee is not applicable for this appointment.',
        ], 200);
      }


      $noShowFee = ($serviceProvider->no_show_fee_percentage / 100) * $appointment->total_amount;
      $noShowFee = round($noShowFee, 2);

      return response()->json([
        'status' => 'success',
        'no_show_fee' => $noShowFee,
        'message' => 'No-Show Fee calculated successfully.',
      ], 200);
    } catch (Exception $e) {
      return response()->json([
        'status' => 'error',
        'message' => 'Something went wrong.',
        'error' => $e->getMessage(),
      ], 500);
    }
  }



}