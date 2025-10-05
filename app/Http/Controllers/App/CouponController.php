<?php

namespace App\Http\Controllers\App;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\User;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\CouponResource;
use Exception;

class CouponController extends Controller
{
    public function getCoupons(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'coupon_code' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        $merchantID = $this->getAuthID($request);
        try {
            if (!is_null($request['coupon_code'])) {
                $code = $request['coupon_code'];
                $coupons = Coupon::where([['merchant_id', $merchantID], ['code', 'like', '%' . $code . '%']])->paginate($this->perPage);
                $coupons = $this->addMeta(CouponResource::collection($coupons));

                return response()->json(compact('coupons'), 201);
            }

            $merchant = User::find($merchantID);
            if (!is_null($merchant)) {

                $coupons = Coupon::where('merchant_id', $merchant->id)->orderBy('id', 'DESC')->paginate($this->perPage);

                $coupons = $this->addMeta(CouponResource::collection($coupons));

                return response()->json(compact('coupons'), 201);
            }
            return $this->errorResponse('Merchant not found', 404);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function addCoupon(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'coupon_code' => 'required|string|max:255',
            'coupon_discount' => 'required|integer',
            'end_date' => 'date|required',
            'product_ids' => 'required|array',
            'limit' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        if (count(Coupon::where('code', $request->coupon_code)->get()) > 0) {
            return $this->errorResponse('Coupon already exists for this coupon code', 400);
        }

        $coupon_details = array();
        foreach ($request->product_ids as $product_id) {
            $data['product_id'] = $product_id;
            array_push($coupon_details, $data);
        }
        $merchantID = $this->getAuthID($request);
        try {
            $coupon =  Coupon::create([
                'merchant_id' => $merchantID,
                'code' => $request['coupon_code'],
                'details' => json_encode($coupon_details),
                'discount'  => $request['coupon_discount'],
                'limit'  => $request['limit'],
                'end_date'  => $request['end_date']
            ]);

            $coupons = Coupon::where('merchant_id', $merchantID)->orderBy('id', 'DESC')->paginate($this->perPage);

            $coupons = $this->addMeta(CouponResource::collection($coupons));

            return response()->json(compact('coupons'), 201);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }



    public function updateCoupon(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'couponID' => 'required|integer',
            'coupon_code' => 'required|string|max:255',
            'coupon_discount' => 'required|integer',
            'end_date' => 'date|required',
            'product_ids' => 'required|array',
            'limit' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        $coupon_details = array();
        foreach ($request->product_ids as $product_id) {
            $data['product_id'] = $product_id;
            array_push($coupon_details, $data);
        }
        $merchantID = $this->getAuthID($request);
        try {

            $merchant = User::find($merchantID);
            if (!is_null($merchant)) {
                $couponID = $request['couponID'];
                if (count(Coupon::where('id', '!=', $couponID)->where('code', $request->coupon_code)->where('merchant_id', $merchant->id)->get()) > 0) {
                    return $this->errorResponse('Coupon already exist for this coupon code', 400);
                }
                $coupon = Coupon::where([['id', $couponID], ['merchant_id', $merchant->id]])->first();
                if (!is_null($coupon)) {
                    $coupon->update([
                        'code' => $request->filled('coupon_code') ? $request->input('coupon_code') : $coupon->code,
                        'details' => json_encode($coupon_details) ?? $coupon->details,
                        'discount'    => $request->filled('coupon_discount') ? $request->input('coupon_discount') : $coupon->discount,
                        'limit' => $request->filled('limit') ? $request->input('limit') : $coupon->limit,
                        'end_date' => $request->filled('end_date') ? $request->input('end_date') : $coupon->end_date,
                    ]);
                }

                $coupons = Coupon::where('merchant_id', $merchant->id)->orderBy('id', 'DESC')->paginate($this->perPage);

                $coupons = $this->addMeta(CouponResource::collection($coupons));

                return response()->json(compact('coupons'), 201);
            }
            return $this->errorResponse('Merchant not found', 404);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function removeCoupon(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'couponID' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        $merchantID = $this->getAuthID($request);
        try {
            $merchant = User::find($merchantID);
            if (!is_null($merchant)) {
                $couponID = $request['couponID'];
                $coupon = Coupon::where([['id', $couponID], ['merchant_id', $merchant->id]])->first();

                if (!is_null($coupon)) {
                    $coupon->delete();
                }

                $coupons = Coupon::where('merchant_id', $merchant->id)->orderBy('id', 'DESC')->paginate($this->perPage);

                $coupons = $this->addMeta(CouponResource::collection($coupons));

                return response()->json(compact('coupons'), 201);
            }
            return $this->errorResponse('Merchant not found', 404);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function applyCoupon(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'coupon_code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        $coupon = Coupon::where('code', $request->coupon_code)->first();
        $customerID = $this->getAuthID($request);
        $customer = User::where([['id', $customerID], ['account_type', 'Buyer']])->first();
        if (!is_null($customer)) {
            if (
                $coupon != null
                && $coupon->status == 1
                && strtotime(date('d-m-Y')) <= $coupon->end_date
                && CouponUsage::where('user_id', $customerID)->where('coupon_id', $coupon->id)->first() == null
                && CouponUsage::where(['coupon_id' => $coupon->id])->count() <= $coupon->limit
            ) {
                $couponDetails = json_decode($coupon->details);
                $couponDiscount = 0;
                $cart = Cart::where([['buyer_id', $customerID], ['status', 0]])->orderBy('id', 'DESC')->first();
                $cartItems = CartItem::where('cart_id', $cart->id)->get();
                foreach ($cartItems as $key => $cartItem) {
                    foreach ($couponDetails as $key => $couponDetail) {
                        if ($couponDetail->product_id == $cartItem->productID) {
                            $couponDiscount += $cartItem->price * $coupon->discount / 100;
                        }
                    }
                }
                if ($this->isCouponAlreadyApplied($customerID, $coupon->id)) {
                    return $this->errorResponse('The coupon is already applied. Please try another coupon', 400);
                } else {
                    $this->storeCouponUsage($customerID, $coupon->id);
                    return response()->json([
                        'success' => true,
                        'discount' => (float) $couponDiscount,
                        'message' => 'Coupon code applied successfully'
                    ]);
                }
            } else {
                return $this->errorResponse('The coupon is invalid, expired or inactive', 400);
            }
        } else {
            return $this->errorResponse('User not found or user not a buyer', 404);
        }
    }

    protected function isCouponAlreadyApplied($userId, $couponId)
    {
        return CouponUsage::where(['user_id' => $userId, 'coupon_id' => $couponId])->count() > 0;
    }
    protected function storeCouponUsage($userId, $couponId)
    {
        CouponUsage::create(['user_id' => $userId, 'coupon_id' => $couponId]);
    }

    public function activateCoupon(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'couponID' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        $merchantID = $this->getAuthID($request);
        try {
            $merchant = User::find($merchantID);
            if (!is_null($merchant)) {
                $couponID = $request['couponID'];
                $coupon = Coupon::where([['id', $couponID], ['merchant_id', $merchant->id]])->first();
                if (!is_null($coupon)) {
                    $coupon->update([
                        'status' => 1
                    ]);
                }
                $coupons = Coupon::where('merchant_id', $merchant->id)->orderBy('id', 'DESC')->paginate($this->perPage);

                $coupons = $this->addMeta(CouponResource::collection($coupons));

                return response()->json(compact('coupons'), 201);
            }
            return $this->errorResponse('Merchant not found', 404);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }
    public function deactivateCoupon(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'couponID' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        $merchantID = $this->getAuthID($request);
        try {
            $merchant = User::find($merchantID);
            if (!is_null($merchant)) {
                $couponID = $request['couponID'];
                $coupon = Coupon::where([['id', $couponID], ['merchant_id', $merchant->id]])->first();
                if (!is_null($coupon)) {
                    $coupon->update([
                        'status' => 0
                    ]);
                }
                $coupons = Coupon::where('merchant_id', $merchant->id)->orderBy('id', 'DESC')->paginate($this->perPage);

                $coupons = $this->addMeta(CouponResource::collection($coupons));

                return response()->json(compact('coupons'), 201);
            }
            return $this->errorResponse('Merchant not found', 404);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }
}
