<?php

namespace App\Http\Controllers\Platform;

use App\Domain\Platform\Actions\ResetUserPassword;
use App\Domain\Platform\Queries\OrganizationListQuery;
use App\Domain\Platform\Queries\PlatformUserListQuery;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request, PlatformUserListQuery $query, OrganizationListQuery $organizations): Response
    {
        return Inertia::render('Platform/Users/Index', [
            'users' => $query->paginate($request->only(['q', 'organization_id', 'role'])),
            'organizations' => $organizations->options(),
        ]);
    }

    public function resetPassword(User $user, ResetUserPassword $reset): RedirectResponse
    {
        abort_if($user->is(request()->user()), 422, 'Gunakan halaman profil untuk mengganti password sendiri.');

        $password = $reset->handle($user);

        // Ditampilkan sekali di UI (flash), tidak disimpan dalam bentuk asli.
        return back()
            ->with('success', "Password {$user->fullname} telah direset")
            ->with('generated_password', $password);
    }
}
