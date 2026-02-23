<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpedizionisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('spedizionis', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable();
            $table->string('peso')->nullable();
            $table->foreignId('tipologia_di_prodotto')->nullable()->constrained('prodotti_disponibilis');
            $table->string('tipologia_di_imballaggio')->nullable();
            $table->string('id_sensore')->nullable();
            $table->string('descrizione_del_prodotto')->nullable();
            $table->string('data_di_raccolto')->nullable();
            $table->string('indirizzo')->nullable();
            $table->string('cap')->nullable();
            $table->string('citta')->nullable();
            $table->string('stato')->nullable();
            $table->foreignId('destinatario')->nullable()->constrained('supply_chain_networks');
            $table->string('temperatura_media')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('spedizionis');
    }
}
