<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\NadirByteCrypt;

class Spedizionis extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome', 'batch_number', 'units', 'bottle_capacity', 'qty', 'tipologia_di_prodotto', 'tipologia_di_imballaggio', 'id_sensore', 'descrizione_del_prodotto', 'data_di_raccolto', 'indirizzo', 'cap', 'citta', 'stato', 'destinatario', 'temperatura_media', 'status', 'transactions', 'product', 'from_crop', 'humidities', 'equivalent_humidity', 'equivalent_temperature', 'shelflives', 'average_shelflife', 'humidity_coefficient', 'adjusted_shelflife', 'residual_shelflife', 'father_id', 'size', 'category', 'expired_at'
    ];

    public function tipologia_di_prodotto()
    {
        return $this->belongsTo(ProdottiDisponibili::class, 'tipologia_di_prodotto', 'id');
    }

    public function destinatario()
    {
        return $this->belongsTo(SupplyChainNetwork::class, 'destinatario', 'id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
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

    public function setCittaAttribute($value)
    {
        $this->attributes['citta'] = NadirByteCrypt::crypt($value);
    }

    public function getCittaAttribute($value)
    {
        return NadirByteCrypt::decrypt($value);
    }

    public function setStatoAttribute($value)
    {
        $this->attributes['stato'] = NadirByteCrypt::crypt($value);
    }

    public function getStatoAttribute($value)
    {
        return NadirByteCrypt::decrypt($value);
    }
}
