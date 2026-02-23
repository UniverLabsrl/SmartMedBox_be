<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Services\NadirByteCrypt;

class EncryptExistingSpedizionisData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('spedizionis')->select('id', 'nome', 'indirizzo', 'cap', 'citta', 'stato')->orderBy('id')->chunk(100, function ($spedizioniss) {
            $encryptionMethod = 'laravel';
            foreach ($spedizioniss as $spedizione) {
                DB::table('spedizionis')->where('id', $spedizione->id)->update([
                    'nome' => $spedizione->nome
                        ? NadirByteCrypt::crypt($spedizione->nome,$encryptionMethod)
                        : null,
                    'indirizzo' => $spedizione->indirizzo
                        ? NadirByteCrypt::crypt($spedizione->indirizzo,$encryptionMethod)
                        : null,
                    'cap' => $spedizione->cap
                        ? NadirByteCrypt::crypt($spedizione->cap,$encryptionMethod)
                        : null,
                    'citta' => $spedizione->citta
                        ? NadirByteCrypt::crypt($spedizione->citta,$encryptionMethod)
                        : null,
                    'stato' => $spedizione->stato
                        ? NadirByteCrypt::crypt($spedizione->stato,$encryptionMethod)
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
