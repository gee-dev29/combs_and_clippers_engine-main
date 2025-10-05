<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class ProductResource extends JsonResource
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
            'productName' => $this->productname,
            'productCode' => $this->product_code,
            'productSlug' => $this->product_slug,
            'productDescription' => $this->description,
            'merchantID' => $this->merchant->id,
            'merchantCode' => $this->merchant->merchant_code,
            'merchantEmail' => $this->merchant_email,
            'merchantPhone' => $this->merchant->phone,
            'link' => $this->link,
            'image_url' => $this->image_url,
            'other_images_url' => $this->photos,
            'price' => $this->price,
            'currency' => $this->currency,
            'deliveryDate' => $this->deliveryperiod,
            'height' => $this->height,
            'weight' => $this->weight,
            'length' => $this->length,
            'width' => $this->width,
            'quantity' => $this->quantity,
            'video_link' => $this->video_link,
            'SKU' => $this->SKU,
            'barcode' => $this->barcode,
            'product_type' => $this->product_type,
            'category' => $this->category,
            'package_box' => $this->box,
            'variants' => $this->variants,
            'active' => $this->active,
            'featured' => $this->featured,
            'sales_count' => $this->sales_count ?? 0,
            'star_rating' => $this->ratings->avg('rating') ?? 0,
            'created_at' => Carbon::parse($this->created_at)->format('M d, Y'),
            'store' => new StoreDetailsResource($this->whenLoaded('store'))
        ];
    }
}
