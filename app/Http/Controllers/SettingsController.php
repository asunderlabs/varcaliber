<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = config('app.default_settings');
        
        foreach (Setting::all() as $setting) {
            $settings[$setting->key] = $setting->value;
        }
        
        return Inertia::render('Admin/Settings', [
            'settings' => array_map(function($key) use ($settings) {
                return [
                    'key' => $key,
                    'value' => $settings[$key]
                ];
            }, array_keys($settings))
        ]);
    }

    public function update()
    {
        $settings = [];
        foreach (request()->all() as $key => $value) {
            $settings[] = ['key' => $key, 'value' => $value];
        }

        Setting::upsert($settings, ['key'], ['value']);

        return back()->with('message', 'Settings updated successfully!');
    }
}
