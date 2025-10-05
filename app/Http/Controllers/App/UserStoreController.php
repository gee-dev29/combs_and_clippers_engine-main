<?php

namespace App\Http\Controllers\App;

use App\Http\Resources\ServiceProviderResource;
use Exception;
use Carbon\Carbon;
use App\Models\Store;
use App\Models\UserStore;
use App\Models\BoothRental;
use Illuminate\Http\Request;
use App\Models\BoothRentalPayment;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserStoreResource;
use App\Jobs\LinkMerchantVirtualAccount;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log as Logger;

class UserStoreController extends Controller
{

    // public function store(Request $request)
    // {

    //     $validator = Validator::make($request->all(), [
    //         'store_code' => 'required|string',
    //         'service_type_id' => 'required|exists:store_service_types,id',
    //     ]);

    //     if ($validator->fails()) {
    //         return $this->validationError($validator);
    //     }

    //     try {
    //         $userId = $this->getAuthID($request);
    //         $storeCode = $request->store_code;


    //         $store = Store::where('store_code', $storeCode)->first();

    //         if (!$store) {
    //             return response()->json([
    //                 'ResponseStatus' => 'Unsuccessful',
    //                 'ResponseCode' => 404,
    //                 'Detail' => 'Store not found.',
    //                 'ResponseMessage' => 'We could not find the store for this Invite Link.',
    //             ], 404);
    //         }


    //         $existingUserStore = UserStore::where([
    //             'user_id' => $userId,
    //             'store_id' => $store->id,
    //             'service_type_id' => $request->service_type_id,
    //         ])->first();


    //         if ($existingUserStore) {
    //             return response()->json([
    //                 'ResponseStatus' => 'Unsuccessful',
    //                 'ResponseCode' => 409,
    //                 'Detail' => 'User already added.',
    //                 'ResponseMessage' => 'The user is already added to this store with the same service type.',
    //             ], 409);
    //         }


    //         UserStore::where('user_id', $userId)
    //             ->where('store_id', '!=', $store->id)
    //             ->update(['current' => false]);


    //         UserStore::where('user_id', $userId)
    //             ->where('store_id', $store->id)
    //             ->update(['current' => true]);


    //         $userStore = UserStore::create([
    //             'user_id' => $userId,
    //             'store_id' => $store->id,
    //             'current' => true,
    //             'service_type_id' => $request->service_type_id,
    //         ]);

    //         $boothRent = $store->boothRent;
    //         if ($boothRent) {
    //             $date = Carbon::now();
    //             $next_payment_date = $this->calculateNextPaymentDate($boothRent, $date);
    //             BoothRentalPayment::create([
    //                 "user_store_id" => $userStore->id,
    //                 "booth_rental_id" => $boothRent->id,
    //                 "next_payment_date" => $next_payment_date,
    //                 "payment_status" => 'upcoming'
    //             ]);
    //         }




    //         return response()->json([
    //             'ResponseStatus' => 'Successful',
    //             'ResponseCode' => 200,
    //             'message' => 'User has been added to the store successfully.',
    //         ], 200);

    //     } catch (Exception $e) {
    //         return response()->json([
    //             'ResponseStatus' => 'Unsuccessful',
    //             'ResponseCode' => 500,
    //             'Detail' => $e->getMessage(),
    //             'ResponseMessage' => 'Something went wrong while adding the user to the store.',
    //         ], 500);
    //     }
    // }



    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'booth_code' => 'nullable|string',
            //'store_id' => 'required_without:booth_code|integer|exists:stores,id',
            //'booth_id' => 'required_without:booth_code|integer|exists:booth_rentals,id',

        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        try {
            $user = $this->getAuthUser($request);
            $userId = $user->id;
            $boothCode = $request->booth_code;
            $storeId = 0;
            //$boothId = $request->booth_id;


            if ($boothCode) {
                $decryptedData = $this->decryptBoothCode($boothCode);
                $storeId = $decryptedData['store_id'];
                //$boothId = $decryptedData['booth_id'];
            }


            $store = Store::findOrFail($storeId);

            $existingCurrentStore = UserStore::where('user_id', $userId)
                ->where('current', true)
                ->first();

            if ($existingCurrentStore) {
                return response()->json([
                    'ResponseStatus' => 'Unsuccessful',
                    'ResponseCode' => 409,
                    'Detail' => 'User already joined a store.',
                    'ResponseMessage' => 'You can only join one store at a time. Please leave your current store first.',
                ], 409);
            }


            // $boothRental = BoothRental::create('id', $boothId)
            //     ->where('store_id', $storeId)
            //     ->first();

            $existingUserStore = UserStore::where([
                'user_id' => $userId,
                'store_id' => $store->id
            ])->whereNull('service_type_id')->first();

            if ($existingUserStore) {
                return response()->json([
                    'ResponseStatus' => 'Unsuccessful',
                    'ResponseCode' => 409,
                    'Detail' => 'User already added.',
                    'ResponseMessage' => 'The user is already added to this store with the same service type.',
                ], 409);
            }


            $boothRental = BoothRental::create([
                'store_id' => $storeId,
                'user_id' => $userId,
                'payment_timeline' => 'weekly',
            ]);

            if (!$boothRental) {
                return response()->json([
                    'ResponseStatus' => 'Unsuccessful',
                    'ResponseCode' => 404,
                    'Detail' => 'Booth initialization failed.',
                    'ResponseMessage' => 'Booth setup initialization failed.',
                ], 404);
            }


            // UserStore::where('user_id', $userId)
            //     ->where('store_id', '!=', $store->id)
            //     ->update(['current' => false]);


            // UserStore::where('user_id', $userId)
            //     ->where('store_id', $store->id)
            //     ->update(['current' => true]);

            UserStore::where('user_id', $userId)->update(['current' => false]);

            $userStore = UserStore::create([
                'user_id' => $userId,
                'store_id' => $store->id,
                'current' => true,
            ]);

            // if ($boothRental) {
            //     $date = Carbon::now();
            //     $next_payment_date = $this->calculateNextPaymentDate($boothRental, $date);
            //     BoothRentalPayment::create([
            //         'user_store_id' => $userStore->id,
            //         'booth_rental_id' => $boothRental->id,
            //         'next_payment_date' => $next_payment_date,
            //         'payment_status' => 'upcoming',
            //     ]);
            // }

            return response()->json([
                'ResponseStatus' => 'Successful',
                'ResponseCode' => 200,
                'message' => 'User has been added to the store successfully.',
                'user' => new ServiceProviderResource($user)
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'ResponseStatus' => 'Unsuccessful',
                'ResponseCode' => 500,
                'Detail' => $e->getMessage(),
                'ResponseMessage' => 'Something went wrong while adding the user to the store.',
            ], 500);
        }
    }


    public function removeUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id' => 'required|integer',
            'user_id' => 'required|integer',
            'service_type_id' => 'nullable|exists:service_types,id'
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        try {
            $merchantId = $this->getAuthID($request);
            $storeId = $request->store_id;
            $userId = $request->user_id;
            $serviceTypeId = $request->service_type_id;

            $store = Store::find($storeId);
            if ($merchantId != $store->merchant_id) {
                return response()->json([
                    'message' => 'Unauthorized access.'
                ], 401);
            }

            $query = UserStore::where('store_id', $storeId)->where('user_id', $userId);

            if ($serviceTypeId) {
                $query->where('service_type_id', $serviceTypeId);
            }

            $userStores = $query->get();


            foreach ($userStores as $userStore) {
                BoothRentalPayment::where('user_store_id', $userStore->id)->delete();
            }

            BoothRental::where('user_id', $userId)
                ->where('store_id', $storeId)
                ->delete();


            $deleted = $query->delete();

            if ($deleted) {
                return response()->json([
                    'ResponseStatus' => "Successful",
                    'ResponseCode' => 200,
                    'message' => 'User has been removed from the store successfully.'
                ], 200);
            } else {
                return response()->json([
                    'ResponseStatus' => "Unsuccessful",
                    'ResponseCode' => 404,
                    'message' => 'No matching record found for removal.'
                ], 404);
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

    public function leaveStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id' => 'required|integer|exists:stores,id',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        try {
            $userId = $this->getAuthID($request);
            $storeId = $request->store_id;

            // Find the user's association with this store
            $userStore = UserStore::where('user_id', $userId)
                ->where('store_id', $storeId)
                ->first();

            if (!$userStore) {
                return response()->json([
                    'ResponseStatus' => 'Unsuccessful',
                    'ResponseCode' => 404,
                    'message' => 'You are not associated with this store.',
                ], 404);
            }

            // Delete booth rental payments associated with this user-store relationship
            BoothRentalPayment::where('user_store_id', $userStore->id)->delete();

            // Delete booth rentals created by this user for this store
            BoothRental::where('user_id', $userId)
                ->where('store_id', $storeId)
                ->delete();

            // Remove the user-store association
            $userStore->delete();

            return response()->json([
                'ResponseStatus' => 'Successful',
                'ResponseCode' => 200,
                'message' => 'You have successfully left the store.',
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'ResponseStatus' => 'Unsuccessful',
                'ResponseCode' => 500,
                'Detail' => $e->getMessage(),
                'message' => 'Something went wrong while leaving the store.',
            ], 500);
        }
    }

    public function setUpBoothRent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id' => 'required|integer|exists:stores,id',
            'booth_id' => 'required|integer|exists:booth_rentals,id',
            'amount' => 'required|numeric|min:0',
            'payment_timeline' => 'required|in:weekly,every two weeks,twice a month,monthly',
            'payment_days' => 'required|in:sunday,monday,tuesday,wednesday,thursday,friday,saturday',
            'service_type_id' => 'required|exists:store_service_types,id',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        try {
            $userId = $this->getAuthID($request);
            $store = Store::findOrFail($request->store_id);



            if (!$store->merchant_id || $userId != $store->merchant_id) {
                return response()->json([
                    "ResponseStatus" => "Unsuccessful",
                    'message' => 'Unauthorized',
                ], 403);
            }

            $booth = BoothRental::where('id', $request->booth_id)
                ->where('store_id', $request->store_id)
                ->first();

            if (!$booth) {
                return response()->json([
                    'ResponseStatus' => "Unsuccessful",
                    'ResponseCode' => 404,
                    'message' => 'The specified booth does not belong to this store.',
                ], 404);
            }


            $booth->update([
                'amount' => $request->amount,
                'payment_timeline' => $request->payment_timeline,
                'payment_days' => $request->payment_days,
                'service_type_id' => $request->service_type_id,
            ]);

            $userStore = UserStore::where([
                'user_id' => $booth->user_id,
                'store_id' => $store->id
            ])->whereNull('service_type_id')->first();

            if ($booth) {
                $date = Carbon::now();
                $next_payment_date = $this->calculateNextPaymentDate($booth, $date);
                $booth_rent_pay = BoothRentalPayment::create([
                    'user_store_id' => $userStore->id,
                    'booth_rental_id' => $booth->id,
                    'next_payment_date' => $next_payment_date,
                    'payment_status' => 'upcoming',
                    'amount' => $booth->amount,
                ]);
                //Link merchant's account
                LinkMerchantVirtualAccount::dispatch($booth_rent_pay->id, 'booth_rental_payment');
            }

            if ($booth) {
                return response()->json([
                    "ResponseStatus" => "Successful",
                    'message' => 'Booth has been set successfully',
                ]);
            }

            return response()->json([
                "ResponseStatus" => "Unsuccessful",
                'message' => 'Booth could not be set',
            ], 500);

        } catch (Exception $e) {
            Logger::info('Login Error', [$e->getMessage() . ' - ' . $e->__toString()]);
            return response()->json([
                "ResponseStatus" => "Unsuccessful",
                "Detail" => $e->getMessage(),
                'message' => 'Something went wrong',
            ], 500);
        }
    }



    public function generateStoreCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id' => 'required|integer|exists:stores,id',
            //'booth_id' => 'required|integer|exists:booth_rentals,id',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        try {
            $merchantId = $this->getAuthID($request);
            $storeId = $request->store_id;
            //$booth_id = $merchantId;


            $store = Store::findOrFail($storeId);
            if ($merchantId != $store->merchant_id) {
                return response()->json([
                    'ResponseStatus' => "Unsuccessful",
                    'ResponseCode' => 401,
                    'message' => 'Unauthorized access.',
                ], 401);
            }


            // $booth = BoothRental::where('id', $boothId)
            //     ->where('store_id', $storeId)
            //     ->first();

            // if (!$booth) {
            //     return response()->json([
            //         'ResponseStatus' => "Unsuccessful",
            //         'ResponseCode' => 404,
            //         'message' => 'The specified booth does not belong to this store.',
            //     ], 404);
            // }


            $newBoothCode = $this->generateBoothCode($storeId, $merchantId);
            // $booth->invite_code = $newBoothCode;
            // $booth->save();

            return response()->json([
                'ResponseStatus' => "Successful",
                'ResponseCode' => 200,
                'data' => $newBoothCode,
                'message' => 'Booth Code generated successfully.',
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                "ResponseStatus" => "Unsuccessful",
                "ResponseCode" => 500,
                "Detail" => $e->getMessage(),
                'message' => 'Something went wrong',
            ], 500);
        }
    }


    public function getStoreUsers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "store_id" => "required|integer|exists:stores,id",
            "service_type_id" => "nullable|exists:store_service_types,id"
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        try {
            $store_id = $request->store_id;
            $service_type_id = $request->service_type_id ?? null;
            $store_users = UserStore::query();

            if ($service_type_id) {
                $store_users->where("service_type_id", $service_type_id);
            }

            $store_users = $store_users->where("store_id", $store_id)->get();

            $store_users = UserStoreResource::collection($store_users);

            return response()->json([
                'ResponseStatus' => "Successful",
                'ResponseCode' => 200,
                'data' => $store_users,
                'message' => 'Store Users Retreived Succesfully.'
            ], 200);






        } catch (Exception $e) {
            Logger::info('View store staff error', [$e->getMessage() . ' - ' . $e->__toString()]);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function switchStores(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "store_id" => "required|integer|exists:stores,id",
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        try {
            $userId = $this->getAuthID($request);
            $storeId = $request->store_id;


            UserStore::where('user_id', $userId)->update(['current' => false]);


            $updatedRows = UserStore::where('user_id', $userId)
                ->where('store_id', $storeId)
                ->update(['current' => true]);

            if ($updatedRows === 0) {
                return response()->json([
                    "ResponseStatus" => "Unsuccessful",
                    "ResponseCode" => 404,
                    "message" => "The user is not associated with this store.",
                ], 404);
            }

            return response()->json([
                'ResponseStatus' => "Successful",
                'ResponseCode' => 200,
                'message' => 'Store switched successfully.',
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                "ResponseStatus" => "Unsuccessful",
                "ResponseCode" => 500,
                'Detail' => $e->getMessage(),
                'message' => 'Something went wrong.',
            ], 500);
        }
    }

    public function sendBoothRentReminder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id' => 'required|integer|exists:stores,id',
            'booth_id' => 'required|integer|exists:booth_rentals,id',
        ]);

        if ($validator->fails()) {
            return response()->json(["ResponseStatus" => "Unsuccessful", 'Detail' => $validator->errors(), "ResponseCode" => 400, "ResponseMessage" => implode(', ', $validator->messages()->all()), "message" => implode(', ', $validator->messages()->all())], 400);
        }
        try {
            $booth = BoothRental::where('id', $request->booth_id)
                ->where('store_id', $request->store_id)
                ->first();

            if (!$booth) {
                return response()->json([
                    'ResponseStatus' => "Unsuccessful",
                    'ResponseCode' => 404,
                    'message' => 'The specified booth does not belong to this store.',
                ], 404);
            }
            $user = $booth->user;
            if (!$user) {
                return response()->json([
                    'ResponseStatus' => "Unsuccessful",
                    'ResponseCode' => 404,
                    'message' => 'The specified booth user does not exist.',
                ], 404);
            }
            $this->Mailer->sendBoothRentReminder($user, 0);
            return response()->json(["ResponseStatus" => "Successful", "ResponseCode" => 200, 'Detail' => 'Email has been sent successfully', "ResponseMessage" => 'Email has been sent successfully'], 200);
        } catch (Exception $e) {
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }


}