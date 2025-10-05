<?php
namespace App\Services;

use Exception;
use App\Models\User;
use App\Models\Service;
use App\Models\Appointment;
use Illuminate\Support\Str;
use App\Models\AppointmentService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use App\Exceptions\ApiResponseException;

class AppointmentBookingService 
{
    /**
     * Function to create user 
     *
     * @param  array $userData
     * @return Appointment
     */
    public function createAppointment(array $data, array $services): Appointment
    {


        $appointment = Appointment::where([
            'merchant_id' => $data['merchant_id'],
            'date' => $data['date'],
            'time' => $data['time'],
            'payment_status' => 0
        ])->first();

        if ($appointment) {
            
            throw new ApiResponseException ("Time slot for {$data['time']} has already been taken. Please, try booking again outside this time.");
        }


        $appointment = Appointment::create($data);

        $appointmentRef = unique_random_string() . $appointment->id . '-' . time();
        $appointment->update(['appointment_ref' => $appointmentRef]);


        foreach ($services as $service) {
            $serviceData = Service::find($service["id"]);
            AppointmentService::create([
                "appointment_id" => $appointment->id,
                "service_id" => $serviceData->id,
                "quantity" => $service['quantity'],
                "price" => $service['quantity'] * $serviceData->price
            ]);
        }
        return $appointment;

    }
}