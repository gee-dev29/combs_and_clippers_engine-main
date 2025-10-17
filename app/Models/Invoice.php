<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Str;

class Invoice extends Model

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid()->toString();
            }
        });
    }
{
    
    protected $fillable = ['merchant_id', 'customer_id', 'merchantAddress', 'merchantPhone', 'merchantEmail', 'merchantName', 'customerName', 'customerEmail', 'customerPhone', 'customerAddress', 'totalcost', 'vat', 'status', 'confirmed', 'items_count', 'deliveryPeriod', 'currency', 'invoiceRef', 'paymentRef', 'payment_status', 'payurl', 'invoice_type', 'startDate', 'endDate'];
    protected $table = 'invoices';

    function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class, 'invoiceID', 'id');
    }

    function invoiceFxItems()
    {
        return $this->hasMany(InvoiceFxItem::class, 'invoiceID', 'id');
    }

    public function orderFiles()
    {
        return $this->hasMany(InvoiceFile::class, 'invoice_id', 'id');
    }

    public function customer()
    {
        return $this->hasOne(User::class, 'id', 'customer_id');
    }

    public function merchant()
    {
        return $this->hasOne(User::class, 'id', 'merchant_id');
    }
    
}
