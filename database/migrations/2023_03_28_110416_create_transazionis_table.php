<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransazionisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transazionis', function (Blueprint $table) {
            $table->id();
            $table->string('codice')->nullable();
            $table->foreignId('prodotto')->nullable()->constrained('spedizionis');
            $table->string('trasportatore')->nullable();
            $table->string('data_di_carico')->nullable();
            $table->string('data_di_scarico')->nullable();
            $table->string('stato')->nullable();
            $table->string('type')->nullable();
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
        Schema::dropIfExists('transazionis');
    }
}
