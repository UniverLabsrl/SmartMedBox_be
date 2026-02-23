<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFatherInSpedizionisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('spedizionis', function (Blueprint $table) {
            $table->foreignId('father_id')->nullable()->constrained('spedizionis');
            $table->string('size')->nullable();
            $table->string('category')->nullable();
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
