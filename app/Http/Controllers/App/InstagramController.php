<?php

namespace App\Http\Controllers\App;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Validator;


class InstagramController extends Controller
{
    protected $api_urls = array(
        'user'                        => 'https://api.instagram.com/v1/users/%s/?access_token=%s',
        'user_feed'                    => 'https://api.instagram.com/v1/users/self/feed?access_token=%s&count=%s&femax_id=%s&min_id=%s',
        'user_recent'                => 'https://api.instagram.com/v1/users/%s/media/recent/?access_token=%s&count=%s&max_id=%s&min_id=%s&max_timestamp=%s&min_timestamp=%s',
        'user_search'                => 'https://api.instagram.com/v1/users/search?q=%s&access_token=%s',
        'user_follows'                => 'https://api.instagram.com/v1/users/%s/follows?access_token=%s',
        'user_followed_by'            => 'https://api.instagram.com/v1/users/%s/followed-by?access_token=%s',
        'user_requested_by'            => 'https://api.instagram.com/v1/users/self/requested-by?access_token=%s',
        'user_relationship'            => 'https://api.instagram.com/v1/users/%s/relationship?access_token=%s',
        'modify_user_relationship'    => 'https://api.instagram.com/v1/users/%s/relationship?access_token=%s',
        'media'                        => 'https://api.instagram.com/v1/media/{user_id}?access_token=%s',
        'media'                        => 'https://api.instagram.com/v1/media/%s?access_token=%s',
        'media_search'                => 'https://api.instagram.com/v1/media/search?lat=%s&lng=%s&max_timestamp=%s&min_timestamp=%s&distance=%s&access_token=%s',
        'media_popular'                => 'https://api.instagram.com/v1/media/popular?access_token=%s',
        'media_comments'            => 'https://api.instagram.com/v1/media/%s/comments?access_token=%s',
        'post_media_comment'        => 'https://api.instagram.com/v1/media/%s/comments?access_token=%s',
        'delete_media_comment'        => 'https://api.instagram.com/v1/media/%s/comments?comment_id=%s&access_token=%s',
        'likes'                        => 'https://api.instagram.com/v1/media/%s/likes?access_token=%s',
        'post_like'                    => 'https://api.instagram.com/v1/media/%s/likes?access_token=%s',
        'remove_like'                => 'https://api.instagram.com/v1/media/%s/likes?access_token=%s',
        'tags'                        => 'https://api.instagram.com/v1/tags/%s?access_token=%s',
        'tags_recent'                => 'https://api.instagram.com/v1/tags/%s/media/recent?max_id=%s&min_id=%s&access_token=%s',
        'tags_search'                => 'https://api.instagram.com/v1/tags/search?q=%s&access_token=%s',
        'locations'                    => 'https://api.instagram.com/v1/locations/%d?access_token=%s',
        'locations_recent'            => 'https://api.instagram.com/v1/locations/%d/media/recent/?max_id=%s&min_id=%s&max_timestamp=%s&min_timestamp=%s&access_token=%s',
        'locations_search'            => 'https://api.instagram.com/v1/locations/search?lat=%s&lng=%s&foursquare_id=%s&distance=%s&access_token=%s',
        'geographies'                 => 'https://api.instagram.com/v1/geographies/%s/media/recent?client_id=%s',
        'post_media'            => 'https://graph.facebook.com/user/media',
        'post_media_publish'    => 'https://graph.facebook.com/user/media_publish',

    );

    public function redirectToInstagram(Request $request)
    {
        $appId = config('services.instagram.client_id');
        $redirectUri = urlencode(config('services.instagram.redirect'));
        return redirect()->to("https://api.instagram.com/oauth/authorize?client_id={$appId}&redirect_uri={$redirectUri}&scope=user_profile,user_media&response_type=code");
    }

    public function handleInstagramCallback(Request $request)
    {
        $code = $request->code;
        //error=access_denied&error_reason=user_denied&error_description=The+user+denied+your+request
        if (empty($code)) return redirect()->route('home')->with('error', 'Failed to login with Instagram.');

        $appId = config('services.instagram.client_id');
        $secret = config('services.instagram.client_secret');
        $redirectUri = config('services.instagram.redirect');

        $client = new Client();

        // Get access token
        $response = $client->request('POST', 'https://api.instagram.com/oauth/access_token', [
            'form_params' => [
                'app_id' => $appId,
                'app_secret' => $secret,
                'grant_type' => 'authorization_code',
                'redirect_uri' => $redirectUri,
                'code' => $code,
            ]
        ]);

        if ($response->getStatusCode() != 200) {
            return redirect()->route('home')->with('error', 'Unauthorized login to Instagram.');
        }

        $content = $response->getBody()->getContents();
        $content = json_decode($content);

        $accessToken = $content->access_token;
        $userId = $content->user_id;

        //save access_token for later use

        //call get user products

        // Get user info
        $response = $client->request('GET', "https://graph.instagram.com/me?fields=id,username,account_type&access_token={$accessToken}");

        $content = $response->getBody()->getContents();
        $oAuth = json_decode($content);

        // Get instagram user name 
        $username = $oAuth->username;

        // do your code here
    }


    public function fetchUserWithCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'merchantID' => 'required|integer',
            'provider' => 'required|string',
            'code' => 'required|string'

        ]);


        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $client = new Client();

        if ($request['provider'] == 'instagram') {
            $appId = config('services.instagram.client_id');
            $secret = config('services.instagram.client_secret');
            $redirectUri = config('services.instagram.redirect');
            $code = $request['code'];
            // Get access token
            $response = $client->request('POST', 'https://api.instagram.com/oauth/access_token', [
                'form_params' => [
                    'app_id' => $appId,
                    'app_secret' => $secret,
                    'grant_type' => 'authorization_code',
                    'redirect_uri' => $redirectUri,
                    'code' => $code,
                ]
            ]);

            if ($response->getStatusCode() != 200) {
                return $this->errorResponse('Unauthorized access to instagram account', 401);
            }

            $content = $response->getBody()->getContents();
            $content = json_decode($content);

            $accessToken = $content->access_token;
            $userId = $content->user_id;

            // Get user info
            //$response = $client->request('GET', "https://graph.instagram.com/me?fields=id,username,account_type&access_token={$accessToken}");
            //https://graph.facebook.com/v7.0/me/accounts?access_token={access-token}
            $response = $client->request('GET', "https://graph.facebook.com/{$userId}/accounts?access_token={$accessToken}");
            if ($response->getStatusCode() != 200) {
                return $this->errorResponse('Unauthorized access', 401);
            }

            $content = $response->getBody()->getContents();
            $content = json_decode($content);

            $pages = $content->data;

            return response()->json(compact('pages'), 201);
        } elseif ($request['provider'] == 'facebook') {

            $req = $request->all();
            return response()->json(compact('req'), 201);

            $redirectUrl = cc('frontend_base_url') . 'products/instagram';
            //$redirectUrl = 'http://localhost:3001/products/instagram';
            $socialUser = Socialite::driver($request['provider'])->redirectUrl($redirectUrl)->stateless()->user();
            //https://developers.facebook.com/docs/facebook-login/manually-build-a-login-flow/
            //https://developers.facebook.com/docs/graph-api/reference/page/

            $access_token = $socialUser->token;
            $user_id = $socialUser->id;

            $response = $client->request('GET', "https://graph.facebook.com/{$user_id}/accounts?access_token={$access_token}");
            //128062534482289
            //https://graph.facebook.com/v7.0/me/accounts?access_token={access-token}
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


    public function fetchPageProducts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'merchantID' => 'required|integer',
            //'provider' => 'required|string',
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

        if ($insta_biz_acct_res->getStatusCode() != 200) {
            $client = new Client();
            $page_response = $client->request('GET', "https://graph.facebook.com/{$page_id}/photos?access_token={$page_access_token}");

            if ($page_response->getStatusCode() != 200) {
                return $this->errorResponse('Unauthorized access', 401);
            }

            $page_content = $page_response->getBody()->getContents();
            $page_content = json_decode($page_content);
            $page_content = $page_content->data;

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

        $insta_biz_acct = $insta_biz_acct_res->getBody()->getContents();
        $insta_biz_acct = json_decode($insta_biz_acct);
        $insta_biz_acct_id = $insta_biz_acct->instagram_business_account->id;

        if (!is_null($insta_biz_acct_id)) {
            $insta_content_res = $client->request('GET', "https://graph.facebook.com/{$insta_biz_acct_id}?fields=profile_picture_url,media_count,media{media_url,caption,like_count,comments_count}&access_token={$page_access_token}");

            if ($insta_content_res->getStatusCode() != 200) {
                return $this->errorResponse('Unauthorized access', 401);
            }

            $insta_content_res = $insta_content_res->getBody()->getContents();
            $insta_content = json_decode($insta_content_res);
            $insta_content = $insta_content->media->data;

            $items = [];
            foreach ($insta_content as $item) {
                $item_id = $item->id;
                $items[] = $item->media_url;
            }


            return response()->json(compact('items'), 200);
        }

        $page_response = $client->request('GET', "https://graph.facebook.com/{$page_id}/photos?access_token={$page_access_token}");

        if ($page_response->getStatusCode() != 200) {
            return $this->errorResponse('Unauthorized access', 401);
        }

        $page_content = $page_response->getBody()->getContents();
        $page_content = json_decode($page_content);
        $page_content = $page_content->data;

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
}
