<?php

namespace App\Http\Controllers\App;

use App\Exceptions\ApiResponseException;
use App\Http\Controllers\Controller;
use App\Http\Requests\AppointmentRequest;
use App\Models\Service;
use App\Models\User;
use App\Services\AppointmentBookingService;
use App\Services\UserService;
use App\Http\Resources\AppointmentResource;
use App\Jobs\LinkMerchantVirtualAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as Logger;

class AppointmentController extends Controller
{
    //

    public function store(AppointmentRequest $request, UserService $userService, AppointmentBookingService $appointmentService)
    {
        $validatedData = $request->validated();

        try {

            $appointment = DB::transaction(function () use ($validatedData, $userService, $appointmentService, $request) {
                $totalCost = 0;
                $userData = [

                    'phone_number' => $validatedData['phone'],
                    'email' => $validatedData['email'],
                    'name' => $validatedData['name'],
                ];

                /** @var User|null */
                $user = User::where('email', $validatedData['email'])
                    ->orWhere('phone', $validatedData['phone'])
                    ->first();

                if (!$user) {
                    $user = $userService->createUser($userData);
                }

                $userId = $this->getAuthID($request);
                /** @var User|null */
                $merchantId = User::find($userId)->id;
                $clientId = $user->id;

                foreach ($request->services as $service) {
                    /** @var Service|null */
                    $serviceData = Service::find($service['id']);
                    $totalCost += $serviceData->price * $service['quantity'];
                }

                $appointmentData = [

                    'payment_status' => 0,
                    'customer_id' => $clientId,
                    'merchant_id' => $merchantId,
                    'store_id' => $validatedData['store_id'],
                    'date' => $validatedData['appointment_date'],
                    'time' => $validatedData['appointment_time'],
                    'phone_number' => $validatedData['phone'],
                    'total_amount' => $totalCost,
                    'currency' => 'NGN',
                ];
                $paymentProvider = 'wallet';
                $appointment =  $appointmentService->createAppointment($appointmentData, $request->services);
                LinkMerchantVirtualAccount::dispatch($appointment->id, 'appointment');
                $paymentRef = "WALL" . unique_random_string();
                $appointment->update(['payment_ref' => $paymentRef, 'payment_gateway' => $paymentProvider]);
                $appointment = new AppointmentResource($appointment);
                return $appointment;
            });

            return response()->json(compact('appointment'), 201);
        } catch (ApiResponseException $ape) {
            Logger::info('bookAppointment Error', [$ape->getMessage() . ' - ' . $ape->__toString()]);

            return $this->errorResponse($ape->getMessage(), 400);
        } catch (\Throwable $e) {
            Logger::info('bookAppointment Error', [$e->getMessage() . ' - ' . $e->__toString()]);

            return response()->json(['ResponseStatus' => 'Unsuccessful', 'ResponseCode' => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', 'ResponseMessage' => 'Something went wrong'], 500);
        }

    }
}
