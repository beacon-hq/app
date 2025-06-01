<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App;
use App\Features\BillingEnabled;
use App\Services\OrganizationService;
use App\Services\TeamService;
use App\Values\User;
use Auth;
use Illuminate\Http\Request;
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
    public function __construct(protected TeamService $teamService, protected OrganizationService $organizationService)
    {
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        if (Auth::hasUser()) {
            return [
                ...parent::share($request),
                'alert' => fn () => $request->session()->get('alert'),
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
                'ziggy' => fn () => [
                    ...(new Ziggy())->toArray(),
                    'location' => $request->url(),
                ],
                'features' => [
                    'pricing.enabled' => Feature::active(BillingEnabled::class),
                ]
            ];
        }

        return [
            ...parent::share($request),
            'ssr' => [
                'enabled' => true,
            ],
            'ziggy' => fn () => [
                ...(new Ziggy())->toArray(),
                'location' => $request->url(),
            ],
            'features' => [
                'pricing.enabled' => Feature::active(BillingEnabled::class),
            ]
        ];
    }

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }
}
