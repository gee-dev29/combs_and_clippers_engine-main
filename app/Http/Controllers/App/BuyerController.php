<?php

namespace App\Http\Controllers\App;

use Exception;
use App\Models\User;
use App\Models\Address;
use App\Repositories\Util;
use Illuminate\Http\Request;
use App\Models\BillingAddress;
use App\Http\Controllers\Controller;
use App\Http\Resources\AddressResource;
use Illuminate\Support\Facades\Validator;
use Propaganistas\LaravelPhone\Rules\Phone;
use App\Http\Resources\BillingAddressResource;

class BuyerController extends Controller
{
    public function myAddresses(Request $request)
    {
        try {
            $customerID = $this->getAuthID($request);
            $customer = User::find($customerID);
            if (!is_null($customer)) {
                $addresses = Address::where('recipient', $customerID)->orderBy('id', 'DESC')->get();
                $addresses = AddressResource::collection($addresses);
                return response()->json(compact('addresses'), 201);
            }
            return $this->errorResponse('User not found', 404);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function addAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            //'name' => 'required|string',
            'street' => 'required|string',
            'city' => 'required|string',
            'state' => 'nullable|string',
            'postal_code' => 'required|string',
            //'phone' => ['required', 'numeric', (new Phone)->country('GB')],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        try {
            $customerID = $this->getAuthID($request);
            $customer = User::find($customerID);

            $country = $this->country;
            $state = $request->filled('state') ? $request->state . ', ' : '';
            $address = $request->street . ', ' . $request->city . ', ' . $state . $country;

            if (!is_null($customer)) {
                $buyerAddress = Address::create([
                    'recipient' => $customerID,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'street' => $request['street'],
                    'city' => $request['city'],
                    'state' => $request['state'],
                    'postal_code' => $request['postal_code'],
                    'country' => $this->country,
                    'phone' => $customer->phone ?? '',
                    'address' => $address,
                ]);

                if (!is_null($buyerAddress)) {
                    $address_str = $buyerAddress->address;
                    $add_info = Util::validateAddressWithGoogle($customer, $address_str);
                    if ($add_info['error'] != 1) {
                        $buyerAddress->update([
                            'longitude' => $add_info['addressDetails']['longitude'],
                            'latitude' => $add_info['addressDetails']['latitude'],
                            'postal_code' => $add_info['addressDetails']['postal_code'],
                            'zip' => $add_info['addressDetails']['postal_code'],
                            'country' => $add_info['addressDetails']['country'],
                            'formatted_address' => $add_info['addressDetails']['formatted_address'],
                            'address_code' => generateAddressCode($customer),
                            'city' => $add_info['addressDetails']['city'],
                            'city_code' => $add_info['addressDetails']['city_code'],
                            'state' => $add_info['addressDetails']['state'],
                            'state_code' => $add_info['addressDetails']['state_code'],
                            'country_code' => $add_info['addressDetails']['country_code'],
                            'street' => $add_info['addressDetails']['street'] ?? $buyerAddress->street,
                        ]);
                    }
                }

                $addresses = Address::where('recipient', $customerID)->orderBy('id', 'DESC')->get();

                $addresses = AddressResource::collection($addresses);
                return response()->json(compact('addresses'), 201);
            }
            return $this->errorResponse('User not found', 404);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }


    public function updateAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'addressId' => 'required|integer',
            // 'name' => 'required|string',
            'street' => 'required|string',
            'city' => 'required|string',
            'state' => 'nullable|string',
            'postal_code' => 'required|string',
            //'phone' => ['required', 'numeric', (new Phone)->country('GB')],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        try {
            $customerID = $this->getAuthID($request);
            $customer = User::find($customerID);
            if (is_null($customer)) {
                return $this->errorResponse('User not found', 404);
            }

            $country = $this->country;
            $state = $request->filled('state') ? $request->state . ', ' : '';
            $address = $request->street . ', ' . $request->city . ', ' . $state . $country;

            $buyerAddress =  Address::where(['recipient' => $customerID, 'id' => $request['addressId']])->first();

            if (is_null($buyerAddress)) {
                return $this->errorResponse('Address not found', 404);
            }

            $buyerAddress->update([
                'street' => $request['street'],
                'city' => $request['city'],
                'state' => $request['state'],
                'postal_code' => $request['postal_code'],
                'country' => $this->country,
                'address' => $address
            ]);

            $address_str = $buyerAddress->address;
            $add_info = Util::validateAddressWithGoogle($customer, $address_str);
            if ($add_info['error'] != 1) {
                $buyerAddress->update([
                    'longitude' => $add_info['addressDetails']['longitude'],
                    'latitude' => $add_info['addressDetails']['latitude'],
                    'postal_code' => $add_info['addressDetails']['postal_code'],
                    'zip' => $add_info['addressDetails']['postal_code'],
                    'country' => $add_info['addressDetails']['country'],
                    'formatted_address' => $add_info['addressDetails']['formatted_address'],
                    'address_code' => generateAddressCode($customer),
                    'city' => $add_info['addressDetails']['city'],
                    'city_code' => $add_info['addressDetails']['city_code'],
                    'state' => $add_info['addressDetails']['state'],
                    'state_code' => $add_info['addressDetails']['state_code'],
                    'country_code' => $add_info['addressDetails']['country_code'],
                    'street' => $add_info['addressDetails']['street'] ?? $buyerAddress->street,
                ]);
            }

            $addresses = Address::where('recipient', $customerID)->orderBy('id', 'DESC')->get();

            $addresses = AddressResource::collection($addresses);
            return response()->json(compact('addresses'), 201);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function removeAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'addressId' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        try {
            $customerID = $this->getAuthID($request);
            $customer = User::find($customerID);
            if (!is_null($customer)) {
                $address = Address::where(['recipient' => $customerID, 'id' => $request['addressId']])->first();
                if (!is_null($address)) {
                    $address->delete();
                } else {
                    return $this->errorResponse('Address not found', 404);
                }

                $addresses = Address::where('recipient', $customerID)->orderBy('id', 'DESC')->get();

                $addresses = AddressResource::collection($addresses);
                return response()->json(compact('addresses'), 201);
            }
            return $this->errorResponse('User not found', 404);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function myBillingAddresses(Request $request)
    {
        try {
            $customerID = $this->getAuthID($request);
            $customer = User::find($customerID);
            if (!is_null($customer)) {
                $billing_addresses = BillingAddress::where('recipient', $customerID)->orderBy('id', 'DESC')->get();
                $billing_addresses = BillingAddressResource::collection($billing_addresses);
                return response()->json(compact('billing_addresses'), 201);
            }
            return $this->errorResponse('User not found', 404);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function addBillingAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            //'name' => 'required|string',
            'street' => 'required|string',
            'city' => 'required|string',
            'state' => 'nullable|string',
            'postal_code' => 'required|string',
            //'phone' => ['required', 'numeric', (new Phone)->country('GB')],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        try {
            $customerID = $this->getAuthID($request);
            $customer = User::find($customerID);

            $country = $this->country;
            $state = $request->filled('state') ? $request->state . ', ' : '';
            $address = $request->street . ', ' . $request->city . ', ' . $state . $country;

            if (!is_null($customer)) {
                $buyerAddress = BillingAddress::create([
                    'recipient' => $customerID,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'street' => $request['street'],
                    'city' => $request['city'],
                    'state' => $request['state'],
                    'postal_code' => $request['postal_code'],
                    'country' => $this->country,
                    'phone' => $customer->phone ?? '',
                    'address' => $address,
                ]);

                if (!is_null($buyerAddress)) {
                    $address_str = $buyerAddress->address;
                    $add_info = Util::validateAddressWithGoogle($customer, $address_str);
                    if ($add_info['error'] != 1) {
                        $buyerAddress->update([
                            'longitude' => $add_info['addressDetails']['longitude'],
                            'latitude' => $add_info['addressDetails']['latitude'],
                            'postal_code' => $add_info['addressDetails']['postal_code'],
                            'zip' => $add_info['addressDetails']['postal_code'],
                            'country' => $add_info['addressDetails']['country'],
                            'formatted_address' => $add_info['addressDetails']['formatted_address'],
                            'address_code' => generateAddressCode($customer),
                            'city' => $add_info['addressDetails']['city'],
                            'city_code' => $add_info['addressDetails']['city_code'],
                            'state' => $add_info['addressDetails']['state'],
                            'state_code' => $add_info['addressDetails']['state_code'],
                            'country_code' => $add_info['addressDetails']['country_code'],
                            'street' => $add_info['addressDetails']['street'] ?? $buyerAddress->street,
                        ]);
                    }
                }

                $billing_addresses = BillingAddress::where('recipient', $customerID)->orderBy('id', 'DESC')->get();

                $billing_addresses = BillingAddressResource::collection($billing_addresses);
                return response()->json(compact('billing_addresses'), 201);
            }
            return $this->errorResponse('User not found', 404);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }


    public function updateBillingAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'addressId' => 'required|integer',
            // 'name' => 'required|string',
            'street' => 'required|string',
            'city' => 'required|string',
            'state' => 'nullable|string',
            'postal_code' => 'required|string',
            //'phone' => ['required', 'numeric', (new Phone)->country('GB')],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        try {
            $customerID = $this->getAuthID($request);
            $customer = User::find($customerID);
            if (is_null($customer)) {
                return $this->errorResponse('User not found', 404);
            }

            $country = $this->country;
            $state = $request->filled('state') ? $request->state . ', ' : '';
            $address = $request->street . ', ' . $request->city . ', ' . $state . $country;

            $buyerAddress =  BillingAddress::where(['recipient' => $customerID, 'id' => $request['addressId']])->first();

            if (is_null($buyerAddress)) {
                return $this->errorResponse('Address not found', 404);
            }

            $buyerAddress->update([
                'street' => $request['street'],
                'city' => $request['city'],
                'state' => $request['state'],
                'postal_code' => $request['postal_code'],
                'country' => $this->country,
                'address' => $address
            ]);

            $address_str = $buyerAddress->address;
            $add_info = Util::validateAddressWithGoogle($customer, $address_str);
            if ($add_info['error'] != 1) {
                $buyerAddress->update([
                    'longitude' => $add_info['addressDetails']['longitude'],
                    'latitude' => $add_info['addressDetails']['latitude'],
                    'postal_code' => $add_info['addressDetails']['postal_code'],
                    'zip' => $add_info['addressDetails']['postal_code'],
                    'country' => $add_info['addressDetails']['country'],
                    'formatted_address' => $add_info['addressDetails']['formatted_address'],
                    'address_code' => generateAddressCode($customer),
                    'city' => $add_info['addressDetails']['city'],
                    'city_code' => $add_info['addressDetails']['city_code'],
                    'state' => $add_info['addressDetails']['state'],
                    'state_code' => $add_info['addressDetails']['state_code'],
                    'country_code' => $add_info['addressDetails']['country_code'],
                    'street' => $add_info['addressDetails']['street'] ?? $buyerAddress->street,
                ]);
            }

            $billing_addresses = BillingAddress::where('recipient', $customerID)->orderBy('id', 'DESC')->get();

            $billing_addresses = BillingAddressResource::collection($billing_addresses);
            return response()->json(compact('billing_addresses'), 201);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function removeBillingAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'addressId' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        try {
            $customerID = $this->getAuthID($request);
            $customer = User::find($customerID);
            if (!is_null($customer)) {
                $address = BillingAddress::where(['recipient' => $customerID, 'id' => $request['addressId']])->first();
                if (!is_null($address)) {
                    $address->delete();
                } else {
                    return $this->errorResponse('Address not found', 404);
                }

                $billing_addresses = BillingAddress::where('recipient', $customerID)->orderBy('id', 'DESC')->get();

                $billing_addresses = BillingAddressResource::collection($billing_addresses);
                return response()->json(compact('billing_addresses'), 201);
            }
            return $this->errorResponse('User not found', 404);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }
}
