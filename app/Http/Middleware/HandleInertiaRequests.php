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
        // Use manifest file hash to ensure proper cache busting
        return md5_file(public_path('build/manifest.json'));
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
                'user' => $request->user(),
            ],
            'school' => [
                'name' => config('school.name'),
                'full_name' => config('school.full_name'),
                'subtitle' => config('school.subtitle'),
                'short_name' => config('school.short_name'),
                'logo' => config('school.logo'),
                'contact' => config('school.contact'),
            ],
        ];
    }
}
