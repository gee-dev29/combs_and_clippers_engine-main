<?php

namespace App\Http\Controllers\App;

use Exception;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\VoucherResource;
use Illuminate\Support\Facades\Validator;

class VoucherController extends Controller
{
    public function issueVoucher(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'discount' => 'required|numeric|min:0',
            'expiry_date' => 'required|date|after:today',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        try {
            $issuerId = $this->getAuthID($request); // Authenticated stylist ID

            $stylist = User::where('id', $issuerId)
                ->withCount(['bookings', 'services'])
                ->whereHas('services') // Ensure the stylist has services
                ->first();

            if (is_null($stylist)) {
                return $this->errorResponse('Unauthorized: You must have services to issue vouchers', 403);
            }

            $voucher = Voucher::create([
                'stylist_id' => $issuerId,
                'user_id' => $request->user_id,
                'code' => strtoupper(uniqid('VCHR-')),
                'discount' => $request->discount,
                'expiry_date' => $request->expiry_date,
            ]);

            return response()->json(['message' => 'Voucher issued successfully', 'voucher' => new VoucherResource($voucher)]);
        } catch (Exception $e) {
            return response()->json([
                "ResponseStatus" => "Unsuccessful",
                "ResponseCode" => 500,
                'Detail' => $e->getMessage(),
                'message' => 'Something went wrong',
                "ResponseMessage" => 'Something went wrong'
            ], 500);
        }
    }

    public function getUserVouchers(Request $request)
    {
        try {
            $user = $this->getAuthUser($request); // Authenticated user

            $vouchers = Voucher::where('user_id', $user->id)
                ->where('expiry_date', '>=', now()) // Only non-expired vouchers
                ->get();

            return response()->json(['vouchers' => VoucherResource::collection($vouchers)]);
        } catch (Exception $e) {
            return response()->json([
                "ResponseStatus" => "Unsuccessful",
                "ResponseCode" => 500,
                'Detail' => $e->getMessage(),
                'message' => 'Something went wrong',
                "ResponseMessage" => 'Something went wrong'
            ], 500);
        }
    }

    public function redeemVoucher(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|exists:vouchers,code',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        try {
            $user = $this->getAuthUser($request); // Authenticated user

            $voucher = Voucher::where('code', $request->code)
                ->where('user_id', $user->id) // Ensure the voucher belongs to the user
                ->where('expiry_date', '>=', now()) // Ensure the voucher is not expired
                ->first();

            if (!$voucher) {
                return response()->json(['message' => 'Voucher is invalid, expired, or does not belong to you'], 403);
            }

            if ($voucher->is_used) {
                return response()->json(['message' => 'Voucher has already been redeemed'], 400);
            }

            $voucher->update(['is_used' => true]); // Mark the voucher as used

            return response()->json(['message' => 'Voucher redeemed successfully', 'voucher' => new VoucherResource($voucher)]);
        } catch (Exception $e) {
            return response()->json([
                "ResponseStatus" => "Unsuccessful",
                "ResponseCode" => 500,
                'Detail' => $e->getMessage(),
                'message' => 'Something went wrong',
                "ResponseMessage" => 'Something went wrong'
            ], 500);
        }
    }

    public function validateVoucher(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|exists:vouchers,code',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        try {
            $user = $this->getAuthUser($request); // Authenticated user

            $voucher = Voucher::where('code', $request->code)
                ->where('user_id', $user->id) // Ensure the voucher belongs to the user
                ->where('expiry_date', '>=', now()) // Ensure the voucher is not expired
                ->first();

            if (!$voucher) {
                return response()->json(['message' => 'Voucher is invalid, expired, or does not belong to you'], 403);
            }

            if ($voucher->is_used) {
                return response()->json(['message' => 'Voucher has already been redeemed'], 400);
            }

            return response()->json(['message' => 'Voucher is valid', 'voucher' => new VoucherResource($voucher)]);
        } catch (Exception $e) {
            return response()->json([
                "ResponseStatus" => "Unsuccessful",
                "ResponseCode" => 500,
                'Detail' => $e->getMessage(),
                'message' => 'Something went wrong',
                "ResponseMessage" => 'Something went wrong'
            ], 500);
        }
    }
}