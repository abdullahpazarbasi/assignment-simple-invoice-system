<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'subtotal_amount',
        'subtotal_currency_code',
    ];

    public function invoice() {
        return $this->belongsTo('App\Models\Invoice');
    }
}
