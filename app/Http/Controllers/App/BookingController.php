<?php

namespace App\Http\Controllers\App;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Store;
use App\Models\Service;
use App\Models\UserStore;
use App\Repositories\Mailer;
use App\Models\Appointment;
use App\Models\BoothRental;
use App\Models\ServicesPromo;
use Illuminate\Http\Request;
use App\Jobs\CreateVirtualAccount;
use App\Models\AppointmentService;
use App\Models\TransactionAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use App\Jobs\LinkMerchantVirtualAccount;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\AppointmentResource;
use Illuminate\Support\Facades\Log as Logger;
use App\Notifications\AppointmentNotification;

class BookingController extends Controller
{


    public function bookAppointment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|date_format:H:i',
            'services' => 'required|array|min:1',
            'services.*.id' => 'required|exists:services,id',
            'services.*.quantity' => 'required|integer|min:1',
            'store_id' => 'required|exists:stores,id',
            'phone' => 'required|numeric',
            'tip_percentage' => 'nullable|numeric',
            'custom_tip' => 'nullable|numeric|min:0',
            'payWith' => 'string|required|in:paystack,wallet',
            'callBackUrl' => 'url|required',
        ]);
        Logger::info('bookAppointment Request ', [$request->all()]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        try {
            $userId = $this->getAuthID($request);
            $client = User::find($userId);
            $services = $request->services;
            $paymentProvider = $request->input('payWith');
            $callBackUrl = $request->input('callBackUrl');
            $totalCost = 0;
            $totalDiscount = 0;
            $currency = "NGN";
            $merchantId = 0;
            $serviceBreakdown = [];

            foreach ($services as $service) {
                $serviceData = Service::find($service['id']);
                $originalPrice = $serviceData->price * $service['quantity'];
                $servicePrice = $originalPrice;
                $discount = 0;
                $promoDetails = null;


                $activePromo = $this->getActivePromo($serviceData->id);

                if ($activePromo) {
                    $discount = $activePromo->discount_amount * $service['quantity'];
                    $servicePrice = $originalPrice - $discount;
                    $totalDiscount += $discount;
                    $promoDetails = $activePromo;

                    Logger::info('Promo Applied', [
                        'service_id' => $serviceData->id,
                        'service_name' => $serviceData->name,
                        'original_price' => $originalPrice,
                        'promo_discount_amount' => $activePromo->discount_amount,
                        'total_discount_amount' => $discount,
                        'final_price' => $servicePrice,
                        'promo_id' => $activePromo->id
                    ]);
                }

                $serviceBreakdown[] = [
                    'service_id' => $serviceData->id,
                    'service_name' => $serviceData->name,
                    'quantity' => $service['quantity'],
                    'original_price' => $originalPrice,
                    'discount' => $discount,
                    'final_price' => $servicePrice,
                    'promo_applied' => !is_null($activePromo),
                    'promo_id' => $activePromo ? $activePromo->id : null
                ];

                $totalCost += $servicePrice;
                $merchantId = $serviceData->merchant_id;
            }

            $merchant = User::find($merchantId);

            if ($request->tip_percentage) {
                $tipAmount = ($request->tip_percentage / 100) * $totalCost;
            } else {
                $tipAmount = $request->custom_tip ?? 0;
            }

            $totalCost += $tipAmount;


            Logger::info('Booking Price Breakdown', [
                'services' => $serviceBreakdown,
                'subtotal_after_discount' => $totalCost - $tipAmount,
                'total_discount' => $totalDiscount,
                'tip_amount' => $tipAmount,
                'final_total' => $totalCost
            ]);

            $appointment = Appointment::where([
                'merchant_id' => $merchantId,
                'date' => $request->appointment_date,
                'time' => $request->appointment_time,
                'payment_status' => 1
            ])->whereNotIn('status', ['Cancelled', 'Denied'])->first();

            if ($appointment) {
                return $this->errorResponse("Time slot for $request->appointment_time has already been taken. Please, try booking again outside this time.", 400);
            }

            DB::beginTransaction();

            /** @var Appointment */
            $appointment = Appointment::create([
                "customer_id" => $userId,
                "merchant_id" => $merchantId,
                "store_id" => $request->store_id,
                "date" => $request->appointment_date,
                "time" => $request->appointment_time,
                "phone_number" => $request->phone,
                "tip" => $tipAmount,
                "total_amount" => $totalCost,
                "discount_amount" => $totalDiscount,
                'currency' => $currency,
            ]);

            $appointmentRef = unique_random_string() . $appointment->id . '-' . time();
            $appointment->update(['appointment_ref' => $appointmentRef]);

            foreach ($services as $index => $service) {
                $serviceData = Service::find($service["id"]);
                $serviceBreakdownItem = $serviceBreakdown[$index];

                AppointmentService::create([
                    "appointment_id" => $appointment->id,
                    "service_id" => $serviceData->id,
                    "quantity" => $service['quantity'],
                    "original_price" => $serviceBreakdownItem['original_price'],
                    "discount_amount" => $serviceBreakdownItem['discount'],
                    "price" => $serviceBreakdownItem['final_price'],
                    "promo_applied" => $serviceBreakdownItem['promo_applied'],
                    "promo_id" => $serviceBreakdownItem['promo_id']
                ]);
            }

            DB::commit();

            //send email
            try {
                $store = \App\Models\Store::find($request->store_id);
                \Mail::to($client->email)->send(new \App\Mail\AppointmentBookingConfirmation($client, $appointment, $merchant, $store));
            } catch (Exception $emailException) {
                Logger::error('Failed to send appointment booking email', [
                    'appointment_id' => $appointment->id,
                    'client_id' => $client->id,
                    'error' => $emailException->getMessage()
                ]);
            }

            //Get payment link
            if ($paymentProvider == "paystack") {
                Logger::info('Callback URL before Paystack', ['url' => $callBackUrl]);
                $paymentLink = $this->paystackUtils->generatePaymentLink($client, $totalCost, "NGN", 0, $callBackUrl);

                Logger::info('PaystackUtils - Input params', [
                    'callback_url' => $callBackUrl,
                    'amount' => $totalCost,
                    'currency' => $currency
                ]);

                if ($paymentLink['error'] == 0) {
                    $paymentUrl = $paymentLink['paymentUrl'];
                    $paymentRef = $paymentLink['paymentRef'];
                    $appointment->update(['payment_url' => $paymentUrl, 'payment_ref' => $paymentRef, 'payment_gateway' => $paymentProvider]);
                    $virtualAccount = \App\Models\TransactionAccount::where('appointment_id', $appointment->id)->first();
                    Logger::info('Virtual Account Debug', [
                        'appointment_id' => $appointment->id,
                        'virtual_account_found' => !is_null($virtualAccount),
                        'virtual_account_data' => $virtualAccount ? $virtualAccount->toArray() : null
                    ]);

                    $appointment = $appointment->fresh();
                    $appointment = new AppointmentResource($appointment);
                    return response()->json(compact('paymentUrl', 'paymentRef', 'appointment'), 201);
                } else {
                    return $this->errorResponse("We couldn't generate a payment link. Please, try again later", 500);
                }
            } else if ($paymentProvider == 'wallet') {
                try {
                    LinkMerchantVirtualAccount::dispatchSync($appointment->id, 'appointment');

                    $paymentRef = "WALL" . unique_random_string();
                    $appointment->update(['payment_ref' => $paymentRef, 'payment_gateway' => $paymentProvider]);

                    $virtualAccount = \App\Models\TransactionAccount::where('appointment_id', $appointment->id)->first();
                    Logger::info('Virtual Account Debug', [
                        'appointment_id' => $appointment->id,
                        'virtual_account_found' => !is_null($virtualAccount),
                        'virtual_account_data' => $virtualAccount ? $virtualAccount->toArray() : null,
                        'provider_env' => env('Virtual_Account_Provider'),
                        'merchant_wallet' => $appointment->serviceProvider ? $appointment->serviceProvider->wallet : null
                    ]);

                    $response = [
                        'paymentRef' => $paymentRef,
                        'appointment' => new AppointmentResource($appointment->fresh()),
                    ];

                    if ($virtualAccount) {
                        $response['virtualAccount'] = [
                            'account_number' => $virtualAccount->account_number,
                            'account_name' => $virtualAccount->account_name,
                            'bank_code' => $virtualAccount->bank_code,
                            'amount' => $virtualAccount->amount,
                            'processing_fee' => $virtualAccount->processing_fee,
                            'total' => $virtualAccount->total,
                        ];
                    }

                    return response()->json($response, 201);
                } catch (Exception $e) {
                    DB::rollBack();
                    Logger::info('Virtual Account Error', [$e->getMessage()]);
                    return $this->errorResponse("Failed to create virtual account", 500);
                }
            }
        } catch (Exception $e) {
            DB::rollBack();
            Logger::info('bookAppointment Error', [$e->getMessage() . ' - ' . $e->__toString()]);
            return response()->json([
                "ResponseStatus" => "Unsuccessful",
                "ResponseCode" => 500,
                'Detail' => $e->getMessage(),
                'message' => 'Something went wrong',
                "ResponseMessage" => 'Something went wrong'
            ], 500);
        }
    }


    public function bookAppointmentPublic(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|date_format:H:i',
            'services' => 'required|array|min:1',
            'services.*.id' => 'required|exists:services,id',
            'services.*.quantity' => 'required|integer|min:1',
            'store_id' => 'required|exists:stores,id',
            'phone' => 'required|numeric',
            'email' => 'required|email',
            'name' => 'required|string|max:255',
            'tip_percentage' => 'nullable|numeric',
            'custom_tip' => 'nullable|numeric|min:0',
            'payWith' => 'string|required|in:paystack,wallet',
            'callBackUrl' => 'url|required',
        ]);

        // Logger::info('bookAppointmentPublic Request ', [$request->all()]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        try {
            /** @var User */
            $client = User::firstOrCreate(
                ['email' => $request->email],
                [
                    'name' => $request->name,
                    'phone' => $request->phone,
                    'user_type' => 'guest',
                    'password' => bcrypt(Str::random(12)),
                    'email_verified_at' => null,
                ]
            );


            if ($client->name !== $request->name || $client->phone !== $request->phone) {
                $client->update([
                    'name' => $request->name,
                    'phone' => $request->phone,
                ]);
            }

            $userId = $client->id;
            $services = $request->services;
            $paymentProvider = $request->input('payWith');
            $callBackUrl = $request->input('callBackUrl');
            $totalCost = 0;
            $totalDiscount = 0;
            $currency = "NGN";
            $merchantId = 0;
            $serviceBreakdown = [];

            foreach ($services as $service) {
                $serviceData = Service::find($service['id']);
                $originalPrice = $serviceData->price * $service['quantity'];
                $servicePrice = $originalPrice;
                $discount = 0;
                $promoDetails = null;

                $activePromo = $this->getActivePromo($serviceData->id);

                if ($activePromo) {
                    $discount = $activePromo->discount_amount * $service['quantity'];
                    $servicePrice = $originalPrice - $discount;
                    $totalDiscount += $discount;
                    $promoDetails = $activePromo;

                    Logger::info('Promo Applied - Public Booking', [
                        'service_id' => $serviceData->id,
                        'service_name' => $serviceData->name,
                        'original_price' => $originalPrice,
                        'promo_discount_amount' => $activePromo->discount_amount,
                        'total_discount_amount' => $discount,
                        'final_price' => $servicePrice,
                        'promo_id' => $activePromo->id,
                        'booking_type' => 'public'
                    ]);
                }

                $serviceBreakdown[] = [
                    'service_id' => $serviceData->id,
                    'service_name' => $serviceData->name,
                    'quantity' => $service['quantity'],
                    'original_price' => $originalPrice,
                    'discount' => $discount,
                    'final_price' => $servicePrice,
                    'promo_applied' => !is_null($activePromo),
                    'promo_id' => $activePromo ? $activePromo->id : null
                ];

                $totalCost += $servicePrice;
                $merchantId = $serviceData->merchant_id;
            }

            $merchant = User::find($merchantId);

            if ($request->tip_percentage) {
                $tipAmount = ($request->tip_percentage / 100) * $totalCost;
            } else {
                $tipAmount = $request->custom_tip ?? 0;
            }

            $totalCost += $tipAmount;

            Logger::info('Public Booking Price Breakdown', [
                'guest_email' => $client->email,
                'guest_name' => $client->name,
                'services' => $serviceBreakdown,
                'subtotal_after_discount' => $totalCost - $tipAmount,
                'total_discount' => $totalDiscount,
                'tip_amount' => $tipAmount,
                'final_total' => $totalCost,
                'booking_type' => 'public'
            ]);


            $appointment = Appointment::where([
                'merchant_id' => $merchantId,
                'date' => $request->appointment_date,
                'time' => $request->appointment_time,
                'payment_status' => 1
            ])->whereNotIn('status', ['Cancelled', 'Denied'])->first();

            if ($appointment) {
                return $this->errorResponse("Time slot for $request->appointment_time has already been taken. Please, try booking again outside this time.", 400);
            }

            DB::beginTransaction();

            /** @var Appointment */
            $appointment = Appointment::create([
                "customer_id" => $userId,
                "merchant_id" => $merchantId,
                "store_id" => $request->store_id,
                "date" => $request->appointment_date,
                "time" => $request->appointment_time,
                "phone_number" => $request->phone,
                "tip" => $tipAmount,
                "total_amount" => $totalCost,
                "discount_amount" => $totalDiscount,
                'currency' => $currency,
                'booking_type' => 'public',
            ]);

            $appointmentRef = unique_random_string() . $appointment->id . '-' . time();
            $appointment->update(['appointment_ref' => $appointmentRef]);

            foreach ($services as $index => $service) {
                $serviceData = Service::find($service["id"]);
                $serviceBreakdownItem = $serviceBreakdown[$index];

                AppointmentService::create([
                    "appointment_id" => $appointment->id,
                    "service_id" => $serviceData->id,
                    "quantity" => $service['quantity'],
                    "original_price" => $serviceBreakdownItem['original_price'],
                    "discount_amount" => $serviceBreakdownItem['discount'],
                    "price" => $serviceBreakdownItem['final_price'],
                    "promo_applied" => $serviceBreakdownItem['promo_applied'],
                    "promo_id" => $serviceBreakdownItem['promo_id']
                ]);
            }

            DB::commit();


            try {
                $store = \App\Models\Store::find($request->store_id);
                \Mail::to($client->email)->send(new \App\Mail\AppointmentBookingConfirmation($client, $appointment, $merchant, $store));
            } catch (Exception $emailException) {
                Logger::error('Failed to send public booking email', [
                    'appointment_id' => $appointment->id,
                    'client_id' => $client->id,
                    'client_email' => $client->email,
                    'booking_type' => 'public',
                    'error' => $emailException->getMessage()
                ]);
            }


            if ($paymentProvider == "paystack") {
                Logger::info('Paystack Payment - Public Booking', [
                    'callback_url' => $callBackUrl,
                    'amount' => $totalCost,
                    'currency' => $currency,
                    'client_email' => $client->email
                ]);

                $paymentLink = $this->paystackUtils->generatePaymentLink($client, $totalCost, "NGN", 0, $callBackUrl);

                if ($paymentLink['error'] == 0) {
                    $paymentUrl = $paymentLink['paymentUrl'];
                    $paymentRef = $paymentLink['paymentRef'];
                    $appointment->update(['payment_url' => $paymentUrl, 'payment_ref' => $paymentRef, 'payment_gateway' => $paymentProvider]);

                    $virtualAccount = \App\Models\TransactionAccount::where('appointment_id', $appointment->id)->first();
                    Logger::info('Virtual Account Debug - Public Booking', [
                        'appointment_id' => $appointment->id,
                        'virtual_account_found' => !is_null($virtualAccount),
                        'virtual_account_data' => $virtualAccount ? $virtualAccount->toArray() : null,
                        'booking_type' => 'public'
                    ]);

                    $appointment = $appointment->fresh();
                    $appointment = new AppointmentResource($appointment);
                    return response()->json(compact('paymentUrl', 'paymentRef', 'appointment'), 201);
                } else {
                    return $this->errorResponse("We couldn't generate a payment link. Please, try again later", 500);
                }
            } else if ($paymentProvider == 'wallet') {
                try {
                    LinkMerchantVirtualAccount::dispatchSync($appointment->id, 'appointment');

                    $paymentRef = "WALL" . unique_random_string();
                    $appointment->update(['payment_ref' => $paymentRef, 'payment_gateway' => $paymentProvider]);

                    $virtualAccount = \App\Models\TransactionAccount::where('appointment_id', $appointment->id)->first();
                    Logger::info('Virtual Account Debug - Public Booking', [
                        'appointment_id' => $appointment->id,
                        'virtual_account_found' => !is_null($virtualAccount),
                        'virtual_account_data' => $virtualAccount ? $virtualAccount->toArray() : null,
                        'provider_env' => env('Virtual_Account_Provider'),
                        'merchant_wallet' => $appointment->serviceProvider ? $appointment->serviceProvider->wallet : null,
                        'booking_type' => 'public',
                        'client_email' => $client->email
                    ]);

                    $response = [
                        'paymentRef' => $paymentRef,
                        'appointment' => new AppointmentResource($appointment->fresh()),
                    ];

                    if ($virtualAccount) {
                        $response['virtualAccount'] = [
                            'account_number' => $virtualAccount->account_number,
                            'account_name' => $virtualAccount->account_name,
                            'bank_code' => $virtualAccount->bank_code,
                            'amount' => $virtualAccount->amount,
                            'processing_fee' => $virtualAccount->processing_fee,
                            'total' => $virtualAccount->total,
                        ];
                    }

                    return response()->json($response, 201);
                } catch (Exception $e) {
                    DB::rollBack();
                    Logger::info('Virtual Account Error - Public Booking', [
                        'error' => $e->getMessage(),
                        'client_email' => $client->email,
                        'booking_type' => 'public'
                    ]);
                    return $this->errorResponse("Failed to create virtual account", 500);
                }
            }
        } catch (Exception $e) {
            DB::rollBack();
            Logger::info('bookAppointmentPublic Error', [
                'error' => $e->getMessage() . ' - ' . $e->__toString(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                "ResponseStatus" => "Unsuccessful",
                "ResponseCode" => 500,
                'Detail' => $e->getMessage(),
                "ResponseMessage" => 'Something went wrong'
            ], 500);
        }
    }


    private function getActivePromo($serviceId)
    {
        $today = now();

        return ServicesPromo::where('service_id', $serviceId)
            // ->where('status', true) 
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->first();
    }

    public function verifyAppointmentBooking(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'paymentReference' => 'string|required',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        try {
            $user = $this->getAuthUser($request);
            if (is_null($user)) {
                return $this->errorResponse('User not found', 404);
            }

            $paymentRef = $request->input('paymentReference');

            $appointment = Appointment::where('payment_ref', $paymentRef)->first();

            if (is_null($appointment)) {
                return $this->errorResponse('Appointment not found', 404);
            }
            //find the appointment merchant
            $merchant = User::find($appointment->merchant_id);
            if (is_null($merchant)) {
                return $this->errorResponse('Merchant not found', 404);
            }

            if ($appointment->payment_gateway == "wallet") {
                $tranxAcct = TransactionAccount::where(['appointment_id' => $appointment->id])->first();
                if (!is_null($tranxAcct) && $tranxAcct->status == 1) {
                    $paymentStatus = "Success";
                    return response()->json(compact('paymentStatus', 'appointment'), 201);
                }
                return $this->errorResponseWithData('Payment verification not successful', 500, []);
            } else {
                //verify paystack Payment
                $payment = $this->paystackUtils->verifyPayment($paymentRef);
                // return $payment;
                if ($payment['error'] == 0) {
                    $paymentStatus = $payment['paymentDetails']['data']['gateway_response'];
                    $customerId = $payment['paymentDetails']['data']['customer']['id'] ?? null;
                    $authorizationCode = $payment['paymentDetails']['data']['authorization']['authorization_code'] ?? null;
                    $amount = $payment['paymentDetails']['data']['amount'];
                    $status = $payment['paymentDetails']['data']['status'];
                    $paymentDetail = $this->updatePendingPayment($paymentRef, $paymentStatus);
                    if ($status == 'success' && $payment['statusCode'] == '00') {
                        //process booking
                        $appointment->update(['status' => 'Paid', 'payment_status' => 1]);
                        //$this->peppUtil->send_booking_confirmation_email($booking);  
                    }

                    $user->notify(new AppointmentNotification($appointment));
                    $merchant->notify(new AppointmentNotification($appointment));

                    $appointment = new AppointmentResource($appointment);
                    return response()->json(compact('paymentStatus', 'appointment'), 201);
                } else {
                    $Detail = $payment['paymentDetails'];
                    return $this->errorResponseWithData('Payment verification not successful', 500, compact('Detail'));
                }
            }
        } catch (Exception $e) {
            Logger::info('verifyAppointmentBooking Error', [$e->getMessage() . ' - ' . $e->__toString()]);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function verifyAppointmentBookingPublic(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'paymentReference' => 'string|required',
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        try {
            $paymentRef = $request->input('paymentReference');
            $email = $request->input('email');

            $appointment = Appointment::where('payment_ref', $paymentRef)->first();

            if (is_null($appointment)) {
                return $this->errorResponse('Appointment not found', 404);
            }


            $customer = User::find($appointment->customer_id);
            if (!$customer || $customer->email !== $email) {
                return $this->errorResponse('Unauthorized access to this appointment', 403);
            }

            $merchant = User::find($appointment->merchant_id);
            if (is_null($merchant)) {
                return $this->errorResponse('Merchant not found', 404);
            }

            if ($appointment->payment_gateway == "wallet") {
                $tranxAcct = TransactionAccount::where(['appointment_id' => $appointment->id])->first();
                if (!is_null($tranxAcct) && $tranxAcct->status == 1) {
                    $paymentStatus = "Success";
                    return response()->json(compact('paymentStatus', 'appointment'), 201);
                }
                return $this->errorResponseWithData('Payment verification not successful', 500, []);
            } else {

                $payment = $this->paystackUtils->verifyPayment($paymentRef);

                if ($payment['error'] == 0) {
                    $paymentStatus = $payment['paymentDetails']['data']['gateway_response'];
                    $customerId = $payment['paymentDetails']['data']['customer']['id'] ?? null;
                    $authorizationCode = $payment['paymentDetails']['data']['authorization']['authorization_code'] ?? null;
                    $amount = $payment['paymentDetails']['data']['amount'];
                    $status = $payment['paymentDetails']['data']['status'];

                    $paymentDetail = $this->updatePendingPayment($paymentRef, $paymentStatus);

                    if ($status == 'success' && $payment['statusCode'] == '00') {
                        $appointment->update(['status' => 'Paid', 'payment_status' => 1]);
                    }


                    $customer->notify(new AppointmentNotification($appointment));
                    $merchant->notify(new AppointmentNotification($appointment));

                    $appointment = new AppointmentResource($appointment);
                    return response()->json(compact('paymentStatus', 'appointment'), 201);
                } else {
                    $Detail = $payment['paymentDetails'];
                    return $this->errorResponseWithData('Payment verification not successful', 500, compact('Detail'));
                }
            }
        } catch (Exception $e) {
            Logger::info('verifyAppointmentBookingPublic Error', [
                'error' => $e->getMessage() . ' - ' . $e->__toString(),
                'email' => $request->input('email'),
                'payment_ref' => $request->input('paymentReference')
            ]);
            return response()->json([
                "ResponseStatus" => "Unsuccessful",
                "ResponseCode" => 500,
                'Detail' => $e->getMessage(),
                "ResponseMessage" => 'Something went wrong'
            ], 500);
        }
    }

    public function myAppointments(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => "required|string|in:upcoming,past",
            'status' => "nullable|string|in:Accepted,Denied,Cancelled,Paid,Delivered,Completed",
            'appointment_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        try {
            $clientID = $this->getAuthID($request);
            $client = User::find($clientID);
            if (!$client) {
                return $this->errorResponse('Client not found.', 404);
            }

            $type = $request->type;
            $fiter_status = $request->status;

            $appointments = Appointment::where(['customer_id' => $client->id, 'payment_status' => 1]);
            if ($type == 'upcoming') {
                //where date > Carbon::now()
                //$appointments = $appointments->whereRaw("STR_TO_DATE(CONCAT(date, ' ', time), '%Y-%m-%d %H:%i') > ?", [Carbon::now()]);
                $appointments = $appointments->where(DB::raw("CONCAT(date, ' ', time)"), '>=', Carbon::now());
            } else {
                //$appointments = $appointments->whereRaw("STR_TO_DATE(CONCAT(date, ' ', time), '%Y-%m-%d %H:%i') < ?", [Carbon::now()]);
                $appointments = $appointments->where(DB::raw("CONCAT(date, ' ', time)"), '<', Carbon::now());
            }

            if (!is_null($fiter_status)) {
                $appointments = $appointments->where("status", $fiter_status);
            }
            if ($request->filled('appointment_date')) {
                $appointments = $appointments->where("date", $request->appointment_date);
            }

            $appointments = $appointments->orderBy('id', 'DESC')->paginate($this->perPage);
            $appointments = $this->addMeta(AppointmentResource::collection($appointments));
            return response()->json(compact('appointments'), 200);
        } catch (Exception $e) {
            Logger::info('myAppointments', [$e->getMessage() . ' - ' . $e->__toString()]);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function myBookings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => "nullable|string|in:upcoming,past",
            'status' => "nullable|string",
            'staff_id' => 'nullable|integer|exists:users,id',
            'appointment_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        try {
            $merchantID = $this->getAuthID($request);
            if ($request->filled("staff_id")) {
                $userStore = UserStore::where([
                    'user_id' => $request->staff_id,
                    'available_status' => 1
                ])->first();
                if (!is_null($userStore)) {
                    $store = $userStore->store;
                    if ($store->merchant_id != $merchantID) {
                        return $this->errorResponse('Staff not under Merchant store.', 404);
                    }
                }
                $merchant = User::find($request->staff_id);
            } else {
                $merchant = User::find($merchantID);
            }

            if (!$merchant) {
                return $this->errorResponse('Merchant not found.', 404);
            }
            $type = $request->type;
            $fiter_status = $request->status;

            if ($merchant->account_type == "Owner") {
                $store_ids = Store::where("merchant_id", $merchant->id)->pluck('id');
                if ($fiter_status === 'Cancelled') {
                    $appointments = Appointment::whereIn("store_id", $store_ids);
                } else {
                    $appointments = Appointment::where(['payment_status' => 1])->whereIn("store_id", $store_ids);
                }
            } else {
                if ($fiter_status === 'Cancelled') {
                    $appointments = Appointment::where(['merchant_id' => $merchant->id]);
                } else {
                    $appointments = Appointment::where(['merchant_id' => $merchant->id, 'payment_status' => 1]);
                }
            }

            if ($type == 'upcoming') {
                //where date > Carbon::now()
                //$appointments = $appointments->whereRaw("STR_TO_DATE(CONCAT(date, ' ', time), '%Y-%m-%d %H:%i') > ?", [Carbon::now()]);
                $appointments = $appointments->where(DB::raw("CONCAT(date, ' ', time)"), '>=', Carbon::now());
            } elseif ($type == 'past') {
                //$appointments = $appointments->whereRaw("STR_TO_DATE(CONCAT(date, ' ', time), '%Y-%m-%d %H:%i') < ?", [Carbon::now()]);
                $appointments = $appointments->where(DB::raw("CONCAT(date, ' ', time)"), '<', Carbon::now());
            }
            if (!is_null($fiter_status)) {
                $appointments = $appointments->where("status", $fiter_status);
            }
            if ($request->filled('appointment_date')) {
                $appointments = $appointments->where("date", $request->appointment_date);
            }

            $appointments = $appointments->orderBy('id', 'DESC')->paginate($this->perPage);
            $appointments = $this->addMeta(AppointmentResource::collection($appointments));
            return response()->json(compact('appointments'), 200);
        } catch (Exception $e) {
            Logger::info('myBookings', [$e->getMessage() . ' - ' . $e->__toString()]);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function viewAppointment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'appointmentID' => "required|integer",
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        try {
            $userID = $this->getAuthID($request);
            $user = User::find($userID);
            if (!$user) {
                return $this->errorResponse('User not found.', 404);
            }

            if ($user->account_type == "Client") {
                $appointment = Appointment::where(['id' => $request->appointmentID, 'customer_id' => $user->id])->first();
            } else {
                $appointment = Appointment::where(['id' => $request->appointmentID, 'merchant_id' => $user->id])->first();
            }
            if (!$appointment) {
                return $this->errorResponse('Appointment not found.', 404);
            }

            $appointment = new AppointmentResource($appointment);
            return response()->json(compact('appointment'), 200);
        } catch (Exception $e) {
            Logger::info('viewAppointment', [$e->getMessage() . ' - ' . $e->__toString()]);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }



    public function cancelAppointment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'appointmentID' => "required|integer",
            'cancel_reason' => "required|string|max:500",
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        try {
            $userID = $this->getAuthID($request);
            $user = User::find($userID);
            if (!$user) {
                return $this->errorResponse('User not found.', 404);
            }


            $appointment = Appointment::where('id', $request->appointmentID)
                ->where(function ($query) use ($user) {
                    $query->where('customer_id', $user->id)
                        ->orWhere('merchant_id', $user->id);
                })->first();

            if (!$appointment) {
                return $this->errorResponse('Appointment not found.', 404);
            }


            // Both client and provider cancellation should map to 'Cancelled'
            $newStatus = 'Cancelled';


            Logger::info('Before appointment update', [
                'appointment_id' => $appointment->id,
                'current_status' => $appointment->status,
                'new_status' => $newStatus,
                'cancel_reason' => $request->cancel_reason,
                'cancelled_by' => $userID
            ]);


            $updateData = [
                'status' => $newStatus,
                'cancel_reason' => $request->cancel_reason,
                'cancelled_by' => $userID,
                'cancelled_at' => now(),
            ];

            $appointment->update($updateData);


            $appointment = $appointment->fresh();


            Logger::info('After appointment update', [
                'appointment_id' => $appointment->id,
                'status' => $appointment->status,
                'cancel_reason' => $appointment->cancel_reason,
                'cancelled_by' => $appointment->cancelled_by,
                'cancelled_at' => $appointment->cancelled_at
            ]);


            $client = User::find($appointment->customer_id);
            $merchant = User::find($appointment->merchant_id);

            if ($client && $client->id !== $userID) {
                $client->notify(new AppointmentNotification($appointment));
            }
            if ($merchant && $merchant->id !== $userID) {
                $merchant->notify(new AppointmentNotification($appointment));
            }

            $appointment = new AppointmentResource($appointment);
            return response()->json(compact('appointment'), 200);
        } catch (Exception $e) {
            Logger::info('cancelAppointment Error', [$e->getMessage() . ' - ' . $e->__toString()]);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function acceptAppointment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'appointmentID' => "required|integer",
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        try {
            $userID = $this->getAuthID($request);
            $user = User::find($userID);
            if (!$user) {
                return $this->errorResponse('User not found.', 404);
            }

            if ($user->account_type == "Client") {
                $appointment = Appointment::where(['id' => $request->appointmentID, 'customer_id' => $user->id])->first();
            } else {
                $appointment = Appointment::where(['id' => $request->appointmentID, 'merchant_id' => $user->id])->first();
            }
            if (!$appointment) {
                return $this->errorResponse('Appointment not found.', 404);
            }

            $appointment->update(['status' => 'Accepted']);


            //send email here

            try {
                $client = User::find($appointment->customer_id);
                $merchant = User::find($appointment->merchant_id);
                $store = \App\Models\Store::find($appointment->store_id);

                if ($client && $merchant && $store) {
                    \Mail::to($client->email)->send(new \App\Mail\AppointmentAccepted($client, $appointment, $merchant, $store));
                }
            } catch (Exception $emailException) {
                Logger::error('Failed to send appointment accepted email', [
                    'appointment_id' => $appointment->id,
                    'client_id' => $appointment->customer_id,
                    'error' => $emailException->getMessage()
                ]);
            }



            $appointment = new AppointmentResource($appointment);
            return response()->json(compact('appointment'), 200);
        } catch (Exception $e) {
            Logger::info('acceptAppointment', [$e->getMessage() . ' - ' . $e->__toString()]);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function completeAppointment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'appointmentID' => "required|integer",
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        try {
            $userID = $this->getAuthID($request);
            $user = User::find($userID);

            if (!$user) {
                return $this->errorResponse('User not found.', 404);
            }

            $appointment = Appointment::where('id', $request->appointmentID)->first();

            if (!$appointment || ($appointment->customer_id !== $userID && $appointment->merchant_id !== $userID)) {
                return $this->errorResponse('Appointment not found or unauthorized.', 404);
            }

            $now = now();
            $updateData = [];

            if ($appointment->customer_id === $userID) {
                $updateData['client_confirmed_at'] = $now;
                $updateData['client_confirmed_by'] = $userID;
            } elseif ($appointment->merchant_id === $userID) {
                $updateData['merchant_confirmed_at'] = $now;
                $updateData['merchant_confirmed_by'] = $userID;
            }

            $clientConfirmed = $appointment->client_confirmed_at || isset($updateData['client_confirmed_at']);
            $merchantConfirmed = $appointment->merchant_confirmed_at || isset($updateData['merchant_confirmed_at']);

            // Define the wasCompleted variable
            $wasCompleted = false;

            if ($clientConfirmed && $merchantConfirmed) {
                $updateData['status'] = 'Completed';
                $appointment->update($updateData);

                // Set to true when appointment is completed
                $wasCompleted = true;

                // Trigger wallet crediting immediately
                Artisan::call('wallets:credit', [
                    '--appointment_id' => $appointment->id
                ]);
            } else {
                $updateData['status'] = 'Delivered';
                $appointment->update($updateData);
            }

            Logger::info('Appointment confirmed', [
                'appointment_id' => $appointment->id,
                'confirmed_by' => $userID,
                'confirmed_by_name' => $user->name,
                'time' => $now
            ]);

            // Send email 
            try {
                $store = \App\Models\Store::find($appointment->store_id);

                if ($appointment->customer_id === $userID) {
                    $merchant = User::find($appointment->merchant_id);
                    if ($merchant && $store) {
                        \Mail::to($merchant->email)->send(new \App\Mail\AppointmentCompleted($merchant, $appointment, $user, $store));
                    }
                } elseif ($appointment->merchant_id === $userID) {
                    $client = User::find($appointment->customer_id);
                    if ($client && $store) {
                        \Mail::to($client->email)->send(new \App\Mail\AppointmentCompleted($client, $appointment, $user, $store));
                    }
                }
            } catch (Exception $emailException) {
                Logger::error('Failed to send appointment confirmation email', [
                    'appointment_id' => $appointment->id,
                    'confirmed_by' => $userID,
                    'error' => $emailException->getMessage()
                ]);
            }

            $appointment = new AppointmentResource($appointment);
            return response()->json(compact('appointment'), 200);
        } catch (Exception $e) {
            Logger::info('completeAppointment', [$e->getMessage() . ' - ' . $e->__toString()]);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function rescheduleAppointment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'appointmentID' => "required|integer",
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        try {
            $userID = $this->getAuthID($request);
            $user = User::find($userID);
            if (!$user) {
                return $this->errorResponse('User not found.', 404);
            }

            $appointment = Appointment::where('id', $request->appointmentID)
                ->where(function ($query) use ($user) {
                    $query->where('merchant_id', $user->id)
                        ->orWhere('customer_id', $user->id);
                })->first();

            if (!$appointment) {
                return $this->errorResponse('Appointment not found.', 404);
            }



            $timeSlot = Appointment::where([
                'merchant_id' => $appointment->merchant_id,
                'date' => $request->appointment_date,
                'time' => $request->appointment_time,
                'payment_status' => 1
            ])->whereNotIn('status', ['Cancelled', 'Denied'])->first();

            if ($timeSlot) {
                return $this->errorResponse("Time slot for $request->appointment_time has already been taken. Please, try booking again outside this time.", 400);
            }

            $appointment->update([
                "date" => $request->appointment_date,
                "time" => $request->appointment_time,
            ]);
            //to do: send email here
            $appointment = new AppointmentResource($appointment);
            return response()->json(compact('appointment'), 200);
        } catch (Exception $e) {
            Logger::info('rescheduleAppointment', [$e->getMessage() . ' - ' . $e->__toString()]);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function retrieveAppointmentsByFilters(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'filter' => "required_without:status|string|in:daily,weekly,monthly,day,week,month",
            'status' => "required_without:filter|string|in:Completed,Confirmed,No-show,Cancelled,In progress,Pending paid,Pending unpaid,All pending",
            'staff_id' => "nullable|integer|exists:users,id",
            'date' => "nullable|date_format:Y-m-d",
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        try {
            $merchantID = $this->getAuthID($request);
            $merchant = User::find($merchantID);

            if (!$merchant) {
                return $this->errorResponse('Service provider not found.', 404);
            }

            $filter = $request->filter;
            $statusFilter = $request->status;
            $staffId = $request->staff_id;
            $dateParam = $request->date;


            if ($staffId) {

                $staffRentsFromMerchant = BoothRental::whereHas('store', function ($query) use ($merchantID) {
                    $query->where('merchant_id', $merchantID);
                })->where('user_id', $staffId)->exists();

                if (!$staffRentsFromMerchant) {
                    return $this->errorResponse('This staff member does not rent a booth from your store.', 403);
                }

                $appointmentsQuery = Appointment::where(['merchant_id' => $staffId]);
            } else {
                $appointmentsQuery = Appointment::where(['merchant_id' => $merchant->id]);
            }

            // Apply date filter if provided
            if (!is_null($filter)) {
                switch ($filter) {
                    case 'daily':
                    case 'day':
                        // If date param is provided, use it, else today
                        $targetDate = $dateParam ? Carbon::parse($dateParam) : Carbon::today();
                        $appointmentsQuery = $appointmentsQuery->whereDate('date', $targetDate);
                        break;

                    case 'weekly':
                    case 'week':
                        // If date param is provided, use it as start date, else use startOfWeek
                        if ($dateParam) {
                            $startOfWeek = Carbon::parse($dateParam)->startOfDay();
                            $endOfWeek = (clone $startOfWeek)->addDays(6)->endOfDay();
                        } else {
                            $startOfWeek = Carbon::now()->startOfWeek();
                            $endOfWeek = Carbon::now()->endOfWeek();
                        }
                        $appointmentsQuery = $appointmentsQuery->whereBetween('date', [
                            $startOfWeek->format('Y-m-d'),
                            $endOfWeek->format('Y-m-d')
                        ]);
                        break;

                    case 'monthly':
                    case 'month':
                        // If date param is provided, use its month/year, else current
                        if ($dateParam) {
                            $target = Carbon::parse($dateParam);
                        } else {
                            $target = Carbon::now();
                        }
                        $appointmentsQuery = $appointmentsQuery->whereMonth('date', $target->month)
                            ->whereYear('date', $target->year);
                        break;
                }
            }

            // Apply status filter if provided
            if (!is_null($statusFilter)) {
                switch ($statusFilter) {
                    case 'Completed':
                        // Use the actual Completed status
                        $appointmentsQuery = $appointmentsQuery->where('status', 'Completed');
                        break;

                    case 'Confirmed':
                        // Where status is accepted
                        $appointmentsQuery = $appointmentsQuery->where('status', 'Accepted');
                        break;

                    case 'No-show':
                        // Appointment is accepted but date has passed by two days
                        $appointmentsQuery = $appointmentsQuery->where('status', 'Accepted')
                            ->where('date', '<', Carbon::now()->subDays(2)->format('Y-m-d'));
                        break;

                    case 'Cancelled':
                        // Use the actual Cancelled status
                        $appointmentsQuery = $appointmentsQuery->where('status', 'Cancelled');
                        break;

                    case 'In progress':
                        // Fetch appointments that are happening now
                        $appointmentsQuery = $appointmentsQuery->where('status', 'Accepted')
                            ->whereNull('merchant_confirmed_at')
                            ->whereNull('client_confirmed_at')
                            ->where('date', Carbon::now()->format('Y-m-d'));

                        $appointments = $appointmentsQuery->get();

                        // Re-filter appointments with duration calculation
                        $appointments = $appointments->filter(function ($appointment) {
                            // Calculate the total duration of services in minutes
                            $totalDuration = 0;
                            foreach ($appointment->appointmentService as $appService) {
                                if ($appService->service) {
                                    $durationString = $appService->service->duration;
                                    $durationInMinutes = $this->parseDurationToMinutes($durationString);
                                    $totalDuration += $durationInMinutes + 30; // Adding 30 min buffer
                                }
                            }

                            // Start time of appointment
                            $appointmentTime = Carbon::parse($appointment->date . ' ' . $appointment->time);

                            // End time with added duration
                            $endTime = (clone $appointmentTime)->addMinutes($totalDuration);

                            // Current time
                            $now = Carbon::now();

                            // Check if current time is between start and end time
                            return $now->greaterThanOrEqualTo($appointmentTime) && $now->lessThanOrEqualTo($endTime);
                        });
                        break;

                    case 'Pending paid':
                        // Appointments that are paid but not yet accepted
                        $appointmentsQuery = $appointmentsQuery->where('payment_status', 1)
                            ->where('status', '!=', 'Accepted')
                            ->where('status', '!=', 'Completed')
                            ->where('status', '!=', 'Cancelled');
                        break;

                    case 'Pending unpaid':
                        // Appointments that are not paid
                        $appointmentsQuery = $appointmentsQuery->where('payment_status', 0);
                        break;

                    case 'All pending':
                        // All appointments that have not been accepted yet
                        $appointmentsQuery = $appointmentsQuery->where('status', '!=', 'Accepted')
                            ->where('status', '!=', 'Completed')
                            ->where('status', '!=', 'Cancelled')
                            ->where('status', '!=', 'No-show');
                        break;
                }
            }

            $appointments = $appointmentsQuery->orderBy('date', 'ASC')->get();

            if ($statusFilter === 'In progress') {
                $appointments = $appointments->filter(function ($appointment) {
                    // Calculate the total duration of the appointment based on services
                    $totalDuration = 0;
                    foreach ($appointment->appointmentService as $appService) {
                        if ($appService->service) {
                            // Use the new duration parsing function
                            $durationString = $appService->service->duration;
                            $durationInMinutes = $this->parseDurationToMinutes($durationString);
                            $totalDuration += $durationInMinutes + 30; // Adding 30 min buffer
                        }
                    }

                    // Start time of appointment
                    $appointmentTime = Carbon::parse($appointment->date . ' ' . $appointment->time);

                    // End time with added duration
                    $endTime = (clone $appointmentTime)->addMinutes($totalDuration);

                    // Current time
                    $now = Carbon::now();

                    // Check if current time is between start and end time
                    return $now->greaterThanOrEqualTo($appointmentTime) && $now->lessThanOrEqualTo($endTime);
                });
            }

            // Group by date for weekly view only if filter is set to weekly
            if (isset($filter) && $filter === 'weekly') {
                $groupedAppointments = $appointments->groupBy(function ($appointment) {
                    return Carbon::parse($appointment->date)->format('m-D-Y');
                });

                $appointments = $groupedAppointments->map(function ($group) {
                    return AppointmentResource::collection($group);
                });
            } else {
                $appointments = AppointmentResource::collection($appointments);
            }

            return response()->json(compact('appointments'), 200);
        } catch (Exception $e) {
            Logger::info('retrieveAppointmentsByFilters', [$e->getMessage() . ' - ' . $e->__toString()]);
            return response()->json([
                "ResponseStatus" => "Unsuccessful",
                "ResponseCode" => 500,
                'Detail' => $e->getMessage(),
                "ResponseMessage" => 'Something went wrong',
            ], 500);
        }
    }


    private // Helper function to parse duration string to minutes
    function parseDurationToMinutes(
        $durationString
    ) {
        $durationString = strtolower(trim($durationString));

        // Regular expressions for various duration formats
        $patterns = [
            '/(\d+)\s?hours?/' => 60,        // 1 hour, 2 hours
            '/(\d+)\s?hrs?/' => 60,          // 1hr, 2hr, 3hrs
            '/(\d+)\s?minutes?/' => 1,       // 1 minute, 30 minutes
            '/(\d+)\s?mins?/' => 1,          // 5min, 10mins
            '/(\d+)\s?m/' => 1,              // 3m, 5m (for shorthand)
        ];

        $totalMinutes = 0;

        // Apply regex and calculate total minutes
        foreach ($patterns as $pattern => $multiplier) {
            if (preg_match_all($pattern, $durationString, $matches)) {
                foreach ($matches[1] as $match) {
                    $totalMinutes += (int) $match * $multiplier;
                }
            }
        }

        return $totalMinutes;
    }
}
