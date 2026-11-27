<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? array_merge(
                    $request->user()->only(['id', 'fullname', 'username', 'email', 'profile_image', 'role', 'major']),
                    ['profile_image_url' => $request->user()->profile_image_url] // Tambahkan ini
                ) : null,
            ],
            'flash' => [
                'success' => session('success'),
                'failed' => session('failed')
            ],
            'is_test' => $this->is_test_env()
        ];
    }

    private function is_test_env()
    {
        return env('APP_STATUS') === 'test';
    }
}
