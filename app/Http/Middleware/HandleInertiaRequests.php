<?php

namespace App\Http\Middleware;

use App\Domain\Organization\Support\OrganizationContext;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Props bersama semua halaman.
     *
     * - auth.user.role { code, name } dipertahankan untuk kompatibilitas menu/layout:
     *   code DEVELOPER (platform) | SUPER (admin organisasi) | ADMIN (anggota unit).
     * - organization: organisasi aktif + terminologi (frontend: useTerms()).
     * - units: unit yang boleh diakses user (untuk menu & filter).
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $context = app(OrganizationContext::class);

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'fullname' => $user->fullname,
                    'username' => $user->username,
                    'email' => $user->email,
                    'profile_image_url' => $user->profile_image_url,
                    'organization_role' => $user->organization_role?->value,
                    'is_platform_admin' => $user->isPlatformAdmin(),
                    'role' => ['code' => $user->accessCode(), 'name' => $user->accessLabel()],
                ] : null,
            ],
            'organization' => fn () => $context->has() ? [
                'id' => $context->organization()->id,
                'name' => $context->organization()->name,
                'slug' => $context->organization()->slug,
                'template' => $context->organization()->template->value,
                'status' => $context->organization()->status->value,
                'terms' => $context->organization()->terms(),
            ] : null,
            'units' => fn () => $context->has()
                ? $context->accessibleUnits()->map->only(['id', 'name', 'code'])->values()
                : [],
            'flash' => [
                'success' => session('success'),
                'failed' => session('failed'),
                // password acak (reset / akun baru) — ditampilkan sekali
                'generated_password' => session('generated_password'),
            ],
            'is_test' => env('APP_STATUS') === 'test',
        ];
    }
}
