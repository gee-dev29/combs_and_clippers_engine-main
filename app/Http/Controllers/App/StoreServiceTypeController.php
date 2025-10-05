<?php

namespace App\Http\Controllers\App;

use Exception;
use App\Models\Store;
use App\Models\ServiceType;
use Illuminate\Http\Request;
use App\Models\StoreServiceType;
use App\Models\Service;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\StoreServicesResource;
use App\Http\Resources\ServiceResource;


class StoreServiceTypeController extends Controller
{


    public function getServiceTypes(Request $request)
    {
        try {
            $service_types = ServiceType::all();
            return response()->json([
                'ResponseStatus' => "Successful",
                'ResponseCode' => 200,
                'data' => $service_types,
                'message' => 'Service types retrieved successfully.'
            ], 200);

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


    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id' => 'required|exists:stores,id'
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }


        try {


            $store_id = $request->store_id;


            $storeServices = StoreServiceType::where('store_id', $store_id)->get();


            $storeServices = StoreServicesResource::collection($storeServices);

            // $services = Service::where("store_id", $store_id)->get();
            // $services = ServiceResource::collection($services);

            return response()->json([
                'ResponseStatus' => "Successful",
                'ResponseCode' => 200,
                'data' => $storeServices,
                //'services' => $services,
                'message' => 'Store service types retrieved successfully.'
            ], 200);

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



    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_type_name' => 'nullable|string|required_without:service_type_id',
            'service_type_id' => 'nullable|integer|exists:service_types,id|required_without:service_type_name',
            'store_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        try {
            $user_id = $this->getAuthID($request);
            $store = Store::find($request->store_id);
            if ($user_id != $store->merchant_id) {
                return response()->json([
                    'message' => 'Unauthorized access.'
                ], 401);
            }

            if ($request->has('service_type_name') && $request->service_type_name) {

                $serviceTypeName = strtolower($request->service_type_name);


                $serviceType = ServiceType::firstOrCreate(
                    ['name' => $serviceTypeName],
                    ['name' => $serviceTypeName]
                );

                $service_type_id = $serviceType->id;
            } else {
                $service_type_id = $request->service_type_id;
            }


            $storeService = StoreServiceType::create([
                'store_id' => $request->store_id,
                'service_type_id' => $service_type_id,
            ]);

            $storeService = new StoreServicesResource($storeService);

            return response()->json([
                'ResponseStatus' => "Successful",
                'ResponseCode' => 201,
                'data' => $storeService,
                'message' => 'Store service type created successfully.'
            ], 201);

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



    // public function update(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'service_type' => 'required|string',
    //         'service_type_id' => 'required|integer',
    //     ]);

    //     if ($validator->fails()) {
    //         return $this->validationError($validator);
    //     }

    //     try {
    //         $user_id = $this->getAuthID($request);
    //         $storeService = StoreServiceType::find($request->service_type_id);

    //         if (!$storeService) {
    //             return response()->json([
    //                 'message' => 'Service type not found.'
    //             ], 404);
    //         }

    //         $store = Store::find($storeService->store_id);

    //         if ($user_id != $store->merchant_id) {
    //             return response()->json([
    //                 'message' => 'Unauthorized access.'
    //             ], 401);
    //         }

    //         $storeService->update([
    //             'service_type' => $request->service_type,
    //         ]);

    //         $storeService = new StoreServicesResource($storeService);

    //         return response()->json([
    //             'ResponseStatus' => "Successful",
    //             'ResponseCode' => 200,
    //             'data' => $storeService,
    //             'message' => 'Store service type updated successfully.'
    //         ], 200);

    //     } catch (Exception $e) {
    //         return response()->json([
    //             "ResponseStatus" => "Unsuccessful",
    //             "ResponseCode" => 500,
    //             'Detail' => $e->getMessage(),
    //             'message' => 'Something went wrong',
    //             "ResponseMessage" => 'Something went wrong'
    //         ], 500);
    //     }
    // }

    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_type_id' => 'required|integer',
            'store_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        try {
            $user_id = $this->getAuthID($request);

            $storeService = StoreServiceType::where('store_id', $request->store_id)
                ->where('service_type_id', $request->service_type_id)
                ->first();

            if (!$storeService) {
                return response()->json([
                    'message' => 'Service type not found for the given store.'
                ], 404);
            }

            $store = Store::find($storeService->store_id);

            if ($user_id != $store->merchant_id) {
                return response()->json([
                    'message' => 'Unauthorized access.'
                ], 401);
            }

            // Delete the StoreServiceType
            $storeService->delete();

            return response()->json([
                'ResponseStatus' => "Successful",
                'ResponseCode' => 200,
                'message' => 'Store service type deleted successfully.'
            ], 200);

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