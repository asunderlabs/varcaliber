<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

use App\Models\Organization;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(TestCase::class, RefreshDatabase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function adminRoutes()
{
    return [
        '/admin/dashboard',
        '/admin/issues',
        '/admin/hours',
        '/admin/invoices',
        '/admin/organizations',
        '/admin/organizations/create',
        '/admin/users',
        '/admin/users/create',
        '/admin/emails',
    ];
}

function login($user = null)
{
    $user = $user ?? User::factory()->create();
    if (! $user->organizations->count()) {
        $organization = Organization::factory()->create();
        $user->organizations()->attach($organization->id);
        $user->load('organizations');
    }

    return test()->actingAs($user);
}

function loginAdmin($user = null)
{
    if (! $user) {
        $user = User::factory()->create(['is_admin' => true]);
        Organization::factory(3)->create();
    }

    return test()->actingAs($user);
}
