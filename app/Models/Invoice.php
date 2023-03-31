<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
    ];

    public function client() {
        return $this->belongsTo('App\Models\User');
    }

    public function items() {
        return $this->hasMany('App\Models\InvoiceItem');
    }
}
