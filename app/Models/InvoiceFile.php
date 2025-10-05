<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceFile extends Model
{
	protected $fillable = ['invoice_id', 'link', 'docType'];
    protected $table = 'invoice_files';
	
}
