<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransazioniHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transazioni_histories', function (Blueprint $table) {
            $table->id();
            $table->integer('assigned_to')->unsigned();
            $table->integer('assigned_by')->unsigned();
            $table->integer('assigned_transaction')->unsigned();
            $table->integer('assigned_transaction_to')->unsigned();
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
        Schema::dropIfExists('transazioni_histories');
    }
}
