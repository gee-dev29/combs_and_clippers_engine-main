<?php

namespace App\Http\Controllers\App;

use Exception;
use App\Models\User;
use App\Models\Discount;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\DiscountResource;
use Illuminate\Support\Facades\Validator;

class DiscountController extends Controller
{
    public function myDiscounts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'keyword' => 'nullable|string|max:50'
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

            $discounts = Discount::where('merchant_id', $merchantID)->latest();

            if ($request->filled('keyword')) {
                $keyword = '%' . $request->keyword . '%';
                $discounts->where('discount_name', 'like', $keyword);
            }

            $discounts = $this->addMeta(DiscountResource::collection($discounts->paginate($this->perPage)));

            return response()->json(compact('discounts'), 200);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function addDiscount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'discount_name' => 'required|string|max:255',
            'discount_type' => 'required|string|in:F,P',
            'discount' => 'required|integer',
            'start_date' => 'required|date|after:yesterday',
            'end_date' => 'required|date|after:start_date',
            'product_ids' => 'required|array',
            'product_ids.*' => 'integer'
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
            $products = $request['product_ids'];

            $discount =  Discount::create([
                'merchant_id' => $merchantID,
                'discount_name' => $request['discount_name'],
                'discount_type' => $request['discount_type'],
                'discount'  => $request['discount'],
                'start_date'  => $request['start_date'],
                'end_date'  => $request['end_date']
            ]);
            
            $discount->products()->attach($products);
            $discounts = Discount::where('merchant_id', $merchantID)->latest()->paginate($this->perPage);
            $discounts = $this->addMeta(DiscountResource::collection($discounts));
            return response()->json(compact('discounts'), 201);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function updateDiscount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'discountID' => 'required|integer',
            'discount_name' => 'required|string|max:255',
            'discount_type' => 'required|string|in:F,P',
            'discount' => 'required|integer',
            'start_date' => 'required|date|after:yesterday',
            'end_date' => 'required|date|after:start_date',
            'product_ids' => 'required|array',
            'product_ids.*' => 'integer'
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

            $products = $request['product_ids'];

            $discount = Discount::where([['id', $request['discountID']], ['merchant_id', $merchantID]])->first();

            if (is_null($discount)) {
                return response()->json(["ResponseStatus" => "Unsuccessful", 'Detail' => 'Discount not found.', "ResponseMessage" => 'Discount not found.', "ResponseCode" => 404], 404);
            }
            $discount->update([
                'discount_name' => $request['discount_name'],
                'discount_type' => $request['discount_type'],
                'discount'  => $request['discount'],
                'start_date'  => $request['start_date'],
                'end_date'  => $request['end_date']
            ]);

            $discount->products()->sync($products);

            $discounts = Discount::where('merchant_id', $merchantID)->latest()->paginate($this->perPage);

            $discounts = $this->addMeta(DiscountResource::collection($discounts));

            return response()->json(compact('discounts'), 201);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }


    public function removeDiscount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'discountIDs' => 'required|array',
            'discountIDs.*' => 'integer'
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

            $discountIDs = $request['discountIDs'];
            foreach ($discountIDs as $discountID) {
                $discount = Discount::where([['id', $discountID], ['merchant_id', $merchantID]])->first();
                if (!is_null($discount)) {
                    $discount->products()->detach();
                    $discount->delete();
                }
            }

            $discounts = Discount::where('merchant_id', $merchantID)->latest()->paginate($this->perPage);

            $discounts = $this->addMeta(DiscountResource::collection($discounts));

            return response()->json(compact('discounts'), 200);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }
}
