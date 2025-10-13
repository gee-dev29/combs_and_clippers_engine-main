<?php

namespace App\Http\Controllers\App;

use stdClass;
use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Store;
use App\Models\Coupon;
use App\Models\Review;
use App\Models\Address;
use App\Models\Booking;
use App\Models\Country;
use App\Models\Service;
use App\Models\Interest;
use App\Models\Referral;
use App\Models\UserStore;
use App\Models\StoreVisit;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\ServicesPromo;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\StoreResource;
use App\Repositories\GoogleValidator;
use App\Http\Resources\BookingResource;
use App\Http\Resources\FXStoreResource;
use App\Http\Resources\ReviewsResource;
use App\Http\Resources\ServiceResource;
use App\Models\ServiceAvailabiltyHours;
use App\Http\Resources\FXServiceResource;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\StorePreviewResource;
use Illuminate\Support\Facades\Log as Logger;
use App\Http\Resources\ServiceProviderResource;
use App\Http\Resources\ServiceProviderReviewResource;
use App\Http\Resources\ServiceProviderDetailsResource;
use App\Http\Resources\ServiceProviderPreviewResource;


class ServiceController extends Controller
{
    public function myServices(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'query' => 'nullable'
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        $merchantID = $this->getAuthID($request);
        $merchant = User::find($merchantID);
        try {
            if ($request->filled('query')) {
                return $this->serviceSearch($request['merchantID'], $request['query']);
            }

            if (!is_null($merchant)) {
                $services = Service::where('merchant_id', $merchant->id)->orderBy('id', 'DESC')->paginate($this->perPage);
                $services = $this->addMeta(ServiceResource::collection($services));
                return response()->json(compact('services'), 200);
            }
            return $this->errorResponse('Merchant not found', 404);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }


    // public function addService(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'name' => 'required|string|max:255',
    //         'description' => 'required|string',
    //         'service_image' => 'required|mimes:jpeg,jpg,png,gif,bmp|max:5140',
    //         'optional_images.*' => 'nullable|mimes:jpeg,jpg,png,gif,bmp|max:5120',
    //         'price' => 'required|numeric',
    //         'home_service_charge' => 'required|numeric',
    //         'currency' => 'required|string',
    //         'price_type' => 'string|required|in:fixed,free',
    //         'status' => 'boolean|nullable',
    //         'duration' => 'string|required',
    //         'buffer' => 'string|nullable',
    //         'payment_preference' => 'string|required|in:online,offline,deposit',
    //         'deposit' => 'integer|required_if:payment_preference,deposit|min:10|max:70',
    //         'location' => 'string|required|in:home,away',
    //         'allow_cancellation' => 'boolean|required',
    //         'allowed_cancellation_period' => 'string|nullable',
    //         'allow_rescheduling' => 'boolean|required',
    //         'allowed_rescheduling_period' => 'string|nullable',
    //         'booking_reminder' => 'boolean|required',
    //         'booking_reminder_period' => 'string|nullable',
    //         'limit_early_booking' => 'boolean|required',
    //         'early_booking_limit_period' => 'string|nullable',
    //         'limit_late_booking' => 'boolean|required',
    //         'late_booking_limit_period' => 'string|nullable',
    //         'checkout_label' => 'string|nullable',
    //     ]);
    //     if ($validator->fails()) {
    //         return $this->validationError($validator);
    //     }

    //     try {
    //         $merchantID = $this->getAuthID($request);
    //         $merchant = User::find($merchantID);
    //         if (!is_null($merchant)) {
    //             $slug = generateSlug($request['name']);
    //             $service =  Service::create([
    //                 'merchant_id' => $merchant->id,
    //                 'name' => $request['name'],
    //                 'description' => $request['description'],
    //                 'slug'  => $slug,
    //                 'price'  => $request['price'],
    //                 'home_service_charge'  => $request['home_service_charge'],
    //                 'currency'  => $request['currency'],
    //                 'status'  => $request['status'],
    //                 'price_type'  => $request['price_type'],
    //                 'image_url'  => '',
    //                 'duration' => $request['duration'],
    //                 'buffer' => $request['buffer'],
    //                 'payment_preference' => $request['payment_preference'],
    //                 'deposit' => $request['deposit'],
    //                 'location' => $request['location'],
    //                 'allow_cancellation' => $request['allow_cancellation'],
    //                 'allowed_cancellation_period' => $request['allowed_cancellation_period'],
    //                 'allow_rescheduling' => $request['allow_rescheduling'],
    //                 'allowed_rescheduling_period' => $request['allowed_rescheduling_period'],
    //                 'booking_reminder' => $request['booking_reminder'],
    //                 'booking_reminder_period' => $request['booking_reminder_period'],
    //                 'limit_early_booking' => $request['limit_early_booking'],
    //                 'early_booking_limit_period' => $request['early_booking_limit_period'],
    //                 'limit_late_booking' => $request['limit_late_booking'],
    //                 'late_booking_limit_period' => $request['late_booking_limit_period'],
    //                 'checkout_label' => $request['checkout_label'],
    //             ]);


    //             if ($request->hasFile('service_image')) {
    //                 $imageArray = $this->imageUtil->saveImgArray($request->file('service_image'), '/services/', $service->id, $request->hasFile('optional_images') ? $request->file('optional_images') : []);

    //                 if (!is_null($imageArray)) {
    //                     $primaryImg = array_shift($imageArray);
    //                     $service->update(['image_url' => $primaryImg]);
    //                 }

    //                 $otherImgs = $imageArray;
    //                 if (!empty($otherImgs)) {
    //                     foreach ($otherImgs as $photo) {
    //                         $productPhotos[] = ['image_url' => $photo];
    //                     }
    //                     $service->photos()->createMany($productPhotos);
    //                 }
    //             }

    //             $services = Service::where('merchant_id', $merchant->id)->orderBy('id', 'DESC')->paginate($this->perPage);

    //             $services = $this->addMeta(ServiceResource::collection($services));

    //             return response()->json(compact('services'), 201);
    //         }
    //         return $this->errorResponse('Merchant not found', 404);
    //     } catch (Exception $e) {
    //         $this->reportExceptionOnBugsnag($e);
    //         return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
    //     }
    // }

    public function addService(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'duration' => 'required|string',
            'service_image' => 'nullable|mimes:jpeg,jpg,png,gif,bmp|max:5140',
            'optional_images.*' => 'nullable|mimes:jpeg,jpg,png,gif,bmp|max:5120',
            'home_service_charge' => 'nullable|numeric',
            'currency' => 'nullable|string',
            'price_type' => 'nullable|string|in:fixed,free',
            'status' => 'nullable|boolean',
            'buffer' => 'nullable|string',
            'location' => 'nullable|string|in:home,away',
            'allow_cancellation' => 'nullable|boolean',
            'allowed_cancellation_period' => 'nullable|string',
            'allow_rescheduling' => 'nullable|boolean',
            'allowed_rescheduling_period' => 'nullable|string',
            'booking_reminder' => 'nullable|boolean',
            'booking_reminder_period' => 'nullable|string',
            'limit_early_booking' => 'nullable|boolean',
            'early_booking_limit_period' => 'nullable|string',
            'limit_late_booking' => 'nullable|boolean',
            'late_booking_limit_period' => 'nullable|string',
            'checkout_label' => 'nullable|string',
            'availability_hours' => 'nullable|array',
            'availability_hours.*.day' => 'required|string',
            'availability_hours.*.start_time' => 'required|date_format:H:i',
            'availability_hours.*.end_time' => 'required|date_format:H:i|after:availability_hours.*.start_time',
            'promo_discount' => 'nullable|numeric',
            'promo_start' => 'nullable|string',
            'promo_end' => 'nullable|string',
            'promo_status' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        try {
            $merchantID = $this->getAuthID($request);
            $merchant = User::find($merchantID);
            if ($merchant->account_type == "Owner") {
                $store = Store::where('merchant_id', $merchantID)->first();
            } else {
                $userStore = UserStore::where([
                    'user_id' => $merchantID,
                    //'available_status' => 1
                ])->first();
                if (!is_null($userStore)) {
                    $store = $userStore->store;
                } else {
                    $store = Store::where('merchant_id', $merchantID)->first();
                }
            }


            if (!is_null($merchant)) {
                $slug = generateSlug($request['name']);

                $service = Service::create([
                    'merchant_id' => $merchant->id,
                    'stored_id' => $store->id ?? null,
                    'name' => $request['name'],
                    'description' => $request['description'],
                    'slug' => $slug,
                    'price' => $request['price'],
                    'home_service_charge' => $request->input('home_service_charge', null),
                    'currency' => $request->input('currency', 'NGN'),
                    'status' => $request->input('status', 1),
                    'price_type' => $request->input('price_type', "fixed"),
                    'image_url' => '',
                    'duration' => $request['duration'],
                    'buffer' => $request->input('buffer', null),
                    'location' => $request->input('location', "No location"),
                    'allow_cancellation' => $request->input('allow_cancellation', 1),
                    'allowed_cancellation_period' => $request->input('allowed_cancellation_period', null),
                    'allow_rescheduling' => $request->input('allow_rescheduling', 1),
                    'allowed_rescheduling_period' => $request->input('allowed_rescheduling_period', null),
                    'booking_reminder' => $request->input('booking_reminder', 1),
                    'booking_reminder_period' => $request->input('booking_reminder_period', null),
                    'limit_early_booking' => $request->input('limit_early_booking', 0),
                    'early_booking_limit_period' => $request->input('early_booking_limit_period', null),
                    'limit_late_booking' => $request->input('limit_late_booking', 1),
                    'late_booking_limit_period' => $request->input('late_booking_limit_period', null),
                    'checkout_label' => $request->input('checkout_label', "Book Now"),
                    'payment_preference' => $store->payment_preferences['payment_preference'] ?? 'in_app',
                ]);
                if (!is_null($store)) {
                    $this->updateBoothProgress($merchantID, $store->id, "setup_my_service");
                }


                if ($request->hasFile('service_image')) {
                    $imageArray = $this->imageUtil->saveImgArray($request->file('service_image'), '/services/', $service->id, $request->hasFile('optional_images') ? $request->file('optional_images') : []);

                    if (!is_null($imageArray)) {
                        $primaryImg = array_shift($imageArray);
                        $service->update(['image_url' => $primaryImg]);
                    }

                    $otherImgs = $imageArray;
                    if (!empty($otherImgs)) {
                        foreach ($otherImgs as $photo) {
                            $productPhotos[] = ['image_url' => $photo];
                        }
                        $service->photos()->createMany($productPhotos);
                    }
                }

                if ($request->has('availability_hours')) {
                    $availabilityHours = $request->input('availability_hours', []);

                    foreach ($availabilityHours as $availability) {
                        ServiceAvailabiltyHours::create([
                            'service_id' => $service->id,
                            'day' => $availability['day'],
                            'start_time' => $availability['start_time'],
                            'end_time' => $availability['end_time']
                        ]);
                    }
                }

                if ($request->filled('promo_discount') && $request->filled('promo_start')) {
                    ServicesPromo::create([
                        'service_id' => $service->id,
                        'discount_amount' => $request->input('promo_discount', null),
                        'start_date' => $request->input('promo_start', null),
                        'end_date' => $request->input('promo_end', null),
                        'promo_status' => $request->input('promo_status', false)
                    ]);
                }


                $services = Service::where('merchant_id', $merchant->id)->orderBy('id', 'DESC')->paginate($this->perPage);

                $services = $this->addMeta(ServiceResource::collection($services));

                return response()->json(compact('services'), 201);
            }
            return $this->errorResponse('Merchant not found', 404);
        } catch (Exception $e) {
            Logger::info('AAdd service Error', [$e->getMessage() . ' - ' . $e->__toString()]);
            $this->reportExceptionOnBugsnag($e);
            return response()->json([
                'ResponseStatus'  => 'Unsuccessful',
                'ResponseCode'    => 500,
                'Detail'          => $e->getMessage(),
                'ResponseMessage' => 'Something went wrong'
            ], 500);
        }
    }



    // public function updateService(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'serviceID' => 'required|integer',
    //         'name' => 'required|string|max:255',
    //         'description' => 'required|string',
    //         'service_image' => 'nullable|mimes:jpeg,jpg,png,gif,bmp|max:5140',
    //         'optional_images.*' => 'nullable|mimes:jpeg,jpg,png,gif,bmp|max:5120',
    //         'price' => 'required|numeric',
    //         'home_service_charge' => 'required|numeric',
    //         'currency' => 'required|string',
    //         'price_type' => 'string|required|in:fixed,free',
    //         'status' => 'boolean|nullable',
    //         'duration' => 'string|required',
    //         'buffer' => 'string|nullable',
    //         'payment_preference' => 'string|required|in:online,offline,deposit',
    //         'deposit' => 'integer|required_if:payment_preference,deposit|min:10|max:70',
    //         'location' => 'string|required|in:home,away',
    //         'allow_cancellation' => 'boolean|required',
    //         'allowed_cancellation_period' => 'string|nullable',
    //         'allow_rescheduling' => 'boolean|required',
    //         'allowed_rescheduling_period' => 'string|nullable',
    //         'booking_reminder' => 'boolean|required',
    //         'booking_reminder_period' => 'string|nullable',
    //         'limit_early_booking' => 'boolean|required',
    //         'early_booking_limit_period' => 'string|nullable',
    //         'limit_late_booking' => 'boolean|required',
    //         'late_booking_limit_period' => 'string|nullable',
    //         'checkout_label' => 'string|nullable',
    //     ]);

    //     if ($validator->fails()) {
    //         return $this->validationError($validator);
    //     }

    //     try {
    //         $merchantID = $this->getAuthID($request);
    //         $merchant = User::find($merchantID);
    //         if (!is_null($merchant)) {
    //             $serviceID = $request['serviceID'];
    //             $service = Service::where([['id', $serviceID], ['merchant_id', $merchant->id]])->first();
    //             if (is_null($service)) {
    //                 return $this->errorResponse('Service not found', 404);
    //             }
    //             $service->update([
    //                 'name' => $request->filled('name') ? $request->input('name') : $service->name,
    //                 'description' => $request->filled('description') ? $request->input('description') : $service->description,
    //                 'price' => $request->filled('price') ? $request->input('price') : $service->price,
    //                 'currency' => $request->filled('currency') ? $request->input('currency') : $service->currency,
    //                 'status' => $request->filled('status') ? $request->input('status') : $service->status,
    //                 'price_type'  => $request->filled('price_type') ? $request->input('price_type') : $service->price_type,
    //                 'duration' => $request->filled('duration') ? $request->input('duration') : $service->duration,
    //                 'buffer' => $request->filled('buffer') ? $request->input('buffer') : $service->buffer,
    //                 'payment_preference' => $request->filled('payment_preference') ? $request->input('payment_preference') : $service->payment_preference,
    //                 'deposit' => $request->filled('deposit'),
    //                 'location' => $request->filled('location') ? $request->input('location') : $service->location,
    //                 'home_service_charge' => $request->filled('home_service_charge') ? $request->input('home_service_charge') : $service->home_service_charge,
    //                 'allow_cancellation' => $request->filled('allow_cancellation') ? $request->input('allow_cancellation') : $service->allow_cancellation,
    //                 'allowed_cancellation_period' => $request->filled('allowed_cancellation_period') ? $request->input('allowed_cancellation_period') : $service->allowed_cancellation_period,
    //                 'allow_rescheduling' => $request->filled('allow_rescheduling') ? $request->input('allow_rescheduling') : $service->allow_rescheduling,
    //                 'allowed_rescheduling_period' => $request->filled('allowed_rescheduling_period') ? $request->input('allowed_rescheduling_period') : $service->allowed_rescheduling_period,
    //                 'booking_reminder' => $request->filled('booking_reminder') ? $request->input('booking_reminder') : $service->booking_reminder,
    //                 'booking_reminder_period' => $request->filled('booking_reminder_period') ? $request->input('booking_reminder_period') : $service->booking_reminder_period,
    //                 'limit_early_booking' => $request->filled('limit_early_booking') ? $request->input('limit_early_booking') : $service->limit_early_booking,
    //                 'early_booking_limit_period' => $request->filled('early_booking_limit_period') ? $request->input('early_booking_limit_period') : $service->early_booking_limit_period,
    //                 'limit_late_booking' => $request->filled('limit_late_booking') ? $request->input('limit_late_booking') : $service->limit_late_booking,
    //                 'late_booking_limit_period' => $request->filled('late_booking_limit_period') ? $request->input('late_booking_limit_period') : $service->late_booking_limit_period,
    //                 'checkout_label' => $request->filled('checkout_label') ? $request->input('checkout_label') : $service->checkout_label,
    //             ]);
    //             if ($request->hasFile('service_image')) {
    //                 if (!is_null($service->image_url)) {
    //                     $this->imageUtil->deleteImage($service->image_url);
    //                 }
    //                 $imageArray = $this->imageUtil->saveImgArray($request->file('service_image'), '/services/', $service->id, $request->hasFile('optional_images') ? $request->file('optional_images') : []);
    //                 if (!is_null($imageArray)) {
    //                     $primaryImg = array_shift($imageArray);
    //                     $service->update(['image_url' => $primaryImg]);
    //                     $otherImgs = $imageArray;
    //                     if (!empty($otherImgs)) {
    //                         foreach ($otherImgs as $photo) {
    //                             $productPhotos[] = ['image_url' => $photo];
    //                         }
    //                         $service->photos()->createMany($productPhotos);
    //                     }
    //                 }
    //             }
    //             $services = Service::where('merchant_id', $merchant->id)->orderBy('id', 'DESC')->paginate($this->perPage);
    //             $services = $this->addMeta(ServiceResource::collection($services));

    //             return response()->json(compact('services'), 201);
    //         }
    //         return $this->errorResponse('Merchant not found', 404);
    //     } catch (Exception $e) {
    //         $this->reportExceptionOnBugsnag($e);
    //         return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
    //     }
    // }


    public function updateService(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'serviceID' => 'required|integer',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'service_image' => 'nullable|mimes:jpeg,jpg,png,gif,bmp|max:5140',
            'optional_images.*' => 'nullable|mimes:jpeg,jpg,png,gif,bmp|max:5120',
            'price' => 'required|numeric',
            'home_service_charge' => 'nullable|numeric',
            'currency' => 'nullable|string',
            'price_type' => 'nullable|string|in:fixed,free',
            'status' => 'nullable|boolean',
            'duration' => 'nullable|string',
            'buffer' => 'nullable|string',
            'location' => 'nullable|string|in:home,away',
            'allow_cancellation' => 'nullable|boolean',
            'allowed_cancellation_period' => 'nullable|string',
            'allow_rescheduling' => 'nullable|boolean',
            'allowed_rescheduling_period' => 'nullable|string',
            'booking_reminder' => 'nullable|boolean',
            'booking_reminder_period' => 'nullable|string',
            'limit_early_booking' => 'nullable|boolean',
            'early_booking_limit_period' => 'nullable|string',
            'limit_late_booking' => 'nullable|boolean',
            'late_booking_limit_period' => 'nullable|string',
            'checkout_label' => 'nullable|string',
            'availability_hours' => 'nullable|array',
            'availability_hours.*.day' => 'required|string',
            'availability_hours.*.start_time' => 'required|date_format:H:i',
            'availability_hours.*.end_time' => 'required|date_format:H:i|after:availability_hours.*.start_time',
            'promo_discount' => 'nullable|numeric',
            'promo_start' => 'nullable|string',
            'promo_end' => 'nullable|string',
            'promo_status' => 'nullable|in:true,false,1,0,"true","false","1","0"',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        try {
            $merchantID = $this->getAuthID($request);
            $merchant = User::find($merchantID);
            if (is_null($merchant)) {
                return $this->errorResponse('Merchant not found', 404);
            }
            $service = Service::where('id', $request->serviceID)
                ->where('merchant_id', $merchantID)
                ->first();

            if (is_null($service)) {
                return $this->errorResponse('Service not found', 404);
            }

            // Update service data
            $service->update($request->only([
                'name',
                'description',
                'price',
                'currency',
                'status',
                'price_type',
                'duration',
                'buffer',
                'location',
                'home_service_charge',
                'allow_cancellation',
                'allowed_cancellation_period',
                'allow_rescheduling',
                'allowed_rescheduling_period',
                'booking_reminder',
                'booking_reminder_period',
                'limit_early_booking',
                'early_booking_limit_period',
                'limit_late_booking',
                'late_booking_limit_period',
                'checkout_label'
            ]));

            // Handle image updates
            if ($request->hasFile('service_image')) {
                if (!is_null($service->image_url)) {
                    $this->imageUtil->deleteImage($service->image_url);
                }
                $imageArray = $this->imageUtil->saveImgArray($request->file('service_image'), '/services/', $service->id, $request->file('optional_images') ?? []);
                if ($imageArray) {
                    $service->update(['image_url' => array_shift($imageArray)]);
                    if ($imageArray) {
                        $service->photos()->createMany(array_map(fn($img) => ['image_url' => $img], $imageArray));
                    }
                }
            }

            // Update availability hours
            if ($request->has('availability_hours')) {
                foreach ($request->availability_hours as $availability) {
                    $service->availabilityHours()->updateOrCreate(
                        ['service_id' => $service->id, 'day' => $availability['day']],
                        [
                            'start_time' => $availability['start_time'],
                            'end_time' => $availability['end_time']
                        ]
                    );
                }
            }


            // Update promos
            if ($request->has('promo_discount')) {
                $promoData = [
                    'discount_amount' => $request->promo_discount,
                    'start_date' => $request->promo_start,
                    'end_date' => $request->promo_end,
                ];


                if ($request->has('promo_status')) {
                    $promoData['status'] = filter_var($request->promo_status, FILTER_VALIDATE_BOOLEAN);
                } else {
                    $promoData['status'] = true;
                }

                $service->promotions()->updateOrCreate(
                    ['service_id' => $service->id],
                    $promoData
                );
            }

            $services = Service::where('merchant_id', $merchant->id)->orderBy('id', 'DESC')->paginate($this->perPage);
            $services = $this->addMeta(ServiceResource::collection($services));

            return response()->json(compact('services'), 201);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json([
                "ResponseStatus" => "Unsuccessful",
                "ResponseCode" => 500,
                'Detail' => $e->getMessage(),
                'message' => 'Something went wrong',
                "ResponseMessage" => 'Something went wrong'
            ], 500);
        }
    }


    public function duplicateService(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'serviceID' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        try {
            $merchantID = $this->getAuthID($request);
            $merchant = User::find($merchantID);
            if (!is_null($merchant)) {
                $serviceID = $request['serviceID'];
                $service = Service::where([['id', $serviceID], ['merchant_id', $merchant->id]])->first();
                if (is_null($service)) {
                    return $this->errorResponse('Service not found', 404);
                }
                // Duplicate the service
                $duplicate = $service->replicate();
                $duplicate->name = $service->name . ' (Copy)';
                $duplicate->slug = $service->slug . mt_rand(1, 1000);
                $duplicate->save();

                // Duplicate and link the photos to the new service
                foreach ($service->photos as $photo) {
                    $newPhoto = $photo->replicate();
                    $newPhoto->service_id = $duplicate->id;
                    $newPhoto->save();
                }
                $services = Service::where('merchant_id', $merchant->id)->orderBy('id', 'DESC')->paginate($this->perPage);
                $services = $this->addMeta(ServiceResource::collection($services));

                return response()->json(compact('services'), 201);
            }
            return $this->errorResponse('Merchant not found', 404);
        } catch (Exception $e) {
            Logger::info('Duplicate Service Error', [$e->getMessage() . ' - ' . $e->__toString()]);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function toggleService(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'serviceID' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        try {
            $merchantID = $this->getAuthID($request);
            $merchant = User::find($merchantID);
            if (!is_null($merchant)) {
                $serviceID = $request['serviceID'];
                $service = Service::where([['id', $serviceID], ['merchant_id', $merchant->id]])->first();
                if (is_null($service)) {
                    return $this->errorResponse('Service not found', 404);
                }
                if ($service->status) {
                    $service->update(['status' => 0]);
                } else {
                    $service->update(['status' => 1]);
                }
                $services = Service::where('merchant_id', $merchant->id)->orderBy('id', 'DESC')->paginate($this->perPage);
                $services = $this->addMeta(ServiceResource::collection($services));
                return response()->json(compact('services'), 201);
            }
            return $this->errorResponse('Merchant not found', 404);
        } catch (Exception $e) {
            Logger::info('Toggle Service Error', [$e->getMessage() . ' - ' . $e->__toString()]);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function deleteService(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'serviceID' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        try {
            $merchantID = $this->getAuthID($request);
            $merchant = User::find($merchantID);
            if (!is_null($merchant)) {
                $serviceID = $request['serviceID'];
                $service = Service::where([['id', $serviceID], ['merchant_id', $merchant->id]])->first();
                if (is_null($service)) {
                    return $this->errorResponse('Service not found', 404);
                }
                $service->photos()->delete();
                $service->delete();
                $services = Service::where('merchant_id', $merchant->id)->orderBy('id', 'DESC')->paginate($this->perPage);
                $services = $this->addMeta(ServiceResource::collection($services));
                return response()->json(compact('services'), 201);
            }
            return $this->errorResponse('Merchant not found', 404);
        } catch (Exception $e) {
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function getService(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'serviceID' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        try {
            $service = Service::find($request->input('serviceID'));
            if (!is_null($service)) {
                $merchant = User::find($service->merchant_id);
                if (!is_null($merchant)) {
                    $service = new ServiceResource($service);
                    return response()->json(compact('service'), 201);
                }
                return $this->errorResponse('Merchant not found', 404);
            }
            return $this->errorResponse('Service not found', 404);
        } catch (Exception $e) {
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function getMerchantServices(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'merchantCode' => 'required|string',
            'keyword' => 'nullable|string|max:100',
            'sortBy' => 'nullable|string|in:productname,price,created_at',
            'direction' => 'nullable|string|in:ASC,DESC'
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        try {
            $sortBy = $request->filled('sortBy') ? $request->sortBy : 'id';
            $direction = $request->filled('direction') ? $request->direction : 'DESC';
            $merchant = User::where('merchant_code', $request['merchantCode'])->first();
            if (is_null($merchant)) {
                return $this->errorResponse('Merchant not found', 404);
            }
            $store = Store::with('owner.store_address')->where('merchant_id', $merchant->id)->latest()->first();
            if (!is_null($store)) {
                //record store visit
                $ip = $request->getClientIp();
                $visit = StoreVisit::where(['merchant_id' => $merchant->id, 'store_id' => $store->id, 'visitor_ip' => $ip])
                    ->whereDate('created_at', Carbon::today())->first();
                if (is_null($visit)) {
                    StoreVisit::create([
                        'merchant_id' => $merchant->id,
                        'store_id' => $store->id,
                        'visitor_ip' => $ip
                    ]);
                }
                $store = new StoreResource($store);
            } else {
                $store = new stdClass;
            }
            $services = Service::where('merchant_id', $merchant->id)->orderBy($sortBy, $direction);
            if ($request->filled('keyword')) {
                $keyword = '%' . $request->keyword . '%';
                $services->where('name', 'like', $keyword)
                    ->orWhere('description', 'like', $keyword);
            }
            $services = $this->addMeta(ServiceResource::collection($services->paginate($this->perPage)));

            $reviews = $merchant->reviewsReceived;
            $reviews = ReviewsResource::collection($reviews);

            return response()->json(compact('store', 'services', 'reviews'), 200);
            //return $this->errorResponse('Please, finish your store setup', 400);
        } catch (Exception $e) {
            Logger::info('Get merchant Services Error', [$e->getMessage() . ' - ' . $e->__toString()]);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }




    protected function serviceSearch($merchantID, $query)
    {
        try {
            $merchant = User::find($merchantID);
            if (!is_null($merchant)) {
                if (filter_var($query, FILTER_VALIDATE_INT)) {
                    //query parameter is an email
                    $id = $query;
                    $services = Service::where([['merchant_id', $merchant->id], ['id', $id]])->paginate($this->perPage);
                    $services = $this->addMeta(ServiceResource::collection($services));

                    return response()->json(compact('services'), 201);
                } elseif (!filter_var($query, FILTER_VALIDATE_INT)) {
                    //query parameter is a transaction reference number  ['title','like','%'.$text.'%']
                    $query = filter_var($query, FILTER_SANITIZE_STRING);
                    //$transcode = $request['query'];
                    $services = Service::where([['merchant_id', $merchant->id], ['name', 'like', '%' . $query . '%']])
                        ->orWhere([['merchant_id', $merchant->id], ['description', 'like', '%' . $query . '%']])
                        ->paginate($this->perPage);
                    $services = $this->addMeta(ServiceResource::collection($services));

                    return response()->json(compact('services'), 201);
                }
                return $this->errorResponse('Your search parameter is invalid', 400);
            }
            return $this->errorResponse('Merchant not found', 404);
        } catch (Exception $e) {
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }


    protected function serviceSearchOpen($query)
    {
        try {
            if (filter_var($query, FILTER_VALIDATE_INT)) {
                //query parameter is an email
                $id = $query;
                $services = Service::where([['id', $id]])->paginate($this->perPage);
                $services = $this->addMeta(ServiceResource::collection($services));

                return response()->json(compact('services'), 201);
            } elseif (!filter_var($query, FILTER_VALIDATE_INT)) {
                //query parameter is a transaction reference number  ['title','like','%'.$text.'%']
                $query = filter_var($query, FILTER_SANITIZE_STRING);
                //$transcode = $request['query'];
                $services = Service::where([['name', 'like', '%' . $query . '%']])
                    ->orWhere([['description', 'like', '%' . $query . '%']])
                    ->paginate($this->perPage);
                $services = $this->addMeta(ServiceResource::collection($services));

                return response()->json(compact('services'), 201);
            }
            return $this->errorResponse('Your search parameter is invalid', 400);
        } catch (Exception $e) {
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }


    public function deleteMultipleServices(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'serviceIDs' => 'required|array',
            'serviceIDs.*' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        try {
            $id = $this->getCustomerID($request);
            $merchant = User::find($id);
            if (!is_null($merchant)) {
                $serviceIDs = $request['serviceIDs'];
                foreach ($serviceIDs as $serviceID) {
                    $service = Service::where([['id', $serviceID], ['merchant_id', $merchant->id]])->first();
                    if (!is_null($service)) {
                        $service->photos()->delete();
                        $service->delete();
                    }
                }
                $services = Service::where('merchant_id', $merchant->id)->orderBy('id', 'DESC')->paginate($this->perPage);
                $services = $this->addMeta(ServiceResource::collection($services));
                return response()->json(compact('services'), 201);
            }
            return $this->errorResponse('Merchant not found', 404);
        } catch (Exception $e) {
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function getBookedSlots(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'merchantID' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        try {
            $merchant = User::find($request->merchantID);
            if (is_null($merchant)) {
                return $this->errorResponse('Merchant not found', 404);
            }

            // Only include appointments that are active (not cancelled or denied)
            $appointments = Appointment::where('merchant_id', $merchant->id)
                ->where('payment_status', 1) // Only paid appointments
                ->whereNotIn('status', ['Cancelled', 'Denied']) // Exclude cancelled/denied
                ->select('date', 'time')
                ->get()
                ->groupBy('date')
                ->map(function ($dateGroup) {
                    return $dateGroup->pluck('time');
                });
            return response()->json($appointments);
        } catch (Exception $e) {
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    // public function getServiceProviders(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'category_id' => 'nullable|exists:categories,id',
    //         'price' => 'nullable|numeric',
    //         'account_type' => 'nullable|string|in:Client,Stylist,Owner',
    //         'lat' => 'nullable',
    //         'long' => 'nullable'
    //         // 'availability' => 'nullable|string',
    //         // 'nearby' => 'nullable|string',
    //     ]);

    //     if ($validator->fails()) {
    //         return $this->validationError($validator);
    //     }
    //     try {
    //         $query = User::withCount(['bookings', 'services']);
    //         if ($request->has('account_type')) {
    //             $query->where('account_type', $request->account_type);
    //         }

    //         if($request->filled('account_type') && $request->account_type == 'Owner'){
    //             if ($request->has('category_id')) {
    //                 $category = $request->input('category_id');
    //                 $query->whereHas('store', function ($q) use ($category) {
    //                     $q->where('store_category', $category);
    //                 });
    //             }
    //         }else{
    //             $query->whereHas('services');
    //             if ($request->has('category_id')) {
    //                 $category = $request->input('category_id');
    //                 $query->whereHas('userStores.store', function ($q) use ($category) {
    //                     $q->where('store_category', $category);
    //                 });
    //             }

    //             if ($request->has('price')) {
    //                 $price = $request->input('price');
    //                 $query->whereHas('services', function ($q) use ($price) {
    //                     $q->where('price', '<=', $price);
    //                 });
    //             }
    //         }


    //         $serviceProviders = $query->orderBy('bookings_count', 'desc')
    //             ->paginate($this->perPage);
    //         $serviceProviders = $this->addMeta(ServiceProviderResource::collection($serviceProviders));
    //         return response()->json(compact('serviceProviders'), 200);
    //     } catch (Exception $e) {
    //         $this->reportExceptionOnBugsnag($e);
    //         return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
    //     }
    // }


    // public function getServiceProviders(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'category_id' => 'nullable|exists:categories,id',
    //         'price' => 'nullable|numeric',
    //         'account_type' => 'nullable|string|in:Client,Stylist,Owner',
    //         'latitude' => 'nullable|numeric',
    //         'longitude' => 'nullable|numeric'
    //     ]);

    //     if ($validator->fails()) {
    //         return $this->validationError($validator);
    //     }

    //     try {
    //         $query = User::withCount(['bookings', 'services']);

    //         if ($request->has('account_type')) {
    //             $query->where('account_type', $request->account_type);
    //         }

    //         if ($request->filled('latitude') && $request->filled('longitude')) {
    //             $latitude = $request->latitude;
    //             $longitude = $request->longitude;
    //             $radius = 10; // Set search radius in km

    //             $stores = Store::whereHas('storeAddress', function ($q) use ($latitude, $longitude, $radius) {
    //                 $q->selectRaw("
    //                     (6371 * acos(cos(radians(?)) * cos(radians(latitude)) 
    //                     * cos(radians(longitude) - radians(?)) + sin(radians(?)) 
    //                     * sin(radians(latitude)))) AS distance", [$latitude, $longitude, $latitude])
    //                 ->having('distance', '<', $radius);
    //             })->pluck('id');

    //             if ($stores->isNotEmpty()) {
    //                 $query->where(function ($q) use ($stores) {
    //                     $q->whereHas('store', function ($sq) use ($stores) {
    //                         $sq->whereIn('id', $stores);
    //                     })->orWhereHas('userStores', function ($sq) use ($stores) {
    //                         $sq->whereIn('store_id', $stores);
    //                     });
    //                 });
    //             }
    //         }

    //         if ($request->filled('account_type') && $request->account_type == 'Owner') {
    //             if ($request->has('category_id')) {
    //                 $category = $request->category_id;
    //                 $query->whereHas('store', function ($q) use ($category) {
    //                     $q->where('store_category', $category);
    //                 });
    //             }
    //         } else {
    //             $query->whereHas('services');
    //             if ($request->has('category_id')) {
    //                 $category = $request->category_id;
    //                 $query->whereHas('userStores.store', function ($q) use ($category) {
    //                     $q->where('store_category', $category);
    //                 });
    //             }

    //             if ($request->has('price')) {
    //                 $price = $request->price;
    //                 $query->whereHas('services', function ($q) use ($price) {
    //                     $q->where('price', '<=', $price);
    //                 });
    //             }
    //         }

    //         $serviceProviders = $query->orderBy('bookings_count', 'desc')
    //             ->paginate($this->perPage);

    //         $serviceProviders = $this->addMeta(ServiceProviderResource::collection($serviceProviders));

    //         return response()->json(compact('serviceProviders'), 200);
    //     } catch (Exception $e) {
    //         $this->reportExceptionOnBugsnag($e);
    //         return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
    //     }
    // }



    // public function getServiceProviders(Request $request)   
    // {
    //     $validator = Validator::make($request->all(), [
    //         'category_id' => 'nullable|exists:categories,id',
    //         'price' => 'nullable|numeric',
    //         'account_type' => 'nullable|string|in:Stylist,Owner',
    //         'latitude' => 'nullable|numeric',    
    //         'longitude' => 'nullable|numeric',     
    //         'provider_name' => 'nullable|string',
    //         'provider_service' => 'nullable|string',
    //         'rating' => 'nullable|numeric|min:1|max:5',
    //         'min_price' => 'nullable|numeric|min:0',
    //         'max_price' => 'nullable|numeric',
    //         'search' => 'nullable|string',
    //         'radius' => 'nullable|numeric|min:1|max:50', // Allow custom radius (max 50km)
    //     ]);

    //     if ($validator->fails()) {
    //         return $this->validationError($validator);
    //     }

    //     try {
    //         $query = User::withCount(['bookings', 'services']);
    //         $query->whereIn('account_type', ['Stylist', 'Owner']);

    //         // Apply all non-location filters first
    //         if ($request->filled('account_type')) {
    //             $query->where('account_type', $request->account_type);
    //         }

    //         if ($request->filled('search')) {
    //             $search = $request->input('search');
    //             $query->where(function ($q) use ($search) {
    //                 $q->where('name', 'LIKE', "%$search%")
    //                 ->orWhere('email', 'LIKE', "%$search%")
    //                 ->orWhere('bio', 'LIKE', "%$search%")
    //                 ->orWhere('specialization', 'LIKE', "%$search%")
    //                 ->orWhereHas('services', function ($q) use ($search) {
    //                     $q->where('name', 'LIKE', "%$search%")
    //                         ->orWhere('description', 'LIKE', "%$search%")
    //                         ->orWhere('checkout_label', 'LIKE', "%$search%");
    //                 })
    //                 ->orWhereHas('rentedStores.userStoreServiceType.serviceType', function ($q) use ($search) {
    //                     $q->where('name', 'LIKE', "%$search%");
    //                 })
    //                 ->orWhereHas('store', function ($q) use ($search) {
    //                     $q->where('store_name', 'LIKE', "%$search%")
    //                         ->orWhere('store_description', 'LIKE', "%$search%");
    //                 });

    //                 if (is_numeric($search)) {
    //                     $q->orWhereHas('services', function ($q) use ($search) {
    //                         $q->where('price', '<=', $search + 5)
    //                         ->where('price', '>=', $search - 5);
    //                     });
    //                 }

    //                 if (preg_match('/(\d+)\s*-\s*(\d+)/', $search, $matches)) {
    //                     $minPrice = $matches[1];
    //                     $maxPrice = $matches[2];
    //                     $q->orWhereHas('services', function ($q) use ($minPrice, $maxPrice) {
    //                         $q->where('price', '>=', $minPrice)
    //                         ->where('price', '<=', $maxPrice);
    //                     });
    //                 }

    //                 if (is_numeric($search) && $search >= 1 && $search <= 5) {
    //                     $userIdsWithinRatingRange = DB::table('reviews')
    //                         ->select('merchant_id')
    //                         ->groupBy('merchant_id')
    //                         ->havingRaw('AVG(rating) >= ?', [$search - 0.5])
    //                         ->havingRaw('AVG(rating) <= ?', [$search + 0.5])
    //                         ->pluck('merchant_id')
    //                         ->toArray();

    //                     $q->orWhereIn('id', $userIdsWithinRatingRange);
    //                 }
    //             });
    //         }

    //         if ($request->filled('provider_name')) {
    //             $providerName = $request->input('provider_name');
    //             $query->where(function ($q) use ($providerName) {
    //                 $q->where('name', 'LIKE', "%$providerName%")
    //                 ->orWhere('firstName', 'LIKE', "%$providerName%")
    //                 ->orWhere('lastName', 'LIKE', "%$providerName%");
    //             });
    //         }

    //         if ($request->filled('provider_service')) {
    //             $providerService = $request->input('provider_service');
    //             $query->where(function ($q) use ($providerService) {
    //                 $q->whereHas('services', function ($q) use ($providerService) {
    //                     $q->where('name', 'LIKE', "%$providerService%")
    //                     ->orWhere('description', 'LIKE', "%$providerService%");
    //                 })->orWhereHas('rentedStores.userStoreServiceType.serviceType', function ($q) use ($providerService) {
    //                     $q->where('name', 'LIKE', "%$providerService%");
    //                 })->orWhere('specialization', 'LIKE', "%$providerService%");
    //             });
    //         }

    //         if ($request->filled('rating')) {
    //             $rating = $request->input('rating');
    //             $userIdsWithRating = DB::table('reviews')
    //                 ->select('merchant_id')
    //                 ->groupBy('merchant_id')
    //                 ->havingRaw('AVG(rating) >= ?', [$rating])
    //                 ->pluck('merchant_id')
    //                 ->toArray();

    //             $query->whereIn('id', $userIdsWithRating);
    //         }

    //         if ($request->filled('min_price') || $request->filled('max_price')) {
    //             $minPrice = $request->input('min_price', 0);
    //             $maxPrice = $request->input('max_price', PHP_INT_MAX);

    //             $query->whereHas('services', function ($q) use ($minPrice, $maxPrice) {
    //                 $q->where('price', '>=', $minPrice)
    //                 ->where('price', '<=', $maxPrice);
    //             });
    //         } else if ($request->filled('price')) {
    //             $price = $request->input('price');
    //             $query->whereHas('services', function ($q) use ($price) {
    //                 $q->where('price', '<=', $price);
    //             });
    //         }

    //         // FIXED LOCATION LOGIC - More accurate and strict
    //         if ($request->filled('latitude') xor $request->filled('longitude')) {
    //             return response()->json([
    //                 "ResponseStatus" => "Unsuccessful",
    //                 "ResponseCode" => 422,
    //                 "ResponseMessage" => "Both latitude and longitude must be provided together."
    //             ], 422);
    //         }

    //         $searchMeta = [];
    //         $distanceData = collect();

    //         if ($request->filled('latitude') && $request->filled('longitude')) {
    //             $latitude = (float) $request->latitude;
    //             $longitude = (float) $request->longitude;
    //             $requestedRadius = $request->filled('radius') ? (float) $request->radius : 10; // Default 10km

    //             // STEP 1: Get all users with their closest store address and distance
    //             $usersWithStoreDistance = DB::table('users')
    //                 ->join('store_addresses', 'users.id', '=', 'store_addresses.merchant_id')
    //                 ->whereIn('users.account_type', ['Stylist', 'Owner'])
    //                 ->whereNotNull('store_addresses.latitude')
    //                 ->whereNotNull('store_addresses.longitude')
    //                 ->where('store_addresses.latitude', '!=', 0)
    //                 ->where('store_addresses.longitude', '!=', 0)
    //                 ->selectRaw("
    //                     users.id as user_id,
    //                     store_addresses.latitude,
    //                     store_addresses.longitude,
    //                     store_addresses.address,
    //                     store_addresses.city,
    //                     store_addresses.state,
    //                     (6371 * acos(
    //                         cos(radians(?)) * cos(radians(store_addresses.latitude)) *
    //                         cos(radians(store_addresses.longitude) - radians(?)) +
    //                         sin(radians(?)) * sin(radians(store_addresses.latitude))
    //                     )) AS distance_km", [$latitude, $longitude, $latitude])
    //                 ->havingRaw('distance_km <= ?', [$requestedRadius])
    //                 ->orderBy('distance_km', 'asc')
    //                 ->get();

    //             \Log::info("Location search results", [
    //                 'search_coords' => ['lat' => $latitude, 'lng' => $longitude],
    //                 'radius_km' => $requestedRadius,
    //                 'users_found' => $usersWithStoreDistance->count(),
    //                 'sample_results' => $usersWithStoreDistance->take(3)->map(function($item) {
    //                     return [
    //                         'user_id' => $item->user_id,
    //                         'distance_km' => round($item->distance_km, 2),
    //                         'location' => $item->city . ', ' . $item->state
    //                     ];
    //                 })->toArray()
    //             ]);

    //             if ($usersWithStoreDistance->isNotEmpty()) {
    //                 // Get unique user IDs within radius
    //                 $nearbyUserIds = $usersWithStoreDistance->pluck('user_id')->unique()->toArray();

    //                 // Apply location filter to our query
    //                 $query->whereIn('id', $nearbyUserIds);

    //                 // Store distance data for each user (use closest store if multiple)
    //                 $distanceData = $usersWithStoreDistance->groupBy('user_id')
    //                     ->map(function ($stores) {
    //                         return $stores->sortBy('distance_km')->first();
    //                     });

    //                 $searchMeta = [
    //                     'search_radius_km' => $requestedRadius,
    //                     'message' => "Showing {$usersWithStoreDistance->count()} service providers within {$requestedRadius}km of your location.",
    //                     'total_providers_in_radius' => $usersWithStoreDistance->count(),
    //                     'user_coordinates' => [
    //                         'latitude' => $latitude,
    //                         'longitude' => $longitude
    //                     ]
    //                 ];

    //                 $orderByDistance = true;
    //             } else {
    //                 // Try with expanded radius if no results found
    //                 $expandedRadius = min($requestedRadius * 2, 50); // Max 50km

    //                 $expandedResults = DB::table('users')
    //                     ->join('store_addresses', 'users.id', '=', 'store_addresses.merchant_id')
    //                     ->whereIn('users.account_type', ['Stylist', 'Owner'])
    //                     ->whereNotNull('store_addresses.latitude')
    //                     ->whereNotNull('store_addresses.longitude')
    //                     ->where('store_addresses.latitude', '!=', 0)
    //                     ->where('store_addresses.longitude', '!=', 0)
    //                     ->selectRaw("
    //                         users.id as user_id,
    //                         (6371 * acos(
    //                             cos(radians(?)) * cos(radians(store_addresses.latitude)) *
    //                             cos(radians(store_addresses.longitude) - radians(?)) +
    //                             sin(radians(?)) * sin(radians(store_addresses.latitude))
    //                         )) AS distance_km", [$latitude, $longitude, $latitude])
    //                     ->havingRaw('distance_km <= ?', [$expandedRadius])
    //                     ->orderBy('distance_km', 'asc')
    //                     ->get();

    //                 if ($expandedResults->isNotEmpty()) {
    //                     $nearbyUserIds = $expandedResults->pluck('user_id')->unique()->toArray();
    //                     $query->whereIn('id', $nearbyUserIds);

    //                     $distanceData = $expandedResults->groupBy('user_id')
    //                         ->map(function ($stores) {
    //                             return $stores->sortBy('distance_km')->first();
    //                         });

    //                     $searchMeta = [
    //                         'search_radius_km' => $expandedRadius,
    //                         'is_expanded_search' => true,
    //                         'message' => "No providers found within {$requestedRadius}km. Showing results within {$expandedRadius}km.",
    //                         'total_providers_in_radius' => $expandedResults->count(),
    //                         'user_coordinates' => [
    //                             'latitude' => $latitude,
    //                             'longitude' => $longitude
    //                         ]
    //                     ];

    //                     $orderByDistance = true;
    //                 } else {
    //                     // No results found even with expanded search
    //                     $query->whereRaw('1 = 0'); // Return empty results
    //                     $searchMeta = [
    //                         'search_radius_km' => $expandedRadius,
    //                         'message' => "No service providers found within {$expandedRadius}km of your location.",
    //                         'total_providers_in_radius' => 0,
    //                         'user_coordinates' => [
    //                             'latitude' => $latitude,
    //                             'longitude' => $longitude
    //                         ]
    //                     ];
    //                 }
    //             }
    //         }

    //         // Ensure users have services
    //         $query->whereHas('services');

    //         // Category filtering
    //         if ($request->filled('account_type') && $request->account_type === 'Owner') {
    //             if ($request->filled('category_id')) {
    //                 $query->whereHas('store', function ($q) use ($request) {
    //                     $q->where('store_category', $request->category_id);
    //                 });
    //             }
    //         } else {
    //             if ($request->filled('category_id')) {
    //                 $query->whereHas('userStores.store', function ($q) use ($request) {
    //                     $q->where('store_category', $request->category_id);
    //                 });
    //             }
    //         }

    //         // IMPROVED SORTING AND PAGINATION
    //         if (isset($orderByDistance) && $orderByDistance && $distanceData->isNotEmpty()) {
    //             // Get all matching users
    //             $serviceProviders = $query->get();

    //             // Sort by distance, then by booking count
    //             $serviceProviders = $serviceProviders->sort(function ($a, $b) use ($distanceData) {
    //                 $distanceA = $distanceData->get($a->id)->distance_km ?? PHP_INT_MAX;
    //                 $distanceB = $distanceData->get($b->id)->distance_km ?? PHP_INT_MAX;

    //                 if (abs($distanceA - $distanceB) < 0.1) { // If distances are very close
    //                     return $b->bookings_count <=> $a->bookings_count;
    //                 }
    //                 return $distanceA <=> $distanceB;
    //             })->values();

    //             // Add distance information to each provider
    //             $serviceProviders = $serviceProviders->map(function ($provider) use ($distanceData) {
    //                 $storeData = $distanceData->get($provider->id);
    //                 if ($storeData) {
    //                     $provider->distance_km = round($storeData->distance_km, 2);
    //                     $provider->distance_miles = round($storeData->distance_km * 0.621371, 2);
    //                     $provider->store_location = [
    //                         'address' => $storeData->address,
    //                         'city' => $storeData->city,
    //                         'state' => $storeData->state,
    //                         'coordinates' => [
    //                             'latitude' => $storeData->latitude,
    //                             'longitude' => $storeData->longitude
    //                         ]
    //                     ];
    //                 } else {
    //                     $provider->distance_km = null;
    //                     $provider->distance_miles = null;
    //                     $provider->store_location = null;
    //                 }
    //                 return $provider;
    //             });

    //             // Manual pagination
    //             $page = $request->get('page', 1);
    //             $perPage = $this->perPage;
    //             $total = $serviceProviders->count();
    //             $items = $serviceProviders->forPage($page, $perPage);

    //             $serviceProviders = new \Illuminate\Pagination\LengthAwarePaginator(
    //                 $items,
    //                 $total,
    //                 $perPage,
    //                 $page,
    //                 ['path' => $request->url(), 'query' => $request->query()]
    //             );

    //         } else {
    //             // Default sorting when no location search
    //             $serviceProviders = $query->orderByDesc('bookings_count')
    //                                     ->paginate($this->perPage);
    //         }

    //         $serviceProviders = $this->addMeta(ServiceProviderResource::collection($serviceProviders));

    //         $response = compact('serviceProviders');

    //         if (!empty($searchMeta)) {
    //             $response['searchMeta'] = $searchMeta; 
    //         }

    //         return response()->json($response, 200);

    //     } catch (Exception $e) {
    //         $this->reportExceptionOnBugsnag($e);
    //         return response()->json([
    //             "ResponseStatus" => "Unsuccessful",
    //             "ResponseCode" => 500,
    //             "Detail" => $e->getMessage(),
    //             "ResponseMessage" => 'Something went wrong'
    //         ], 500);
    //     }
    // }


    public function getServiceProviders(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'nullable|exists:categories,id',
            'price' => 'nullable|numeric',
            'account_type' => 'nullable|string|in:Stylist,Owner',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'provider_name' => 'nullable|string',
            'provider_service' => 'nullable|string',
            'rating' => 'nullable|numeric|min:1|max:5',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric',
            'search' => 'nullable|string',
            'radius' => 'nullable|numeric|min:1|max:50',
            'service_type' => 'nullable|string|in:barber,nail technician,tattoo artist,makeup artist',
            'availability' => 'nullable|string|in:Today,Tomorrow,Next week',
            'location' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        try {
            // Apply coordinate corrections if needed
            $this->applyCoordinateCorrections($request);
            // Determine if we're searching for stores or service providers
            $searchingForStores = $request->filled('account_type') && $request->account_type === 'Owner';

            if ($searchingForStores) {
                // Query for stores
                $query = Store::with([
                    'owner:id,merchant_code,name,firstName,lastName,email,phone,profile_image_link,bio,specialization,account_type',
                    'category:id,categoryname',
                    'storeAddress:merchant_id,address,city,state,country,latitude,longitude,formatted_address',
                    'workdoneImages:stores_id,image_url',
                    'services:id,store_id,name,description,price,image_url',
                    'serviceTypes.serviceType:id,name'
                ]);
            } else {
                // Original query for service providers
                $query = User::withCount(['bookings', 'services']);
                $query->whereIn('account_type', ['Stylist', 'Owner']);
            }

            // Apply service_type/specialization filter
            if ($request->filled('service_type')) {
                $serviceType = $request->input('service_type');

                if ($searchingForStores) {
                    // For stores, check both owner specialization and service types
                    $query->where(function ($q) use ($serviceType) {
                        $q->whereHas('owner', function ($q) use ($serviceType) {
                            $q->where('specialization', 'LIKE', "%$serviceType%");
                        })->orWhereHas('serviceTypes.serviceType', function ($q) use ($serviceType) {
                            $q->where('name', 'LIKE', "%$serviceType%");
                        });
                    });
                } else {
                    // For service providers, check both specialization and service types
                    $query->where(function ($q) use ($serviceType) {
                        $q->where('specialization', 'LIKE', "%$serviceType%")
                            ->orWhereHas('rentedStores.userStoreServiceType.serviceType', function ($q) use ($serviceType) {
                                $q->where('name', 'LIKE', "%$serviceType%");
                            });
                    });
                }
            }

            // Apply availability filter
            if ($request->filled('availability')) {
                $availability = $request->input('availability');
                $today = now();
                $dayOfWeek = null;

                switch ($availability) {
                    case 'Today':
                        $dayOfWeek = $today->format('l'); // Full day name (Monday, Tuesday, etc.)
                        break;
                    case 'Tomorrow':
                        $dayOfWeek = $today->copy()->addDay()->format('l'); // Use copy() to avoid mutating original
                        break;
                    case 'Next week':
                        $dayOfWeek = $today->copy()->addWeek()->format('l'); // Use copy() to avoid mutating original
                        break;
                }

                if ($dayOfWeek) {
                    if ($searchingForStores) {
                        // For stores, check days_available JSON field
                        $query->whereRaw("JSON_CONTAINS(days_available, ?)", [json_encode($dayOfWeek)]);
                    } else {
                        // For service providers, check both store availability and service availability hours
                        $query->where(function ($q) use ($dayOfWeek) {
                            // Check if they have a store with availability
                            $q->whereHas('store', function ($storeQuery) use ($dayOfWeek) {
                                $storeQuery->whereRaw("JSON_CONTAINS(days_available, ?)", [json_encode($dayOfWeek)]);
                            })
                                // OR check service availability hours (fallback)
                                ->orWhereHas('services.availabilityHours', function ($serviceQuery) use ($dayOfWeek) {
                                    $serviceQuery->where(function ($subQ) use ($dayOfWeek) {
                                        $subQ->where('day', $dayOfWeek) // Full name: Monday
                                            ->orWhere('day', strtolower($dayOfWeek)) // lowercase: monday
                                            ->orWhere('day', substr($dayOfWeek, 0, 3)) // Short: Mon
                                            ->orWhere('day', strtolower(substr($dayOfWeek, 0, 3))); // lowercase short: mon
                                    });
                                });
                        });
                    }
                }
            }

            // Flexible location filter (city, state, country, address, formatted_address)
            if ($request->filled('location')) {
                $location = trim($request->input('location'));

                // Detect if the search looks like a state query
                $isStateSearch = Str::contains(strtolower($location), 'state');

                if ($isStateSearch) {
                    // Broader search when it's a state query
                    if ($searchingForStores) {
                        $query->whereHas('storeAddress', function ($q) use ($location) {
                            $q->where(function ($subQ) use ($location) {
                                // Strip "state" for looser matching
                                $stateName = str_ireplace('state', '', $location);
                                $subQ->where('state', 'LIKE', "%$stateName%")
                                    ->orWhere('formatted_address', 'LIKE', "%$stateName%");
                            });
                        });
                    } else {
                        $query->whereHas('store.storeAddress', function ($q) use ($location) {
                            $stateName = str_ireplace('state', '', $location);
                            $q->where(function ($subQ) use ($stateName) {
                                $subQ->where('state', 'LIKE', "%$stateName%")
                                    ->orWhere('formatted_address', 'LIKE', "%$stateName%");
                            });
                        });
                    }
                } else {
                    // Normal city/country/address search
                    if ($searchingForStores) {
                        $query->whereHas('storeAddress', function ($q) use ($location) {
                            $q->where(function ($subQ) use ($location) {
                                $subQ->where('city', 'LIKE', "%$location%")
                                    ->orWhere('state', 'LIKE', "%$location%")
                                    ->orWhere('country', 'LIKE', "%$location%")
                                    ->orWhere('address', 'LIKE', "%$location%")
                                    ->orWhere('formatted_address', 'LIKE', "%$location%");
                            });
                        });
                    } else {
                        $query->whereHas('store.storeAddress', function ($q) use ($location) {
                            $q->where(function ($subQ) use ($location) {
                                $subQ->where('city', 'LIKE', "%$location%")
                                    ->orWhere('state', 'LIKE', "%$location%")
                                    ->orWhere('country', 'LIKE', "%$location%")
                                    ->orWhere('address', 'LIKE', "%$location%")
                                    ->orWhere('formatted_address', 'LIKE', "%$location%");
                            });
                        });
                    }
                }
            }


            // Apply search filters
            if ($request->filled('search')) {
                $search = $request->input('search');

                if ($searchingForStores) {
                    $query->where(function ($q) use ($search) {
                        $q->where('store_name', 'LIKE', "%$search%")
                            ->orWhere('store_description', 'LIKE', "%$search%")
                            ->orWhereHas('owner', function ($q) use ($search) {
                                $q->where('name', 'LIKE', "%$search%")
                                    ->orWhere('firstName', 'LIKE', "%$search%")
                                    ->orWhere('lastName', 'LIKE', "%$search%")
                                    ->orWhere('bio', 'LIKE', "%$search%")
                                    ->orWhere('specialization', 'LIKE', "%$search%");
                            })
                            ->orWhereHas('services', function ($q) use ($search) {
                                $q->where('name', 'LIKE', "%$search%")
                                    ->orWhere('description', 'LIKE', "%$search%");
                            })
                            ->orWhereHas('serviceTypes.serviceType', function ($q) use ($search) {
                                $q->where('name', 'LIKE', "%$search%");
                            });

                        // Handle numeric search for price
                        if (is_numeric($search)) {
                            $q->orWhereHas('services', function ($q) use ($search) {
                                $q->where('price', '<=', $search + 5)
                                    ->where('price', '>=', $search - 5);
                            });
                        }

                        // Handle price range search
                        if (preg_match('/(\d+)\s*-\s*(\d+)/', $search, $matches)) {
                            $minPrice = $matches[1];
                            $maxPrice = $matches[2];
                            $q->orWhereHas('services', function ($q) use ($minPrice, $maxPrice) {
                                $q->where('price', '>=', $minPrice)
                                    ->where('price', '<=', $maxPrice);
                            });
                        }


                        if (is_numeric($search) && $search >= 1 && $search <= 5) {
                            $storeIdsWithinRatingRange = DB::table('reviews')
                                ->select('merchant_id')
                                ->groupBy('merchant_id')
                                ->havingRaw('AVG(rating) >= ?', [$search - 0.5])
                                ->havingRaw('AVG(rating) <= ?', [$search + 0.5])
                                ->pluck('merchant_id')
                                ->toArray();

                            $q->orWhereIn('merchant_id', $storeIdsWithinRatingRange);
                        }
                    });
                } else {
                    // Original search logic for service providers
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'LIKE', "%$search%")
                            ->orWhere('email', 'LIKE', "%$search%")
                            ->orWhere('bio', 'LIKE', "%$search%")
                            ->orWhere('specialization', 'LIKE', "%$search%")
                            ->orWhereHas('services', function ($q) use ($search) {
                                $q->where('name', 'LIKE', "%$search%")
                                    ->orWhere('description', 'LIKE', "%$search%")
                                    ->orWhere('checkout_label', 'LIKE', "%$search%");
                            })
                            ->orWhereHas('rentedStores.userStoreServiceType.serviceType', function ($q) use ($search) {
                                $q->where('name', 'LIKE', "%$search%");
                            })
                            ->orWhereHas('store', function ($q) use ($search) {
                                $q->where('store_name', 'LIKE', "%$search%")
                                    ->orWhere('store_description', 'LIKE', "%$search%");
                            });

                        if (is_numeric($search)) {
                            $q->orWhereHas('services', function ($q) use ($search) {
                                $q->where('price', '<=', $search + 5)
                                    ->where('price', '>=', $search - 5);
                            });
                        }

                        if (preg_match('/(\d+)\s*-\s*(\d+)/', $search, $matches)) {
                            $minPrice = $matches[1];
                            $maxPrice = $matches[2];
                            $q->orWhereHas('services', function ($q) use ($minPrice, $maxPrice) {
                                $q->where('price', '>=', $minPrice)
                                    ->where('price', '<=', $maxPrice);
                            });
                        }

                        if (is_numeric($search) && $search >= 1 && $search <= 5) {
                            $userIdsWithinRatingRange = DB::table('reviews')
                                ->select('merchant_id')
                                ->groupBy('merchant_id')
                                ->havingRaw('AVG(rating) >= ?', [$search - 0.5])
                                ->havingRaw('AVG(rating) <= ?', [$search + 0.5])
                                ->pluck('merchant_id')
                                ->toArray();

                            $q->orWhereIn('id', $userIdsWithinRatingRange);
                        }
                    });
                }
            }

            // Apply provider name filter
            if ($request->filled('provider_name')) {
                $providerName = $request->input('provider_name');

                if ($searchingForStores) {
                    $query->where(function ($q) use ($providerName) {
                        $q->where('store_name', 'LIKE', "%$providerName%")
                            ->orWhereHas('owner', function ($q) use ($providerName) {
                                $q->where('name', 'LIKE', "%$providerName%")
                                    ->orWhere('firstName', 'LIKE', "%$providerName%")
                                    ->orWhere('lastName', 'LIKE', "%$providerName%");
                            });
                    });
                } else {
                    $query->where(function ($q) use ($providerName) {
                        $q->where('name', 'LIKE', "%$providerName%")
                            ->orWhere('firstName', 'LIKE', "%$providerName%")
                            ->orWhere('lastName', 'LIKE', "%$providerName%");
                    });
                }
            }

            // Apply provider service filter
            if ($request->filled('provider_service')) {
                $providerService = $request->input('provider_service');

                if ($searchingForStores) {
                    $query->where(function ($q) use ($providerService) {
                        $q->whereHas('services', function ($q) use ($providerService) {
                            $q->where('name', 'LIKE', "%$providerService%")
                                ->orWhere('description', 'LIKE', "%$providerService%");
                        })->orWhereHas('serviceTypes.serviceType', function ($q) use ($providerService) {
                            $q->where('name', 'LIKE', "%$providerService%");
                        });
                    });
                } else {
                    $query->where(function ($q) use ($providerService) {
                        $q->whereHas('services', function ($q) use ($providerService) {
                            $q->where('name', 'LIKE', "%$providerService%")
                                ->orWhere('description', 'LIKE', "%$providerService%");
                        })->orWhereHas('rentedStores.userStoreServiceType.serviceType', function ($q) use ($providerService) {
                            $q->where('name', 'LIKE', "%$providerService%");
                        })->orWhere('specialization', 'LIKE', "%$providerService%");
                    });
                }
            }

            // Apply rating filter
            if ($request->filled('rating')) {
                $rating = $request->input('rating');

                if ($searchingForStores) {
                    $storeIdsWithRating = DB::table('reviews')
                        ->select('merchant_id')
                        ->groupBy('merchant_id')
                        ->havingRaw('AVG(rating) >= ?', [$rating])
                        ->pluck('merchant_id')
                        ->toArray();

                    $query->whereIn('merchant_id', $storeIdsWithRating);
                } else {
                    $userIdsWithRating = DB::table('reviews')
                        ->select('merchant_id')
                        ->groupBy('merchant_id')
                        ->havingRaw('AVG(rating) >= ?', [$rating])
                        ->pluck('merchant_id')
                        ->toArray();

                    $query->whereIn('id', $userIdsWithRating);
                }
            }

            // LOCATION LOGIC - Works for both stores and service providers
            if ($request->filled('latitude') xor $request->filled('longitude')) {
                return response()->json([
                    "ResponseStatus" => "Unsuccessful",
                    "ResponseCode" => 422,
                    "ResponseMessage" => "Both latitude and longitude must be provided together."
                ], 422);
            }

            $searchMeta = [];
            $distanceData = collect();

            if ($request->filled('latitude') && $request->filled('longitude')) {
                $latitude = (float) $request->latitude;
                $longitude = (float) $request->longitude;
                $requestedRadius = $request->filled('radius') ? (float) $request->radius : 25; // Default 25km

                // Get entities within radius based on type
                if ($searchingForStores) {
                    $entitiesWithDistance = DB::table('stores')
                        ->join('store_addresses', 'stores.merchant_id', '=', 'store_addresses.merchant_id')
                        ->join('users', 'stores.merchant_id', '=', 'users.id')
                        ->where('users.account_type', 'Owner')
                        ->whereNotNull('store_addresses.latitude')
                        ->whereNotNull('store_addresses.longitude')
                        ->where('store_addresses.latitude', '!=', 0)
                        ->where('store_addresses.longitude', '!=', 0)
                        ->selectRaw("
                        stores.id as entity_id,
                        stores.merchant_id,
                        store_addresses.latitude,
                        store_addresses.longitude,
                        store_addresses.address,
                        store_addresses.city,
                        store_addresses.state,
                        (6371 * acos(
                            cos(radians(?)) * cos(radians(store_addresses.latitude)) *
                            cos(radians(store_addresses.longitude) - radians(?)) +
                            sin(radians(?)) * sin(radians(store_addresses.latitude))
                        )) AS distance_km", [$latitude, $longitude, $latitude])
                        ->havingRaw('distance_km <= ?', [$requestedRadius])
                        ->orderBy('distance_km', 'asc')
                        ->get();
                } else {
                    $entitiesWithDistance = DB::table('users')
                        ->join('store_addresses', 'users.id', '=', 'store_addresses.merchant_id')
                        ->whereIn('users.account_type', ['Stylist', 'Owner'])
                        ->whereNotNull('store_addresses.latitude')
                        ->whereNotNull('store_addresses.longitude')
                        ->where('store_addresses.latitude', '!=', 0)
                        ->where('store_addresses.longitude', '!=', 0)
                        ->selectRaw("
                        users.id as entity_id,
                        users.id as merchant_id,
                        store_addresses.latitude,
                        store_addresses.longitude,
                        store_addresses.address,
                        store_addresses.city,
                        store_addresses.state,
                        (6371 * acos(
                            cos(radians(?)) * cos(radians(store_addresses.latitude)) *
                            cos(radians(store_addresses.longitude) - radians(?)) +
                            sin(radians(?)) * sin(radians(store_addresses.latitude))
                        )) AS distance_km", [$latitude, $longitude, $latitude])
                        ->havingRaw('distance_km <= ?', [$requestedRadius])
                        ->orderBy('distance_km', 'asc')
                        ->get();
                }

                Log::info("Location search results", [
                    'search_type' => $searchingForStores ? 'stores' : 'service_providers',
                    'search_coords' => ['lat' => $latitude, 'lng' => $longitude],
                    'radius_km' => $requestedRadius,
                    'entities_found' => $entitiesWithDistance->count(),
                ]);

                if ($entitiesWithDistance->isNotEmpty()) {
                    $nearbyEntityIds = $entitiesWithDistance->pluck('entity_id')->unique()->toArray();

                    if ($searchingForStores) {
                        $query->whereIn('id', $nearbyEntityIds);
                    } else {
                        $query->whereIn('id', $nearbyEntityIds);
                    }

                    $distanceData = $entitiesWithDistance->groupBy('entity_id')
                        ->map(function ($entities) {
                            return $entities->sortBy('distance_km')->first();
                        });

                    $searchMeta = [
                        'search_radius_km' => $requestedRadius,
                        'message' => "Showing {$entitiesWithDistance->count()} " . ($searchingForStores ? 'stores' : 'service providers') . " within {$requestedRadius}km of your location.",
                        'total_entities_in_radius' => $entitiesWithDistance->count(),
                        'user_coordinates' => [
                            'latitude' => $latitude,
                            'longitude' => $longitude
                        ]

                    ];

                    $orderByDistance = true;
                } else {
                    // Try with expanded radius if no results found
                    $expandedRadius = min($requestedRadius * 2, 100); // Max 100km

                    // Retry with expanded radius
                    if ($searchingForStores) {
                        $entitiesWithDistance = DB::table('stores')
                            ->join('store_addresses', 'stores.merchant_id', '=', 'store_addresses.merchant_id')
                            ->join('users', 'stores.merchant_id', '=', 'users.id')
                            ->where('users.account_type', 'Owner')
                            ->whereNotNull('store_addresses.latitude')
                            ->whereNotNull('store_addresses.longitude')
                            ->where('store_addresses.latitude', '!=', 0)
                            ->where('store_addresses.longitude', '!=', 0)
                            ->selectRaw("
                            stores.id as entity_id,
                            stores.merchant_id,
                            store_addresses.latitude,
                            store_addresses.longitude,
                            store_addresses.address,
                            store_addresses.city,
                            store_addresses.state,
                            (6371 * acos(
                                cos(radians(?)) * cos(radians(store_addresses.latitude)) *
                                cos(radians(store_addresses.longitude) - radians(?)) +
                                sin(radians(?)) * sin(radians(store_addresses.latitude))
                            )) AS distance_km", [$latitude, $longitude, $latitude])
                            ->havingRaw('distance_km <= ?', [$expandedRadius])
                            ->orderBy('distance_km', 'asc')
                            ->get();
                    } else {
                        $entitiesWithDistance = DB::table('users')
                            ->join('store_addresses', 'users.id', '=', 'store_addresses.merchant_id')
                            ->whereIn('users.account_type', ['Stylist', 'Owner'])
                            ->whereNotNull('store_addresses.latitude')
                            ->whereNotNull('store_addresses.longitude')
                            ->where('store_addresses.latitude', '!=', 0)
                            ->where('store_addresses.longitude', '!=', 0)
                            ->selectRaw("
                            users.id as entity_id,
                            users.id as merchant_id,
                            store_addresses.latitude,
                            store_addresses.longitude,
                            store_addresses.address,
                            store_addresses.city,
                            store_addresses.state,
                            (6371 * acos(
                                cos(radians(?)) * cos(radians(store_addresses.latitude)) *
                                cos(radians(store_addresses.longitude) - radians(?)) +
                                sin(radians(?)) * sin(radians(store_addresses.latitude))
                            )) AS distance_km", [$latitude, $longitude, $latitude])
                            ->havingRaw('distance_km <= ?', [$expandedRadius])
                            ->orderBy('distance_km', 'asc')
                            ->get();
                    }

                    if ($entitiesWithDistance->isNotEmpty()) {
                        $nearbyEntityIds = $entitiesWithDistance->pluck('entity_id')->unique()->toArray();

                        if ($searchingForStores) {
                            $query->whereIn('id', $nearbyEntityIds);
                        } else {
                            $query->whereIn('id', $nearbyEntityIds);
                        }

                        $distanceData = $entitiesWithDistance->groupBy('entity_id')
                            ->map(function ($entities) {
                                return $entities->sortBy('distance_km')->first();
                            });

                        $searchMeta = [
                            'search_radius_km' => $expandedRadius,
                            'message' => "Showing {$entitiesWithDistance->count()} " . ($searchingForStores ? 'stores' : 'service providers') . " within {$expandedRadius}km of your location (expanded search).",
                            'total_entities_in_radius' => $entitiesWithDistance->count(),
                            'user_coordinates' => [
                                'latitude' => $latitude,
                                'longitude' => $longitude
                            ]
                        ];

                        $orderByDistance = true;
                    } else {
                        $query->whereRaw('1 = 0'); // Return empty results
                        $searchMeta = [
                            'search_radius_km' => $expandedRadius,
                            'message' => "No " . ($searchingForStores ? 'stores' : 'service providers') . " found within {$expandedRadius}km of your location.",
                            'total_entities_in_radius' => 0,
                            'user_coordinates' => [
                                'latitude' => $latitude,
                                'longitude' => $longitude
                            ]
                        ];
                    }
                }
            }

            // Ensure entities have services
            if (!$searchingForStores) {
                $query->whereHas('services');
            }

            // Apply price filters AFTER location filtering to get more results
            if ($request->filled('min_price') || $request->filled('max_price')) {
                $minPrice = $request->input('min_price', 0);
                $maxPrice = $request->input('max_price', PHP_INT_MAX);

                $query->whereHas('services', function ($q) use ($minPrice, $maxPrice) {
                    $q->where('price', '>=', $minPrice)
                        ->where('price', '<=', $maxPrice);
                });
            } else if ($request->filled('price')) {
                $price = $request->input('price');
                $query->whereHas('services', function ($q) use ($price) {
                    $q->where('price', '<=', $price);
                });
            }

            // Category filtering
            if ($request->filled('category_id')) {
                if ($searchingForStores) {
                    $query->where('store_category', $request->category_id);
                } else {
                    if ($request->filled('account_type') && $request->account_type === 'Owner') {
                        $query->whereHas('store', function ($q) use ($request) {
                            $q->where('store_category', $request->category_id);
                        });
                    } else {
                        $query->whereHas('userStores.store', function ($q) use ($request) {
                            $q->where('store_category', $request->category_id);
                        });
                    }
                }
            }

            // Apply account type filter for service providers
            if (!$searchingForStores && $request->filled('account_type')) {
                $query->where('account_type', $request->account_type);
            }

            // SORTING AND PAGINATION
            $sortBy = $request->input('sort_by');
            $sortDirection = strtolower($request->input('sort_direction', 'asc')) === 'desc' ? 'desc' : 'asc';

            if (isset($orderByDistance) && $orderByDistance && $distanceData->isNotEmpty()) {
                $entities = $query->get();

                // Sort by distance first, with secondary sort if user requested
                $entities = $entities->sort(function ($a, $b) use ($distanceData, $searchingForStores, $sortBy, $sortDirection) {
                    $entityIdA = $a->id;
                    $entityIdB = $b->id;

                    $distanceA = $distanceData->get($entityIdA)->distance_km ?? PHP_INT_MAX;
                    $distanceB = $distanceData->get($entityIdB)->distance_km ?? PHP_INT_MAX;

                    // Primary: distance
                    if (abs($distanceA - $distanceB) > 0.1) {
                        return $distanceA <=> $distanceB;
                    }

                    // Secondary: user-specified sort
                    if ($sortBy === 'name') {
                        $nameA = $searchingForStores ? $a->store_name : ($a->name ?: trim(($a->firstName ?? '') . ' ' . ($a->lastName ?? '')));
                        $nameB = $searchingForStores ? $b->store_name : ($b->name ?: trim(($b->firstName ?? '') . ' ' . ($b->lastName ?? '')));
                        $cmp = strcasecmp($nameA, $nameB);
                        return $sortDirection === 'desc' ? -$cmp : $cmp;
                    }

                    if ($sortBy === 'bookings_count') {
                        $countA = $searchingForStores ? $a->bookings()->count() : $a->bookings_count;
                        $countB = $searchingForStores ? $b->bookings()->count() : $b->bookings_count;
                        $cmp = $countA <=> $countB;
                        return $sortDirection === 'desc' ? -$cmp : $cmp;
                    }

                    return 0;
                })->values();

                // Attach distance info to each entity
                $entities = $entities->map(function ($entity) use ($distanceData) {
                    $entityId = $entity->id;

                    if ($distanceData->has($entityId)) {
                        $distanceKm = round($distanceData->get($entityId)->distance_km, 2);
                        $distanceMiles = round($distanceKm * 0.621371, 2);

                        // Always attach to model (for all account_type)
                        $entity->distance_km = $distanceKm;
                        $entity->distance_miles = $distanceMiles;
                    } else {
                        $entity->distance_km = null;
                        $entity->distance_miles = null;
                    }

                    return $entity;
                });

                // Manual pagination
                $page = $request->get('page', 1);
                $perPage = $this->perPage;
                $total = $entities->count();
                $items = $entities->forPage($page, $perPage);

                $entities = new \Illuminate\Pagination\LengthAwarePaginator(
                    $items,
                    $total,
                    $perPage,
                    $page,
                    ['path' => $request->url(), 'query' => $request->query()]
                );
            } else {
                // Default sorting when no distance filter
                if ($searchingForStores) {
                    $entities = $query->withCount('bookings');

                    if ($sortBy === 'name') {
                        $entities->orderBy('store_name', $sortDirection);
                    } elseif ($sortBy === 'bookings_count') {
                        $entities->orderBy('bookings_count', $sortDirection);
                    } else {
                        $entities->orderByDesc('bookings_count');
                    }

                    $entities = $entities->paginate($this->perPage);
                } else {
                    if ($sortBy === 'name') {
                        $query->orderByRaw(
                            "LOWER(COALESCE(NULLIF(TRIM(name), ''), CONCAT(TRIM(firstName),' ',TRIM(lastName)))) {$sortDirection}"
                        );
                    } elseif ($sortBy === 'bookings_count') {
                        $query->orderBy('bookings_count', $sortDirection);
                    } else {
                        $query->orderByDesc('bookings_count');
                    }

                    $entities = $query->paginate($this->perPage);
                }
            }


            // Transform data based on type
            if ($searchingForStores) {
                $transformedData = $this->addMeta(StorePreviewResource::collection($entities));
                $response = ['stores' => $transformedData];
            } else {
                $transformedData = $this->addMeta(ServiceProviderResource::collection($entities));
                $response = ['serviceProviders' => $transformedData];
            }

            if (!empty($searchMeta)) {
                $response['searchMeta'] = $searchMeta;
            }

            return response()->json($response, 200);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            Logger::info('getServiceProviders Error', [$e->getMessage() . ' - ' . $e->__toString()]);
            return response()->json([
                "ResponseStatus" => "Unsuccessful",
                "ResponseCode" => 500,
                "Detail" => $e->getMessage(),
                "ResponseMessage" => 'Something went wrong'
            ], 500);
        }
    }

    public function getManyServiceProviders(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'nullable|exists:categories,id',
            'price' => 'nullable|numeric',
            'account_type' => 'nullable|string|in:Stylist,Owner',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'provider_name' => 'nullable|string',
            'provider_service' => 'nullable|string',
            'rating' => 'nullable|numeric|min:1|max:5',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric',
            'search' => 'nullable|string',
            'radius' => 'nullable|numeric|min:1|max:50',
            'service_type' => 'nullable|string|in:barber,nail technician,tattoo artist,makeup artist',
            'availability' => 'nullable|string|in:Today,Tomorrow,Next week',
            'location' => 'nullable|string',
            'sort_by' => 'nullable|string|in:name,bookings_count,created_at',
            'sort_direction' => 'nullable|string|in:asc,desc'
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        try {
            // Apply coordinate corrections if needed
            $this->applyCoordinateCorrections($request);
            // Determine if we're searching for stores or service providers
            $searchingForStores = $request->filled('account_type') && $request->account_type === 'Owner';

            if ($searchingForStores) {
                // Query for stores
                $query = Store::with([
                    'owner:id,merchant_code,name,firstName,lastName,email,phone,profile_image_link,bio,specialization,account_type',
                    'category:id,categoryname',
                    'storeAddress:merchant_id,address,city,state,country,latitude,longitude,formatted_address',
                    'workdoneImages:stores_id,image_url',
                    'services:id,store_id,name,description,price,image_url',
                    'serviceTypes.serviceType:id,name'
                ]);
            } else {
                // Original query for service providers
                $query = User::withCount(['bookings', 'services']);
                $query->whereIn('account_type', ['Stylist', 'Owner']);
            }

            // Apply service_type/specialization filter
            if ($request->filled('service_type')) {
                $serviceType = $request->input('service_type');

                if ($searchingForStores) {
                    // For stores, check both owner specialization and service types
                    $query->where(function ($q) use ($serviceType) {
                        $q->whereHas('owner', function ($q) use ($serviceType) {
                            $q->where('specialization', 'LIKE', "%$serviceType%");
                        })->orWhereHas('serviceTypes.serviceType', function ($q) use ($serviceType) {
                            $q->where('name', 'LIKE', "%$serviceType%");
                        });
                    });
                } else {
                    // For service providers, check both specialization and service types
                    $query->where(function ($q) use ($serviceType) {
                        $q->where('specialization', 'LIKE', "%$serviceType%")
                            ->orWhereHas('rentedStores.userStoreServiceType.serviceType', function ($q) use ($serviceType) {
                                $q->where('name', 'LIKE', "%$serviceType%");
                            });
                    });
                }
            }

            // Apply availability filter
            if ($request->filled('availability')) {
                $availability = $request->input('availability');
                $today = now();
                $dayOfWeek = null;

                switch ($availability) {
                    case 'Today':
                        $dayOfWeek = $today->format('l'); // Full day name (Monday, Tuesday, etc.)
                        break;
                    case 'Tomorrow':
                        $dayOfWeek = $today->copy()->addDay()->format('l'); // Use copy() to avoid mutating original
                        break;
                    case 'Next week':
                        $dayOfWeek = $today->copy()->addWeek()->format('l'); // Use copy() to avoid mutating original
                        break;
                }

                if ($dayOfWeek) {
                    if ($searchingForStores) {
                        // For stores, check days_available JSON field
                        $query->whereRaw("JSON_CONTAINS(days_available, ?)", [json_encode($dayOfWeek)]);
                    } else {
                        // For service providers, check both store availability and service availability hours
                        $query->where(function ($q) use ($dayOfWeek) {
                            // Check if they have a store with availability
                            $q->whereHas('store', function ($storeQuery) use ($dayOfWeek) {
                                $storeQuery->whereRaw("JSON_CONTAINS(days_available, ?)", [json_encode($dayOfWeek)]);
                            })
                                // OR check service availability hours (fallback)
                                ->orWhereHas('services.availabilityHours', function ($serviceQuery) use ($dayOfWeek) {
                                    $serviceQuery->where(function ($subQ) use ($dayOfWeek) {
                                        $subQ->where('day', $dayOfWeek) // Full name: Monday
                                            ->orWhere('day', strtolower($dayOfWeek)) // lowercase: monday
                                            ->orWhere('day', substr($dayOfWeek, 0, 3)) // Short: Mon
                                            ->orWhere('day', strtolower(substr($dayOfWeek, 0, 3))); // lowercase short: mon
                                    });
                                });
                        });
                    }
                }
            }

            // Flexible location filter (city, state, country, address, formatted_address)
            if ($request->filled('location')) {
                $location = trim($request->input('location'));

                // Detect if the search looks like a state query
                $isStateSearch = Str::contains(strtolower($location), 'state');

                if ($isStateSearch) {
                    // Broader search when it's a state query
                    if ($searchingForStores) {
                        $query->whereHas('storeAddress', function ($q) use ($location) {
                            $q->where(function ($subQ) use ($location) {
                                // Strip "state" for looser matching
                                $stateName = str_ireplace('state', '', $location);
                                $subQ->where('state', 'LIKE', "%$stateName%")
                                    ->orWhere('formatted_address', 'LIKE', "%$stateName%");
                            });
                        });
                    } else {
                        $query->whereHas('store.storeAddress', function ($q) use ($location) {
                            $stateName = str_ireplace('state', '', $location);
                            $q->where(function ($subQ) use ($stateName) {
                                $subQ->where('state', 'LIKE', "%$stateName%")
                                    ->orWhere('formatted_address', 'LIKE', "%$stateName%");
                            });
                        });
                    }
                } else {
                    // Normal city/country/address search
                    if ($searchingForStores) {
                        $query->whereHas('storeAddress', function ($q) use ($location) {
                            $q->where(function ($subQ) use ($location) {
                                $subQ->where('city', 'LIKE', "%$location%")
                                    ->orWhere('state', 'LIKE', "%$location%")
                                    ->orWhere('country', 'LIKE', "%$location%")
                                    ->orWhere('address', 'LIKE', "%$location%")
                                    ->orWhere('formatted_address', 'LIKE', "%$location%");
                            });
                        });
                    } else {
                        $query->whereHas('store.storeAddress', function ($q) use ($location) {
                            $q->where(function ($subQ) use ($location) {
                                $subQ->where('city', 'LIKE', "%$location%")
                                    ->orWhere('state', 'LIKE', "%$location%")
                                    ->orWhere('country', 'LIKE', "%$location%")
                                    ->orWhere('address', 'LIKE', "%$location%")
                                    ->orWhere('formatted_address', 'LIKE', "%$location%");
                            });
                        });
                    }
                }
            }


            // Apply search filters
            if ($request->filled('search')) {
                $search = $request->input('search');

                if ($searchingForStores) {
                    $query->where(function ($q) use ($search) {
                        $q->where('store_name', 'LIKE', "%$search%")
                            ->orWhere('store_description', 'LIKE', "%$search%")
                            ->orWhereHas('owner', function ($q) use ($search) {
                                $q->where('name', 'LIKE', "%$search%")
                                    ->orWhere('firstName', 'LIKE', "%$search%")
                                    ->orWhere('lastName', 'LIKE', "%$search%")
                                    ->orWhere('bio', 'LIKE', "%$search%")
                                    ->orWhere('specialization', 'LIKE', "%$search%");
                            })
                            ->orWhereHas('services', function ($q) use ($search) {
                                $q->where('name', 'LIKE', "%$search%")
                                    ->orWhere('description', 'LIKE', "%$search%");
                            })
                            ->orWhereHas('serviceTypes.serviceType', function ($q) use ($search) {
                                $q->where('name', 'LIKE', "%$search%");
                            });

                        // Handle numeric search for price
                        if (is_numeric($search)) {
                            $q->orWhereHas('services', function ($q) use ($search) {
                                $q->where('price', '<=', $search + 5)
                                    ->where('price', '>=', $search - 5);
                            });
                        }

                        // Handle price range search
                        if (preg_match('/(\d+)\s*-\s*(\d+)/', $search, $matches)) {
                            $minPrice = $matches[1];
                            $maxPrice = $matches[2];
                            $q->orWhereHas('services', function ($q) use ($minPrice, $maxPrice) {
                                $q->where('price', '>=', $minPrice)
                                    ->where('price', '<=', $maxPrice);
                            });
                        }


                        if (is_numeric($search) && $search >= 1 && $search <= 5) {
                            $storeIdsWithinRatingRange = DB::table('reviews')
                                ->select('merchant_id')
                                ->groupBy('merchant_id')
                                ->havingRaw('AVG(rating) >= ?', [$search - 0.5])
                                ->havingRaw('AVG(rating) <= ?', [$search + 0.5])
                                ->pluck('merchant_id')
                                ->toArray();

                            $q->orWhereIn('merchant_id', $storeIdsWithinRatingRange);
                        }
                    });
                } else {
                    // Original search logic for service providers
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'LIKE', "%$search%")
                            ->orWhere('email', 'LIKE', "%$search%")
                            ->orWhere('bio', 'LIKE', "%$search%")
                            ->orWhere('specialization', 'LIKE', "%$search%")
                            ->orWhereHas('services', function ($q) use ($search) {
                                $q->where('name', 'LIKE', "%$search%")
                                    ->orWhere('description', 'LIKE', "%$search%")
                                    ->orWhere('checkout_label', 'LIKE', "%$search%");
                            })
                            ->orWhereHas('rentedStores.userStoreServiceType.serviceType', function ($q) use ($search) {
                                $q->where('name', 'LIKE', "%$search%");
                            })
                            ->orWhereHas('store', function ($q) use ($search) {
                                $q->where('store_name', 'LIKE', "%$search%")
                                    ->orWhere('store_description', 'LIKE', "%$search%");
                            });

                        if (is_numeric($search)) {
                            $q->orWhereHas('services', function ($q) use ($search) {
                                $q->where('price', '<=', $search + 5)
                                    ->where('price', '>=', $search - 5);
                            });
                        }

                        if (preg_match('/(\d+)\s*-\s*(\d+)/', $search, $matches)) {
                            $minPrice = $matches[1];
                            $maxPrice = $matches[2];
                            $q->orWhereHas('services', function ($q) use ($minPrice, $maxPrice) {
                                $q->where('price', '>=', $minPrice)
                                    ->where('price', '<=', $maxPrice);
                            });
                        }

                        if (is_numeric($search) && $search >= 1 && $search <= 5) {
                            $userIdsWithinRatingRange = DB::table('reviews')
                                ->select('merchant_id')
                                ->groupBy('merchant_id')
                                ->havingRaw('AVG(rating) >= ?', [$search - 0.5])
                                ->havingRaw('AVG(rating) <= ?', [$search + 0.5])
                                ->pluck('merchant_id')
                                ->toArray();

                            $q->orWhereIn('id', $userIdsWithinRatingRange);
                        }
                    });
                }
            }

            // Apply provider name filter
            if ($request->filled('provider_name')) {
                $providerName = $request->input('provider_name');

                if ($searchingForStores) {
                    $query->where(function ($q) use ($providerName) {
                        $q->where('store_name', 'LIKE', "%$providerName%")
                            ->orWhereHas('owner', function ($q) use ($providerName) {
                                $q->where('name', 'LIKE', "%$providerName%")
                                    ->orWhere('firstName', 'LIKE', "%$providerName%")
                                    ->orWhere('lastName', 'LIKE', "%$providerName%");
                            });
                    });
                } else {
                    $query->where(function ($q) use ($providerName) {
                        $q->where('name', 'LIKE', "%$providerName%")
                            ->orWhere('firstName', 'LIKE', "%$providerName%")
                            ->orWhere('lastName', 'LIKE', "%$providerName%");
                    });
                }
            }

            // Apply provider service filter
            if ($request->filled('provider_service')) {
                $providerService = $request->input('provider_service');

                if ($searchingForStores) {
                    $query->where(function ($q) use ($providerService) {
                        $q->whereHas('services', function ($q) use ($providerService) {
                            $q->where('name', 'LIKE', "%$providerService%")
                                ->orWhere('description', 'LIKE', "%$providerService%");
                        })->orWhereHas('serviceTypes.serviceType', function ($q) use ($providerService) {
                            $q->where('name', 'LIKE', "%$providerService%");
                        });
                    });
                } else {
                    $query->where(function ($q) use ($providerService) {
                        $q->whereHas('services', function ($q) use ($providerService) {
                            $q->where('name', 'LIKE', "%$providerService%")
                                ->orWhere('description', 'LIKE', "%$providerService%");
                        })->orWhereHas('rentedStores.userStoreServiceType.serviceType', function ($q) use ($providerService) {
                            $q->where('name', 'LIKE', "%$providerService%");
                        })->orWhere('specialization', 'LIKE', "%$providerService%");
                    });
                }
            }

            // Apply rating filter
            if ($request->filled('rating')) {
                $rating = $request->input('rating');

                if ($searchingForStores) {
                    $storeIdsWithRating = DB::table('reviews')
                        ->select('merchant_id')
                        ->groupBy('merchant_id')
                        ->havingRaw('AVG(rating) >= ?', [$rating])
                        ->pluck('merchant_id')
                        ->toArray();

                    $query->whereIn('merchant_id', $storeIdsWithRating);
                } else {
                    $userIdsWithRating = DB::table('reviews')
                        ->select('merchant_id')
                        ->groupBy('merchant_id')
                        ->havingRaw('AVG(rating) >= ?', [$rating])
                        ->pluck('merchant_id')
                        ->toArray();

                    $query->whereIn('id', $userIdsWithRating);
                }
            }

            // LOCATION LOGIC - Works for both stores and service providers
            if ($request->filled('latitude') xor $request->filled('longitude')) {
                return response()->json([
                    "ResponseStatus" => "Unsuccessful",
                    "ResponseCode" => 422,
                    "ResponseMessage" => "Both latitude and longitude must be provided together."
                ], 422);
            }

            $searchMeta = [];
            $distanceData = collect();
            $orderByDistance = false;

            if ($request->filled('latitude') && $request->filled('longitude')) {
                $latitude = (float) $request->latitude;
                $longitude = (float) $request->longitude;
                $requestedRadius = $request->filled('radius') ? (float) $request->radius : null; // 👈 radius optional

                // Get entities with distance (no radius restriction yet)
                if ($searchingForStores) {
                    $entitiesWithDistance = DB::table('stores')
                        ->join('store_addresses', 'stores.merchant_id', '=', 'store_addresses.merchant_id')
                        ->join('users', 'stores.merchant_id', '=', 'users.id')
                        ->where('users.account_type', 'Owner')
                        ->whereNotNull('store_addresses.latitude')
                        ->whereNotNull('store_addresses.longitude')
                        ->where('store_addresses.latitude', '!=', 0)
                        ->where('store_addresses.longitude', '!=', 0)
                        ->selectRaw("
                stores.id as entity_id,
                stores.merchant_id,
                store_addresses.latitude,
                store_addresses.longitude,
                store_addresses.address,
                store_addresses.city,
                store_addresses.state,
                (6371 * acos(
                    cos(radians(?)) * cos(radians(store_addresses.latitude)) *
                    cos(radians(store_addresses.longitude) - radians(?)) +
                    sin(radians(?)) * sin(radians(store_addresses.latitude))
                )) AS distance_km", [$latitude, $longitude, $latitude])
                        ->orderBy('distance_km', 'asc')
                        ->get();
                } else {
                    $entitiesWithDistance = DB::table('users')
                        ->join('store_addresses', 'users.id', '=', 'store_addresses.merchant_id')
                        ->whereIn('users.account_type', ['Stylist', 'Owner'])
                        ->whereNotNull('store_addresses.latitude')
                        ->whereNotNull('store_addresses.longitude')
                        ->where('store_addresses.latitude', '!=', 0)
                        ->where('store_addresses.longitude', '!=', 0)
                        ->selectRaw("
                users.id as entity_id,
                users.id as merchant_id,
                store_addresses.latitude,
                store_addresses.longitude,
                store_addresses.address,
                store_addresses.city,
                store_addresses.state,
                (6371 * acos(
                    cos(radians(?)) * cos(radians(store_addresses.latitude)) *
                    cos(radians(store_addresses.longitude) - radians(?)) +
                    sin(radians(?)) * sin(radians(store_addresses.latitude))
                )) AS distance_km", [$latitude, $longitude, $latitude])
                        ->orderBy('distance_km', 'asc')
                        ->get();
                }

                if ($entitiesWithDistance->isNotEmpty()) {
                    // If radius is provided → filter
                    if ($requestedRadius) {
                        $entitiesWithDistance = $entitiesWithDistance
                            ->filter(fn($e) => $e->distance_km <= $requestedRadius)
                            ->values();
                    }

                    $nearbyEntityIds = $entitiesWithDistance->pluck('entity_id')->unique()->toArray();

                    $query->whereIn('id', $nearbyEntityIds);

                    $distanceData = $entitiesWithDistance->groupBy('entity_id')
                        ->map(fn($entities) => $entities->sortBy('distance_km')->first());

                    $searchMeta = [
                        'search_radius_km' => $requestedRadius ?: 'unlimited',
                        'message' => "Showing {$entitiesWithDistance->count()} " . ($searchingForStores ? 'stores' : 'service providers') .
                            ($requestedRadius ? " within {$requestedRadius}km" : " ordered by nearest first"),
                        'total_entities' => $entitiesWithDistance->count(),
                        'user_coordinates' => [
                            'latitude' => $latitude,
                            'longitude' => $longitude
                        ]
                    ];

                    $orderByDistance = true;
                } else {
                    $query->whereRaw('1 = 0'); // nothing found
                    $searchMeta = [
                        'search_radius_km' => $requestedRadius ?: 'unlimited',
                        'message' => "No " . ($searchingForStores ? 'stores' : 'service providers') .
                            ($requestedRadius ? " within {$requestedRadius}km." : " found."),
                        'total_entities' => 0,
                        'user_coordinates' => [
                            'latitude' => $latitude,
                            'longitude' => $longitude
                        ]
                    ];
                }
            }

            // --- SORTING ---
            $sortBy = $request->input('sort_by');
            $sortDirection = $request->input('sort_direction', 'asc'); // default asc

            // Whitelisted sorting logic
            if ($sortBy) {
                if ($searchingForStores) {
                    // Sorting for stores
                    if ($sortBy === 'name') {
                        $query->orderBy('store_name', $sortDirection);
                    } elseif ($sortBy === 'bookings_count') {
                        $query->withCount('bookings')->orderBy('bookings_count', $sortDirection);
                    } else {
                        $query->orderBy($sortBy, $sortDirection);
                    }
                } else {
                    // Sorting for service providers
                    if ($sortBy === 'name') {
                        $query->orderBy('name', $sortDirection);
                    } elseif ($sortBy === 'bookings_count') {
                        $query->orderBy('bookings_count', $sortDirection);
                    } else {
                        $query->orderBy($sortBy, $sortDirection);
                    }
                }
            }

            // --- SORTING + PAGINATION ---
            if ($orderByDistance && $distanceData->isNotEmpty()) {
                $entities = $query->get();

                // Sort by distance, then bookings
                $entities = $entities->sort(function ($a, $b) use ($distanceData, $searchingForStores) {
                    $distanceA = $distanceData->get($a->id)->distance_km ?? PHP_INT_MAX;
                    $distanceB = $distanceData->get($b->id)->distance_km ?? PHP_INT_MAX;

                    if (abs($distanceA - $distanceB) < 0.1) {
                        return $searchingForStores
                            ? $b->bookings()->count() <=> $a->bookings()->count()
                            : $b->bookings_count <=> $a->bookings_count;
                    }
                    return $distanceA <=> $distanceB;
                })->values();

                // Attach distance + location to response
                $entities = $entities->map(function ($entity) use ($distanceData, $searchingForStores) {
                    // For stores, use store.id; for users, use user.id
                    $entityId = $searchingForStores ? $entity->id : $entity->id;

                    if ($distanceData->has($entityId)) {
                        $distanceKm = round($distanceData->get($entityId)->distance_km, 2);
                        $distanceMiles = round($distanceKm * 0.621371, 2);

                        $entity->distance_km = $distanceKm;
                        $entity->distance_miles = $distanceMiles;
                    } else {
                        $entity->distance_km = null;
                        $entity->distance_miles = null;
                    }

                    return $entity;
                });



                // Manual pagination
                $page = $request->get('page', 1);
                $perPage = $this->perPage;
                $total = $entities->count();
                $items = $entities->forPage($page, $perPage);

                $entities = new \Illuminate\Pagination\LengthAwarePaginator(
                    $items,
                    $total,
                    $perPage,
                    $page,
                    ['path' => $request->url(), 'query' => $request->query()]
                );
            } else {
                // Default sorting
                $entities = $searchingForStores
                    ? $query->withCount('bookings')->orderByDesc('bookings_count')->paginate($this->perPage)
                    : $query->orderByDesc('bookings_count')->paginate($this->perPage);
            }

            // Transform data based on type
            if ($searchingForStores) {
                $transformedData = $this->addMeta(StorePreviewResource::collection($entities));
                $response = ['stores' => $transformedData];
            } else {
                $transformedData = $this->addMeta(ServiceProviderResource::collection($entities));
                $response = ['serviceProviders' => $transformedData];
            }

            if (!empty($searchMeta)) {
                $response['searchMeta'] = $searchMeta;
            }

            return response()->json($response, 200);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            Logger::info('getServiceProviders Error', [$e->getMessage() . ' - ' . $e->__toString()]);
            return response()->json([
                "ResponseStatus" => "Unsuccessful",
                "ResponseCode" => 500,
                "Detail" => $e->getMessage(),
                "ResponseMessage" => 'Something went wrong'
            ], 500);
        }
    }

    // public function getManyServiceProviders(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'latitude' => 'nullable|numeric',
    //         'longitude' => 'nullable|numeric',
    //         'radius' => 'nullable|numeric|min:1|max:50',
    //         'search' => 'nullable|string',
    //         'provider_name' => 'nullable|string',
    //         'provider_service' => 'nullable|string',
    //         'rating' => 'nullable|numeric|min:1|max:5',
    //         'min_price' => 'nullable|numeric|min:0',
    //         'max_price' => 'nullable|numeric',
    //         'service_type' => 'nullable|string',
    //         'available_today' => 'nullable|boolean',
    //         'accepts_card' => 'nullable|boolean',
    //         'sort_by' => 'nullable|string|in:bookings_count,services_count,name',
    //         'sort_direction' => 'nullable|string|in:asc,desc',
    //         'availability' => 'nullable|string|in:Today,Tomorrow,Next week',
    //         'location' => 'nullable|string',
    //     ]);

    //     if ($validator->fails()) {
    //         return $this->validationError($validator);
    //     }

    //     try {
    //         // Apply coordinate corrections if needed
    //         $this->applyCoordinateCorrections($request);

    //         $query = User::withCount(['bookings', 'services']);
    //         $query->whereIn('account_type', ['Stylist', 'Owner']);

    //         // LOCATION FILTERING - Apply early for better state coverage
    //         $searchMeta = [];
    //         $distanceData = collect();
    //         $orderByDistance = false;

    //         if ($request->filled('latitude') && $request->filled('longitude')) {
    //             $latitude = (float) $request->latitude;
    //             $longitude = (float) $request->longitude;
    //             $requestedRadius = $request->filled('radius') ? (float) $request->radius : 25; // Increased default radius

    //             // Get service providers within radius
    //             $entitiesWithDistance = DB::table('users')
    //                 ->join('store_addresses', 'users.id', '=', 'store_addresses.merchant_id')
    //                 ->whereIn('users.account_type', ['Stylist', 'Owner'])
    //                 ->whereNotNull('store_addresses.latitude')
    //                 ->whereNotNull('store_addresses.longitude')
    //                 ->where('store_addresses.latitude', '!=', 0)
    //                 ->where('store_addresses.longitude', '!=', 0)
    //                 ->selectRaw("
    //                 users.id as entity_id,
    //                 users.id as merchant_id,
    //                 store_addresses.latitude,
    //                 store_addresses.longitude,
    //                 store_addresses.address,
    //                 store_addresses.city,
    //                 store_addresses.state,
    //                 (6371 * acos(
    //                     cos(radians(?)) * cos(radians(store_addresses.latitude)) *
    //                     cos(radians(store_addresses.longitude) - radians(?)) +
    //                     sin(radians(?)) * sin(radians(store_addresses.latitude))
    //                 )) AS distance_km", [$latitude, $longitude, $latitude])
    //                 ->havingRaw('distance_km <= ?', [$requestedRadius])
    //                 ->orderBy('distance_km', 'asc')
    //                 ->get();

    //             if ($entitiesWithDistance->isNotEmpty()) {
    //                 $nearbyEntityIds = $entitiesWithDistance->pluck('entity_id')->unique()->toArray();
    //                 $query->whereIn('id', $nearbyEntityIds);

    //                 $distanceData = $entitiesWithDistance->groupBy('entity_id')
    //                     ->map(function ($entities) {
    //                         return $entities->sortBy('distance_km')->first();
    //                     });

    //                 $searchMeta = [
    //                     'search_radius_km' => $requestedRadius,
    //                     'message' => "Showing {$entitiesWithDistance->count()} service providers within {$requestedRadius}km of your location.",
    //                     'total_entities_in_radius' => $entitiesWithDistance->count(),
    //                     'user_coordinates' => [
    //                         'latitude' => $latitude,
    //                         'longitude' => $longitude
    //                     ]
    //                 ];

    //                 $orderByDistance = true;
    //             } else {
    //                 // Try with expanded radius if no results found
    //                 $expandedRadius = min($requestedRadius * 2, 100); // Increased max radius

    //                 $entitiesWithDistance = DB::table('users')
    //                     ->join('store_addresses', 'users.id', '=', 'store_addresses.merchant_id')
    //                     ->whereIn('users.account_type', ['Stylist', 'Owner'])
    //                     ->whereNotNull('store_addresses.latitude')
    //                     ->whereNotNull('store_addresses.longitude')
    //                     ->where('store_addresses.latitude', '!=', 0)
    //                     ->where('store_addresses.longitude', '!=', 0)
    //                     ->selectRaw("
    //                     users.id as entity_id,
    //                     users.id as merchant_id,
    //                     store_addresses.latitude,
    //                     store_addresses.longitude,
    //                     store_addresses.address,
    //                     store_addresses.city,
    //                     store_addresses.state,
    //                     (6371 * acos(
    //                         cos(radians(?)) * cos(radians(store_addresses.latitude)) *
    //                         cos(radians(store_addresses.longitude) - radians(?)) +
    //                         sin(radians(?)) * sin(radians(store_addresses.latitude))
    //                     )) AS distance_km", [$latitude, $longitude, $latitude])
    //                     ->havingRaw('distance_km <= ?', [$expandedRadius])
    //                     ->orderBy('distance_km', 'asc')
    //                     ->get();

    //                 if ($entitiesWithDistance->isNotEmpty()) {
    //                     $nearbyEntityIds = $entitiesWithDistance->pluck('entity_id')->unique()->toArray();
    //                     $query->whereIn('id', $nearbyEntityIds);

    //                     $distanceData = $entitiesWithDistance->groupBy('entity_id')
    //                         ->map(function ($entities) {
    //                             return $entities->sortBy('distance_km')->first();
    //                         });

    //                     $searchMeta = [
    //                         'search_radius_km' => $expandedRadius,
    //                         'message' => "Showing {$entitiesWithDistance->count()} service providers within {$expandedRadius}km of your location (expanded search).",
    //                         'total_entities_in_radius' => $entitiesWithDistance->count(),
    //                         'user_coordinates' => [
    //                             'latitude' => $latitude,
    //                             'longitude' => $longitude
    //                         ]
    //                     ];

    //                     $orderByDistance = true;
    //                 } else {
    //                     $query->whereRaw('1 = 0'); // Return empty results
    //                     $searchMeta = [
    //                         'search_radius_km' => $expandedRadius,
    //                         'message' => "No service providers found within {$expandedRadius}km of your location.",
    //                         'total_entities_in_radius' => 0,
    //                         'user_coordinates' => [
    //                             'latitude' => $latitude,
    //                             'longitude' => $longitude
    //                         ]
    //                     ];
    //                 }
    //             }
    //         }

    //         // Enhanced search functionality
    //         if ($request->filled('search')) {
    //             $search = $request->input('search');
    //             $query->where(function ($q) use ($search) {
    //                 // Basic user fields
    //                 $q->where('name', 'LIKE', "%$search%")
    //                     ->orWhere('email', 'LIKE', "%$search%")
    //                     ->orWhere('bio', 'LIKE', "%$search%")
    //                     ->orWhere('specialization', 'LIKE', "%$search%")
    //                     ->orWhere('firstName', 'LIKE', "%$search%")
    //                     ->orWhere('lastName', 'LIKE', "%$search%")
    //                     // Services fields
    //                     ->orWhereHas('services', function ($q) use ($search) {
    //                         $q->where('name', 'LIKE', "%$search%")
    //                             ->orWhere('description', 'LIKE', "%$search%")
    //                             ->orWhere('checkout_label', 'LIKE', "%$search%");
    //                     })
    //                     // Service types
    //                     ->orWhereHas('rentedStores.userStoreServiceType.serviceType', function ($q) use ($search) {
    //                         $q->where('name', 'LIKE', "%$search%");
    //                     })
    //                     // Store fields
    //                     ->orWhereHas('store', function ($q) use ($search) {
    //                         $q->where('store_name', 'LIKE', "%$search%")
    //                             ->orWhere('store_description', 'LIKE', "%$search%");
    //                     });

    //                 // Price-based search
    //                 if (is_numeric($search)) {
    //                     // If it's a number, it could be a price or a rating
    //                     $q->orWhereHas('services', function ($q) use ($search) {
    //                         $q->where('price', '<=', $search + 5)
    //                             ->where('price', '>=', $search - 5);
    //                     });

    //                     // Rating-based search (if number is between 1-5)
    //                     if ($search >= 1 && $search <= 5) {
    //                         $userIdsWithinRatingRange = DB::table('reviews')
    //                             ->select('merchant_id')
    //                             ->groupBy('merchant_id')
    //                             ->havingRaw('AVG(rating) >= ?', [$search - 0.5])
    //                             ->havingRaw('AVG(rating) <= ?', [$search + 0.5])
    //                             ->pluck('merchant_id')
    //                             ->toArray();

    //                         $q->orWhereIn('id', $userIdsWithinRatingRange);
    //                     }
    //                 }

    //                 // Search for price ranges (if format is like "10-50")
    //                 if (preg_match('/(\d+)\s*-\s*(\d+)/', $search, $matches)) {
    //                     $minPrice = $matches[1];
    //                     $maxPrice = $matches[2];
    //                     $q->orWhereHas('services', function ($q) use ($minPrice, $maxPrice) {
    //                         $q->where('price', '>=', $minPrice)
    //                             ->where('price', '<=', $maxPrice);
    //                     });
    //                 }
    //             });
    //         }

    //         // Flexible location filter (city, state, country, address)
    //         if ($request->filled('location')) {
    //             $location = $request->input('location');
    //             $query->where(function ($q) use ($location) {
    //                 $q->orWhereHas('store.storeAddress', function ($addressQ) use ($location) {
    //                     $addressQ->where('city', 'LIKE', "%$location%")
    //                         ->orWhere('state', 'LIKE', "%$location%")
    //                         ->orWhere('country', 'LIKE', "%$location%")
    //                         ->orWhere('address', 'LIKE', "%$location%")
    //                         ->orWhere('formatted_address', 'LIKE', "%$location%")
    //                     ;
    //                 });
    //             });
    //         }

    //         // Enhanced provider name filter
    //         if ($request->filled('provider_name')) {
    //             $providerName = $request->input('provider_name');
    //             $query->where(function ($q) use ($providerName) {
    //                 $q->where('name', 'LIKE', "%$providerName%")
    //                     ->orWhere('firstName', 'LIKE', "%$providerName%")
    //                     ->orWhere('lastName', 'LIKE', "%$providerName%");
    //             });
    //         }

    //         // Enhanced provider service filter
    //         if ($request->filled('provider_service')) {
    //             $providerService = $request->input('provider_service');
    //             $query->where(function ($q) use ($providerService) {
    //                 $q->whereHas('services', function ($q) use ($providerService) {
    //                     $q->where('name', 'LIKE', "%$providerService%")
    //                         ->orWhere('description', 'LIKE', "%$providerService%");
    //                 })->orWhereHas('rentedStores.userStoreServiceType.serviceType', function ($q) use ($providerService) {
    //                     $q->where('name', 'LIKE', "%$providerService%");
    //                 })->orWhere('specialization', 'LIKE', "%$providerService%");
    //             });
    //         }

    //         // Enhanced rating filter
    //         if ($request->filled('rating')) {
    //             $rating = $request->input('rating');
    //             $userIdsWithRating = DB::table('reviews')
    //                 ->select('merchant_id')
    //                 ->groupBy('merchant_id')
    //                 ->havingRaw('AVG(rating) >= ?', [$rating])
    //                 ->pluck('merchant_id')
    //                 ->toArray();

    //             $query->whereIn('id', $userIdsWithRating);
    //         }

    //         // Enhanced price range filter - Apply AFTER location filtering
    //         if ($request->filled('min_price') || $request->filled('max_price')) {
    //             $minPrice = $request->input('min_price', 0);
    //             $maxPrice = $request->input('max_price', PHP_INT_MAX);

    //             $query->whereHas('services', function ($q) use ($minPrice, $maxPrice) {
    //                 $q->where('price', '>=', $minPrice)
    //                     ->where('price', '<=', $maxPrice);
    //             });
    //         } else if ($request->filled('price')) {
    //             $price = $request->input('price');
    //             $query->whereHas('services', function ($q) use ($price) {
    //                 $q->where('price', '<=', $price);
    //             });
    //         }

    //         // Filter by service type
    //         if ($request->filled('service_type')) {
    //             $serviceType = $request->input('service_type');
    //             $query->where(function ($q) use ($serviceType) {
    //                 $q->where('specialization', 'LIKE', "%$serviceType%")
    //                     ->orWhereHas('rentedStores.userStoreServiceType.serviceType', function ($q) use ($serviceType) {
    //                         $q->where('name', 'LIKE', "%$serviceType%");
    //                     });
    //             });
    //         }

    //        // Apply availability filter
    //        if ($request->filled('availability')) {
    //         $availability = $request->input('availability');
    //         $today = now();
    //         $dayOfWeek = null;

    //         switch ($availability) {
    //             case 'Today':
    //                 $dayOfWeek = $today->format('l'); // Full day name (Monday, Tuesday, etc.)
    //                 break;
    //             case 'Tomorrow':
    //                 $dayOfWeek = $today->copy()->addDay()->format('l'); // Use copy() to avoid mutating original
    //                 break;
    //             case 'Next week':
    //                 $dayOfWeek = $today->copy()->addWeek()->format('l'); // Use copy() to avoid mutating original
    //                 break;
    //         }

    //         if ($dayOfWeek) {
    //             if ($searchingForStores) {
    //                 // For stores, check days_available JSON field
    //                 $query->whereRaw("JSON_CONTAINS(days_available, ?)", [json_encode($dayOfWeek)]);
    //             } else {
    //                 // For service providers, check both store availability and service availability hours
    //                 $query->where(function ($q) use ($dayOfWeek) {
    //                     // Check if they have a store with availability
    //                     $q->whereHas('store', function ($storeQuery) use ($dayOfWeek) {
    //                         $storeQuery->whereRaw("JSON_CONTAINS(days_available, ?)", [json_encode($dayOfWeek)]);
    //                     })
    //                         // OR check service availability hours (fallback)
    //                         ->orWhereHas('services.availabilityHours', function ($serviceQuery) use ($dayOfWeek) {
    //                             $serviceQuery->where(function ($subQ) use ($dayOfWeek) {
    //                                 $subQ->where('day', $dayOfWeek) // Full name: Monday
    //                                     ->orWhere('day', strtolower($dayOfWeek)) // lowercase: monday
    //                                     ->orWhere('day', substr($dayOfWeek, 0, 3)) // Short: Mon
    //                                     ->orWhere('day', strtolower(substr($dayOfWeek, 0, 3))); // lowercase short: mon
    //                             });
    //                         });
    //                 });
    //             }
    //         }
    //     }

    //         // Filter by accepts card
    //         if ($request->filled('accepts_card') && $request->boolean('accepts_card')) {
    //             $query->where('accepts_card', true);
    //         }

    //         // SORTING AND PAGINATION with distance priority
    //         if ($orderByDistance && $distanceData->isNotEmpty()) {
    //             $serviceProviders = $query->get();

    //             // Sort by distance first, then by other criteria
    //             $serviceProviders = $serviceProviders->sort(function ($a, $b) use ($distanceData) {
    //                 $distanceA = $distanceData->get($a->id)->distance_km ?? PHP_INT_MAX;
    //                 $distanceB = $distanceData->get($b->id)->distance_km ?? PHP_INT_MAX;

    //                 if (abs($distanceA - $distanceB) < 0.1) {
    //                     return $b->bookings_count <=> $a->bookings_count;
    //                 }
    //                 return $distanceA <=> $distanceB;
    //             })->values();

    //             // Add distance information
    //             $serviceProviders = $serviceProviders->map(function ($provider) use ($distanceData) {
    //                 $locationData = $distanceData->get($provider->id);

    //                 if ($locationData) {
    //                     $provider->distance_km = round($locationData->distance_km, 2);
    //                     $provider->distance_miles = round($locationData->distance_km * 0.621371, 2);
    //                     $provider->location = [
    //                         'address' => $locationData->address,
    //                         'city' => $locationData->city,
    //                         'state' => $locationData->state,
    //                         'coordinates' => [
    //                             'latitude' => $locationData->latitude,
    //                             'longitude' => $locationData->longitude
    //                         ]
    //                     ];
    //                 }
    //                 return $provider;
    //             });

    //             // Manual pagination
    //             $page = $request->get('page', 1);
    //             $perPage = $this->perPage;
    //             $total = $serviceProviders->count();
    //             $items = $serviceProviders->forPage($page, $perPage);

    //             $serviceProviders = new \Illuminate\Pagination\LengthAwarePaginator(
    //                 $items,
    //                 $total,
    //                 $perPage,
    //                 $page,
    //                 ['path' => $request->url(), 'query' => $request->query()]
    //             );
    //         } else {
    //             // Default sorting
    //             $sortBy = $request->input('sort_by', 'bookings_count');
    //             $sortDirection = $request->input('sort_direction', 'desc');
    //             $sortDirection = in_array($sortDirection, ['asc', 'desc']) ? $sortDirection : 'desc';

    //             $query->orderBy($sortBy, $sortDirection);
    //             $serviceProviders = $query->paginate($this->perPage);
    //         }

    //         // Add metadata
    //         $transformedData = $this->addMeta(ServiceProviderPreviewResource::collection($serviceProviders));
    //         $response = ['serviceProviders' => $transformedData];

    //         if (!empty($searchMeta)) {
    //             $response['searchMeta'] = $searchMeta;
    //         }

    //         return response()->json($response, 200);
    //     } catch (Exception $e) {
    //         $this->reportExceptionOnBugsnag($e);
    //         return response()->json([
    //             "ResponseStatus" => "Unsuccessful",
    //             "ResponseCode" => 500,
    //             'Detail' => $e->getMessage(),
    //             "ResponseMessage" => 'Something went wrong'
    //         ], 500);
    //     }
    // }



    public function getServiceProvider(Request $request, $merchantCode)
    {
        try {
            $check = User::where('merchant_code', $merchantCode)->first();

            if (!is_null($check) && $check->account_type == 'Owner') {
                $serviceProvider = $check;
            } else {
                $serviceProvider = User::where('merchant_code', $merchantCode)
                    ->withCount(['bookings'])
                    // ->whereHas('store')
                    // ->with('store')
                    ->whereHas('services')
                    ->withCount('services')
                    ->first();
            }

            if (is_null($serviceProvider)) {
                return $this->errorResponse("Service provider not found", 404);
            }

            $serviceProvider = new ServiceProviderResource($serviceProvider);
            return response()->json(compact('serviceProvider'), 200);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(['ResponseStatus' => 'Unsuccessful', 'ResponseCode' => 500, 'Detail' => $e->getMessage(), 'ResponseMessage' => 'Something went wrong'], 500);
        }
    }


    public function getServiceProviderReviews(Request $request, $code)
    {
        try {
            $serviceProvider = User::where("merchant_code", $code)->first();
            if (is_null($serviceProvider)) {
                return $this->errorResponse('Service provider not found', 404);
            }

            $condition = ['merchant_id' => $serviceProvider->id];
            $reviews = Review::where($condition)->latest()->paginate($this->perPage);
            $reviews = $this->addMeta(ServiceProviderReviewResource::collection($reviews));
            return response()->json(compact('reviews'), 200);
        } catch (Exception $e) {
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function getServiceProviderServices(Request $request, $code)
    {
        try {
            $serviceProvider = User::where("merchant_code", $code)->first();
            if (is_null($serviceProvider)) {
                return $this->errorResponse('Service provider not found', 404);
            }

            $services = Service::where('merchant_id', $serviceProvider->id)->latest();
            $services = $this->addMeta(ServiceResource::collection($services->paginate($this->perPage)));
            return response()->json(compact('services'), 200);
        } catch (Exception $e) {
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function getRecentlyUsedProviders(Request $request)
    {
        /** @var User|null */
        $user = User::find($this->getAuthID($request));
        if (is_null($user)) {
            return $this->errorResponse('User not found', 404);
        }
        try {
            $serviceProviderIds = Appointment::where('customer_id', $user->id)->distinct('merchant_id')->pluck('merchant_id');
            $serviceProviders = User::withCount(['bookings', 'services'])
                // ->whereHas('store')
                // ->with('store')
                ->whereIn('id', $serviceProviderIds)
                ->whereHas('services') // Ensure the user has services
                ->withCount('services')
                ->orderBy('bookings_count', 'desc')
                ->paginate($this->perPage);
            $serviceProviders = $this->addMeta(ServiceProviderResource::collection($serviceProviders));
            return response()->json(compact('serviceProviders'), 200);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }


    public function getServiceProviderInfo(Request $request, $merchantCode)
    {
        try {
            // Fetch the service provider with necessary counts and checks
            /** @var User|null */
            $serviceProvider = User::where('merchant_code', $merchantCode)
                ->withCount(['bookings', 'services'])
                ->whereHas('services') // Ensure the user has services
                ->first();

            if (is_null($serviceProvider)) {
                return $this->errorResponse("Service provider not found", 404);
            }

            // Retrieve the active rented store
            $activeRentedStore = $serviceProvider->rentedStores()
                ->where('available_status', true)
                ->with(['store.storeAddress']) // Include linked store and its address
                ->first();

            // Check if the rented store, its linked store, and the address exist
            if ($activeRentedStore && $activeRentedStore->store && $activeRentedStore->store->storeAddress) {
                // Attach the store to the service provider
                $serviceProvider->active_store = $activeRentedStore->store;

                // Find nearby service providers with active stores in the same location
                $location = $activeRentedStore->store->storeAddress;
                $nearbyServiceProviders = User::whereHas('rentedStores', function ($query) use ($location) {
                    $query->where('available_status', true)
                        ->whereHas('store.storeAddress', function ($subQuery) use ($location) {
                            $subQuery->where('city', $location->city)
                                ->where('state', $location->state);
                        });
                })
                    ->where('id', '!=', $serviceProvider->id) // Exclude the current service provider
                    ->limit(3) // Limit results to 3
                    ->get();
            } else {
                $nearbyServiceProviders = collect(); // Empty collection if no active rented store or linked address
            }

            // Attach nearby service providers to the service provider resource
            $serviceProvider->nearbyServiceProviders = $nearbyServiceProviders;

            // Transform service provider into resource
            $serviceProvider = new ServiceProviderDetailsResource($serviceProvider);

            return response()->json(compact('serviceProvider'), 200);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json([
                "ResponseStatus" => "Unsuccessful",
                "ResponseCode" => 500,
                'Detail' => $e->getMessage(),
                "ResponseMessage" => 'Something went wrong',
            ], 500);
        }
    }

    public function getSuggestedServiceProviders(Request $request)
    {
        /** @var User|null */
        $user = User::find($this->getAuthID($request));

        if (is_null($user)) {
            return $this->errorResponse('User not found', 404);
        }

        try {
            // Fetch recently used service providers
            $recentServiceProviderIds = Appointment::where('customer_id', $user->id)
                ->distinct('merchant_id')
                ->pluck('merchant_id');

            // Fetch service_type_ids based on the user's interests
            $serviceTypeIds = Interest::whereIn('id', $user->userInterests()->pluck('interest_id'))
                ->pluck('service_type_id');

            // Fetch service providers linked to the interests through UserStore
            $interestBasedProviderIds = UserStore::whereIn('service_type_id', $serviceTypeIds)
                //->where('available_status', true) 
                ->pluck('user_id');

            $allProviderIds = $recentServiceProviderIds->merge($interestBasedProviderIds)->unique();
            // Fetch service providers with related data
            $serviceProviders = User::withCount(['bookings', 'services'])
                ->whereIn('id', $allProviderIds)
                ->whereHas('services')
                ->orderBy('bookings_count', 'desc')
                ->paginate($this->perPage);

            $serviceProviders = $this->addMeta(ServiceProviderPreviewResource::collection($serviceProviders));

            return response()->json(compact('serviceProviders'), 200);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json([
                "ResponseStatus" => "Unsuccessful",
                "ResponseCode" => 500,
                'Detail' => $e->getMessage(),
                "ResponseMessage" => 'Something went wrong',
            ], 500);
        }
    }

    /**
     * Apply coordinate corrections for known inaccurate locations
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    private function applyCoordinateCorrections($request)
    {
        if (!$request->filled('latitude') || !$request->filled('longitude')) {
            return;
        }

        $lat = (float) $request->latitude;
        $lng = (float) $request->longitude;

        // Known coordinate corrections
        $corrections = [
            [
                'from' => ['lat' => 6.522704399999999, 'lng' => 3.6217802],
                'to' => ['lat' => 6.5243793, 'lng' => 3.3792057],
                'tolerance' => 0.0001,
                'description' => 'Override to Lagos City'
            ]
        ];

        foreach ($corrections as $correction) {
            if (
                abs($lat - $correction['from']['lat']) < $correction['tolerance'] &&
                abs($lng - $correction['from']['lng']) < $correction['tolerance']
            ) {

                $request->merge([
                    'latitude' => $correction['to']['lat'],
                    'longitude' => $correction['to']['lng']
                ]);
                break;
            }
        }
    }
}
