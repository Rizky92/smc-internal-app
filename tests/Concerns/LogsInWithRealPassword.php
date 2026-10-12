<?php

namespace Tests\Concerns;

use Illuminate\Support\Facades\DB;

/**
 * CreatesPetugas::createPegawaiRecord() deliberately leaves `user`.`password`
 * as a plain, unencrypted empty string - every other test in this suite
 * authenticates via Livewire::actingAs()/Dusk's loginAs(), which sets the
 * session directly and never re-checks a password. A test that drives the
 * real login form needs `password` to actually be
 * AES_ENCRYPT(<plaintext>, KHANZA_PASSKEY), matching what
 * LoginController::store() decrypts and compares against what was typed into
 * the browser. Kept out of CreatesPetugas on purpose, so this one-off need
 * never touches the fixture the other 441 tests rely on.
 */
trait LogsInWithRealPassword
{
    protected function givePlaintextPassword(string $nik, string $plaintext): void
    {
        DB::connection('mysql_sik')->statement(
            'update `user` set `password` = AES_ENCRYPT(?, ?) where AES_DECRYPT(id_user, ?) = ?',
            [$plaintext, config('khanza.app.passkey'), config('khanza.app.userkey'), $nik]
        );
    }
}
