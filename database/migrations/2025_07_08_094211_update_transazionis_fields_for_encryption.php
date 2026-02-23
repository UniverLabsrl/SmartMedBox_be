<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTransazionisFieldsForEncryption extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transazionis', function (Blueprint $table) {
            $table->text('nome')->change();
            $table->text('indirizzo')->change();
            $table->text('cap')->change();
            $table->text('comune')->change();
            $table->text('country')->change();

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
