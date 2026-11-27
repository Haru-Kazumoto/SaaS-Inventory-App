<?php

namespace App\Http\Services;

use App\Models\Major;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    //ambil jurusan dari user yang sedang login
    public function getMajorFromCurrentUser(): ?Major
    {
        $user = Auth::user();

        // Jika user tidak memiliki major, langsung return null
        $majorCode = $user?->major?->code;
        if (!$majorCode) {
            return null;
        }

        return Major::where('code', $majorCode)->first();
    }


}
