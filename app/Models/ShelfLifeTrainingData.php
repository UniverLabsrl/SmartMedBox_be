<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShelfLifeTrainingData extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id', 'product_type', 'temp_eq', 'cov1_eq', 'cov1_eq', 'cov1_eq', 'cov2_eq', 'cov3_eq', 'cov4_eq', 'cov5_eq', 'light_eq', 'vibration_eq', 'hum_eq', 'formula_type', 'formula_result', 'actual_result'
    ];

    
}
