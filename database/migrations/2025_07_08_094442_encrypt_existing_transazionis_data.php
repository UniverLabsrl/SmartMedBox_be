<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Services\NadirByteCrypt;

class EncryptExistingTransazionisData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('transazionis')->select('id', 'nome', 'indirizzo', 'cap', 'comune', 'country')->orderBy('id')->chunk(100, function ($transazionis) {
            $encryptionMethod = 'laravel';
            foreach ($transazionis as $transazione) {
                DB::table('transazionis')->where('id', $transazione->id)->update([
                    'nome' => $transazione->nome
                        ? NadirByteCrypt::crypt($transazione->nome,$encryptionMethod)
                        : null,
                    'indirizzo' => $transazione->indirizzo
                        ? NadirByteCrypt::crypt($transazione->indirizzo,$encryptionMethod)
                        : null,
                    'cap' => $transazione->cap
                        ? NadirByteCrypt::crypt($transazione->cap,$encryptionMethod)
                        : null,
                    'comune' => $transazione->comune
                        ? NadirByteCrypt::crypt($transazione->comune,$encryptionMethod)
                        : null,
                    'country' => $transazione->country
                        ? NadirByteCrypt::crypt($transazione->country,$encryptionMethod)
                        : null,
                ]);
            }
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
