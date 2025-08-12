<?php

namespace App\Http\Middleware;

use App\Models\Organization;
use App\Models\Setting;
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
     *
     * @return string|null
     */
    public function version(Request $request)
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array
     */
    public function share(Request $request)
    {
        $cheduledMaintenanceMessageSetting = Setting::where('key', 'scheduled_maintenance_notification_message')->first();
        $data = [
            'app' => [
                'env' => config('app.env'),
            ],
            'settings' => config('settings'),
            'activeOrganization' => session('activeOrganization'),
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'scheduledMaintenanceMessage' => $cheduledMaintenanceMessageSetting?->value ?? false
        ];

        if (auth()->user()) {
            $organizations = auth()->user()->is_admin ? Organization::all() : auth()->user()->organizations;
            $organizations = $organizations->map(function ($organization) {
                return [
                    'id' => $organization->id,
                    'name' => $organization->name,
                ];
            });

            $data['organizations'] = $organizations;

            $data['auth'] = [
                'user' => fn () => $request->user()->only('id', 'name', 'first_name', 'email', 'is_admin'),
            ];
        }

        return array_merge(parent::share($request), $data);
    }
}
