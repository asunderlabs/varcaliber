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
