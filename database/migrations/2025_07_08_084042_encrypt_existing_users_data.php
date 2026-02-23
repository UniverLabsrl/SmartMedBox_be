<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Services\NadirByteCrypt;


class EncryptExistingUsersData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('users')->select('id', 'nome', 'indirizzo', 'cap', 'citta', 'stato')->orderBy('id')->chunk(100, function ($users) {
            $encryptionMethod = 'laravel';
            foreach ($users as $user) {
                DB::table('users')->where('id', $user->id)->update([
                    'nome' => NadirByteCrypt::crypt($user->nome,$encryptionMethod),
                    'indirizzo' => $user->indirizzo
                        ? NadirByteCrypt::crypt($user->indirizzo,$encryptionMethod)
                        : null,
                    'cap' => $user->cap
                        ? NadirByteCrypt::crypt($user->cap,$encryptionMethod)
                        : null,
                    'citta' => $user->citta
                        ? NadirByteCrypt::crypt($user->citta,$encryptionMethod)
                        : null,
                    'stato' => $user->stato
                        ? NadirByteCrypt::crypt($user->stato,$encryptionMethod)
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
