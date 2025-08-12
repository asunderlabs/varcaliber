<?php

use App\Models\User;

test('admin can access admin route', function (string $route) {
    loginAdmin()->get($route)->assertStatus(200);
})->with(adminRoutes());
