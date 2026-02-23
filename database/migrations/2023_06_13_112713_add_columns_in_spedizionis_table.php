<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsInSpedizionisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('spedizionis', function (Blueprint $table) {
            $table->string('batch_number')->nullable();
            $table->string('units')->nullable();
            $table->string('bottle_capacity')->nullable();
            $table->renameColumn('peso', 'qty');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('spedizionis', function (Blueprint $table) {
            //
        });
    }
}
