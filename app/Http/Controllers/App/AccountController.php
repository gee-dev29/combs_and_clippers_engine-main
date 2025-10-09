<?php

namespace App\Http\Controllers\App;

use Exception;
use App\Models\Bank;
use App\Models\User;
use App\Models\Order;
use App\Models\Referral;
use App\Models\SocialLink;
use App\Models\Interest;
use App\Models\UserInterest;
use App\Repositories\Util;
use App\Models\StoreAddress;
use Illuminate\Http\Request;
use App\Models\PickupAddress;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\UserResource;
use Bmatovu\MtnMomo\Products\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log as Logger;


class AccountController extends Controller
{
  /**
   * Fetches a customer profile information
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $merchantID
   * @return \Illuminate\Http\Response
   */ 
  public function getProfileInfo(Request $request)
  {
    $customerID = $this->getAuthID($request);
    /** @var User|null */
    $customer = User::find($customerID);
 
    if (!is_null($customer)) {
      
      $userProfile = new UserResource($customer);

      return response()->json(compact('userProfile'), 201);
    }
    return $this->errorResponse('User not found', 404);
  }

  /**
   * Modify a customer profile info 
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $userID
   * @return \Illuminate\Http\Response
   */
  public function modifyProfileInfo(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'name' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z]+(?:\s[a-zA-Z]+)+$/'],
      'bio' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z]+(?:\s[a-zA-Z]+)+$/'],
      'phone' => ['nullable', 'string', 'regex:/^[0-9+\-\s()]+$/', Rule::unique(User::class)->ignore($this->getAuthUser($request)->id)],
      'email' => ['nullable', 'email', Rule::unique(User::class)->ignore($this->getAuthUser($request)->id)],
      'profile_image' => 'nullable|mimes:jpeg,jpg,png,gif,bmp|max:5120',
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }
    try{
      $user_id = $this->getAuthID($request);
      $user = User::find($user_id);
      if (!is_null($user)) {
        $name = $request->input('name');
        $phone = $request->phone;
        $name_arr = explode(" ", $name);
        if ($request->hasFile('profile_image')) {
           //$imageArray = $this->imageUtil->saveImgArray($request->file('profile_image'), '/users/', $user->id, []);
          // if (!is_null($imageArray)) {
          //   $profileimage = array_shift($imageArray);
          // }
          $file = $request->file('profile_image');
          $fileName = time() . '.' . $file->getClientOriginalExtension();
          $folderPath = 'profile_pics/' . $user->id;
          $profileimage = $file->storeAs($folderPath, $fileName, 'public');
        }
        $user->update([
          'name' => $name ?? $user->name,
          'firstName' => isset($name_arr[0]) ? $name_arr[0] : $user->firstName,
          'lastName' => isset($name_arr[1]) ? $name_arr[1] : $user->lastName,
          'email' => $request->email ?? $user->email,
          'email_verified' => ($request['email'] !== $user->email) ? 0 : $user->email_verified,
          'email_verified_at' => ($request['email'] !== $user->email) ? NULL : $user->email_verified_at,
          'phone' => $phone ?? $user->phone,
          'bio' => $request->bio ?? $user->bio,
          'profile_image_link' => $profileimage ?? $user->profile_image_link,
        ]);
        $userProfile = new UserResource($user);
        return response()->json(compact('userProfile'), 201);
      }
      return $this->errorResponse('User not found', 404);
    } catch (Exception $e) {
      Logger::info('modifyProfileInfo Error', [$e->getMessage() . ' - ' . $e->__toString()]);
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, "ResponseMessage" => $e->getMessage()], 500);
    }
  }



  /**
 * Update a customer profile info and store address
 *
 * @param  \Illuminate\Http\Request  $request
 * @return \Illuminate\Http\Response
 */
public function updateProfileInfo(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z]+(?:\s[a-zA-Z]+)+$/'],
        'bio' => ['nullable', 'string', 'max:255'],
        'phone' => ['nullable', 'string', 'regex:/^[0-9+\-\s()]+$/', Rule::unique(User::class)->ignore($this->getAuthUser($request)->id)],
        'email' => ['nullable', 'email', Rule::unique(User::class)->ignore($this->getAuthUser($request)->id)],
        'profile_image' => 'nullable|mimes:jpeg,jpg,png,gif,bmp|max:5120',
        'store_name' => 'nullable|string|max:255',
        'street' => 'nullable|string',
        'city' => 'nullable|string',
        'state' => 'nullable|string',
        'postal_code' => 'nullable|string',
    ]);

    if ($validator->fails()) {
        return $this->validationError($validator);
    }

    try {
        $user_id = $this->getAuthID($request);
        $user = User::find($user_id);
        
        if (!is_null($user)) {
            $name = $request->input('name');
            $phone = $request->phone;
            $name_arr = explode(" ", $name);
            
         
            $profileimage = $user->profile_image_link;
            if ($request->hasFile('profile_image')) {
                $file = $request->file('profile_image');
                $fileName = time() . '.' . $file->getClientOriginalExtension();
                $folderPath = 'profile_pics/' . $user->id;
                $profileimage = $file->storeAs($folderPath, $fileName, 'public');
            }
            
         
            $user->update([
                'name' => $name ?? $user->name,
                'firstName' => isset($name_arr[0]) ? $name_arr[0] : $user->firstName,
                'lastName' => isset($name_arr[1]) ? $name_arr[1] : $user->lastName,
                'email' => $request->email ?? $user->email,
                'email_verified' => ($request['email'] !== $user->email) ? 0 : $user->email_verified,
                'email_verified_at' => ($request['email'] !== $user->email) ? NULL : $user->email_verified_at,
                'phone' => $phone ?? $user->phone,
                'bio' => $request->bio ?? $user->bio,
                'profile_image_link' => $profileimage,
            ]);

            
            $storeAddressFields = ['store_name', 'street', 'city', 'state', 'postal_code'];
            $hasStoreAddressUpdate = collect($storeAddressFields)->some(function ($field) use ($request) {
                return $request->filled($field);
            });

            if ($hasStoreAddressUpdate) {
                $store_address = StoreAddress::where('merchant_id', $user->id)->first();
                
                if ($store_address) {
                  
                    $updateData = [];
                    
                    if ($request->filled('store_name')) {
                        $updateData['name'] = $request->input('store_name');
                    }
                    
                    if ($request->filled('street')) {
                        $updateData['street'] = $request->input('street');
                    }
                    
                    if ($request->filled('city')) {
                        $updateData['city'] = $request->input('city');
                    }
                    
                    if ($request->filled('state')) {
                        $updateData['state'] = $request->input('state');
                    }
                    
                    if ($request->filled('postal_code')) {
                        $updateData['postal_code'] = $request->input('postal_code');
                        $updateData['zip'] = $request->input('postal_code');
                    }

                 
                    if (array_intersect(['street', 'city', 'state'], array_keys($updateData))) {
                        $street = $request->input('street', $store_address->street);
                        $city = $request->input('city', $store_address->city);
                        $state = $request->input('state', $store_address->state);
                        $country = $store_address->country ?? $this->country;
                        
                        $updateData['address'] = $street . ', ' . $city . ', ' . $state . ', ' . $country;
                    }

                    $store_address->update($updateData);

                
                    if (isset($updateData['address'])) {
                        $add_info = Util::validateAddressWithGoogle($user, $updateData['address']);
                        if ($add_info['error'] == 0) {
                            $store_address->update([
                                'address' => $add_info['addressDetails']['address'],
                                'street' => $add_info['addressDetails']['street'],
                                'formatted_address' => $add_info['addressDetails']['formatted_address'],
                                'country' => $add_info['addressDetails']['country'],
                                'country_code' => $add_info['addressDetails']['country_code'],
                                'city' => $add_info['addressDetails']['city'],
                                'city_code' => $add_info['addressDetails']['city_code'],
                                'state' => $add_info['addressDetails']['state'],
                                'state_code' => $add_info['addressDetails']['state_code'],
                                'longitude' => $add_info['addressDetails']['longitude'],
                                'latitude' => $add_info['addressDetails']['latitude']
                            ]);
                        } else {
                            return $this->errorResponse('Address validation error: ' . $add_info['responseMessage'], 400);
                        }
                    }
                } else {
                    
                    if ($request->filled(['street', 'city', 'state'])) {
                        $address = $request->input('street') . ', ' . $request->input('city') . ', ' . $request->input('state') . ', ' . $this->country;
                        
                        $store_address = StoreAddress::create([
                            'merchant_id' => $user->id,
                            'name' => $request->input('store_name', $user->name),
                            'email' => $user->email,
                            'phone' => $user->phone,
                            'street' => $request->input('street'),
                            'city' => $request->input('city'),
                            'state' => $request->input('state'),
                            'country' => $this->country,
                            'zip' => $request->input('postal_code'),
                            'postal_code' => $request->input('postal_code'),
                            'address' => $address
                        ]);

                        
                        $add_info = Util::validateAddressWithGoogle($user, $address);
                        if ($add_info['error'] == 0) {
                            $store_address->update([
                                'address' => $add_info['addressDetails']['address'],
                                'street' => $add_info['addressDetails']['street'],
                                'formatted_address' => $add_info['addressDetails']['formatted_address'],
                                'country' => $add_info['addressDetails']['country'],
                                'country_code' => $add_info['addressDetails']['country_code'],
                                'city' => $add_info['addressDetails']['city'],
                                'city_code' => $add_info['addressDetails']['city_code'],
                                'state' => $add_info['addressDetails']['state'],
                                'state_code' => $add_info['addressDetails']['state_code'],
                                'longitude' => $add_info['addressDetails']['longitude'],
                                'latitude' => $add_info['addressDetails']['latitude']
                            ]);
                        }
                    }
                }
            }

           
            $user->refresh();
            $userProfile = new UserResource($user);
            
            return response()->json(compact('userProfile'), 200);
        }
        
        return $this->errorResponse('User not found', 404);
        
    } catch (Exception $e) {
        Logger::info('modifyProfileInfo Error', [$e->getMessage() . ' - ' . $e->__toString()]);
        return response()->json([
            "ResponseStatus" => "Unsuccessful", 
            "ResponseCode" => 500, 
            "ResponseMessage" => $e->getMessage()
        ], 500);
    }
}

  /**
   * Modify a customer account type
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $userID
   * @return \Illuminate\Http\Response
   */
  public function changeAccountType(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'accountType' => 'required|string',

    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }
    $customerID = $this->getAuthID($request);
    $customer = User::find($customerID);
    if (!is_null($customer)) {

      $customer->update(['account_type' => $request['accountType']]);

      $userProfile = new UserResource($customer);
      return response()->json(compact('userProfile'), 201);
    }
    return $this->errorResponse('User not found', 404);
  }


  public function addUserInterests(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'interest_ids' => 'required|array',
      'interest_ids.*' => 'integer'
    ]);
    if ($validator->fails()) {
      return response()->json(["ResponseStatus" => "Unsuccessful", 'Detail' => $validator->errors(), "ResponseCode" => 401, "ResponseMessage" => implode(', ', $validator->messages()->all())], 401);
    }

    try {
      $userID = $this->getAuthID($request);
      $user = User::find($userID);
      if (!$user) {
        return response()->json(["ResponseStatus" => "Unsuccessful", 'Detail' => 'User not found.', "ResponseMessage" => "User not found.", "ResponseCode" => 401], 401);
      }
      $interest_ids = $request->interest_ids;


      foreach ($interest_ids as $interest_id) {
        UserInterest::create(['user_id' => $userID, 'interest_id' => $interest_id]);
      }
      //$user->interests()->sync($interest_ids);
      $UserInterests = $user->interests;
      return response()->json(compact('UserInterests'), 201);
    } catch (Exception $e) {
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, "ResponseMessage" => $e->getMessage()], 500);
    }
  }

  public function deleteUserInterests(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'interest_ids' => 'required|array',
      'interest_ids.*' => 'integer'
    ]);
    if ($validator->fails()) {
      return response()->json(["ResponseStatus" => "Unsuccessful", 'Detail' => $validator->errors(), "ResponseCode" => 401, "ResponseMessage" => implode(', ', $validator->messages()->all())], 401);
    }

    try {
      $userID = $this->getAuthID($request);
      $user = User::find($userID);
      if (!$user) {
        return response()->json(["ResponseStatus" => "Unsuccessful", 'Detail' => 'User not found.', "ResponseMessage" => "User not found.", "ResponseCode" => 401], 401);
      }
      $interest_ids = $request->interest_ids;


      foreach ($interest_ids as $interest_id) {
        UserInterest::where(['user_id' => $userID, 'interest_id' => $interest_id])->delete();
      }
      //$user->interests()->sync($interest_ids);
      $UserInterests = $user->interests;
      return response()->json(compact('UserInterests'), 201);
    } catch (Exception $e) {
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, "ResponseMessage" => $e->getMessage()], 500);
    }
  }

  public function getUserInterests(Request $request)
  {
    try {
      $userID = $this->getAuthID($request);
      $user = User::find($userID);
      if (!$user) {
        return response()->json(["ResponseStatus" => "Unsuccessful", 'Detail' => 'User not found.', "ResponseMessage" => "User not found.", "ResponseCode" => 401], 401);
      }

      $UserInterests = $user->interests;
      return response()->json(compact('UserInterests'), 201);
    } catch (Exception $e) {
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, "ResponseMessage" => $e->getMessage()], 500);
    }
  }

  public function getInterests(Request $request)
  {
    try {

      $interests = Interest::all();
      return response()->json(compact('interests'), 201);
    } catch (Exception $e) {
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, "ResponseMessage" => $e->getMessage()], 500);
    }
  }


  /**
   * Add customer bank account details
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $userID, $bankNme, $accountNo, $accountName
   * @return \Illuminate\Http\Response
   */
  public function addBankAccount(Request $request)
  {

    $validator = Validator::make($request->all(), [
      'bankName' => 'required|string',
      'accountNo' => 'required|string',
      'accountName' => 'required|string',
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }
    $customerID = $this->getAuthID($request);
    $customer = User::find($customerID);
    if (!is_null($customer)) {

      $customer->update([
        'bank' => $request['bankName'],
        'accountno' => $request['accountNo'],
        'accountname' => $request['accountName'],
      ]);

      $userProfile = new UserResource($customer);
      return response()->json(compact('userProfile'), 201);
    }
    return $this->errorResponse('User not found', 404);
  }

   /**
     * Get bank List
     *
    * @param  \Illuminate\Http\Request  $request
    * @param  
    * @return \Illuminate\Http\Response
    */
  public function getBanks(Request $request)
  {
      $validator = Validator::make($request->all(), [
          'query' => 'nullable|string'
      ]);

      if($validator->fails()){
          return response()->json(["ResponseStatus" => "Unsuccessful", 'Detail' =>  $validator->errors(), "ResponseCode" => 401, "ResponseMessage" => implode(', ',$validator->messages()->all()), "message" => implode(', ',$validator->messages()->all())], 401);
      }
      try{
          if($request->filled('query')){
              $banks = Bank::where('bank', 'LIKE', '%'. $request->input('query').'%')->whereNotNull('bankcode')->get();
          }else{
              $banks = Bank::whereNotNull('id')->get();
          }
          
          return response()->json(compact('banks'),201);
        
      } catch (Exception $e) {
          return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'],500);
      }
  }


  /**
   * Name Enquiry
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  
   * @return \Illuminate\Http\Response
   */
  public function nameEnquiryWithAccountNo(Request $request)
  {
      $validator = Validator::make($request->all(), [
          'accountNo' => 'required|numeric',
          'bankCode' => 'required|numeric'
      ]);

      if($validator->fails()){
          return response()->json(["ResponseStatus" => "Unsuccessful", 'Detail' =>  $validator->errors(), "ResponseCode" => 401, "ResponseMessage" => implode(', ',$validator->messages()->all()), "message" => implode(', ',$validator->messages()->all())], 401);
      }
      try{
          $bank = Bank::where('bankcode',$request->input('bankCode'))->orWhere('vfd_bankcode',$request->input('bankCode'))->first();
          //$accountDetails = $this->providusUtils->getNIPAccountDetails($request->input('accountNo'), $request->input('bankCode'));
          // $accountDetails = $this->vfdUtil->nameEnquiry($request->input('accountNo'), $bank->vfd_bankcode);
          // //accountInfo
          // Logger::info('VFD Name Enquiry Response', [$accountDetails]);
          
          // if ($accountDetails['error'] == 0 && $accountDetails['statusCode'] == '00') {
          //     $accountInfo = $accountDetails['accountInfo'];
          //     $accountDetails = [
          //         "bankCode" => $request->input('bankCode'),
          //         "accountName" => $accountInfo->name,
          //         "transactionReference" => "",
          //         "bvn" => $accountInfo->bvn,
          //         "responseMessage" => $accountDetails['responseMessage'],
          //         "accountNumber" => $accountInfo->account->number,
          //         "responseCode" => $accountDetails['statusCode']
          //     ];
              
          // }else{
          //   return response()->json(["ResponseStatus" => "Unsuccessful", 'Detail' => $accountDetails['responseMessage'],  "ResponseMessage" => $accountDetails['responseMessage'], "ResponseCode" => 400], 400);
          // }

          $accountDetails = [
            "bankCode" => $request->input('bankCode'),
            "accountName" => "Some Name",
            "transactionReference" => "",
            "bvn" => "00000002032",
            "responseMessage" => "Success",
            "accountNumber" => $request->input('accountNo'),
            "responseCode" => 200
        ];
          
          return response()->json(compact('accountDetails'),201);
        
      } catch (Exception $e) {
          return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'],500);
      }
  }

  /**
   * Add a customer social media link
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $userID
   * @return \Illuminate\Http\Response
   */
  public function addSocialMediaLink(Request $request)
  {

    $validator = Validator::make($request->all(), [
      'mediaLink' => 'required|string',
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }
    $customerID = $this->getAuthID($request);
    $customer = User::find($customerID);
    if (!is_null($customer)) {

      $user = $customer->user;
      SocialLink::create([
        'user_id' => $user->id,
        'customer_id' => $customer->id,
        'social_media_link' => $request['mediaLink']
      ]);

      $userProfile = new UserResource($customer);
      return response()->json(compact('userProfile'), 201);
    }
    return $this->errorResponse('User not found', 404);
  }


  /**
   * Remove a customer social media link
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $userID
   */
  public function removeSocialMediaLink(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'mediaLink_id' => 'required|integer',
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }
    $customerID = $this->getAuthID($request);
    $customer = User::find($customerID);
    if (!is_null($customer)) {

      $mediaLink = SocialLink::find($request['mediaLink_id']);
      if (!is_null($mediaLink)) {
        $mediaLink->delete();
      }

      $userProfile = new UserResource($customer);
      return response()->json(compact('userProfile'), 201);
    }
    return $this->errorResponse('User not found', 404);
  }




  /**
   * Modify a customer API setting
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $userID, $mediaLink_id
   */
  public function changeAPIsetting(Request $request)
  {
    //'apimode', 'userapi'
    $validator = Validator::make($request->all(), [
      'useapi' => 'required',
      'apimode' => 'required|string',
      'responseUrl' => 'string|nullable',

    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }
    $customerID = $this->getAuthID($request);
    $customer = User::find($customerID);
    if (!is_null($customer)) {

      $customer->update(['apimode' => $request['apimode'], 'useapi' => $request['useapi'], 'callbackurl' => $request['responseUrl']]);


      $userProfile = new UserResource($customer);
      return response()->json(compact('userProfile'), 201);
    }
    return $this->errorResponse('User not found', 404);
  }


  /**
   * Get a merchant's pickup address
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $userID
   * @return \Illuminate\Http\Response
   */
  public function getPickupAddress(Request $request)
  {
    try {
      $user = $this->getAuthUser($request);
      if (!is_null($user)) {
        $pickup_address = PickupAddress::where('merchant_id', $user->id)->first();
        if (!is_null($pickup_address)) {
          return response()->json(compact('pickup_address'), 201);
        } else {
          return $this->errorResponse('merchant does not have a pickup address', 404);
        }
      }
      return $this->errorResponse('Merchant not found', 404);
    } catch (Exception $e) {
      $this->reportExceptionOnBugsnag($e);
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
    }
  }

  /**
   * Add a merchant's pickup address
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $userID
   * @return \Illuminate\Http\Response
   */
  public function addPickupAddress(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'street' => 'required|string',
      'city' => 'required|string',
      'state' => 'required|string',
      'zip_code' => 'numeric|nullable',
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }
    try {
      $userID = $this->getAuthID($request);
      $user = User::find($userID);
      if (!is_null($user)) {
        $address = $request->input('street') . ', ' . $request->input('city') . ', ' . $request->input('state') . ', ' . $this->country;
        $pickup_address = PickupAddress::updateOrCreate(
          ['merchant_id' => $user->id],
          [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'street' => $request->input('street'),
            'city' => $request->input('city'),
            'state' => $request->input('state'),
            'country' => $this->country,
            'zip' => $request->input('zip_code'),
            'postal_code' => $request->input('zip_code'),
            'address' => $address
          ]
        );

        if (!is_null($pickup_address)) {
          $address_str = $pickup_address->address;
          //validate address
          $add_info = Util::validateAddressWithGoogle($user, $address_str);
          if ($add_info['error'] == 0) {
            //update the address after validation
            $pickup_address->update([
              //'address_code' => $add_info['addressDetails']['address_code'],
              'address' => $add_info['addressDetails']['address'],
              'street' => $add_info['addressDetails']['street'],
              'formatted_address' => $add_info['addressDetails']['formatted_address'],
              'country' => $add_info['addressDetails']['country'],
              'country_code' => $add_info['addressDetails']['country_code'],
              'city' => $add_info['addressDetails']['city'],
              'city_code' => $add_info['addressDetails']['city_code'],
              'state' => $add_info['addressDetails']['state'],
              'state_code' => $add_info['addressDetails']['state_code'],
              'longitude' => $add_info['addressDetails']['longitude'],
              'latitude' => $add_info['addressDetails']['latitude']
            ]);
          } else {
            return $this->errorResponse('Address error: ' . $add_info['responseMessage'], 400);
          }
        }

        return response()->json(compact('pickup_address'), 201);
      }
      return $this->errorResponse('Merchant not found', 404);
    } catch (Exception $e) {
      $this->reportExceptionOnBugsnag($e);
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
    }
  }

  /**
   * Fetch referral statistics
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  Header Authorization
   */
  public function getReferralStats(Request $request)
  {
    try {
      $user = $this->getAuthUser($request);

      if (!is_null($user)) {
        $referrals = Referral::where('referrer_id', $user->id)->get();
        if (!is_null($referrals)) {
          $total_referred = $referrals->count();
          $buyers_referred = $referrals->where('customer_type', 'Buyer')->count();
          $sellers_referred = $total_referred - $buyers_referred;
          $cust_ids = Referral::where('referrer_id', $user->id)->get(['customer_id'])->toArray();
          $tranx_count = Order::whereIn('merchant_id', $cust_ids)->count();
        } else {
          $sellers_referred = 0;
          $buyers_referred = 0;
          $tranx_count = 0;
          //return response()->json(["ResponseStatus" => "Unsuccessful", 'Detail' => 'Customer does not have referrals', 'message' => 'Customer does not have referrals', "ResponseCode" => 401], 401);
        }


        return response()->json(compact('sellers_referred', 'buyers_referred', 'tranx_count'), 201);
      }
      return $this->errorResponse('User not found', 404);
    } catch (Exception $e) {
      $this->reportExceptionOnBugsnag($e);
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
    }
  }

  //generateReferralCode()
  public function setReferralCode()
  {
    $customers = User::all();
    foreach ($customers as $customer) {
      $customer->update(['referral_code' => generateReferralCode()]);
    }
  }

  public function addStoreAddress(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'street' => 'required|string',
      'city' => 'required|string',
      'state' => 'required|string',
      'zip_code' => 'numeric|nullable',
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }
    try {
      $userID = $this->getAuthID($request);
      $user = User::find($userID);
      if (!is_null($user)) {
        $address = $request->input('street') . ', ' . $request->input('city') . ', ' . $request->input('state') . ', ' . $this->country;
        $store_address = StoreAddress::updateOrCreate(
          ['merchant_id' => $user->id],
          [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'street' => $request->input('street'),
            'city' => $request->input('city'),
            'state' => $request->input('state'),
            'country' => $this->country,
            'zip' => $request->input('zip_code'),
            'postal_code' => $request->input('zip_code'),
            'address' => $address
          ]
        );

        if (!is_null($store_address)) {
          $address_str = $store_address->address;
          //validate address
          $add_info = Util::validateAddressWithGoogle($user, $address_str);
          if ($add_info['error'] == 0) {
            //update the address after validation
            $store_address->update([
              //'address_code' => $add_info['addressDetails']['address_code'],
              'address' => $add_info['addressDetails']['address'],
              'street' => $add_info['addressDetails']['street'],
              'formatted_address' => $add_info['addressDetails']['formatted_address'],
              'country' => $add_info['addressDetails']['country'],
              'country_code' => $add_info['addressDetails']['country_code'],
              'city' => $add_info['addressDetails']['city'],
              'city_code' => $add_info['addressDetails']['city_code'],
              'state' => $add_info['addressDetails']['state'],
              'state_code' => $add_info['addressDetails']['state_code'],
              'longitude' => $add_info['addressDetails']['longitude'],
              'latitude' => $add_info['addressDetails']['latitude']
            ]);
          } else {
            return $this->errorResponse('Address error: ' . $add_info['responseMessage'], 400);
          }
        }

        return response()->json(compact('store_address'), 201);
      }
      return $this->errorResponse('Merchant not found', 404);
    } catch (Exception $e) {
      $this->reportExceptionOnBugsnag($e);
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
    }
  }


  public function getStoreAddress(Request $request)
  {
    try {
      $user = $this->getAuthUser($request);
      if (!is_null($user)) {
        $store_address = StoreAddress::where('merchant_id', $user->id)->first();
        if (!is_null($store_address)) {
          return response()->json(compact('store_address'), 201);
        } else {
          return $this->errorResponse('Merchant does not have a store address', 404);
        }
      }
      return $this->errorResponse('Merchant not found', 404);
    } catch (Exception $e) {
      $this->reportExceptionOnBugsnag($e);
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
    }
  }

  public function getMerchantPickupAddress(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'merchantID' => 'required|integer',
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }
    try {
      $merchantID = $request->merchantID;
      $user = User::find($merchantID);
      if (!is_null($user)) {
        $pickup_address = PickupAddress::where('merchant_id', $user->id)->first();
        if (!is_null($pickup_address)) {
          return response()->json(compact('pickup_address'), 201);
        } else {
          return $this->errorResponse('Merchant does not have a pickup address', 404);
        }
      }
      return $this->errorResponse('Merchant not found', 404);
    } catch (Exception $e) {
      $this->reportExceptionOnBugsnag($e);
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
    }
  }

  public function getMerchantStoreAddress(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'merchantID' => 'required|integer',
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }
    try {
      $merchantID = $request->merchantID;
      $user = User::find($merchantID);
      if (!is_null($user)) {
        $store_address = StoreAddress::where('merchant_id', $user->id)->first();
        if (!is_null($store_address)) {
          return response()->json(compact('store_address'), 201);
        } else {
          return $this->errorResponse('Merchant does not have a store address', 404);
        }
      }
      return $this->errorResponse('Merchant not found', 404);
    } catch (Exception $e) {
      $this->reportExceptionOnBugsnag($e);
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
    }
  }

  public function sendTestOtp()
  {
    $to = '2348085744967';
    $name = 'Abdulquddus';
    $otp_code = '12345';

    $otp = $this->WhatsappMessenger->sendOtpVerification($to, $name, $otp_code);
    return response()->json($otp);
  }

  public function sendTestPaymentSuccessful()
  {
    $to = '2348085744967';
    $buyer = 'Abdulquddus';
    $merchant = 'Bambam Collection';
    $amount = 5000;
    $date = "April 19th, 2023";
    $button_param = 'Abd-pepp-123';

    $payment = $this->WhatsappMessenger->sendPaymentSuccessful($to, $buyer, $merchant, $amount, $date, $button_param);
    return response()->json($payment);
  }

  public function confirmMomoAccount($phone)
  {
    try {
      //check if user is active on momo
      $collection = new Collection();
      $isAccountActive = $collection->isActive($phone);
      if ($isAccountActive) {
        $accountHolder = $collection->getAccountHolderBasicInfo($phone);
        return response()->json(compact('isAccountActive', 'accountHolder'), 200);
      }
      $accountHolder = null;
      return response()->json(compact('isAccountActive', 'accountHolder'), 200);
    } catch (Exception $e) {
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Account not found', "ResponseMessage" => 'Account not found'], 500);
    }
  }

  public function getNotifications(Request $request)
  {
    try {
      $user_id = $this->getAuthID($request);
      $user = User::find($user_id);
      if (is_null($user)) {
        return $this->errorResponse('User not found', 404);
      }
      $notifications = $user->unreadNotifications;

      $count = $notifications->count();
      return response()->json(compact('notifications', 'count'), 200);
    } catch (Exception $e) {
      $this->reportExceptionOnBugsnag($e);
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
    }
  }

  public function markNotificationsAsRead(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'notificationID' => 'required|string',
    ]);

    if ($validator->fails()) {
      return $this->validationError($validator);
    }
    try {
      $user_id = $this->getAuthID($request);
      $user = User::find($user_id);
      if (is_null($user)) {
        return $this->errorResponse('User not found', 404);
      }
      $notificationId = $request->input('notificationID');
      $userUnreadNotification = $user->unreadNotifications()
        ->where('id', $notificationId)
        ->first();

      if (!is_null($userUnreadNotification)) {
        $userUnreadNotification->markAsRead();
      } else {
        return response()->json(["ResponseStatus" => "Unsuccessful", 'Detail' => 'notification not found.', "ResponseMessage" => "notification not found.", "ResponseCode" => 401], 401);
      }

      $notifications = $user->unreadNotifications();
      $count = $notifications->count();
      return response()->json(compact('notifications', 'count'), 200);
    } catch (Exception $e) {
      $this->reportExceptionOnBugsnag($e);
      return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
    }
  }
}