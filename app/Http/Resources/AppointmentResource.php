<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            "appointment_id" => $this->id,
            "store_name" => $this->store->store_name,
            "store_address" => $this->store->storeAddress->address,
            "services_ordered" => $this->appointmentService->map(function ($service_ordered) {
                return [
                    "appointment_service_id" => $service_ordered->id,
                    "service_id" => $service_ordered->service->id,
                    "service_name" => $service_ordered->service->name,
                    "service_duration" => $service_ordered->service->duration,
                    "service_description" => $service_ordered->service->description,
                    "price" => $service_ordered->price,
                    "quantity" => $service_ordered->quantity,
                    'image_url' => $service_ordered->image_url,
                    'other_images' => $service_ordered->photos,
                ];
            }),
            "appointment_date" => $this->date,
            "appointment_time" => $this->time,
            "service_provider_id" => $this->merchant_id,
            "service_provider_name" => $this->serviceProvider->name,
            "service_provider_type" => $this->serviceProvider->account_type,
            "customer_name" => $this->customer->name,
            // "guest_name" => $this->booking_type === 'public' ? $this->customer->name : $this->guest_name,
            "customer_image" => $this->customer->profile_image_link,
            'currency' => $this->currency,
            "total_services_amount" => $this->total_amount - $this->tip,
            "tip" => $this->tip,
            "total_amount" => $this->total_amount,
            "processing_fee" => $this->processing_fee,
            "status" => $this->status,
            'payment_gateway' => $this->payment_gateway,
            'cancel_reason' => $this->cancel_reason,
            'cancelled_by' => $this->cancelled_by,
            'cancelled_at' => $this->cancelled_at,
            'cancelled_by_user' => $this->when($this->cancelled_by, function() {
                $cancelledByUser = \App\Models\User::find($this->cancelled_by);
                return $cancelledByUser ? [
                    'name' => $cancelledByUser->name,
                    'account_type' => $cancelledByUser->account_type
                ] : null;
            }),
            'payment_ref' => $this->payment_ref,
            'payment_url' => $this->payment_url,
            'virtualAccount' => $this->virtualAccount,
            'appointment_ref' => $this->appointment_ref,
            'payment_status' => $this->payment_status,
            'disbursement_status' => $this->disbursement_status,
            'reason_for_cancelation' => $this->reason_for_cancelation,
            'merchant_confirmed_at' => $this->merchant_confirmed_at,
            'client_confirmed_at' => $this->client_confirmed_at,
            'merchant_confirmed_by' => $this->merchant_confirmed_by,
            'client_confirmed_by' => $this->client_confirmed_by,
            'bookingDate' => Carbon::parse($this->created_at)->format('M d, Y'),
        ];
    }
}