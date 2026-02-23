<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\NadirByteCrypt;


class Transazionis extends Model
{
    use HasFactory;

    protected $fillable = [
        'codice', 'prodotto', 'trasportatore', 'data_di_carico', 'data_di_scarico', 'stato', 'type'
    ];


    public function trasportatore()
    {
        return $this->belongsTo(User::class, 'trasportatore', 'id');
    }

    public function setNomeAttribute($value)
    {
        $this->attributes['nome'] = NadirByteCrypt::crypt($value);
    }

    public function getNomeAttribute($value)
    {
        return NadirByteCrypt::decrypt($value);
    }
    public function setIndirizzoAttribute($value)
    {
        $this->attributes['indirizzo'] = NadirByteCrypt::crypt($value);
    }

    public function getIndirizzoAttribute($value)
    {
        return NadirByteCrypt::decrypt($value);
    }

    public function setCapAttribute($value)
    {
        $this->attributes['cap'] = NadirByteCrypt::crypt($value);
    }

    public function getCapAttribute($value)
    {
        return NadirByteCrypt::decrypt($value);
    }

    public function setComuneAttribute($value)
    {
        $this->attributes['comune'] = NadirByteCrypt::crypt($value);
    }

    public function getComuneAttribute($value)
    {
        return NadirByteCrypt::decrypt($value);
    }

    public function setCountryAttribute($value)
    {
        $this->attributes['country'] = NadirByteCrypt::crypt($value);
    }

    public function getCountryAttribute($value)
    {
        return NadirByteCrypt::decrypt($value);
    }
}
