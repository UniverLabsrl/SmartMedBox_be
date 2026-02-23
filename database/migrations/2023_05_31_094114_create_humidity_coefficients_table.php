<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHumidityCoefficientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('humidity_coefficients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained('prodotti_disponibilis');
            $table->string('from_humidity')->nullable();
            $table->string('to_humidity')->nullable();
            $table->string('coefficient')->nullable();
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
        Schema::dropIfExists('humidity_coefficients');
    }
}
