<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['merchant_email', 'productname', 'description', 'product_slug', 'price', 'currency', 'deliveryperiod', 'link', 'html_link', 'image_url', 'other_images_url', 'merchant_id', 'height', 'weight', 'width', 'length', 'quantity', 'video_link', 'SKU', 'barcode', 'product_type', 'category_id', 'attributes', 'product_code', 'store_id', 'box_size_id', 'active', 'featured', 'recommended'];
    protected $table = 'products';

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'productID', 'id');
    }

    public function orderTranx()
    {
        return $this->orderItems->order_id;
    }

    public function transactions()
    {
        return $this->hasManyThrough(Transaction::class, OrderItem::class, 'productID', 'order_id', 'id', 'order_id')->select(['posting_date', 'trans_status', 'amount', 'customer_email']);
    }

    public function orderTransaction()
    {
        return $this->hasManyThrough(Transaction::class, OrderItem::class, 'productID', 'order_id', 'id', 'order_id');
    }

    public function category()
    {
        return $this->hasOne(Category::class, 'id', 'category_id')->select(['id', 'categoryname']);
    }

    public function photos()
    {
        return $this->hasMany(ProductPhoto::class, 'productID');
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function box()
    {
        return $this->belongsTo(PackageBox::class, 'box_size_id', 'id')->select(['id', 'box_size_id', 'name', 'description_image_url', 'height', 'width', 'length', 'max_weight']);
    }

    public function merchant()
    {
        return $this->belongsTo(User::class, 'merchant_id', 'id');
    }

    public function discounts()
    {
        return $this->belongsToMany(Discount::class, 'discount_products', 'product_id', 'discount_id')->withTimestamps();
    }

    public function activeDiscounts()
    {
        $today = now()->format('Y-m-d');

        return $this->discounts
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today);
    }

    public function hasActiveDiscount()
    {
        return $this->activeDiscounts()->exists();
    }

    public function calculateDiscount()
    {
        $totalDiscount = 0;

        foreach ($this->activeDiscounts() as $discount) {
            if ($discount->discount_type === 'F') {
                $totalDiscount += $discount->discount;
            } elseif ($discount->discount_type === 'P') {
                $totalDiscount += ($discount->discount / 100) * $this->price;
            }
        }

        return $totalDiscount;
    }

    public function getDiscountedPrice()
    {
        return $this->price - $this->calculateDiscount();
    }

    public function getDiscountedPercent()
    {
        return ($this->calculateDiscount() / $this->price) * 100;
    }

    function variants()
    {
        return $this->hasMany(ProductVariant::class, 'product_id', 'id');
    }

    public function sales()
    {
        return $this->hasManyThrough(Order::class, OrderItem::class, 'productID', 'id', 'id', 'order_id')->where('payment_status', 1);
    }

    function ratings()
    {
        return $this->hasMany(ProductRating::class, 'product_id', 'id');
    }
}
