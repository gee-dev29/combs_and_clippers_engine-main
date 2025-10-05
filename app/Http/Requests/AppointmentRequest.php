<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Tymon\JWTAuth\Facades\JWTAuth;

class AppointmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return in_array(JWTAuth::parseToken()->authenticate()->account_type, ['Stylist', 'Owner']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'appointment_time' => 'required|date_format:H:i',
            'services' => 'required|array|min:1',
            'services.*.id' => 'required|exists:services,id',
            'services.*.quantity' => 'required|integer|min:1',
            'name' => 'required|max:30',
            'store_id' => 'required|exists:stores,id',
            'phone' => 'required|numeric',
            'email' => 'required|email',
            'custom_tip' => 'nullable|numeric|min:0',
        ];
    }
}
