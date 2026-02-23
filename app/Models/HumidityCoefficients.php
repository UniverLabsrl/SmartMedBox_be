<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HumidityCoefficients extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'from_humidity', 'to_humidity', 'coefficient'
    ];

    public function product_id()
    {
        return $this->belongsTo(ProdottiDisponibili::class, 'product_id', 'id');
    }
}
