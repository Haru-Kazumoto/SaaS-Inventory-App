<?php

namespace App\Http\Middleware;

use App\Domain\Organization\Support\OrganizationContext;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

/**
 * Set konteks tenant dari user login & blokir organisasi yang tidak aktif.
 * - Pengelola platform: tanpa konteks tenant (melihat lintas organisasi lewat area /platform).
 * - User tanpa organisasi: ditolak.
 * - Organisasi ditangguhkan/berhenti: hanya boleh logout.
 */
class ResolveOrganization
{
    public function __construct(private OrganizationContext $context) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->isPlatformAdmin()) {
            return $next($request);
        }

        $organization = $user->organization;

        if (! $organization) {
            abort(403, 'Akun Anda belum terhubung ke organisasi mana pun. Hubungi admin.');
        }

        if (! $organization->isUsable() && ! $request->routeIs('logout')) {
            return Inertia::render('Errors/OrganizationInactive', [
                'organization' => ['name' => $organization->name, 'status' => $organization->status->value],
            ])->toResponse($request)->setStatusCode(403);
        }

        $this->context->set($organization, $user);

        return $next($request);
    }

    public function terminate(): void
    {
        $this->context->clear();
    }
}
