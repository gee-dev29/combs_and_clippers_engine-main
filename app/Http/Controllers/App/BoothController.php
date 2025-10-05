<?php

namespace App\Http\Controllers\App;

use Exception;
use Carbon\Carbon;
use App\Models\Store;
use App\Models\UserStore;
use App\Models\BoothRental;
use Illuminate\Http\Request;
use App\Models\BoothRentalPayment;
use App\Http\Controllers\Controller;
use App\Models\BoothRentPaymentHistory;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\BoothRentalResource;
use App\Http\Resources\BoothRentalPaymentResource;
use App\Notifications\BoothRentalNotification;

class BoothController extends Controller
{
    public function setUpBoothRent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id' => 'required|integer|exists:stores,id',
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


            $boothRental = BoothRental::create([
                'store_id' => $request->store_id,
                'amount' => $request->amount,
                'payment_timeline' => $request->payment_timeline,
                'payment_days' => $request->payment_days,
                'service_type_id' => $request->service_type_id,
            ]);

            if ($boothRental) {
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
            return response()->json([
                "ResponseStatus" => "Unsuccessful",
                "Detail" => $e->getMessage(),
                'message' => 'Something went wrong',
            ], 500);
        }
    }

    public function updateBoothRent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id' => 'required|integer|exists:stores,id',
            'booth_id' => 'required|integer|exists:booth_rentals,id',
            'amount' => 'nullable|numeric|min:0',
            'payment_timeline' => 'nullable|in:weekly,every two weeks,twice a month,monthly',
            'payment_days' => 'nullable|in:sunday,monday,tuesday,wednesday,thursday,friday,saturday',
            'service_type_id' => 'nullable|exists:store_service_types,id',
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


            $boothRental = BoothRental::where('store_id', $request->store_id)
                ->where('id', $request->booth_id)
                ->first();

            if (!$boothRental) {
                return response()->json([
                    "ResponseStatus" => "Unsuccessful",
                    'message' => 'Booth rental settings not found for this store and booth.',
                ], 404);
            }


            $boothRental->update($request->only(['amount', 'payment_timeline', 'payment_days', 'service_type_id']));

            return response()->json([
                "ResponseStatus" => "Successful",
                'message' => 'Booth rental settings have been updated successfully.',
            ]);

        } catch (Exception $e) {
            return response()->json([
                "ResponseStatus" => "Unsuccessful",
                "Detail" => $e->getMessage(),
                'message' => 'Something went wrong.',
            ], 500);
        }
    }


    public function viewBoothRents(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id' => 'required|integer|exists:stores,id',
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

            $boothRental = BoothRental::where('store_id', $request->store_id)->get();

            $boothRental = BoothRentalResource::collection($boothRental);

            if (!$boothRental) {
                return response()->json([
                    "ResponseStatus" => "Unsuccessful",
                    'message' => 'Booth rental settings not found for this store',
                ], 404);
            }

            return response()->json([
                "ResponseStatus" => "Successful",
                'message' => 'Booth rental settings retrieved successfully',
                'data' => $boothRental
            ]);

        } catch (Exception $e) {
            return response()->json([
                "ResponseStatus" => "Unsuccessful",
                "Detail" => $e->getMessage(),
                'message' => 'Something went wrong',
            ], 500);
        }
    }

    public function showBoothRent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id' => 'required|integer|exists:stores,id',
            'booth_id' => 'required|integer|exists:booth_rentals,id',
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


            $boothRental = BoothRental::where('store_id', $request->store_id)
                ->where('id', $request->booth_id)
                ->first();

            if (!$boothRental) {
                return response()->json([
                    "ResponseStatus" => "Unsuccessful",
                    'message' => 'Booth rental setting not found for this store and booth',
                ], 404);
            }


            $boothRental = new BoothRentalResource($boothRental);

            return response()->json([
                "ResponseStatus" => "Successful",
                'message' => 'Booth rental setting retrieved successfully',
                'data' => $boothRental
            ]);

        } catch (Exception $e) {
            return response()->json([
                "ResponseStatus" => "Unsuccessful",
                "Detail" => $e->getMessage(),
                'message' => 'Something went wrong',
            ], 500);
        }
    }



    public function deleteBoothRent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id' => 'required|integer|exists:stores,id',
            'booth_id' => 'required|integer|exists:booth_rentals,id',
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


            $boothRental = BoothRental::where('store_id', $request->store_id)
                ->where('id', $request->booth_id)
                ->first();


            if (!$boothRental) {
                return response()->json([
                    "ResponseStatus" => "Unsuccessful",
                    'message' => 'Booth rental settings not found for this store and booth.',
                ], 404);
            }


            $boothRental->delete();

            return response()->json([
                "ResponseStatus" => "Successful",
                'message' => 'Booth rental settings have been deleted successfully.',
            ]);

        } catch (Exception $e) {
            return response()->json([
                "ResponseStatus" => "Unsuccessful",
                "Detail" => $e->getMessage(),
                'message' => 'Something went wrong.',
            ], 500);
        }
    }


    public function markAsPaid(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "store_user_id" => "required|integer",
            "booth_id" => "required|integer|exists:booth_rentals,id",
            "store_id" => "required|integer|exists:stores,id",
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        try {
            $storeOwnerId = $this->getAuthID($request);
            $store = Store::findOrFail($request->store_id);


            if (!$store->merchant_id || $storeOwnerId != $store->merchant_id) {
                return response()->json([
                    "ResponseStatus" => "Unsuccessful",
                    'message' => 'Unauthorized',
                ], 403);
            }


            $boothRental = BoothRental::where('store_id', $request->store_id)
                ->where('id', $request->booth_id)
                ->first();

            if (!$boothRental) {
                return response()->json([
                    "ResponseStatus" => "Unsuccessful",
                    'message' => 'Booth rental settings not found for this store and booth.',
                ], 404);
            }


            $storeUser = UserStore::where("user_id", $request->store_user_id)
                ->where("store_id", $store->id)
                // ->where("service_type_id", $boothRental->service_type_id) //removing for user to be able to mark 
                ->first();

            if (!$storeUser) {
                return response()->json([
                    "ResponseStatus" => "Unsuccessful",
                    'message' => 'This user does not belong to this store or any store.',
                ], 404);
            }


            $payment_date = Carbon::now();
            $next_payment_date = $this->calculateNextPaymentDate($boothRental, $payment_date);


            $boothRentPayment = BoothRentalPayment::updateOrCreate(
                [
                    "user_store_id" => $storeUser->id,
                    "booth_rental_id" => $boothRental->id
                ],
                [
                    "last_payment_date" => $payment_date,
                    "next_payment_date" => $next_payment_date,
                    "amount" => $boothRental->amount,
                    "payment_status" => 'paid'
                ]
            );


            if ($boothRentPayment) {
                BoothRentPaymentHistory::create([
                    "booth_rent_payment_id" => $boothRentPayment->id,
                    "amount_paid" => $boothRental->amount,
                    "payment_date" => $payment_date
                ]);
            }

            $boothRentPayment->userStore->user->notify(new BoothRentalNotification($boothRentPayment));
            $store->owner->notify(new BoothRentalNotification($boothRentPayment));

            return response()->json([
                'status' => 'success',
                'message' => 'Payment recorded successfully.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                "ResponseStatus" => "Unsuccessful",
                "Detail" => $e->getMessage(),
                'message' => 'Something went wrong.',
            ], 500);
        }
    }



    public function boothUsersPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "store_id" => "required|integer|exists:stores,id",
            "filter" => "nullable|string|in:all,paid,upcoming,late,due,not_paid",
            "booth_id" => "nullable|integer|exists:booth_rentals,id",
            "group" => "nullable|boolean",
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        try {
            $storeOwnerId = $this->getAuthID($request);
            $store = Store::findOrFail($request->store_id);

            if (!$store->merchant_id || $storeOwnerId != $store->merchant_id) {
                return response()->json([
                    "ResponseStatus" => "Unsuccessful",
                    'message' => 'Unauthorized access.',
                ], 403);
            }

            if($request->filled("booth_id")){
                $boothRental = BoothRental::where('store_id', $request->store_id)
                    ->where('id', $request->booth_id)
                    ->first();
                $boothRentalIds = [$boothRental->id];
            }else{
                $boothRentalIds = BoothRental::where('store_id', $request->store_id)
                    ->pluck('id');
            }
            

            if (is_null($boothRentalIds)) {
                return response()->json([
                    "ResponseStatus" => "Unsuccessful",
                    'message' => 'Booth rental settings not found for this store and booth.',
                ], 404);
            }


            $query = BoothRentalPayment::whereIn('booth_rental_id', $boothRentalIds);
            $today = Carbon::today();

            switch ($request->filter) {
                case 'due':
                    $query->whereDate('next_payment_date', $today)->where('payment_status', '!=', 'paid');
                    break;
                case 'upcoming':
                    $query->whereDate('next_payment_date', $today->copy()->addDay())->where('payment_status', '!=', 'paid');
                    break;
                case 'late':
                    $query->whereDate('next_payment_date', '<', $today)->where('payment_status', '!=', 'paid');
                    break;
                case 'paid':
                    $query->where('payment_status', 'paid');
                    break;
                case 'not_paid':
                    $query->where('payment_status', '!=', 'paid');
                    break;
                default:
                    $query->where('payment_status', '!=', 'paid');
            }

            $payments = $query->get();
            $group = $request->boolean('group', true);

            if ($group) {

                $groupedPayments = $payments->groupBy(function ($payment) use ($today) {
                    $nextPaymentDate = Carbon::parse($payment->next_payment_date);

                    if (!$nextPaymentDate) {
                        return 'unknown';
                    } elseif ($nextPaymentDate->isToday()) {
                        return 'due today';
                    } elseif ($nextPaymentDate->isTomorrow()) {
                        return 'due tomorrow';
                    } elseif ($nextPaymentDate->isPast()) {
                        return 'overdue';
                    } else {
                        return 'not yet due';
                    }
                });


                $formattedGroups = $groupedPayments->map(function ($group) {
                    return BoothRentalPaymentResource::collection($group);
                });

                return response()->json([
                    'status' => 'success',
                    'data' => $formattedGroups,
                ]);
            } else {

                $payments = BoothRentalPaymentResource::collection($payments);

                return response()->json([
                    'status' => 'success',
                    'data' => $payments,
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                "ResponseStatus" => "Unsuccessful",
                "Detail" => $e->getMessage(),
                'message' => 'Something went wrong.',
            ], 500);
        }
    }

    public function findBooth(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'store_id' => 'nullable|integer|exists:stores,id',
            'location' => 'nullable|array',
            'location.state' => 'nullable|string',
            'location.city' => 'nullable|string',
            'location.street' => 'nullable|string',
            'name' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        try {

            $query = BoothRental::query();


            if ($request->has('store_id')) {
                $query->where('store_id', $request->store_id);
            }


            if ($request->has('location')) {
                $location = $request->location;


                if (isset($location['state'])) {
                    $query->whereHas('store.storeAddress', function ($q) use ($location) {
                        $q->whereRaw('LOWER(state) = ?', [strtolower($location['state'])]);
                    });
                }


                if (isset($location['city'])) {
                    $query->whereHas('store.storeAddress', function ($q) use ($location) {
                        $q->whereRaw('LOWER(city) = ?', [strtolower($location['city'])]);
                    });
                }


                if (isset($location['street'])) {
                    $query->whereHas('store.storeAddress', function ($q) use ($location) {
                        $q->whereRaw('LOWER(street) = ?', [strtolower($location['street'])]);
                    });
                }
            }

            if ($request->has('name') && !empty($request->input('name'))) {
                $store_name = $request->name;
                $query->whereHas('store', function ($q) use ($store_name) {
                    $q->whereRaw('LOWER(store_name) = ?', [strtolower($store_name)]);
                });
            }



            $booths = $query->get();
            $booths = BoothRentalResource::collection($booths);

            // if ($booths->isEmpty()) {
            //     return response()->json([
            //         "ResponseStatus" => "Unsuccessful",
            //         'message' => 'No booths found matching the criteria.',
            //     ], 404);
            // }

            return response()->json([
                "ResponseStatus" => "Successful",
                'message' => 'Booth rental settings retrieved successfully.',
                'data' => $booths
            ]);

        } catch (Exception $e) {
            return response()->json([
                "ResponseStatus" => "Unsuccessful",
                "Detail" => $e->getMessage(),
                'message' => 'Something went wrong.',
            ], 500);
        }
    }

    // Function to handle validation errors, similar to your existing method
    public function validationError($validator)
    {
        return response()->json([
            "ResponseStatus" => "Unsuccessful",
            "errors" => $validator->errors(),
            'message' => 'Validation failed.',
        ], 422);
    }
}