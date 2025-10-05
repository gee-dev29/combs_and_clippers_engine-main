<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FavoriteStylistResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'merchant_code' => $this->stylist->merchant_code,
            'merchant_id' => $this->stylist->id,
            'merchant_name' => $this->stylist->name,
            'merchant_account' => $this->stylist->account_type,
            'store_category' => !is_null($this->stylist->store) ? $this->stylist->store->category : "",
            'merchant_profile_image_link' => !empty($this->stylist->profile_image_link) ? asset('storage/' . $this->stylist->profile_image_link) : "",
            'merchant_avg_rating' => $this->stylist->reviewsReceived->avg('rating') ?? 0,
        ];
    }
}


//     public function getFavoritedByUsers(Request $request)
// {
//     try {
//         $stylistId = $this->getAuthID($request); // Authenticated stylist ID

//         // Get users who have favorited this stylist
//         $favoritedBy = User::whereHas('favoriteStylists', function ($query) use ($stylistId) {
//             $query->where('id', $stylistId);
//         })->get();

//         return response()->json(['favoritedBy' => $favoritedBy]);
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