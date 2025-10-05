<?php

namespace App\Http\Controllers\App;

use Exception;
use App\Models\User;
use App\Models\Appointment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\FavoriteStylistResource;
use App\Http\Resources\FavouriteStylistResource;
use App\Http\Resources\ServiceProviderPreviewResource;
use App\Models\FavoriteStylist; // Import the FavoriteStylist model

class FavoriteStylistController extends Controller
{
    public function addFavoriteStylist(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'stylist_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        try {
            $user = $this->getAuthUser($request);

            $hasUsedStylist = Appointment::where('customer_id', $user->id)
                ->where('merchant_id', $request->stylist_id)
                ->exists();

            if (!$hasUsedStylist) {
                return response()->json(['message' => 'Cannot mark a stylist as favorite without prior use'], 400);
            }

            $exists = FavoriteStylist::where('user_id', $user->id)
                ->where('stylist_id', $request->stylist_id)
                ->exists();

            if ($exists) {
                $favoriteStylists = FavoriteStylistResource::collection($user->favoriteStylists()->get());
                return response()->json([
                    'message' => 'Stylist already in favorites',
                    'favoriteStylists' => $favoriteStylists,
                ]);
            }

            FavoriteStylist::create([
                'user_id' => $user->id,
                'stylist_id' => $request->stylist_id,
            ]);

            $favoriteStylists = FavoriteStylistResource::collection($user->favoriteStylists()->get());
            return response()->json([
                'message' => 'Stylist added to favorites',
                'favoriteStylists' => $favoriteStylists,
            ]);
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

    public function removeFavoriteStylist(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'stylist_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        try {
            $user = $this->getAuthUser($request);

            $favoriteStylist = FavoriteStylist::where('user_id', $user->id)
                ->where('stylist_id', $request->stylist_id)
                ->first();

            if ($favoriteStylist) {
                $favoriteStylist->delete();
            }

            $favoriteStylists = FavoriteStylistResource::collection($user->favoriteStylists()->get());
            return response()->json([
                'message' => $favoriteStylist ? 'Stylist removed from favorites' : 'Stylist not found in favorites',
                'favoriteStylists' => $favoriteStylists,
            ]);
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

    public function getFavoriteStylists(Request $request)
    {
        try {
            $user = $this->getAuthUser($request);

            $favoriteStylists = FavoriteStylistResource::collection($user->favoriteStylists()->get());

            return response()->json([
                'favoriteStylists' => $favoriteStylists,
            ]);
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

    public function clearFavoriteStylists(Request $request)
    {
        try {
            $user = $this->getAuthUser($request);

            // Remove all favorite stylists
            FavoriteStylist::where('user_id', $user->id)->delete();

            return response()->json(['message' => 'All favorite stylists removed']);
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

    public function suggestStylistsForFavorites(Request $request)
    {
        try {
            $user = $this->getAuthUser($request);

            // Get stylists the user has used but not favorited
            $suggestedStylists = Appointment::where('customer_id', $user->id)
                ->whereNotIn('merchant_id', FavoriteStylist::where('user_id', $user->id)
                    ->pluck('stylist_id'))
                ->with('serviceProvider')
                ->get()
                ->pluck('serviceProvider')
                ->unique();

            $suggestedStylists = ServiceProviderPreviewResource::collection($suggestedStylists);

            return response()->json(['suggestedStylists' => $suggestedStylists]);
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

    public function getPopularStylists(Request $request)
    {
        try {
            $popularStylists = User::withCount(['favoritedByUsers', 'appointments'])
                ->orderBy('favorited_by_users_count', 'desc')
                ->orderBy('appointments_count', 'desc')
                ->take(10) // Limit to top 10
                ->get();

            $popularStylists = ServiceProviderPreviewResource::collection($popularStylists);

            return response()->json(['popularStylists' => $popularStylists]);
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