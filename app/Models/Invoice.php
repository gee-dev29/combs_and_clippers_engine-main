<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
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
