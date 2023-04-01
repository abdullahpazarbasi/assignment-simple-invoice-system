<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_number',
        'invoice_number',
        'total',
    ];
}