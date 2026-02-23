<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProdottiDisponibili;

class ProdottiDisponibiliSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ProdottiDisponibili::create([
            'nome_prodotto' => 'Arancia bio - 1',
        ]);
        ProdottiDisponibili::create([
            'nome_prodotto' => 'Arancia bio - 2',
        ]);
        ProdottiDisponibili::create([
            'nome_prodotto' => 'Arancia - 1',
        ]);
        ProdottiDisponibili::create([
            'nome_prodotto' => 'Arancia - 2',
        ]);
        ProdottiDisponibili::create([
            'nome_prodotto' => 'Limone - 1',
        ]);
        ProdottiDisponibili::create([
            'nome_prodotto' => 'Limone - 2',
        ]);
    }
}
