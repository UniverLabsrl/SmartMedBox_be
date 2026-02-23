<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use InvalidArgumentException;

class NadirByteCrypt
{
    /**
     * Cifra una stringa secondo l'algoritmo scelto
     */
    public static function crypt(string $value, string $method = 'laravel'): string
    {
        return match ($method) {
            'laravel' => Crypt::encryptString($value),
            // altri metodi qui
            default => throw new InvalidArgumentException("Metodo di criptazione '$method' non supportato"),
        };
    }

    /**
     * Decripta una stringa secondo l'algoritmo scelto
     */
    public static function decrypt(string $value, string $method = 'laravel'): string
    {
        return match ($method) {
            'laravel' => Crypt::decryptString($value),
            // altri metodi qui
            default => throw new InvalidArgumentException("Metodo di decriptazione '$method' non supportato"),
        };
    }
}
