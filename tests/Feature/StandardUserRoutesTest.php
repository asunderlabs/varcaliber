<?php

use App\Models\Invoice;
use App\Models\Organization;
use App\Models\User;

$routes = [
    '/dashboard',
    '/reports',
    '/billing',
    '/billing/invoices',
    '/billing/payments',
    '/billing/paymentMethods',
];

test('user can access user route', function (string $route) {
    login(User::find(2))->get($route)->assertStatus(200);
})->with($routes);

test('user CANNOT access admin route', function (string $route) {
    login(User::find(2))->get($route)->assertStatus(403);
})->with(adminRoutes());

test('user can access own invoice', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $user->organizations()->attach($organization->id);
    $invoice = Invoice::factory()->create(['organization_id' => $organization->id]);
    login($user)->get("/billing/invoices/{$invoice->id}")->assertStatus(200);
});

test('user CANNOT access invoice belonging to another organization', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $invoice = Invoice::factory()->create(['organization_id' => $organization->id]);
    login($user)->get("/billing/invoices/{$invoice->id}")->assertStatus(403);
});
