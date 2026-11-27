<?php

namespace App\Domain\Platform\Actions;

use App\Models\User;
use Illuminate\Support\Str;

/** Buat password acak baru untuk pengguna; password dikembalikan sekali untuk diberikan ke pengguna. */
final class ResetUserPassword
{
    public function handle(User $user): string
    {
        $password = Str::password(12, symbols: false);

        $user->forceFill(['password' => $password])->save(); // di-hash oleh cast 'hashed'

        return $password;
    }
}
