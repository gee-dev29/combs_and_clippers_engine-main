<?php

namespace App\Http\Controllers\App;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Store;
use App\Models\Product;
use App\Models\Category;
use App\Models\PackageBox;
use App\Models\StoreVisit;
use Illuminate\Support\Str;
use App\Models\ProductPhoto;
use Illuminate\Http\Request;
use App\Models\ProductRating;
use App\Models\ProductRequest;
use App\Models\ProductVariant;
use App\Jobs\AddProductToSendy;
use App\Jobs\SendProductRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\StoreResource;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ProductRatingResource;


class ProductController extends Controller
{
    public function getPackageBoxes(Request $request)
    {
        /** @var \Illuminate\Database\Eloquent\Collection */
        $boxes = PackageBox::all();
        return response()->json(compact('boxes'), 200);
    }

    /**
     * Fetches a given merchant's products 
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $merchantID
     * @return \Illuminate\Http\Response
     */

    public function myProducts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search' => 'nullable',
            'min_price' => 'nullable|numeric',
            'max_price' => 'nullable|numeric',
            'product_type' => 'nullable|string',
            'category_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        $merchantID = $this->getAuthID($request);
        $merchant = User::find($merchantID);
        try {
            if ($request->filled('search')) {
                return $this->productSearch($merchantID, $request['search']);
            }
            if (!is_null($merchant)) {
                $store = $merchant->store;
                if (!is_null($store)) {
                    $store = new StoreResource($store);
                }
                $products = Product::where('merchant_id', $merchant->id)->latest('id');
                if ($request->filled('min_price')) {
                    $products->where("price", '>=', $request->min_price);
                }
                if ($request->filled('max_price')) {
                    $products->where("price", '<=', $request->max_price);
                }
                if ($request->filled('product_type')) {
                    $products->where("product_type", $request->product_type);
                }
                if ($request->filled('category_id')) {
                    $products->where("category_id", $request->category_id);
                }

                $products = $this->addMeta(ProductResource::collection($products->paginate($this->perPage)));
                return response()->json(compact('store', 'products'), 200);
            }
            return $this->errorResponse('Merchant not found', 404);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }


    public function showProduct(Request $request, $id)
    {
        try {
            $merchantID = $this->getAuthID($request);
            /** @var Product|null */
            $product = Product::where(['id' => $id, 'merchant_id' => $merchantID])->first();
            if (!is_null($product)) {
                $product = new ProductResource($product);
                return response()->json(compact('product'), 200);
            }
            return $this->errorResponse('Product not found', 404);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function getMerchantProduct(Request $request, $code)
    {
        try {
            /** @var Product|null */
            $product = Product::where('product_code', $code)->orWhere('product_slug', $code)->orWhere('id', $code)->first();
            if (!is_null($product)) {
                $merchant = $product->merchant;
                if (!is_null($merchant)) {
                    $store = $product->store;
                    //record store visit
                    $ip = $request->getClientIp();
                    $visit = StoreVisit::where(['merchant_id' => $merchant->id, 'store_id' => $store->id, 'visitor_ip' => $ip])
                        ->whereDate('created_at', Carbon::today())->first();
                    if (is_null($visit)) {
                        StoreVisit::create([
                            'merchant_id' => $merchant->id,
                            'store_id' => $store->id,
                            'visitor_ip' => $ip
                        ]);
                    }
                    $product = new ProductResource($product);
                    return response()->json(compact('product'), 200);
                }
                return $this->errorResponse('Merchant not found', 404);
            }
            return $this->errorResponse('Product not found', 404);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function addProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'productname' => 'required|string|max:255|regex:/^[a-zA-Z]+[\w\s-]*$/',
            'description' => 'required|string',
            'link' => 'string|nullable',
            'product_image' => 'required|mimes:jpeg,jpg,png,gif,bmp|max:5120',
            'optional_images.*' => 'nullable|mimes:jpeg,jpg,png,gif,bmp|max:5120',
            'price' => 'required|numeric',
            'deliveryperiod' => 'required|integer',
            'height' => 'numeric|nullable',
            'weight' => 'numeric|nullable',
            'length' => 'numeric|nullable',
            'width' => 'numeric|nullable',
            'quantity' => 'integer|required',
            'video_link' => 'string|nullable',
            'SKU' => 'string|nullable',
            'barcode' => 'integer|nullable',
            'product_type' => 'string|required|in:Physical,Digital',
            'category_id' => 'integer|required',
            'box_size_id' => 'integer|nullable',
            'store_id' => 'integer|nullable',
            'product_variants' => 'array|nullable',
            'product_variants.*.price' => 'required|numeric',
            'product_variants.*.quantity' => 'required|integer',
            'product_variants.*.inStock' => 'required|boolean',
            'product_variants.*.attributes' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        $merchantID = $this->getAuthID($request);
        try {
            $store = Store::where('merchant_id', $merchantID)->latest()->first();
            if (!is_null($store)) {
                $store_id = $store->id;
                $merchant = User::find($merchantID);
                if (!is_null($merchant)) {
                    $productSlug = generateSlug($request['productname']);
                    /** @var Product */
                    $product =  Product::create([
                        'merchant_email' => $merchant->email,
                        'productname' => $request['productname'],
                        'store_id' => $store_id,
                        'description' => $request['description'],
                        'product_slug'  => $productSlug,
                        'price'  => $request['price'],
                        'currency'  => $this->currency,
                        'deliveryperiod'  => $request['deliveryperiod'],
                        'link'    => $request['link'],
                        'image_url'  => '',
                        'other_images_url'  => '',
                        'merchant_id' => $merchant->id,
                        'height' => $request['height'],
                        'weight' => $request['weight'],
                        'width' => $request['width'],
                        'length' => $request['length'],
                        'quantity' => $request['quantity'],
                        'video_link' => $request['video_link'],
                        'SKU' => $request['SKU'],
                        'barcode' => $request['barcode'],
                        'product_type' => $request['product_type'],
                        'category_id' => $request['category_id'],
                        'box_size_id' => $request['box_size_id'],
                        'product_code' => substr(Str::uuid(), 0, 12),
                    ]);

                    if ($request->hasFile('product_image')) {
                        $imageArray = $this->imageUtil->saveImgArray($request->file('product_image'), '/products/', $product->id, $request->hasFile('optional_images') ? $request->file('optional_images') : []);
                        if (!is_null($imageArray)) {
                            $primaryImg = array_shift($imageArray);
                            $otherImgs = $imageArray;
                            $product->update(['image_url' => $primaryImg]);
                            if (!empty($otherImgs)) {
                                foreach ($otherImgs as $photo) {
                                    $productPhotos[] = ['image_link' => $photo];
                                }
                                $product->photos()->createMany($productPhotos);
                            }
                        }
                    }

                    $productVariants = $request->input('product_variants');
                    if (!is_null($productVariants) && !is_null($product)) {
                        foreach ($productVariants as $productVariant) {
                            ProductVariant::create([
                                'product_id' => $product->id,
                                'attributes' => $productVariant['attributes'],
                                'price' => $productVariant['price'],
                                'quantity' => $productVariant['quantity'],
                                'inStock' => $productVariant['inStock']
                            ]);
                        }
                    }
                    $products = Product::where('merchant_id', $merchant->id)->orderBy('id', 'DESC')->paginate($this->perPage);
                    $products = $this->addMeta(ProductResource::collection($products));
                    return response()->json(compact('products'), 201);
                }
                return $this->errorResponse('Merchant not found', 404);
            }
            return $this->errorResponse('Store not found', 404);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function updateProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'productID' => 'required|integer',
            'productname' => 'required|string|max:255|regex:/^[a-zA-Z]+[\w\s-]*$/',
            'description' => 'required|string',
            'link' => 'string|nullable',
            'product_image' => 'nullable|mimes:jpeg,jpg,png,gif,bmp|max:5120',
            'optional_images.*' => 'nullable|mimes:jpeg,jpg,png,gif,bmp|max:5120',
            'price' => 'required|numeric',
            'deliveryperiod' => 'required|integer',
            'height' => 'numeric|nullable',
            'weight' => 'numeric|nullable',
            'length' => 'numeric|nullable',
            'width' => 'numeric|nullable',
            'quantity' => 'integer|required',
            'video_link' => 'string|nullable',
            'SKU' => 'string|nullable',
            'barcode' => 'integer|nullable',
            'product_type' => 'string|required|in:Physical,Digital',
            'category_id' => 'integer|required',
            'box_size_id' => 'integer|nullable',
            'store_id' => 'integer|nullable',
            'product_variants' => 'array|nullable',
            'product_variants.*.id' => 'nullable|integer',
            'product_variants.*.price' => 'required|numeric',
            'product_variants.*.quantity' => 'required|integer',
            'product_variants.*.inStock' => 'required|boolean',
            'product_variants.*.attributes' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        $merchantID = $this->getAuthID($request);
        try {

            $merchant = User::find($merchantID);
            if (!is_null($merchant)) {
                $productID = $request['productID'];
                $product = Product::where([['id', $productID], ['merchant_id', $merchant->id]])->first();

                if (!is_null($product)) {
                    $product->update([
                        'productname' => $request->filled('productname') ? $request->input('productname') : $product->productname,
                        'store_id' => $request->filled('store_id') ? $request->input('store_id') : $product->store_id,
                        'description' => $request->filled('description') ? $request->input('description') : $product->description,
                        'link'    => $request->filled('link') ? $request->input('link') : $product->link,
                        'price' => $request->filled('price') ? $request->input('price') : $product->price,
                        'currency' => $this->currency,
                        'deliveryperiod' => $request->filled('deliveryperiod') ? $request->input('deliveryperiod') : $product->deliveryperiod,
                        'updated_at' => Carbon::now(),
                        'height' => $request->filled('height') ? $request->input('height') : $product->height,
                        'weight' => $request->filled('weight') ? $request->input('weight') : $product->weight,
                        'width' => $request->filled('width') ? $request->input('width') : $product->width,
                        'length' => $request->filled('length') ? $request->input('length') : $product->length,
                        'quantity' => $request->filled('quantity') ? $request->input('quantity') : $product->quantity,
                        'video_link' => $request->filled('video_link') ? $request->input('video_link') : $product->video_link,
                        'SKU' => $request->filled('SKU') ? $request->input('SKU') : $product->SKU,
                        'barcode' => $request->filled('barcode') ? $request->input('barcode') : $product->barcode,
                        'product_type' => $request->filled('product_type') ? $request->input('product_type') : $product->product_type,
                        'category_id' => $request->filled('category_id') ? $request->input('category_id') : $product->category_id,
                        'box_size_id' => $request->filled('box_size_id') ? $request->input('box_size_id') : $product->box_size_id
                    ]);
                }

                if ($request->hasFile('product_image')) {
                    if (!is_null($product->image_url)) {
                        $this->imageUtil->deleteImage($product->image_url);
                    }
                    $imageArray = $this->imageUtil->saveImgArray($request->file('product_image'), '/products/', $product->id, $request->hasFile('optional_images') ? $request->file('optional_images') : []);

                    if (!is_null($imageArray)) {
                        $primaryImg = array_shift($imageArray);
                        $otherImgs = $imageArray;
                        $product->update(['image_url' => $primaryImg]);
                        if (!empty($otherImgs)) {
                            foreach ($otherImgs as $photo) {
                                $productPhotos[] = ['image_link' => $photo];
                            }
                            $product->photos()->createMany($productPhotos);
                        }
                    }
                }

                $productVariants = $request->input('product_variants');
                if (!is_null($productVariants) && !is_null($product)) {
                    foreach ($productVariants as $productVariant) {
                        if (isset($productVariant['id']) && !empty($productVariant['id'])) {
                            $variant = ProductVariant::where(['id' => $productVariant['id'], 'product_id' => $product->id])->first();
                            if (!is_null($variant)) {
                                $variant->update([
                                    'attributes' => $productVariant['attributes'],
                                    'price' => $productVariant['price'],
                                    'quantity' => $productVariant['quantity'],
                                    'inStock' => $productVariant['inStock']
                                ]);
                            }
                        } else {
                            ProductVariant::create([
                                'product_id' => $product->id,
                                'attributes' => $productVariant['attributes'],
                                'price' => $productVariant['price'],
                                'quantity' => $productVariant['quantity'],
                                'inStock' => $productVariant['inStock']
                            ]);
                        }
                    }
                }
                $products = Product::where('merchant_id', $merchant->id)->orderBy('id', 'DESC')->paginate($this->perPage);
                $products = $this->addMeta(ProductResource::collection($products));
                return response()->json(compact('products'), 201);
            }
            return $this->errorResponse('Merchant not found', 404);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function removeProduct(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'productID' => 'required|integer',

        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        $merchantID = $this->getAuthID($request);
        try {
            $merchant = User::find($merchantID);
            if (!is_null($merchant)) {
                $productID = $request['productID'];
                $product = Product::where([['id', $productID], ['merchant_id', $merchant->id]])->first();

                if (!is_null($product)) {
                    $product->variants()->delete();
                    $product->photos()->delete();
                    $product->delete();
                }

                $products = Product::where('merchant_id', $merchant->id)->orderBy('id', 'DESC')->paginate($this->perPage);

                $products = $this->addMeta(ProductResource::collection($products));

                return response()->json(compact('products'), 201);
            }
            return $this->errorResponse('Merchant not found', 404);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function removeProductPhoto(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'productID' => 'required|integer',
            'image_url' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        $merchantID = $this->getAuthID($request);
        try {
            $merchant = User::find($merchantID);
            if (!is_null($merchant)) {
                $productID = $request['productID'];
                //find the product with the image_url in the Products table
                $product = Product::where(['id' => $productID, 'merchant_id' => $merchant->id, 'image_url' => $request->image_url])->first();

                if (!is_null($product)) {
                    //empty the image_url column
                    $product->update(['image_url' => '']);
                    //remove image from storage
                    $this->imageUtil->deleteImage($request->image_url);
                } else {
                    //find the image_url in the product photo table
                    $photo = ProductPhoto::where(['productID' => $productID, 'image_link' => $request->image_url])->first();
                    if (!is_null($photo)) {
                        //delete image record
                        $photo->delete();
                        //remove image from storage
                        $this->imageUtil->deleteImage($request->image_url);
                    }
                }
                $product = Product::where(['id' => $productID, 'merchant_id' => $merchant->id])->first();
                $product = new ProductResource($product);
                return response()->json(compact('product'), 201);
            }
            return $this->errorResponse('Merchant not found', 404);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function getMerchantStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'merchantCode' => 'required|string',
            'keyword' => 'nullable|string|max:100',
            'sortBy' => 'nullable|string|in:productname,price,created_at',
            'direction' => 'nullable|string|in:ASC,DESC',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        try {
            $sortBy = $request->filled('sortBy') ? $request->sortBy : 'id';
            $direction = $request->filled('direction') ? $request->direction : 'DESC';
            $merchant = User::where('merchant_code', $request['merchantCode'])->first();
            if (!is_null($merchant)) {
                // if (!$merchant->hasActiveSubscription()) {
                //     return $this->errorResponse('You cannot purchase from this store as the merchant does not have an active subscription', 403);
                // }
                $store = Store::with('owner.pickup_address', 'owner.store_address')->where('merchant_id', $merchant->id)->latest()->first();
                if (!is_null($store)) {
                    //record store visit
                    $ip = $request->getClientIp();
                    $visit = StoreVisit::where(['merchant_id' => $merchant->id, 'store_id' => $store->id, 'visitor_ip' => $ip])
                        ->whereDate('created_at', Carbon::today())->first();
                    if (is_null($visit)) {
                        StoreVisit::create([
                            'merchant_id' => $merchant->id,
                            'store_id' => $store->id,
                            'visitor_ip' => $ip
                        ]);
                    }
                    $store = new StoreResource($store);
                    $products = Product::where('merchant_id', $merchant->id)->orderBy($sortBy, $direction);
                    if ($request->filled('keyword')) {
                        $keyword = '%' . $request->keyword . '%';
                        $products->where('productname', 'like', $keyword)
                            ->orWhere('description', 'like', $keyword);
                    }
                    $products = $this->addMeta(ProductResource::collection($products->paginate($this->perPage)));
                    return response()->json(compact('store', 'products'), 200);
                }
                return $this->errorResponse('Please, finish your store setup', 400);
            }
            return $this->errorResponse('Merchant not found', 404);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    protected function productSearch($merchantID, $search)
    {
        try {
            $merchant = User::find($merchantID);
            if (!is_null($merchant)) {
                // if ($merchant->usertype != 'Buyer') {
                if (filter_var($search, FILTER_VALIDATE_INT)) {
                    //search parameter is an email
                    $id = $search;
                    $products = Product::where([['merchant_id', $merchant->id], ['id', $id]])->paginate($this->perPage);
                    $products = $this->addMeta(ProductResource::collection($products));

                    return response()->json(compact('products'), 201);
                } elseif (!filter_var($search, FILTER_VALIDATE_INT)) {
                    //search parameter is a transaction reference number  ['title','like','%'.$text.'%']
                    $search = filter_var($search, FILTER_SANITIZE_STRING);
                    //$transcode = $request['search'];
                    $products = Product::where([['merchant_id', $merchant->id], ['productname', 'like', '%' . $search . '%']])
                        ->orWhere([['merchant_id', $merchant->id], ['description', 'like', '%' . $search . '%']])
                        ->paginate($this->perPage);
                    $products = $this->addMeta(ProductResource::collection($products));

                    return response()->json(compact('products'), 201);
                }
                return $this->errorResponse('Your search parameter is invalid', 400);
            }
            return $this->errorResponse('Customer/merchant not found', 404);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function searchProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        $merchantID = $this->getAuthID($request);
        try {

            $merchant = User::find($merchantID);
            if (!is_null($merchant)) {


                // if ($merchant->usertype != 'Buyer') {
                if (filter_var($request['search'], FILTER_VALIDATE_INT)) {
                    //search parameter is an email
                    $id = $request['search'];
                    $products = Product::where([['merchant_id', $merchant->id], ['id', $id]])->paginate($this->perPage);
                    $products = $this->addMeta(ProductResource::collection($products));

                    return response()->json(compact('products'), 201);
                } elseif (!filter_var($request['search'], FILTER_VALIDATE_INT)) {
                    //search parameter is a transaction reference number  ['title','like','%'.$text.'%']
                    $search = filter_var($request['search'], FILTER_SANITIZE_STRING);
                    //$transcode = $request['search'];
                    $products = Product::where([['merchant_id', $merchant->id], ['productname', 'like', '%' . $search . '%']])
                        ->orWhere([['merchant_id', $merchant->id], ['description', 'like', '%' . $search . '%']])
                        ->paginate($this->perPage);
                    $products = $this->addMeta(ProductResource::collection($products));

                    return response()->json(compact('products'), 201);
                }
                return $this->errorResponse('Your search parameter is invalid', 400);
            }
            return $this->errorResponse('Customer/merchant not found', 404);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function requestProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_name' => 'required|string',
            'product_category' => 'required|string',
            'email' => 'required|email',
            'product_link' => 'nullable|string',
            'additional_info' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        try {
            $category = Category::where('categoryname', $request->product_category)->first();

            if (is_null($category)) {
                return $this->errorResponse('Category not found', 404);
            }

            $merchant_ids = Product::where('category_id', $category->id)->distinct('merchant_id')->get(['merchant_id'])->toArray();

            $merchants = User::whereIn('id', $merchant_ids)->where('email_verified', 1)->whereHas('store', function ($query) {
                $query->where('approved', '=', 1);
            })->get();

            if (!$merchants->count()) {
                return $this->errorResponse('Sorry! There are currently no merchants that sell this category of product', 404);
            }

            $productRequest = ProductRequest::create([
                'product_name' => $request->product_name,
                'product_category' => $request->product_category,
                'email' => $request->email,
                'product_link' => $request->product_link,
                'additional_info' => $request->additional_info,
            ]);

            SendProductRequest::dispatch($merchants, $productRequest);

            return response()->json(["ResponseStatus" => "successful", "ResponseCode" => 200, "ResponseMessage" => "Product request submitted successfully. We'll let you know once we find a match for your product.", "message" => "Product request submitted successfully. We'll let you know once we find a match for your product."], 200);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function duplicateProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'productID' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        try {
            $merchantID = $this->getAuthID($request);
            $merchant = User::find($merchantID);
            if (!is_null($merchant)) {
                $productID = $request['productID'];
                $product = Product::where([['id', $productID], ['merchant_id', $merchant->id]])->first();
                if (is_null($product)) {
                    return $this->errorResponse('Product not found', 404);
                }
                // Duplicate the product
                $duplicate = $product->replicate();
                $duplicate->productname = $product->productname . ' (Copy)';
                $duplicate->product_slug = $product->product_slug . mt_rand(1, 1000);
                $duplicate->product_code = $product->product_code . mt_rand(1, 9999);
                $duplicate->save();

                // Duplicate and link the photos to the new product
                foreach ($product->photos as $photo) {
                    $newPhoto = $photo->replicate();
                    $newPhoto->productID = $duplicate->id;
                    $newPhoto->save();
                }
                // Duplicate and link the variants to the new product
                foreach ($product->variants as $variant) {
                    $newVariant = $variant->replicate();
                    $newVariant->product_id = $duplicate->id;
                    $newVariant->save();
                }
                $products = Product::where('merchant_id', $merchant->id)->orderBy('id', 'DESC')->paginate($this->perPage);
                $products = $this->addMeta(ProductResource::collection($products));

                return response()->json(compact('products'), 201);
            }
            return $this->errorResponse('Merchant not found', 404);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function toggleProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'productID' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        try {
            $merchantID = $this->getAuthID($request);
            $merchant = User::find($merchantID);
            if (!is_null($merchant)) {
                $productID = $request['productID'];
                $product = Product::where([['id', $productID], ['merchant_id', $merchant->id]])->first();
                if (is_null($product)) {
                    return $this->errorResponse('Product not found', 404);
                }
                if ($product->active) {
                    // Disable the product
                    $product->update(['active' => 0]);
                } else {
                    $product->update(['active' => 1]);
                }
                $products = Product::where('merchant_id', $merchant->id)->orderBy('id', 'DESC')->paginate($this->perPage);
                $products = $this->addMeta(ProductResource::collection($products));

                return response()->json(compact('products'), 201);
            }
            return $this->errorResponse('Merchant not found', 404);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function rateProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'productID' => 'required|integer',
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        try {
            $user = $this->getAuthUser($request);
            $user = User::find($user->id);
            if (is_null($user)) {
                return $this->errorResponse('User not found', 404);
            }

            $product = Product::where('id', $request->productID)->first();
            if (is_null($product)) {
                return $this->errorResponse('Product not found', 404);
            }

            $rating = ProductRating::create([
                'product_id' => $product->id,
                'user_id' => $user->id,
                'rating' => $request->rating,
                'title' => $request->title,
                'description' => $request->description,
            ]);

            $rating = new ProductRatingResource($rating);

            return response()->json(compact('rating'), 201);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }

    public function getProductReviews(Request $request, $code)
    {
        try {
            $product = Product::where('product_code', $code)->orWhere('product_slug', $code)->orWhere('id', $code)->first();
            if (is_null($product)) {
                return $this->errorResponse('Product not found', 404);
            }

            $condition = ['product_id' => $product->id];
            $reviews = ProductRating::where($condition)->latest()->paginate(20);
            $reviews = $this->addMeta(ProductRatingResource::collection($reviews));
            $review_count = ProductRating::selectRaw("rating, count(id) as total_review")
                ->where($condition)
                ->groupBy('rating')
                ->get()
                ->keyBy('rating');
            $review_stats = collect([1, 2, 3, 4, 5])->mapWithKeys(function ($rating) use ($review_count) {
                return [$rating => $review_count->get($rating, (object)['rating' => $rating, 'total_review' => 0])];
            });
            return response()->json(compact('reviews', 'review_stats'), 200);
        } catch (Exception $e) {
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }
}
