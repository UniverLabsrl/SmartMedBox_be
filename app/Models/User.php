<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\NewAccessToken;
use Illuminate\Support\Str;
use App\Services\NadirByteCrypt;
use Illuminate\Support\Facades\Log;


class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nome', 'email', 'password', 'indirizzo', 'cap', 'citta', 'stato', 'codice', 'terms', 'role', 'trick_token'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Create a new personal access token for the user.
     *
     * @param  string  $name
     * @param  array  $abilities
     * @return \Laravel\Sanctum\NewAccessToken
     */
    public function createToken(string $name, array $abilities = ['*'])
    {
        $token = $this->tokens()->create([
            'name' => $name,
            'token' => hash('sha256', $plainTextToken = Str::random(240)),
            'abilities' => $abilities,
        ]);

        return new NewAccessToken($token, $token->getKey().'|'.$plainTextToken);
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
