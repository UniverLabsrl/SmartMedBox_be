<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdottiDisponibili extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome_prodotto', 'humidity_coefficients', 'algorithm_type', 'reference_temperature_1', 'shelflife_rt_1', 'reference_temperature_2', 'shelflife_rt_2', 'reference_temperature_3', 'shelflife_rt_3', 'k1', 'k2', 'k3'
    ];
}
