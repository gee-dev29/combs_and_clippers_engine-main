<?php

namespace App\Http\Controllers\App;

use Exception;
use App\Models\User;
use App\Models\Store;
use App\Models\Product;
use App\Models\VfdWebhook;
use App\Repositories\Util;
use App\Models\StoreAddress;
use Illuminate\Http\Request;
use App\Repositories\VFDUtils;
use Illuminate\Support\Carbon;
use App\Models\DeliverySetting;
use App\Models\UserBankDetails;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\BankDetails;
use App\Models\NotificationSetting;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\LoginResource;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\StorePreviewResource;
use App\Http\Resources\DeliverySettingResource;
use Illuminate\Pagination\LengthAwarePaginator;


class SettingsController extends Controller
{
    public function updatePersonalInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255|regex:/^[a-zA-Z]+(?:\s[a-zA-Z]+)+$/',
            'email' => ['nullable', 'email', 'max:255', Rule::unique(User::class)->ignore($this->getAuthUser($request)->id)],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        $user_id = $this->getAuthID($request);
        /** @var User|null */
        $user = User::find($user_id);
        if (!is_null($user)) {
            $name = $request->input('name');
            $name_arr = explode(" ", $name);
            $user->update([
                'name' => $name,
                'firstName' => isset($name_arr[0]) ? $name_arr[0] : null,
                'lastName' => isset($name_arr[1]) ? $name_arr[1] : null,
                'email' => $request->email,
                'email_verified' => ($request['email'] !== $user->email) ? 0 : $user->email_verified,
                'email_verified_at' => ($request['email'] !== $user->email) ? NULL : $user->email_verified_at
            ]);
            $userProfile = new UserResource($user);
            return $this->successResponse("Profile updated successfully", 201, compact('userProfile'));
        }
        return $this->errorResponse('User not found', 404);
    }

    public function getLoginDevices(Request $request)
    {
        $user_id = $this->getAuthID($request);
        $logins = User::find($user_id)->authentications()->distinct('ip_address')->get();
        $logins = LoginResource::collection($logins);
        return response()->json(compact('logins'), 200);
    }

    public function updateStore(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'store_id' => 'required|integer',
                'store_name' => 'required|string|max:255|regex:/^[a-zA-Z]+[\w\s-]*$/',
                'store_category' => 'required|integer',
                'store_sub_category' => 'required|integer',
                'website' => 'nullable|string|max:255',
                'store_icon' => 'nullable|mimes:jpeg,jpg,png,gif,bmp|max:5120',
                'store_banner' => 'nullable|mimes:jpeg,jpg,png,gif,bmp|max:5120',
                'store_description' => 'nullable|string',
                'store_phone' => ['nullable', 'string', 'regex:/^[0-9+\-\s()]+$/', Rule::unique(User::class)->ignore($this->getAuthUser($request)->id)],
                'refund_allowed' => 'nullable|integer',
                'replacement_allowed' => 'nullable|integer',
                'store_state' => 'nullable|string|max:255',
                'store_city' => 'required|string|max:255',
                'store_postal_code' => 'required|string|max:255',
                'store_address' => 'required|string|max:255',
            ]
        );

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $merchantID = $this->getAuthID($request);
        /** @var User|null */
        $merchant = User::find($merchantID);
        if (is_null($merchant)) {
            return $this->errorResponse('Merchant not found', 404);
        }
        try {
            /** @var Store|null */
            $store = Store::where(['id' => $request->store_id, 'merchant_id' => $merchantID])->first();
            if (!is_null($store)) {
                $store->update([
                    'store_name' => $request->input('store_name'),
                    'store_category' => $request->input('store_category'),
                    'store_sub_category' => $request->input('store_sub_category'),
                    'website' => $request->input('website'),
                    'store_description' => $request->input('store_description'),
                    'refund_allowed' => $request->filled('refund_allowed') ? $request->input('refund_allowed') : 0,
                    'replacement_allowed' => $request->filled('replacement_allowed') ? $request->input('replacement_allowed') : 0
                ]);

                if ($request->hasFile('store_icon')) {
                    $imageArray = $this->imageUtil->saveImgArray($request->file('store_icon'), '/merchants/stores/icons/', $store->id, []);
                    if (!is_null($imageArray)) {
                        $icon = array_shift($imageArray);
                        $store->update(['store_icon' => $icon]);
                    }
                }

                if ($request->filled('store_phone')) {
                    $merchant->update(['phone' => $request->store_phone]);
                }

                if ($request->hasFile('store_banner')) {
                    $imageArray = $this->imageUtil->saveImgArray($request->file('store_banner'), '/merchants/stores/banners/', $store->id, []);
                    if (!is_null($imageArray)) {
                        $banner = array_shift($imageArray);
                        $store->update(['store_banner' => $banner]);
                    }
                }

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

                $userProfile = new UserResource($merchant);
                return $this->successResponse("Store updated successfully", 200, compact('userProfile'));
            }
            return $this->errorResponse('Store not found', 404);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function previewStore(Request $request)
    {
        $merchantID = $this->getAuthID($request);
        try {
            $merchant = User::where('id', $merchantID)->first();
            if (!is_null($merchant)) {
                $store = Store::where('merchant_id', $merchant->id)->latest()->first();
                if (!is_null($store)) {
                    $store = new StorePreviewResource($store);
                }
                $products = Product::where('merchant_id', $merchant->id)->latest('id')->paginate($this->perPage);
                $products = $this->addMeta(ProductResource::collection($products));
                return response()->json(compact('store', 'products'), 200);
            }
            return $this->errorResponse('Merchant not found', 404);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function removeStoreIcon(Request $request)
    {
        try {
            $merchantID = $this->getAuthID($request);
            $store = Store::where('merchant_id', $merchantID)->latest()->first();
            if (!is_null($store)) {
                //remove image from storage
                $this->imageUtil->deleteImage($store->store_icon);
                $store->update([
                    'store_icon' => ''
                ]);
                return response()->json(compact('store'), 201);
            }
            return $this->errorResponse('Store not found', 404);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function removeStoreBanner(Request $request)
    {
        try {
            $merchantID = $this->getAuthID($request);
            $store = Store::where('merchant_id', $merchantID)->latest()->first();
            if (!is_null($store)) {
                //remove image from storage
                $this->imageUtil->deleteImage($store->store_banner);
                $store->update([
                    'store_banner' => ''
                ]);
                return response()->json(compact('store'), 201);
            }
            return $this->errorResponse('Store not found', 404);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|min:8|confirmed|string',
            'password_confirmation' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        try {
            $user = $this->getAuthUser($request);
            if (!$user) {
                return $this->errorResponse('User not found.', 404);
            }

            if (!(Hash::check($request->current_password, $user->password))) {
                //current password is incorrect
                return $this->errorResponse('Your current password is incorrect.', 401);
            }

            if (strcmp($request->current_password, $request->password) == 0) {
                // Current password and new password same
                return $this->errorResponse('New Password cannot be same as your current password.', 400);
            }

            //Change Password
            $user->update(['password' => Hash::make($request->input('password'))]);

            return $this->successResponse('Password successfully changed!', 200);
        } catch (Exception $e) {
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function updateNotificationSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "notify_new_order_via_email" => 'boolean|nullable',
            "notify_new_order_via_sms" => 'boolean|nullable',
            "notify_new_order_via_push_notification" => 'boolean|nullable',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        $user_id = $this->getAuthID($request);
        $merchant = User::find($user_id);
        if (is_null($merchant)) {
            return $this->errorResponse('Merchant not found', 404);
        }
        $notification_settings = NotificationSetting::updateOrCreate(
            ['user_id' => $user_id],
            [
                "notify_new_order_via_email" => $request->notify_new_order_via_email,
                "notify_new_order_via_sms" => $request->notify_new_order_via_sms,
                "notify_new_order_via_push_notification" => $request->notify_new_order_via_push_notification
            ]
        );
        $notification_settings = $merchant->notificationSetting;
        return response()->json(compact('notification_settings'), 201);
    }

    public function getNotificationSettings(Request $request)
    {
        try {
            $user_id = $this->getAuthID($request);
            $merchant = User::find($user_id);
            if (is_null($merchant)) {
                return $this->errorResponse('Merchant not found', 404);
            }
            $notification_settings = $merchant->notificationSetting;
            return response()->json(compact('notification_settings'), 200);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function stripeOnboard(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'redirectUrl' => 'url|required',
            'cancelUrl' => 'url|required',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        try {
            $redirectUrl = $request->input('redirectUrl');
            $cancelUrl = $request->input('cancelUrl');
            $user = $this->getAuthUser($request);
            if (!$user) {
                return $this->errorResponse('User not found.', 404);
            }

            if (is_null($user->stripe_account_id)) {
                //onboard user on stripe
                $account = $this->stripeUtils->createAccount($user, 'express');
                if ($account['error'] != 1) {
                    $user->update([
                        'stripe_account_id' => $account['responseDetails']['id']
                    ]);
                } else {
                    return response()->json(compact('account'));
                }
            }

            $accountId = $user->stripe_account_id;
            //get stripe account link
            $link = $this->stripeUtils->createAccountLink($accountId, $cancelUrl, $redirectUrl);
            return response()->json(compact('link'));
        } catch (Exception $e) {
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage() . ' - ' . $e->__toString(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function addDeliverySettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country' => 'nullable|string|max:50',
            'region' => 'required|string|max:50',
            'delivery_time' => 'required|string',
            'delivery_fee' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        try {
            $user = $this->getAuthUser($request);
            if (is_null($user)) {
                return $this->errorResponse('Merchant not found.', 404);
            }

            $deliverySetting = DeliverySetting::updateOrCreate(
                ['user_id' => $user->id, 'region' => $request->region],
                [
                    'delivery_time' => $request->delivery_time,
                    'delivery_fee' => $request->delivery_fee,
                ]
            );

            $deliverySettings = DeliverySetting::where('user_id', $user->id)->latest()->paginate($this->perPage);
            $deliverySettings = $this->addMeta(DeliverySettingResource::collection($deliverySettings));
            return response()->json(compact('deliverySettings'), 201);
        } catch (Exception $e) {
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function myDeliverySettings(Request $request)
    {
        try {
            $user = $this->getAuthUser($request);
            if (is_null($user)) {
                return $this->errorResponse('Merchant not found.', 404);
            }

            $deliverySettings = DeliverySetting::where('user_id', $user->id)->latest()->paginate($this->perPage);
            $deliverySettings = $this->addMeta(DeliverySettingResource::collection($deliverySettings));
            return response()->json(compact('deliverySettings'), 200);
        } catch (Exception $e) {
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function setRewardPreference(Request $request)
    {
        $validate = Validator::make(
            $request->all(),
            [
                "referral_reward" => "nullable|bool",
                "existing_client_reward" => "nullable|integer|required_if:referral_reward,true",
                "new_client_reward" => "nullable|integer|required_if:referral_reward,true",
                "loyalty_reward" => "nullable|bool",
                "goal" => "nullable|integer|required_if:loyalty_reward,true",
                "reward" => "nullable|integer|required_if:loyalty_reward,true",
            ]
        );

        if ($validate->fails()) {
            return $this->validationError($validate);
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

            // Check if the user is a client
            if (strtolower($user->account_type) === 'client') {
                return response()->json([
                    "ResponseStatus" => "Unsuccessful",
                    "ResponseCode" => 403,
                    "Detail" => "Access Denied",
                    "ResponseMessage" => "Clients cannot set reward preferences.",
                ], 403);
            }

            // Fetch store (if available)
            $store = Store::where('merchant_id', $merchantId)->first();
            $storeId = $store->id ?? null;

            if ($store) {
                // Update rewards in store
                $rewards = $store->rewards ?: [];

                if ($request->has("referral_reward")) {
                    $rewards['referral_reward'] = [
                        'active' => $request->referral_reward,
                        'existing_client_reward' => $request->existing_client_reward ?? $rewards['referral_reward']['existing_client_reward'] ?? null,
                        'new_client_reward' => $request->new_client_reward ?? $rewards['referral_reward']['new_client_reward'] ?? null,
                    ];
                    $this->updateGrowServiceProgress($merchantId, $storeId, "setup_referal_reward");
                }

                if ($request->has("loyalty_reward")) {
                    $rewards['loyalty_reward'] = [
                        'active' => $request->loyalty_reward,
                        'goal' => $request->goal ?? $rewards['loyalty_reward']['goal'] ?? null,
                        'reward' => $request->reward ?? $rewards['loyalty_reward']['reward'] ?? null,
                    ];
                    $this->updateGrowServiceProgress($merchantId, $storeId, "setup_loyalty_reward");
                }

                $store->rewards = $rewards;
                $store->save();
            } else {
                // Update rewards in user settings if no store exists
                $rewards = $user->rewards ?: [];

                if ($request->has("referral_reward")) {
                    $rewards['referral_reward'] = [
                        'active' => $request->referral_reward,
                        'existing_client_reward' => $request->existing_client_reward ?? $rewards['referral_reward']['existing_client_reward'] ?? null,
                        'new_client_reward' => $request->new_client_reward ?? $rewards['referral_reward']['new_client_reward'] ?? null,
                    ];
                }

                if ($request->has("loyalty_reward")) {
                    $rewards['loyalty_reward'] = [
                        'active' => $request->loyalty_reward,
                        'goal' => $request->goal ?? $rewards['loyalty_reward']['goal'] ?? null,
                        'reward' => $request->reward ?? $rewards['loyalty_reward']['reward'] ?? null,
                    ];
                }

                $user->rewards = $rewards;
                $user->save();
            }

            // Update Grow Service Progress without store ID if no store exists
            if ($request->has("referral_reward")) {
                $this->updateGrowServiceProgress($merchantId, $storeId, "setup_referal_reward");
            }
            if ($request->has("loyalty_reward")) {
                $this->updateGrowServiceProgress($merchantId, $storeId, "setup_loyalty_reward");
            }

            return response()->json([
                "ResponseStatus" => "successful",
                "Detail" => "Reward Preference Updated",
                "ResponseMessage" => "Reward Preference has been updated successfully.",
                "ResponseCode" => 200
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                "ResponseStatus" => "Unsuccessful",
                "ResponseCode" => 500,
                "Detail" => $e->getMessage(),
                "ResponseMessage" => "Something went wrong"
            ], 500);
        }
    }


    public function getRewardPreference(Request $request)
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

            // Check if the user is a client (case insensitive)
            if (strtolower($user->account_type) === 'client') {
                return response()->json([
                    "ResponseStatus" => "Unsuccessful",
                    "ResponseCode" => 403,
                    "Detail" => "Access Denied",
                    "ResponseMessage" => "Clients cannot access reward preferences.",
                ], 403);
            }

            // Check if the user has a store
            $store = Store::where('merchant_id', $merchantId)->first();

            if ($store) {
                return response()->json([
                    "ResponseStatus" => "successful",
                    "Detail" => "Reward Preference Retrieved",
                    "ResponseMessage" => "Reward Preference has been retrieved successfully.",
                    "ResponseCode" => 200,
                    "data" => $store->rewards
                ], 200);
            } else {
                return response()->json([
                    "ResponseStatus" => "successful",
                    "Detail" => "Reward Preference Retrieved",
                    "ResponseMessage" => "Reward Preference has been retrieved successfully.",
                    "ResponseCode" => 200,
                    "data" => $user->rewards
                ], 200);
            }
        } catch (Exception $e) {
            return response()->json([
                "ResponseStatus" => "Unsuccessful",
                "ResponseCode" => 500,
                "Detail" => $e->getMessage(),
                "ResponseMessage" => "Something went wrong"
            ], 500);
        }
    }


    public function setPaymentPreference(Request $request)
    {
        $validate = Validator::make(
            $request->all(),
            [
                "payment_preference" => "required|string",
            ]
        );

        if ($validate->fails()) {
            return $this->validationError($validate);
        }

        try {
            $merchantId = $this->getAuthID($request);
            $user = User::find($merchantId);
            $store = Store::where('merchant_id', $merchantId)->first();

            if ($store) {
                // Update store payment preferences
                $payment_preferences = $store->payment_preferences ?: [];
                $payment_preferences['payment_preference'] = $request->payment_preference;
                $store->payment_preferences = $payment_preferences;

                if ($store->save()) {
                    return response()->json([
                        "ResponseStatus" => "successful",
                        'Detail' => 'Payment Preference Updated',
                        "ResponseMessage" => 'Payment Preference has been updated successfully.',
                        "ResponseCode" => 200
                    ], 200);
                }
            } else {
                // Update user payment preferences if no store exists
                $payment_preferences = $user->payment_preferences ?: [];
                $payment_preferences['payment_preference'] = $request->payment_preference;
                $user->payment_preferences = $payment_preferences;

                if ($user->save()) {
                    return response()->json([
                        "ResponseStatus" => "successful",
                        'Detail' => 'Payment Preference Updated',
                        "ResponseMessage" => 'Payment Preference has been updated successfully.',
                        "ResponseCode" => 200
                    ], 200);
                }
            }
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


    public function getPaymentPreference(Request $request)
    {
        try {
            $merchantId = $this->getAuthID($request);
            $user = User::find($merchantId);
            $store = Store::where('merchant_id', $merchantId)->first();

            if ($store) {
                return response()->json([
                    "ResponseStatus" => "successful",
                    'Detail' => 'Payment Preference Retrieved',
                    "ResponseMessage" => 'Payment Preference has been retrieved successfully.',
                    "ResponseCode" => 200,
                    "data" => $store->payment_preferences
                ], 200);
            } else {
                return response()->json([
                    "ResponseStatus" => "successful",
                    'Detail' => 'Payment Preference Retrieved',
                    "ResponseMessage" => 'Payment Preference has been retrieved successfully.',
                    "ResponseCode" => 200,
                    "data" => $user->payment_preferences
                ], 200);
            }
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



    public function setBookingPreference(Request $request)
    {
        $validate = Validator::make($request->all(), [
            "auto_confirm" => 'nullable|bool',
            "multiple_services" => 'nullable|bool',
            "client_phone_number" => 'nullable|bool'
        ]);

        if ($validate->fails()) {
            return $this->validationError($validate);
        }

        try {
            $merchantId = $this->getAuthID($request);
            $user = User::find($merchantId);

            if (!$user) {
                return response()->json([
                    "ResponseStatus" => "Unsuccessful",
                    "ResponseCode" => 404,
                    "Detail" => "User not found.",
                    "ResponseMessage" => "We could not find this user.",
                ], 404);
            }

            // Check if user is a client (case-insensitive)
            if (strtolower($user->account_type) === 'client') {
                return response()->json([
                    "ResponseStatus" => "Unsuccessful",
                    "ResponseCode" => 403,
                    "Detail" => "Access Denied.",
                    "ResponseMessage" => "Clients cannot set booking preferences.",
                ], 403);
            }

            // Check if the user has a store
            $store = Store::where('merchant_id', $merchantId)->first();

            if ($store) {
                // Update preferences in the store
                $booking_preferences = $store->booking_preferences ?: [];
            } else {
                // Update preferences in the user settings
                $booking_preferences = $user->booking_preferences ?: [];
            }

            if ($request->has("auto_confirm")) {
                $booking_preferences['auto_confirm'] = $request->auto_confirm;
            }
            if ($request->has("multiple_services")) {
                $booking_preferences['multiple_services'] = $request->multiple_services;
            }
            if ($request->has("client_phone_number")) {
                $booking_preferences['client_phone_number'] = $request->client_phone_number;
            }

            if ($store) {
                $store->booking_preferences = $booking_preferences;
                $store->save();
            } else {
                $user->booking_preferences = $booking_preferences;
                $user->save();
            }

            return response()->json([
                "ResponseStatus" => "successful",
                'Detail' => 'Booking Preference Updated',
                "ResponseMessage" => 'Booking Preference has been updated successfully.',
                "ResponseCode" => 200
            ], 200);

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

    public function getBookingPreference(Request $request)
    {
        try {
            $merchantId = $this->getAuthID($request);
            $user = User::find($merchantId);

            if (!$user) {
                return response()->json([
                    "ResponseStatus" => "Unsuccessful",
                    "ResponseCode" => 404,
                    "Detail" => "User not found.",
                    "ResponseMessage" => "We could not find this user.",
                ], 404);
            }

            // Check if user is a client (case-insensitive)
            if (strtolower($user->account_type) === 'client') {
                return response()->json([
                    "ResponseStatus" => "Unsuccessful",
                    "ResponseCode" => 403,
                    "Detail" => "Access Denied.",
                    "ResponseMessage" => "Clients cannot access booking preferences.",
                ], 403);
            }

            // Check if the user has a store
            $store = Store::where('merchant_id', $merchantId)->first();

            $preferences = $store ? $store->booking_preferences : $user->booking_preferences;

            return response()->json([
                "ResponseStatus" => "successful",
                'Detail' => 'Booking Preference Retrieved',
                "ResponseMessage" => 'Booking Preference has been retrieved successfully.',
                "ResponseCode" => 200,
                "data" => $preferences
            ], 200);

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



    public function setAvailability(Request $request)
    {
        $validate = Validator::make($request->all(), [
            "booking_interval" => "nullable",
            "last_minute_limit" => "nullable"
        ]);

        if ($validate->fails()) {
            return $this->validationError($validate);
        }

        try {
            $merchantId = $this->getAuthID($request);
            $user = User::where('id', $merchantId)->first();

            if (!$user) {
                return response()->json([
                    "ResponseStatus" => "Unsuccessful",
                    "ResponseCode" => 404,
                    "Detail" => "User not found.",
                    "ResponseMessage" => "We could not find the user.",
                ], 404);
            }

            if (strtolower($user->account_type) === "client") {
                return response()->json([
                    "ResponseStatus" => "Unsuccessful",
                    "ResponseCode" => 403,
                    "Detail" => "Permission Denied.",
                    "ResponseMessage" => "Clients are not allowed to update availability.",
                ], 403);
            }

            $store = Store::where('merchant_id', $merchantId)->first();
            if ($store) {
                // Store exists, update store availability
                $availability = $store->availability ?: [];
            } else {
                // No store, update user availability instead
                $availability = $user->availability ?: [];
            }

            if ($request->has("booking_interval")) {
                $availability['booking_interval'] = $request->booking_interval;
            }
            if ($request->has("last_minute_limit")) {
                $availability['last_minute_limit'] = $request->last_minute_limit;
            }

            if ($store) {
                $store->availability = $availability;
                $store->save();
            } else {
                $user->availability = $availability;
                $user->save();
            }

            return response()->json([
                "ResponseStatus" => "successful",
                'Detail' => 'Availability Updated',
                "ResponseMessage" => 'Availability has been updated successfully.',
                "ResponseCode" => 200
            ], 200);
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

    public function getAvailability(Request $request)
    {
        try {
            $merchantId = $this->getAuthID($request);
            $user = User::where('id', $merchantId)->first();

            if (!$user) {
                return response()->json([
                    "ResponseStatus" => "Unsuccessful",
                    "ResponseCode" => 404,
                    "Detail" => "User not found.",
                    "ResponseMessage" => "We could not find the user.",
                ], 404);
            }

            if (strtolower($user->account_type) === "client") {
                return response()->json([
                    "ResponseStatus" => "Unsuccessful",
                    "ResponseCode" => 403,
                    "Detail" => "Permission Denied.",
                    "ResponseMessage" => "Clients are not allowed to access availability settings.",
                ], 403);
            }

            $store = Store::where('merchant_id', $merchantId)->first();
            $availability = $store ? $store->availability : $user->availability;

            return response()->json([
                "ResponseStatus" => "successful",
                'Detail' => 'Availability Info Retrieved',
                "ResponseMessage" => 'Availability Info has been retrieved successfully.',
                "ResponseCode" => 200,
                "data" => $availability,
            ], 200);
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



    public function setBookingLimit(Request $request)
    {
        $validate = Validator::make($request->all(), [
            "future_booking_limit" => "nullable",
            "recurring_booking_limit" => 'nullable'
        ]);

        if ($validate->fails()) {
            return $this->validationError($validate);
        }

        try {
            $merchantId = $this->getAuthID($request);
            $user = User::where('id', $merchantId)->first();

            if (!$user) {
                return response()->json([
                    "ResponseStatus" => "Unsuccessful",
                    "ResponseCode" => 404,
                    "Detail" => "User not found.",
                    "ResponseMessage" => "We could not find the user.",
                ], 404);
            }

            if (strtolower($user->account_type) === "client") {
                return response()->json([
                    "ResponseStatus" => "Unsuccessful",
                    "ResponseCode" => 403,
                    "Detail" => "Permission Denied.",
                    "ResponseMessage" => "Clients are not allowed to update booking limits.",
                ], 403);
            }

            $store = Store::where('merchant_id', $merchantId)->first();
            if ($store) {
                // Store exists, update store booking limits
                $bookingLimit = $store->booking_limits ?: [];
            } else {
                // No store, update user booking limits instead
                $bookingLimit = $user->booking_limits ?: [];
            }

            if ($request->has("future_booking_limit")) {
                $bookingLimit['future_booking_limit'] = $request->future_booking_limit;
            }
            if ($request->has("recurring_booking_limit")) {
                $bookingLimit['recurring_booking_limit'] = $request->recurring_booking_limit;
            }

            if ($store) {
                $store->booking_limits = $bookingLimit;
                $store->save();
            } else {
                $user->booking_limits = $bookingLimit;
                $user->save();
            }

            return response()->json([
                "ResponseStatus" => "successful",
                'Detail' => 'Booking Limit Updated',
                "ResponseMessage" => 'Booking Limit has been updated successfully.',
                "ResponseCode" => 200
            ], 200);
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

    public function getBookingLimit(Request $request)
    {
        try {
            $merchantId = $this->getAuthID($request);
            $user = User::where('id', $merchantId)->first();

            if (!$user) {
                return response()->json([
                    "ResponseStatus" => "Unsuccessful",
                    "ResponseCode" => 404,
                    "Detail" => "User not found.",
                    "ResponseMessage" => "We could not find the user.",
                ], 404);
            }

            if (strtolower($user->account_type) === "client") {
                return response()->json([
                    "ResponseStatus" => "Unsuccessful",
                    "ResponseCode" => 403,
                    "Detail" => "Permission Denied.",
                    "ResponseMessage" => "Clients are not allowed to access booking limits.",
                ], 403);
            }

            $store = Store::where('merchant_id', $merchantId)->first();
            $bookingLimit = $store ? $store->booking_limits : $user->booking_limits;

            return response()->json([
                "ResponseStatus" => "successful",
                'Detail' => 'Booking Limit Retrieved',
                "ResponseMessage" => 'Booking Limit has been retrieved successfully.',
                "ResponseCode" => 200,
                "data" => $bookingLimit,
            ], 200);
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




    public function setStorePreferences(Request $request)
    {
        $validate = Validator::make($request->all(), [
            "payment_preference" => "nullable|string",
            "auto_confirm" => "nullable|boolean",
            "multiple_services" => "nullable|boolean",
            "client_phone_number" => "nullable|boolean",
            "booking_interval" => "nullable|integer|min:10",
            "last_minute_limit" => "nullable|array",
            "last_minute_limit.hour" => "required_with:last_minute_limit|integer",
            "last_minute_limit.minute" => "required_with:last_minute_limit|integer",
            "future_booking_limit" => "nullable|array",
            "future_booking_limit.value" => "required_with:future_booking_limit|integer",
            "future_booking_limit.unit" => "required_with:future_booking_limit|string|in:day,days,week,weeks,month,months",
            "recurring_booking_limit" => "nullable|integer",
        ]);

        if ($validate->fails()) {
            return $this->validationError($validate);
        }

        try {
            $merchantId = $this->getAuthID($request);
            $user = User::where('id', $merchantId)->first();

            if (!$user) {
                return response()->json([
                    "ResponseStatus" => "Unsuccessful",
                    "ResponseCode" => 404,
                    "Detail" => "User not found.",
                    "ResponseMessage" => "We could not find the user.",
                ], 404);
            }

            if (strtolower($user->account_type) === "client") {
                return response()->json([
                    "ResponseStatus" => "Unsuccessful",
                    "ResponseCode" => 403,
                    "Detail" => "Permission Denied.",
                    "ResponseMessage" => "Clients are not allowed to update store preferences.",
                ], 403);
            }

            $store = Store::where('merchant_id', $merchantId)->first();
            $settings = $store ?: $user; // Store settings if exists, otherwise User settings

            // Update payment preference
            if ($request->has("payment_preference")) {
                $payment_preferences = $settings->payment_preferences ?: [];
                $payment_preferences['payment_preference'] = $request->payment_preference;
                $settings->payment_preferences = $payment_preferences;
            }

            // Update booking preferences
            $booking_preferences = $settings->booking_preferences ?: [];
            foreach (["auto_confirm", "multiple_services", "client_phone_number"] as $field) {
                if ($request->has($field)) {
                    $booking_preferences[$field] = $request->$field;
                }
            }
            $settings->booking_preferences = $booking_preferences;

            // Update availability
            $availability = $settings->availability ?: [];
            foreach (["booking_interval", "last_minute_limit"] as $field) {
                if ($request->has($field)) {
                    $availability[$field] = $request->$field;
                }
            }
            $settings->availability = $availability;

            // Update booking limits
            $booking_limits = $settings->booking_limits ?: [];
            foreach (["future_booking_limit", "recurring_booking_limit"] as $field) {
                if ($request->has($field)) {
                    $booking_limits[$field] = $request->$field;
                }
            }
            $settings->booking_limits = $booking_limits;

            if ($settings->save()) {
                return response()->json([
                    "ResponseStatus" => "successful",
                    "ResponseCode" => 200,
                    "Detail" => "Preferences Updated",
                    "ResponseMessage" => "All preferences have been updated successfully.",
                ], 200);
            }
        } catch (Exception $e) {
            return response()->json([
                "ResponseStatus" => "Unsuccessful",
                "ResponseCode" => 500,
                "Detail" => $e->getMessage(),
                "ResponseMessage" => "Something went wrong.",
            ], 500);
        }
    }

    public function getStorePreferences(Request $request)
    {
        try {
            $merchantId = $this->getAuthID($request);
            $user = User::where('id', $merchantId)->first();

            if (!$user) {
                return response()->json([
                    "ResponseStatus" => "Unsuccessful",
                    "ResponseCode" => 404,
                    "Detail" => "User not found.",
                    "ResponseMessage" => "We could not find the user.",
                ], 404);
            }

            if (strtolower($user->account_type) === "client") {
                return response()->json([
                    "ResponseStatus" => "Unsuccessful",
                    "ResponseCode" => 403,
                    "Detail" => "Permission Denied.",
                    "ResponseMessage" => "Clients are not allowed to access store preferences.",
                ], 403);
            }

            $store = Store::where('merchant_id', $merchantId)->first();
            $settings = $store ?: $user; // Fetch from Store if exists, else from User

            $preferences = [
                "payment_preferences" => $settings->payment_preferences ?: [],
                "booking_preferences" => $settings->booking_preferences ?: [],
                "availability" => $settings->availability ?: [],
                "booking_limits" => $settings->booking_limits ?: [],
            ];

            return response()->json([
                "ResponseStatus" => "successful",
                "ResponseCode" => 200,
                "Detail" => "Preferences retrieved successfully.",
                "ResponseMessage" => "Preferences have been fetched successfully.",
                "data" => $preferences
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




    public function generateProfileLink(Request $request)
    {
        try {
            $merchant = $this->getAuthUser($request);
            $store = Store::where('merchant_id', $merchant->id)->first();

            $merchantCode = $merchant->merchant_code;
            $front_url = cc('frontend_base_url');
            $link = $front_url . "{$merchantCode}";

           
            $this->updateGrowServiceProgress($merchant->id, $store->id ?? null, "create_profile_link");

            return response()->json([
                "ResponseStatus" => "successful",
                "ResponseCode" => 200,
                "Detail" => "Profile Link retrieved successfully.",
                "ResponseMessage" => "Profile Link has been fetched successfully.",
                "data" => $link
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


    // public function editProfileLink(Request $request)
    // {
    //     $validate = Validator::make($request->all(), [
    //         'merchant_code' => 'unique:users,merchant_code'
    //     ]);

    //     if ($validate->fails()) {
    //         return $this->validationError($validate);
    //     }

    //     try {
    //         $merchant = $this->getAuthUser($request);
    //         $existing_merchant = User::where('merchant_code', $request->merchant_code)->first();
    //         if ($existing_merchant) {
    //             return response()->json([
    //                 "ResponseStatus" => "Unsuccessful",
    //                 "ResponseCode" => 400,
    //                 "Detail" => "Merchant Code already exists.",
    //                 "ResponseMessage" => "Merchant Code already exists. Please choose a unique one.",
    //             ], 400);
    //         }

    //         $merchant->merchant_code = $request->merchant_code;
    //         $merchant->save();

    //         $front_url = cc('frontend_base_url');
    //         $link = $front_url . "{$merchant->merchant_code}";

    //         return response()->json([
    //             "ResponseStatus" => "successful",
    //             "ResponseCode" => 200,
    //             "Detail" => "Profile Link Edited successfully.",
    //             "ResponseMessage" => "Profile Link have been edited successfully the new link is in the data.",
    //             "data" => $link
    //         ], 200);



    //     } catch (Exception $e) {
    //         return response()->json([
    //             "ResponseStatus" => "Unsuccessful",
    //             "ResponseCode" => 500,
    //             "Detail" => $e->getMessage(),
    //             "ResponseMessage" => "Something went wrong.",
    //         ], 500);
    //     }
    // }




public function editProfileLink(Request $request)
{
    $validate = Validator::make($request->all(), [
        'merchant_code' => 'unique:users,merchant_code'
    ]);

    if ($validate->fails()) {
        return $this->validationError($validate);
    }

    try {
        $merchant = $this->getAuthUser($request);
        $existing_merchant = User::where('merchant_code', $request->merchant_code)->first();
        if ($existing_merchant) {
            return response()->json([
                "ResponseStatus" => "Unsuccessful",
                "ResponseCode" => 400,
                "Detail" => "Merchant Code already exists.",
                "ResponseMessage" => "Merchant Code already exists. Please choose a unique one.",
            ], 400);
        }

        $merchant->merchant_code = $request->merchant_code;
        
        
        if (!$merchant->has_edited_profile_link) {
            $merchant->has_edited_profile_link = true;
            $merchant->profile_link_edited_at = now();
        }
        
        $merchant->save();

        $front_url = cc('frontend_base_url');
        $link = $front_url . "{$merchant->merchant_code}";

        return response()->json([
            "ResponseStatus" => "successful",
            "ResponseCode" => 200,
            "Detail" => "Profile Link Edited successfully.",
            "ResponseMessage" => "Profile Link have been edited successfully the new link is in the data.",
            "data" => $link
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





    public function generateStoreLink(Request $request)
    {
        try {
            $merchant = $this->getAuthUser($request);

            $store = Store::where('merchant_id', $merchant->id)->first();

            if (!$store) {
                return response()->json([
                    "ResponseStatus" => "Unsuccessful",
                    "ResponseCode" => 404,
                    "Detail" => "No store found for this merchant.",
                    "ResponseMessage" => "You need to create a store first before generating a store link.",
                ], 404);
            }


            $storeIdentifier = $this->generateUrlSlug($store->store_name);
            $front_url = cc('frontend_base_url');
            $storeLink = $front_url  . $storeIdentifier;


            $store->store_link = $storeLink;
            $store->save();


            if (method_exists($this, 'updateGrowServiceProgress')) {
                $this->updateGrowServiceProgress($merchant->id, $store->id, "create_store_link");
            }

            return response()->json([
                "ResponseStatus" => "successful",
                "ResponseCode" => 200,
                "Detail" => "Store Link generated successfully.",
                "ResponseMessage" => "Store Link has been generated and saved successfully.",
                "data" => [
                    "store_link" => $storeLink,
                    "store_id" => $store->id,
                    "store_name" => $store->store_name
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                "ResponseStatus" => "Unsuccessful",
                "ResponseCode" => 500,
                "Detail" => $e->getMessage(),
                "ResponseMessage" => "Something went wrong while generating store link.",
            ], 500);
        }
    }


    public function updateStoreLink(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'store_name' => 'required|string|max:255',
            'store_id' => 'sometimes|integer|exists:stores,id'
        ]);

        if ($validate->fails()) {
            return $this->validationError($validate);
        }

        try {
            $merchant = $this->getAuthUser($request);


            if ($request->has('store_id')) {
                $store = Store::where('id', $request->store_id)
                    ->where('merchant_id', $merchant->id)
                    ->first();
            } else {
                $store = Store::where('merchant_id', $merchant->id)->first();
            }

            if (!$store) {
                return response()->json([
                    "ResponseStatus" => "Unsuccessful",
                    "ResponseCode" => 404,
                    "Detail" => "Store not found or you don't have permission to modify this store.",
                    "ResponseMessage" => "Store not found or access denied.",
                ], 404);
            }


            if ($store->merchant_id !== $merchant->id) {
                return response()->json([
                    "ResponseStatus" => "Unsuccessful",
                    "ResponseCode" => 403,
                    "Detail" => "You don't have permission to modify this store.",
                    "ResponseMessage" => "Access denied. You can only modify your own stores.",
                ], 403);
            }

            $existingStore = Store::where('store_name', $request->store_name)
                ->where('merchant_id', $merchant->id)
                ->where('id', '!=', $store->id)
                ->first();

            if ($existingStore) {
                return response()->json([
                    "ResponseStatus" => "Unsuccessful",
                    "ResponseCode" => 400,
                    "Detail" => "You already have a store with this name.",
                    "ResponseMessage" => "Store name already exists for your account. Please choose a different name.",
                ], 400);
            }

            $store->store_name = $request->store_name;

            $front_url = cc('frontend_base_url');
            $storeSlug = $this->generateUrlSlug($request->store_name);
            $newStoreLink = $front_url . $storeSlug;
            $store->store_link = $newStoreLink;

            $store->save();

            return response()->json([
                "ResponseStatus" => "successful",
                "ResponseCode" => 200,
                "Detail" => "Store Link updated successfully.",
                "ResponseMessage" => "Store Link has been updated successfully. The new link is in the data.",
                "data" => [
                    "store_link" => $newStoreLink,
                    "store_id" => $store->id,
                    "store_name" => $store->store_name,
                    "store_slug" => $storeSlug
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                "ResponseStatus" => "Unsuccessful",
                "ResponseCode" => 500,
                "Detail" => $e->getMessage(),
                "ResponseMessage" => "Something went wrong while updating store link.",
            ], 500);
        }
    }


    private function generateUrlSlug($storeName)
    {

        $slug = strtolower(trim($storeName));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');

        return $slug;
    }

    public function setBankDetials(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'bank_account_name' => 'nullable|string',
            'bank_account_number' => 'nullable|numeric',
            'bank_routing_number' => 'nullable|numeric',
            'bank_account_code' => 'nullable|string'
        ]);

        if ($validate->fails()) {
            return $this->validationError($validate);
        }

        try {

            $merchant = $this->getAuthUser($request);
            $merchantId = $merchant->id;
            $existingBankDetail = $merchant->bank_details;

            if (
                (!$request->filled('bank_account_name') && (!$existingBankDetail || !$existingBankDetail->bank_name)) ||
                (!$request->filled('bank_account_number') && (!$existingBankDetail || !$existingBankDetail->account_number))
            ) {
                throw new Exception('Both bank name and account number are required but were not provided.');
            }
            $bankDetails = UserBankDetails::updateOrCreate(
                ['user_id' => $merchantId],
                [
                    'bank_name' => $request->bank_account_name ?? $existingBankDetail->bank_name ?? null,
                    'account_number' => $request->bank_account_number ?? $existingBankDetail->account_number ?? null,
                    'routing_number' => $request->bank_routing_number ?? $existingBankDetail->routing_number ?? null,
                    'bank_code' => $request->bank_account_code ?? $existingBankDetail->bank_code ?? null,
                ]
            );

            $bankDetails = new BankDetails($bankDetails);

            return response()->json([
                "ResponseStatus" => "successful",
                "ResponseCode" => 200,
                "Detail" => "Bank Details Updated successfully.",
                "ResponseMessage" => "The Bank Details Have been Succesfully Updated",
                "data" => $bankDetails
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


    public function setBankDetailsV2(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'bank_account_number' => 'required|numeric',
            'bank_account_code' => 'required|string',
            'bank_routing_number' => 'nullable|numeric'
        ]);

        if ($validate->fails()) {
            return $this->validationError($validate);
        }

        try {

            $merchant = $this->getAuthUser($request);
            $merchantId = $merchant->id;
            $existingBankDetail = $merchant->bank_details;

            // $vfdUtil = new VfdUtils();
            // $nameEnquiry = $vfdUtil->nameEnquiry($request->bank_account_number, $request->bank_account_code);

            // if ($nameEnquiry['error'] != 0) {
            //     return response()->json([
            //         "ResponseStatus" => "Unsuccessful",
            //         "ResponseCode" => 400,
            //         "Detail" => "Invalid Bank Account Number or Bank Account Code",
            //         "ResponseMessage" => $nameEnquiry['responseMessage'],
            //     ], 400);
            // }
            // ;

            // $bankName = $nameEnquiry['accountInfo']['bank'];
            // $accountNumber = $nameEnquiry['accountInfo']['account']['number'];
            // $accountName = $nameEnquiry['accountInfo']['name'];



            // $bankDetails = UserBankDetails::updateOrCreate(
            //     ['user_id' => $merchantId],
            //     [
            //         'bank_name' => $bankName ?? $existingBankDetail->bank_name ?? null,
            //         'account_number' => $accountNumber ?? $existingBankDetail->account_number ?? null,
            //         'routing_number' => $request->bank_routing_number ?? $existingBankDetail->routing_number ?? null,
            //         'account_name' => $accountName ?? $existingBankDetail->account_name ?? null,
            //         'bank_code' => $request->bank_account_code ?? $existingBankDetail->bank_code ?? null,
            //     ]
            // );

            $bankDetails = UserBankDetails::updateOrCreate(
                ['user_id' => $merchantId],
                [
                    'bank_name' => $merchant->name,
                    'account_number' => $request->bank_account_number ?? $existingBankDetail->account_number ?? null,
                    'routing_number' => $request->bank_routing_number ?? $existingBankDetail->routing_number ?? null,
                    'account_name' => $existingBankDetail->account_name ?? null,
                    'bank_code' => $request->bank_account_code ?? $existingBankDetail->bank_code ?? null,
                ]
            );

            $bankDetails = new BankDetails($bankDetails);

            return response()->json([
                "ResponseStatus" => "successful",
                "ResponseCode" => 200,
                "Detail" => "Bank Details Updated successfully.",
                "ResponseMessage" => "The Bank Details Have been Succesfully Updated",
                "data" => $bankDetails
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

    public function getBankDetails(Request $request)
    {
        try {

            $user = $this->getAuthUser($request);

            if (!is_null($user->bank_details)) {
                $bankDetails = new BankDetails($user->bank_details);
            } else {
                $bankDetails = [];
            }


            return response()->json([
                "ResponseStatus" => "successful",
                "ResponseCode" => 200,
                "Detail" => "Bank Details fecthed successfully.",
                "ResponseMessage" => "The Bank Details Have been Succesfully fecthed",
                "data" => $bankDetails
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

    public function setWithdrawalSchedule(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'withdrawal_schedule' => 'required|in:auto_daily,auto_weekly,auto_monthly,manual',
        ]);

        if ($validate->fails()) {
            return $this->validationError($validate);
        }

        try {
            $merchant = $this->getAuthUser($request);
            $merchant->withdrawal_schedule = $request->withdrawal_schedule;
            $merchant->save();

            return response()->json([
                "ResponseStatus" => "successful",
                "ResponseCode" => 200,
                "Detail" => "Withdrawal schedule set successfully.",
                "ResponseMessage" => "The withdrawal schedule has been successfully updated.",
                "data" => $merchant->withdrawal_schedule
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

    public function getWithdrawalSchedule(Request $request)
    {
        try {
            $merchant = $this->getAuthUser($request);

            return response()->json([
                "ResponseStatus" => "successful",
                "ResponseCode" => 200,
                "Detail" => "Withdrawal schedule retrieved successfully.",
                "ResponseMessage" => "The withdrawal schedule has been fetched successfully.",
                "data" => $merchant->withdrawal_schedule
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

    public function getStoreEarnings(Request $request)
    {
        try {
            // Validate request parameters
            $validate = Validator::make($request->all(), [
                'period' => 'required|string|in:today,week,month,total,custom',
                'start_date' => 'required_if:period,custom|date',
                'end_date' => 'required_if:period,custom|date|after_or_equal:start_date',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:5|max:100',
                'store_id' => 'required|integer|exists:stores,id'
            ]);

            if ($validate->fails()) {
                return $this->validationError($validate);
            }

            $period = $request->period;
            $page = $request->page ?? 1;
            $perPage = $request->per_page ?? 15;
            $storeId = $request->store_id;

            // Determine date range based on selected period
            $dateRange = $this->getDateRange($period, $request->start_date, $request->end_date);

            // Get balance summary
            $balanceSummary = $this->getBalanceSummary($dateRange['start'], $dateRange['end'], $storeId);

            // Get combined transactions with pagination
            $transactions = $this->getTransactions($dateRange['start'], $dateRange['end'], $storeId, $page, $perPage);

            return response()->json([
                'summary' => $balanceSummary,
                'transactions' => $transactions,
                'period' => $period,
                'store_id' => $storeId,
                'date_range' => [
                    'start' => $dateRange['start'] ? $dateRange['start']->format('Y-m-d') : null,
                    'end' => $dateRange['end'] ? $dateRange['end']->format('Y-m-d') : null,
                ],
            ]);

        } catch (Exception $e) {
            return response()->json([
                "ResponseStatus" => "Unsuccessful",
                "ResponseCode" => 500,
                "Detail" => $e->getMessage(),
                "ResponseMessage" => "Something went wrong.",
            ], 500);
        }

    }

    private function getDateRange($period, $startDate = null, $endDate = null)
    {
        $now = Carbon::now();

        switch ($period) {
            case 'today':
                return [
                    'start' => $now->copy()->startOfDay(),
                    'end' => $now->copy()->endOfDay(),
                ];
            case 'week':
                return [
                    'start' => $now->copy()->startOfWeek(),
                    'end' => $now->copy()->endOfWeek(),
                ];
            case 'month':
                return [
                    'start' => $now->copy()->startOfMonth(),
                    'end' => $now->copy()->endOfMonth(),
                ];
            case 'total':
                return [
                    'start' => null,
                    'end' => null,
                ];
            case 'custom':
                return [
                    'start' => Carbon::parse($startDate)->startOfDay(),
                    'end' => Carbon::parse($endDate)->endOfDay(),
                ];
            default:
                return [
                    'start' => $now->copy()->startOfDay(),
                    'end' => $now->copy()->endOfDay(),
                ];
        }
    }

    private function getBalanceSummary($startDate, $endDate, $storeId)
    {
        // Query for appointments - directly filter by store_id
        $appointmentsQuery = DB::table('appointments')
            ->where('payment_status', 1)
            ->where('store_id', $storeId);

        // Query for booth rentals - join with booth_rental_payments to filter by user_store_id
        $boothRentalsQuery = DB::table('booth_rental_payments')
            ->join('booth_rentals', 'booth_rental_payments.booth_rental_id', '=', 'booth_rentals.id')
            ->where('booth_rentals.store_id', $storeId);

        // Apply date filters if not requesting total
        if ($startDate && $endDate) {
            $appointmentsQuery->whereBetween('appointments.created_at', [$startDate, $endDate]);
            $boothRentalsQuery->whereBetween('booth_rental_payments.created_at', [$startDate, $endDate]);
        }

        // Get sums from both tables
        $appointmentsSum = $appointmentsQuery->sum('total_amount');
        $boothRentalsSum = $boothRentalsQuery->sum('booth_rental_payments.amount');

        // Calculate total balance
        $totalBalance = $appointmentsSum + $boothRentalsSum;

        return [
            'total_balance' => $totalBalance,
            'appointments_income' => $appointmentsSum,
            'booth_rentals_income' => $boothRentalsSum,
        ];
    }

    private function getTransactions($startDate, $endDate, $storeId, $page = 1, $perPage = 15)
    {
        // Get appointments data with store filter
        $appointmentsQuery = DB::table('appointments')
            ->join('users', 'appointments.merchant_id', '=', 'users.id')

            // ->join('user_stores', 'users.id', '=', 'user_stores.user_id')
            // ->join('service_types', 'service_types.id', '=', 'user_stores.service_type_id')  //Commenting out the service type until its resolved

            ->where('appointments.payment_status', 1)
            ->where('appointments.store_id', $storeId)
            ->select(
                'appointments.id',
                'total_amount as amount',
                'appointments.created_at as date',
                DB::raw("'appointment' as source"),
                'appointments.merchant_id',
                'users.id as user_id',
                'users.name as user_name',
                // 'service_types.name as service_type',     //Commenting out the service type until its resolved
                'users.profile_image_link  as user_profile_image',
            );

        // Get booth rentals data with multiple joins to get the user_id as merchant_id
        $boothRentalsQuery = DB::table('booth_rent_payment_histories')
            ->join('booth_rental_payments', 'booth_rent_payment_histories.booth_rent_payment_id', '=', 'booth_rental_payments.id')
            ->join('booth_rentals', 'booth_rental_payments.booth_rental_id', '=', 'booth_rentals.id')
            ->join('users', 'booth_rentals.user_id', '=', 'users.id')
            // ->join('user_stores', 'user_stores.user_id', '=', 'users.id')
            // ->join('service_types', 'service_types.id', '=', 'user_stores.service_type_id') //Commenting out the service type until its resolved
            ->where('booth_rental_payments.user_store_id', $storeId)
            ->select(
                'booth_rent_payment_histories.id',
                'amount_paid as amount',
                'booth_rent_payment_histories.created_at as date',
                DB::raw("'booth_rental' as source"),
                'booth_rentals.user_id as merchant_id',
                'users.id as user_id',
                'users.name as user_name',
                // 'service_types.name as service_type',   //Commenting out the service type until its resolved
                'users.profile_image_link  as user_profile_image'
            );

        // Apply date range filter if not total
        if ($startDate && $endDate) {
            $appointmentsQuery->whereBetween('appointments.created_at', [$startDate, $endDate]);
            $boothRentalsQuery->whereBetween('booth_rent_payment_histories.created_at', [$startDate, $endDate]);
        }

        // Get all bindings from both queries
        $appointmentsBindings = $appointmentsQuery->getBindings();
        $boothRentalsBindings = $boothRentalsQuery->getBindings();

        // Using DB::raw with proper bindings for the union query
        $sql = $appointmentsQuery->toSql() . ' UNION ALL ' . $boothRentalsQuery->toSql();
        $bindings = array_merge($appointmentsBindings, $boothRentalsBindings);

        // Create the combined query with proper bindings
        $combinedQuery = DB::table(DB::raw('(' . $sql . ') as combined_transactions'))
            ->setBindings($bindings)
            ->orderBy('date', 'desc');

        // Get total count for pagination
        $totalCount = $combinedQuery->count();

        // Apply pagination
        $paginatedResults = $combinedQuery->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        // Create a paginator instance
        $paginator = new LengthAwarePaginator(
            $paginatedResults,
            $totalCount,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return $paginator;
    }

}