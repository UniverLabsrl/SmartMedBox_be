<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShelfLifeTrainingData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('shelf_life_training_data', function (Blueprint $table) {
            $table->id();
            $table->integer('shipment_id');
            $table->float('temp_eq');
            $table->float('hum_eq');
            $table->integer('product_type');
            $table->integer('formula_type');
            $table->float('formula_result');
            $table->float('actual_result');
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
        //
    }
}
