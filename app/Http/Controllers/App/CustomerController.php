<?php

namespace App\Http\Controllers\App;

use App\Models\User;
use App\Models\Order;
use App\Models\Appointment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResource;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\CustomerDetailsResource;


class CustomerController extends Controller
{
    /**
     * Fetches the customers their transactions of a given merchant 
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $merchantID
     * @return \Illuminate\Http\Response
     */
    public function myCustomers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'query' => 'nullable'
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        $merchantID = $this->getAuthID($request);
        $merchant = User::find($merchantID);

        if (is_null($merchant)) {
            return $this->errorResponse('Merchant not found', 404);
        }

        $condition = ['merchant_id' => $merchant->id, 'payment_status' => 1];

        if ($request->filled('query')) {
            return $this->customerSearch($condition, $request['query']);
        }

        $buyer_ids = Appointment::where($condition)->distinct('customer_id')->get(['customer_id'])->toArray();
        $customers = User::whereIn('id', $buyer_ids)->paginate($this->perPage);
        $customers = $this->addMeta(CustomerResource::collection($customers));
        return response()->json(compact('customers'), 200);
    }

    protected function customerSearch($condition, $query)
    {
        $buyer_ids = Appointment::where($condition)->distinct('customer_id')->get(['customer_id'])->toArray();
        if (filter_var($query, FILTER_VALIDATE_EMAIL)) {
            //query parameter is an email
            $email = $query;
            $customers = User::whereIn('id', $buyer_ids)->where('email', $email)->paginate($this->perPage);
            $customers = $this->addMeta(CustomerResource::collection($customers));
            return response()->json(compact('customers'), 200);
        } elseif (!filter_var($query, FILTER_VALIDATE_INT)) {
            //query parameter is a string ?
            $name = filter_var($query, FILTER_SANITIZE_STRING);
            $keyword = '%' . $name . '%';
            $customers = User::whereIn('id', $buyer_ids)
                ->where(function ($query) use ($keyword) {
                    $query->where('name', 'like', $keyword)
                        ->orWhere('email', 'like', $keyword)
                        ->orWhere('phone', 'like', $keyword);
                })
                ->paginate($this->perPage);
            $customers = $this->addMeta(CustomerResource::collection($customers));
            return response()->json(compact('customers'), 200);
        }
    }

    public function getCustomerDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customerID' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        $merchantID = $this->getAuthID($request);
        $merchant = User::find($merchantID);

        if (is_null($merchant)) {
            return $this->errorResponse('Merchant not found', 404);
        }

        $condition = ['merchant_id' => $merchant->id, 'customer_id' => $request->customerID, 'payment_status' => 1];

        $buyer_ids = Appointment::where($condition)->distinct('customer_id')->get(['customer_id'])->toArray();
        $customer = User::whereIn('id', $buyer_ids)->first();
        $customer = new CustomerDetailsResource($customer);
        return response()->json(compact('customer'), 200);
    }
}
