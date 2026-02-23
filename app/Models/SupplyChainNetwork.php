<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplyChainNetwork extends Model
{
    use HasFactory;


    public function network_owner() {
        return $this->belongsTo(User::class, 'network_owner', 'id');
    }

    public function network_user() {
        return $this->belongsTo(User::class, 'network_user', 'id');
    }
}
