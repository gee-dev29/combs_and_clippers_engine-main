<?php

namespace App\Http\Controllers\App;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;



class TwoFactorAuthController extends Controller
{
  public function generateSecret(Request $request)
  {
    try {
      $email = $this->getAuthEmail($request);
      // Initialise the 2FA class
      $google2fa = app('pragmarx.google2fa');
      //generate secret
      $google2fa_secret = $google2fa->generateSecretKey();

      // Generate the QR image. This is the image the user will scan with their app
      // to set up two factor authentication
      $QR_Image = $google2fa->getQRCodeInline(
        config('app.name'),
        $email,
        $google2fa_secret
      );

      $user = User::where('email', $email)->first();
      $user->update(['google2fa_secret' => $google2fa_secret]);

      $data = [
        "success" => true,
        'QR_image' => $QR_Image,
        "google2fa_secret" => $google2fa_secret
      ];
      // Pass the QR barcode image and secret to our response
      return response()->json(compact('data'), 201);
    } catch (Exception $e) {
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
    }
  }

  public function enable2fa(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'code' => 'required|string',
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }
    try {
      $email = $this->getAuthEmail($request);
      $user = User::where('email', $email)->first();
      $secret = $request->input('code');
      $google2fa = app('pragmarx.google2fa');
      $valid = $google2fa->verifyKey($user->google2fa_secret, $secret);
      if ($valid) {
        $user->google2fa_enabled = 1;
        $user->sms2fa_enabled = 0;
        $user->sms_otp = NULL;
        $user->save();
        return response()->json(["success" => true, "message" => "Two factor authentication enabled successfully."], 200);
      } else {
        return $this->errorResponse('Invalid verification Code, Please try again', 401);
      }
    } catch (Exception $e) {
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
    }
  }


  public function disable2fa(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'current-password' => 'required'
    ]);
    if ($validator->fails()) {
      return $this->validationError($validator);
    }
    $email = $this->getAuthEmail($request);
    $user = User::where('email', $email)->first();
    if (!(Hash::check($request['current-password'], $user->password))) {
      // The password does not match
      return $this->errorResponse('The password does not match with your account password. Please try again', 401);
    }

    $user->google2fa_enabled = 0;
    $user->google2fa_secret = null;
    $user->save();
    return response()->json(["success" => true, "message" => "Two factor authentication disabled successfully."], 200);
  }

  public function authenticate(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'code' => 'required|string',
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }
    try {
      $email = $this->getAuthEmail($request);
      $user = User::where('email', $email)->first();
      $secret = $request->input('code');
      $google2fa = app('pragmarx.google2fa');
      $valid = $google2fa->verifyKey($user->google2fa_secret, $secret);
      if ($valid) {
        return response()->json(["success" => true, "message" => "User authenticated successfully."], 200);
      } else {
        return $this->errorResponse('User not authenticated', 401);
      }
    } catch (Exception $e) {
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
    }
  }

  public function generateOTP(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'phone' => 'required|numeric',
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }
    try {
      $email = $this->getAuthEmail($request);
      $user = User::where('email', $email)->first();
      $otp = rand(100000, 999999);
      $message = "Your two factor authentication code is " . $otp;
      $otpSent = $this->sendOTP($request['phone'], $message);

      if ($otpSent == 'sent') {
        $user->sms_otp = $otp;
        $user->save();
        return response()->json(["success" => true, "message" => "An OTP code has been sent to your number."], 200);
      } else {
        return $this->errorResponse('OTP code could not be sent, Please try again', 401);
      }
    } catch (Exception $e) {
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
    }
  }

  public function resendOTP(Request $request)
  {
    try {
      $email = $this->getAuthEmail($request);
      $user = User::where('email', $email)->first();
      $otp = rand(100000, 999999);
      $message = "Your two factor authentication code is " . $otp;
      $otpSent = $this->sendOTP($user->phone, $message);

      if ($otpSent == 'sent') {
        $user->sms_otp = $otp;
        $user->save();
        return response()->json(["success" => true, "message" => "An OTP code has been sent to your number."], 200);
      } else {
        return $this->errorResponse('OTP code could not be sent, Please try again', 401);
      }
    } catch (Exception $e) {
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
    }
  }


  public function enableSms2Fa(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'otp' => 'required|integer',
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }
    try {
      $email = $this->getAuthEmail($request);
      $user = User::where('email', $email)->first();
      $savedOTP = $user->sms_otp;
      if ($savedOTP === $request['otp']) {
        $user->sms2fa_enabled = 1;
        $user->sms_otp = NULL;
        $user->google2fa_enabled = 0;
        $user->google2fa_secret = NULL;
        $user->save();
        return response()->json(["success" => true, "message" => "SMS two factor authentication enabled successfully."], 200);
      } else {
        return $this->errorResponse('Invalid or used OTP Code, Please try again', 401);
      }
    } catch (Exception $e) {
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
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
      $email = $this->getAuthEmail($request);
      $user = User::where('email', $email)->first();
      $savedOTP = $user->sms_otp;
      if ($savedOTP === $request['otp']) {
        $user->sms_otp = NULL;
        $user->save();
        return response()->json(["success" => true, "message" => "OTP code validated successfully."], 200);
      } else {
        return $this->errorResponse('Invalid or used OTP Code, Please try again', 401);
      }
    } catch (Exception $e) {
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
    }
  }

  public function disableSms2Fa(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'current-password' => 'required'
    ]);
    if ($validator->fails()) {
      return $this->validationError($validator);
    }
    $email = $this->getAuthEmail($request);
    $user = User::where('email', $email)->first();
    if (!(Hash::check($request['current-password'], $user->password))) {
      // The password does not match
      return $this->errorResponse('The password does not match with your account password. Please try again', 401);
    }

    $user->sms2fa_enabled = 0;
    $user->sms_otp = null;
    $user->save();
    return response()->json(["success" => true, "message" => "SMS two factor authentication disabled successfully."], 200);
  }
}
