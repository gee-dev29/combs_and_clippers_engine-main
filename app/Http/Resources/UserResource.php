<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
  public function toArray($request)
  {
    return [
      'id' => $this->id,
      'name' => $this->name,
      'firstName' => $this->firstName,
      'lastName' => $this->lastName,
      'phone' => $this->phone,
      // 'phone_verified' => $this->phone ? true : false,
      // '_phone_verified' => $this->phone ? true : false,
      'phone_verified' => $this->phone_verified ?? false,
      '_phone_verified' => $this->phone_verified ?? false,
      'email' => $this->email,
      'bio' => $this->bio,
      'account_type' => $this->account_type,
      'specialization' => $this->specialization,
      'merchantCode' => $this->merchant_code,
      'AccountStatus' => ($this->accountstatus == 1) ? 'Active' : 'Inactive',
      'referral_code' => $this->referral_code,
      'email_verified' => $this->email_verified,
      'profile_image_link' => !is_null($this->profile_image_link) ? asset('storage/' . $this->profile_image_link) : "",
      // 'profile_image_link' => $this->getImageUrl($this->profile_image_link),
      'cover_image_link' => !is_null($this->cover_image_link) ? asset('storage/' . $this->cover_image_link) : "",
      'bankName' => $this->bank,
      'bankAccountNo' => $this->accountno,
      'accountName' => $this->accountname,
      'hasStore' => $this->hasStore(),
      'hasProduct' => $this->hasProduct(),
      'hasUsedFreeTrial' => $this->hasUsedFreeTrial(),
      'hasActiveSubscription' => $this->hasActiveSubscription(),
      'hasStripeAccount' => !is_null($this->stripe_account_id) ? true : false,
      'wallet' => $this->wallet,
      'store' => new StoreDetailsResource($this->store),
      'store_address' => $this->store_address,
      'pickup_address' => $this->pickup_address,
      "store_work_done_images" => (!is_null($this->store) && !is_null($this->store->workdoneImages)) ? $this->store->workdoneImages->map(function ($image) {
        return [
          "id" => $image->id,
          "image_url" => asset('storage/' . $image->image_url),
        ];
      }) : [],
      "user_work_done_images" => $this->workDoneImages->map(function ($image) {
        return [
          "id" => $image->id,
          "image_url" => asset('storage/' . $image->image_url),
        ];
      }),
      "has_add_schedule" => !empty($this->availability),
      //"has_setup_service" => !empty($this->services) && count($this->services) > 0,
      "has_setup_service" => !empty($this->owner->services) && count($this->owner->services) > 0,
      // "debug_services_count" => count($this->services ?? []),
      // "debug_services" => $this->services->pluck('id', 'name'),
      "has_setup_portfolio" => !empty($this->workDoneImages) && count($this->workDoneImages) > 0,
      "has_create_bio" => !empty($this->bio),
      "has_accept_payment" => in_array($this->payment_preferences['payment_preference'] ?? '', ['in_app', 'in app']),
      // "has_create_profile_link" => !empty($this->merchant_code),
      "has_create_profile_link" => $this->has_edited_profile_link ?? false,
      "has_setup_referal_reward" => !empty($this->rewards['referral_reward'] ?? ''),
      "has_setup_loyalty_reward" => !empty($this->rewards['loyalty_reward'] ?? ''),
      "has_schedule_protection" => !empty($this->store_address) && !empty($this->availability),
      'grow_progress' => $this->growProgress ?? 0,
      'boot_progress' => $this->bootProgress ?? 0,
      'rewards' => $this->rewards ?? [],
      'payment_preferences' => $this->payment_preferences ?? [],
      'booking_preferences' => $this->booking_preferences ?? [],
      'availability' => $this->availability ?? [],
      'booking_limits' => $this->booking_limits ?? [],
      'rentedBooths' => $this->rentedBooths,
      'joined_store' => $this->getCurrentStore(),

    ];
  }


  private function getImageUrl($imageUrl)
    {
        if (is_null($imageUrl) || empty($imageUrl)) {
            return "";
        }

        if (str_starts_with($imageUrl, 'http://') || str_starts_with($imageUrl, 'https://')) {
            return $imageUrl;
        }
        return asset('storage/' . $imageUrl);
  }

  private function getCurrentStore()
  {
    $userStore = $this->userStores()->where('current', true)->first();
    return $userStore ? new StorePreviewMiniResource($userStore->store) : null;
  }
}