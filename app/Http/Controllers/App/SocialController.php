<?php

namespace App\Http\Controllers\App;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Store;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Validator;

class SocialController extends Controller
{
    public function redirectToProvider($redirectTo = 'onboarding')
    {
        switch ($redirectTo) {
            case 'onboarding':
                $redirect_url = cc('onboarding_social_redirect_url');
                break;
            case 'settings':
                $redirect_url = cc('settings_social_redirect_url');
                break;
            default:
                $redirect_url = cc('onboarding_social_redirect_url');
        }
        return Socialite::driver('facebook')->scopes([
            'pages_show_list', 'pages_read_engagement', 'public_profile'
        ])->redirectUrl($redirect_url)->redirect();
    }

    public function instagramRedirectToProvider($redirectTo = 'onboarding')
    {
        switch ($redirectTo) {
            case 'onboarding':
                $redirect_url = cc('onboarding_social_redirect_url');
                break;
            case 'settings':
                $redirect_url = cc('settings_social_redirect_url');
                break;
            default:
                $redirect_url = cc('onboarding_social_redirect_url');
        }
        return Socialite::driver('facebook')->scopes([
            'pages_show_list', 'pages_read_engagement', 'public_profile', 'instagram_basic'
        ])->redirectUrl($redirect_url)->redirect();
    }

    public function getSocialUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'provider' => 'required|string',
            'code' => 'required|string',
            'redirectTo' => 'nullable|string',

        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $redirectUrl = cc('onboarding_social_redirect_url');

        if ($request->filled('redirectTo')) {
            $redirectTo = $request->redirectTo;
            switch ($redirectTo) {
                case 'onboarding':
                    $redirectUrl = cc('onboarding_social_redirect_url');
                    break;
                case 'settings':
                    $redirectUrl = cc('settings_social_redirect_url');
                    break;
                default:
                    $redirectUrl = cc('onboarding_social_redirect_url');
            }
        }

        $client = new Client();
        if ($request['provider'] == 'facebook') {
            $socialUser = Socialite::driver($request['provider'])->redirectUrl($redirectUrl)->stateless()->user();

            $access_token = $socialUser->token;
            $user_id = $socialUser->id;

            $response = $client->request('GET', "https://graph.facebook.com/{$user_id}/accounts?access_token={$access_token}");
            if ($response->getStatusCode() != 200) {
                return $this->errorResponse('Unauthorized access', 401);
            }

            $content = $response->getBody()->getContents();
            $content = json_decode($content);

            $pages = $content->data;

            return response()->json(compact('pages'), 201);
        } else {
            return $this->errorResponse('Unknown provider', 400);
        }
        return $this->errorResponse('Invalid request', 400);
    }



    public function getProductsFromSocial(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page_id' => 'required|string',
            'page_access_token' => 'required|string'

        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $page_access_token = $request['page_access_token'];
        $page_id =  $request['page_id'];

        $client = new Client();

        $insta_biz_acct_res = $client->request('GET', "https://graph.facebook.com/{$page_id}?fields=instagram_business_account&access_token={$page_access_token}");
        $insta_biz_acct = $insta_biz_acct_res->getBody()->getContents();
        $insta_biz_acct = json_decode($insta_biz_acct);

        

        if (!property_exists($insta_biz_acct, 'instagram_business_account')) {
            Log::info("Fetch social product 1 - ", [$insta_biz_acct]);
            $client = new Client();
            $page_response = $client->request('GET', "https://graph.facebook.com/{$page_id}/photos?access_token={$page_access_token}");
            if ($page_response->getStatusCode() != 200) {
                return $this->errorResponse('Unauthorized access', 401);
            }

            $page_content = $page_response->getBody()->getContents();
            $page_content = json_decode($page_content);
            $page_content = $page_content->data;

            Log::info("Fetch social product decoded - ", [$page_content]);
            $items = [];

            foreach ($page_content as $item) {
                $item_id = $item->id;
                $item_response = $client->request('GET', "https://graph.facebook.com/{$item_id}?fields=id,images&access_token={$page_access_token}");

                $item_content = $item_response->getBody()->getContents();
                $item_content = json_decode($item_content);
                $items[] = $item_content->images[2]->source;
            }

            return response()->json(compact('items'), 200);
        }

        $insta_biz_acct_id = $insta_biz_acct->instagram_business_account->id;

        if (!is_null($insta_biz_acct_id)) {
            $insta_content_res = $client->request('GET', "https://graph.facebook.com/{$insta_biz_acct_id}?fields=profile_picture_url,media_count,media{media_url,caption,like_count,comments_count}&access_token={$page_access_token}");
            if ($insta_content_res->getStatusCode() != 200) {
                return $this->errorResponse('Unauthorized access', 401);
            }

            $insta_content_res = $insta_content_res->getBody()->getContents();
            $insta_content = json_decode($insta_content_res);
            $insta_content = $insta_content->media->data;

            //Log::info("Facebook response 2 - ", [$insta_content]);

            $items = [];
            foreach ($insta_content as $item) {
                if (!property_exists($item, 'media_url')) {
                    continue;
                }
                $item_id = $item->id;
                $media_url = $item->media_url;
                $caption =  property_exists($item, 'caption') ? $item->caption : '';
                $items[] = [
                    'media_url' => $media_url,
                    'caption' => $caption,
                ];
            }
            return response()->json(compact('items'), 200);
        }

        $page_response = $client->request('GET', "https://graph.facebook.com/{$page_id}/photos?access_token={$page_access_token}");

        if ($page_response->getStatusCode() != 200) {
            return $this->errorResponse('Unauthorized access', 401);
        }

        $page_content = $page_response->getBody()->getContents();
        $page_content = json_decode($page_content);
        Log::info("Normal page response - ", [$page_content]);
        $page_content = $page_content->data;

        $items = [];

        foreach ($page_content as $item) {
            Log::info("Normal page response each item- ", [$item]);
            $item_id = $item->id;
            $item_response = $client->request('GET', "https://graph.facebook.com/{$item_id}?fields=id,images&access_token={$page_access_token}");

            $item_content = $item_response->getBody()->getContents();
            $item_content = json_decode($item_content);
            $items[] = $item_content->images[2]->source;
        }

        return response()->json(compact('items'), 200);
    }

    public function addProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'productname' => 'required|string|max:255|regex:/^[a-zA-Z]+[\w\s-]*$/',
            'description' => 'required|string',
            'link' => 'string|nullable',
            'product_image'  => 'required|string',
            'optional_images.*' => 'nullable|mimes:jpeg,jpg,png,gif,bmp|max:5120',
            'price'  => 'required|numeric',
            'deliveryperiod'  => 'required|integer',
            'height' => 'numeric|nullable',
            'weight' => 'numeric|nullable',
            'length' => 'numeric|nullable',
            'width' => 'numeric|nullable',
            'quantity' => 'integer|nullable',
            'video_link' => 'string|nullable',
            'SKU' => 'string|nullable',
            'barcode' => 'integer|nullable',
            'product_type' => 'string|required',
            'category_id' => 'integer|nullable',
            'box_size_id' => 'integer|nullable',
            'store_id' => 'integer|nullable',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        $merchantID = $this->getAuthID($request);
        $merchant = User::find($merchantID);
        if (!is_null($merchant)) {
            if ($merchant->account_type != 'Buyer') {
                $store_id = $request->filled('store_id') ? $request->store_id : Store::where('merchant_id', $merchantID)->latest()->first()->id;
                $image_url = $request['product_image'];
                //$image_ext = pathinfo($image_url, PATHINFO_EXTENSION);
                $img_name = pathinfo($image_url, PATHINFO_FILENAME);
                $img_array = explode('?', $img_name);
                $image_name = $img_array[0];
                $image_path = $this->imageUtil->saveImageFromSocial($image_url, '/products/', $image_name );
                $product = Product::create([
                    'merchant_email' => $merchant->email,
                    'productname' => $request['productname'],
                    'store_id' => $store_id,
                    'description' => $request['description'],
                    'product_slug'  => generateSlug($request['productname']),
                    'price'  => $request['price'],
                    'currency'  => $this->currency,
                    'deliveryperiod'  => $request['deliveryperiod'],
                    'link'    => $request['link'],
                    'html_link'  => '',
                    'image_url'  => $image_path,
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
                    'product_code' => substr(Str::uuid(), 0, 6),
                ]);

                if ($request->hasFile('optional_images')) {
                    $imageArray = $this->imageUtil->saveImgArray($request->file('optional_images'), '/products/', $product->id, []);
                    if (!empty($imageArray)) {
                        foreach ($imageArray as $photo) {
                            $productPhotos[] = ['image_link' => $photo];
                        }
                        $product->photos()->createMany($productPhotos);
                    }
                }

                $products = Product::where('merchant_id', $merchant->id)->orderBy('id', 'DESC')->paginate($this->perPage);

                $products = $this->addMeta(ProductResource::collection($products));

                return response()->json(compact('products'), 201);
            }
            return $this->errorResponse('User is not a merchant', 401);
        }
        return $this->errorResponse('Customer/merchant not found', 404);
    }

    public function addAllProducts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'products' => 'required|array'
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        $merchantID = $this->getAuthID($request);
        $merchant = User::find($merchantID);
        if (!is_null($merchant)) {
            if ($merchant->account_type != 'Buyer') {
                $store_id = Store::where('merchant_id', $merchantID)->latest()->first()->id;
                $products = $request['products'];
                foreach ($products as $product) {
                    $image_url = $product['product_image'];
                    $img_name = pathinfo($image_url, PATHINFO_FILENAME);
                    $img_array = explode('?', $img_name);
                    $image_name = $img_array[0];
                    $image_path = $this->imageUtil->saveImageFromSocial($image_url, '/products/', $image_name );
                    $newProduct = Product::create([
                        'merchant_email' => $merchant->email,
                        'productname' => isset($product['productname']) ? $product['productname'] : '',
                        'store_id' => $store_id,
                        'description' => isset($product['description']) ? $product['description'] : '',
                        'product_slug'  => isset($product['productname']) ? generateSlug($product['productname']) : '',
                        'price'  => isset($product['price']) ? $product['price'] : '',
                        'currency'  => $this->currency,
                        'deliveryperiod'  => isset($product['deliveryperiod']) ? $product['deliveryperiod'] : '',
                        'link'    => isset($product['link']) ? $product['link'] : '',
                        'html_link'  => '',
                        'image_url'  => $image_path,
                        'other_images_url'  => '',
                        'merchant_id' => $merchant->id,
                        'height' => isset($product['height']) ? $product['height'] : '',
                        'weight' => isset($product['weight']) ? $product['weight'] : '',
                        'width' => isset($product['width']) ? $product['width'] : '',
                        'length' => isset($product['length']) ? $product['length'] : '',
                        'quantity' => isset($product['quantity']) ? $product['quantity'] : '',
                        'video_link' => isset($product['video_link']) ? $product['video_link'] : '',
                        'SKU' => isset($product['SKU']) ? $product['SKU'] : '',
                        'barcode' => isset($product['barcode']) ? $product['barcode'] : '',
                        'product_type' => isset($product['product_type']) ? $product['product_type'] : '',
                        'category_id' => isset($product['category_id']) ? $product['category_id'] : '',
                        'box_size_id' => isset($product['box_size_id']) ? $product['box_size_id'] : '',
                        'product_code' => substr(Str::uuid(), 0, 6),
                    ]);

                    if (isset($product['optional_images']) && !empty($product['optional_images'])) {
                        $imageArray = $this->imageUtil->saveImgArray($product['optional_images'], '/products/', $newProduct->id, []);
                        if (!empty($imageArray)) {
                            foreach ($imageArray as $photo) {
                                $productPhotos[] = ['image_link' => $photo];
                            }
                            $newProduct->photos()->createMany($productPhotos);
                        }
                    }
                }

                $products = Product::where('merchant_id', $merchant->id)->orderBy('id', 'DESC')->paginate($this->perPage);

                $products = $this->addMeta(ProductResource::collection($products));

                return response()->json(compact('products'), 201);
            }
            return $this->errorResponse('User is not a merchant', 401);
        }
        return $this->errorResponse('Customer/merchant not found', 404);
    }
}
