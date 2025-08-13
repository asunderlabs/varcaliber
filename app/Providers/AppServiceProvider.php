<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Opcodes\LogViewer\Facades\LogViewer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (config('app.key') && Schema::hasTable('settings')) {

            foreach (Setting::all() as $setting) {
                Config::set('settings.' . $setting->key, $setting->value);
            }

            $features = [];
            $features[] = config('settings.issues_enabled') === 'true' ? 'issues' : null;
            $features[] = config('settings.time_tracking_enabled') === 'true' ? 'time_tracking' : null;
            $features[] = config('settings.reports_enabled') === 'true' ? 'reports' : null;
            $features[] = config('settings.organization_stats_enabled') === 'true' ? 'organization_stats' : null;

            Config::set('enabled_features', array_values(array_filter($features)));
        }

        LogViewer::auth(function ($request) {
            if (! $request->user()) {
                abort(404);
            }

            return $request->user()->is_admin;
        });

        // Model::preventLazyLoading(! $this->app->isProduction());
    }
}
