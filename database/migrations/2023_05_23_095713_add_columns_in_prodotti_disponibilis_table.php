<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsInProdottiDisponibilisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('prodotti_disponibilis', function (Blueprint $table) {
            $table->integer('algorithm_type')->nullable();
            $table->string('reference_temperature_1')->nullable();
            $table->string('shelflife_rt_1')->nullable();
            $table->string('reference_temperature_2')->nullable();
            $table->string('shelflife_rt_2')->nullable();
            $table->string('reference_temperature_3')->nullable();
            $table->string('shelflife_rt_3')->nullable();
            $table->string('k1')->nullable();
            $table->string('k2')->nullable();
            $table->string('k3')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('prodotti_disponibilis', function (Blueprint $table) {
            //
        });
    }
}
