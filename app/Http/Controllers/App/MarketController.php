<?php

namespace App\Http\Controllers\App;

use Exception;
use App\Models\City;
use App\Models\User;
use App\Models\Store;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\StoreCategory;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\StoreResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ServiceProviderResource;
use Illuminate\Support\Facades\Validator;


class MarketController extends Controller
{
    public function getAllStores(Request $request)
    {
        try {
            $stores = User::selectRaw('users.id, users.name, users.phone, users.email, users.profile_image_link, users.merchant_code as merchant_code')
                //->has('activeSubscriptions')
                ->whereHas('store', function ($query) {
                    $query->where('approved', '=', 1);
                })
                ->with('store')
                ->where('account_type', 'Merchant')
                ->paginate($this->perPage);
            return response()->json(compact('stores'), 200);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function getFeaturedStores(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'location' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        try {
            $stores = Store::where('featured', true)
                ->where('approved', true)
                ->inRandomOrder();

            if ($request->filled('location')) {
                $location = "%" . $request->location . "%";
                $stores->whereHas('storeAddress', function ($query) use ($location) {
                    $query->where('city', 'like', $location)
                        ->orWhere('state', 'like', $location)
                        ->orWhere('address', 'like', $location);
                });
            }

            $stores = $stores->paginate($this->perPage);
            $stores = $this->addMeta(StoreResource::collection($stores));

            return response()->json(compact('stores'), 200);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function categorizeStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'location' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        try {
            $categories = StoreCategory::all()->pluck('categoryname', 'id');

            $stores = [];

            foreach ($categories as $id => $category) {
                $storesInCategory = User::selectRaw('users.id, users.name, users.phone, users.email, users.profile_image_link, users.merchant_code as merchant_code, s.*')
                    //->has('activeSubscriptions')
                    ->join('stores AS s', 's.merchant_id', '=', 'users.id')
                    ->where('users.account_type', 'Merchant')
                    ->where('s.approved', '=', 1)
                    ->where('s.store_category', $id)
                    ->latest('s.id')
                    ->take(4); // Limit to 4 stores per category

                if ($request->filled('location')) {
                    $location = "%" . $request->location . "%";
                    $storesInCategory->whereHas('pickup_address', function ($query) use ($location) {
                        $query->where('city', 'like', $location)
                            ->orWhere('state', 'like', $location)
                            ->orWhere('address', 'like', $location);
                    });
                }

                $storesInCategory = $storesInCategory->get();

                if ($storesInCategory->count()) {
                    $stores[$category] = $storesInCategory;
                }
            }
            return response()->json(compact('stores'), 200);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function getTopSellingStores(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'location' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        try {
            // $stores = User::selectRaw('users.id, users.name, users.phone, users.email, users.profile_image_link, users.merchant_code as merchant_code')
            //     ->has('activeSubscriptions')
            //     ->whereHas('store', function ($query) {
            //         $query->where('approved', '=', 1);
            //     })
            //     ->has('sales')
            //     ->with('store')
            //     ->withCount('sales')
            //     ->where('account_type', 'Merchant')
            //     ->orderBy('sales_count', 'DESC')
            //     ->paginate($this->perPage);
            // return response()->json(compact('stores'), 200);

            $stores = User::selectRaw('users.id, users.name, users.phone, users.email, users.profile_image_link, users.merchant_code as merchant_code')
                //->has('activeSubscriptions')
                ->whereHas('store', function ($query) {
                    $query->where('top_selling', '=', 1)
                        ->where('approved', '=', 1);
                })
                ->with('store')
                ->where('account_type', 'Merchant')
                ->inRandomOrder('123456');

            if ($request->filled('location')) {
                $location = "%" . $request->location . "%";
                $stores->whereHas('pickup_address', function ($query) use ($location) {
                    $query->where('city', 'like', $location)
                        ->orWhere('state', 'like', $location)
                        ->orWhere('address', 'like', $location);
                });
            }

            $stores = $stores->paginate($this->perPage);
            return response()->json(compact('stores'), 200);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }


    public function getStoresByCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_name' => 'required|string',
            'location' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        try {
            $category = StoreCategory::where('categoryname', $request->category_name)->value('id');
            $stores = User::selectRaw('users.id, users.name, users.phone, users.email, users.profile_image_link, users.merchant_code as merchant_code')
                //->has('activeSubscriptions')
                ->whereHas('store', function ($query) use ($category) {
                    $query->where('store_category', '=', $category)
                        ->where('approved', '=', 1);
                })
                ->with('store')
                ->where('account_type', 'Merchant');

            if ($request->filled('location')) {
                $location = "%" . $request->location . "%";
                $stores->whereHas('pickup_address', function ($query) use ($location) {
                    $query->where('city', 'like', $location)
                        ->orWhere('state', 'like', $location)
                        ->orWhere('address', 'like', $location);
                });
            }

            $stores = $stores->paginate($this->perPage);

            return response()->json(compact('stores'), 200);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function getStoresByLocation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'location' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        try {
            $location = "%" . $request->location . "%";
            $stores = User::selectRaw('users.id, users.name, users.phone, users.email, users.profile_image_link, users.merchant_code as merchant_code')
                //->has('activeSubscriptions')
                ->whereHas('store', function ($query) {
                    $query->where('approved', '=', 1);
                })
                ->whereHas('pickup_address', function ($query) use ($location) {
                    $query->where('city', 'like', $location)
                        ->orWhere('state', 'like', $location)
                        ->orWhere('address', 'like', $location);
                })
                // ->orWhereHas('store_address', function ($query) use ($location) {
                //     $query->where('city', 'like', $location)
                //         ->orWhere('state', 'like', $location);
                // })
                ->with('store')
                ->where('account_type', 'Merchant')
                ->paginate($this->perPage);
            return response()->json(compact('stores'), 200);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    // public function search(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'keyword' => 'required|string|max:100',
    //         'location' => 'nullable|string'
    //     ]);

    //     if ($validator->fails()) {
    //         return $this->validationError($validator);
    //     }

    //     try {
    //         $keyword = '%' . $request->keyword . '%';
    //         $stores = User::selectRaw('users.id, users.name, users.phone, users.email, users.profile_image_link, users.merchant_code as merchant_code')
    //             //->has('activeSubscriptions')
    //             ->whereHas('store', function ($query) use ($keyword) {
    //                 $query->where('approved', '=', 1);
    //                 $query->where(function ($query) use ($keyword) {
    //                     $query->where('store_name', 'like', $keyword);
    //                 });
    //             })
    //             ->with('store')
    //             //->with('store.products')
    //             //serviceTypes.serviceType
    //             ->orWhereHas('store.serviceTypes.serviceType', function ($query) use ($keyword) {
    //                 $query->where('name', 'like', $keyword);
    //             })
    //             ->orWhereHas('products', function ($query) use ($keyword) {
    //                 $query->where('productname', 'like', $keyword)
    //                     ->orWhere('description', 'like', $keyword);
    //             })
    //             ->withCount([
    //                 'products' => function ($query) use ($keyword) {
    //                     $query->where('productname', 'like', $keyword)
    //                         ->orWhere('description', 'like', $keyword);
    //                 }
    //             ])
    //             ->where('account_type', 'Merchant')
    //             ->orderBy('products_count', 'DESC');

    //         if ($request->filled('location')) {
    //             $location = "%" . $request->location . "%";
    //             $stores->whereHas('pickup_address', function ($query) use ($location) {
    //                 $query->where('city', 'like', $location)
    //                     ->orWhere('state', 'like', $location)
    //                     ->orWhere('address', 'like', $location);
    //             });
    //         }

    //         $stores = $stores->paginate($this->perPage);

    //         return response()->json(compact('stores'), 200);
    //     } catch (Exception $e) {
    //         $this->reportExceptionOnBugsnag($e);
    //         return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
    //     }
    // }

    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'keyword' => 'required|string|max:100',
            'location' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric'
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        try {
            $keyword = '%' . $request->keyword . '%';

            $users = User::select('users.id', 'users.name', 'users.phone', 'users.email', 'users.profile_image_link', 'users.merchant_code')
                ->where('account_type', '!=', 'Client')
                ->where(function ($query) use ($keyword) {
                    $query->where('name', 'like', $keyword)
                        ->orWhereHas('store', function ($query) use ($keyword) {
                            $query->where('approved', 1)
                                ->where('store_name', 'like', $keyword);
                        })
                        ->orWhereHas('store.serviceTypes.serviceType', function ($query) use ($keyword) {
                            $query->where('name', 'like', $keyword);
                        })
                        ->orWhereHas('products', function ($query) use ($keyword) {
                            $query->where('productname', 'like', $keyword)
                                ->orWhere('description', 'like', $keyword);
                        })
                        ->orWhereHas('services', function ($query) use ($keyword) {
                            $query->where('name', 'like', $keyword)
                                ->orWhere('description', 'like', $keyword);
                        });
                })
                ->withCount([
                    'products' => function ($query) use ($keyword) {
                        $query->where('productname', 'like', $keyword)
                            ->orWhere('description', 'like', $keyword);
                    },
                    'services' => function ($query) use ($keyword) {
                        $query->where('name', 'like', $keyword)
                            ->orWhere('description', 'like', $keyword);
                    }
                ])
                ->orderByDesc('products_count')
                ->orderByDesc('services_count');

            if ($request->filled('location')) {
                $location = '%' . $request->location . '%';

                // Apply location filter only to stores
                $users->whereHas('store.storeAddress', function ($query) use ($location) {
                    $query->where('city', 'like', $location)
                        ->orWhere('state', 'like', $location)
                        ->orWhere('address', 'like', $location);
                });
            }


            if ($request->filled('latitude') xor $request->filled('longitude')) {
                return response()->json([
                    "ResponseStatus" => "Unsuccessful",
                    "ResponseCode" => 422,
                    "ResponseMessage" => "Both latitude and longitude must be provided together."
                ], 422);
            }

            if ($request->filled('latitude') && $request->filled('longitude')) {
                $latitude = $request->latitude;
                $longitude = $request->longitude;
                $radius = 10; // in km

                $stores = Store::whereHas('storeAddress', function ($q) use ($latitude, $longitude) {
                    $q->selectRaw("
            (6371 * acos(
                cos(radians(?)) * cos(radians(latitude)) *
                cos(radians(longitude) - radians(?)) +
                sin(radians(?)) * sin(radians(latitude))
            )) AS distance", [$latitude, $longitude, $latitude])
                        ->having('distance', '<', 10);
                })->pluck('id');

                if ($stores->isNotEmpty()) {
                    $users->where(function ($q) use ($stores) {
                        $q->whereHas('store', function ($q) use ($stores) {
                            $q->whereIn('id', $stores);
                        })->orWhereHas('userStores', function ($q) use ($stores) {
                            $q->whereIn('store_id', $stores);
                        });
                    });
                } else {
                    // No nearby stores found; force empty result
                    $users->whereRaw('1 = 0');
                }
            }

            $users = $users->paginate($this->perPage);
            $users = $this->addMeta(ServiceProviderResource::collection($users));

            return response()->json(compact('users'), 200);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json([
                "ResponseStatus" => "Unsuccessful",
                "ResponseCode" => 500,
                "Detail" => $e->getMessage(),
                "ResponseMessage" => "Something went wrong"
            ], 500);
        }
    }



    public function getCities(Request $request)
    {
        try {
            $cities = City::all();
            return response()->json(compact('cities'), 200);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function getFeaturedProducts(Request $request)
    {
        try {
            $products = Product::whereHas('store')
                ->with('store')
                ->withCount('sales')
                ->where('active', 1)
                ->where('featured', 1)
                ->inRandomOrder();


            $products = $products->paginate($this->perPage);
            $products = $this->addMeta(ProductResource::collection($products));

            return response()->json(compact('products'), 200);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function getNewProducts(Request $request)
    {
        try {
            $products = Product::whereHas('store')
                ->with('store')
                ->withCount('sales')
                ->where('active', 1)
                ->latest();
            $products = $products->paginate($this->perPage);
            $products = $this->addMeta(ProductResource::collection($products));
            return response()->json(compact('products'), 200);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function getPopularProducts(Request $request)
    {
        try {
            $products = Product::whereHas('store')
                ->with('store')
                ->where('active', 1)
                ->has('sales')
                ->withCount('sales')
                ->orderBy('sales_count', 'DESC');
            $products = $products->paginate($this->perPage);
            $products = $this->addMeta(ProductResource::collection($products));
            return response()->json(compact('products'), 200);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function getRecommendedProducts(Request $request)
    {
        try {
            $products = Product::whereHas('store')
                ->with('store')
                ->withCount('sales')
                ->where('active', 1)
                ->where('recommended', 1)
                ->inRandomOrder();


            $products = $products->paginate($this->perPage);
            $products = $this->addMeta(ProductResource::collection($products));

            return response()->json(compact('products'), 200);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function getProductsByCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        try {
            $products = Product::whereHas('store')
                ->with('store')
                ->withCount('sales')
                ->where('active', 1)
                ->where('category_id', $request->category_id)
                ->inRandomOrder();


            $products = $products->paginate($this->perPage);
            $products = $this->addMeta(ProductResource::collection($products));

            return response()->json(compact('products'), 200);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), "ResponseMessage" => 'Something went wrong'], 500);
        }
    }
}