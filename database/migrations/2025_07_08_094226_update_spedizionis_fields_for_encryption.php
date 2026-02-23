<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSpedizionisFieldsForEncryption extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('spedizionis', function (Blueprint $table) {
            $table->text('nome')->change();
            $table->text('indirizzo')->change();
            $table->text('cap')->change();
            $table->text('citta')->change();
            $table->text('stato')->change();

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
