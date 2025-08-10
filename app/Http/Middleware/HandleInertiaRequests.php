<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App;
use App\Features\BillingEnabled;
use App\Services\OrganizationService;
use App\Services\ProductService;
use App\Services\TeamService;
use App\Values\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Middleware;
use Laravel\Pennant\Feature;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    public function __construct(protected TeamService $teamService, protected OrganizationService $organizationService, protected ProductService $productService)
    {
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $data = [
            'ziggy' => fn () => [
                ...(new Ziggy())->toArray(),
                'location' => $request->url(),
            ],
            'features' => [
                'pricing.enabled' => Feature::active(BillingEnabled::class),
            ],
            'docsUrl' => config('beacon.docs.url'),
            'products' => Cache::flexible('products', [3000, 6000], fn () => $this->productService->all()),
            'status' => config('beacon.status.enabled') ? [
                ... Cache::get('beacon.status', [
                    'data' => [
                        'status' => 'operational',
                        'message' => 'All systems operational.',
                    ],
                ]),
                'url' => config('services.cachet.url'),
            ] : null,
        ];

        if (Auth::hasUser()) {
            return [
                ...parent::share($request),
                ... $data,
                'alert' => fn () => $request->session()->pull('alert'),
                'notifications' => fn () => $request->session()->get('notifications') ?? [],
                'auth' => [
                    'user' => $request->user(),
                    'permissions' => $request->user()?->getPermissionsViaRoles()->pluck('name') ?? [],
                    'currentTeam' => App::context()->team,
                ],
                'teams' => fn () => $this->teamService->all(auth()->user()->id, limitToOrganization: false),
                'organizations' => $this->organizationService->all(User::from(Auth::user())),
                'theme' => fn () => $request->user()?->theme ?? 'system',
                'ssr' => [
                    'enabled' => true,
                ],
            ];
        }

        return [
            ...parent::share($request),
            ... $data,
            'theme' => 'system',
        ];
    }

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }
}
