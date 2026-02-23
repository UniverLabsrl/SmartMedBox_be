<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewVarsToShelfLifeTrainingDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shelf_life_training_data', function (Blueprint $table) {
            $table->float('cov1_eq')->after('temp_eq')->nullable();
            $table->float('cov2_eq')->after('cov1_eq')->nullable();
            $table->float('cov3_eq')->after('cov2_eq')->nullable();
            $table->float('cov4_eq')->after('cov3_eq')->nullable();
            $table->float('cov5_eq')->after('cov4_eq')->nullable();
            $table->float('light_eq')->after('cov5_eq')->nullable();
            $table->float('vibration_eq')->after('light_eq')->nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shelf_life_training_data', function (Blueprint $table) {
            //
        });
    }
}
