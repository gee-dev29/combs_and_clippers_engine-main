<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $fillable = ['invoiceID', 'productID', 'productname', 'image_url', 'description', 'quantity', 'unitPrice', 'totalCost'];
    protected $table = 'invoice_items';
    
}
