<?php

use App\Models\Organization;
use App\Models\User;

test('admin can store user', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->make([]);
    $data = $user->toArray();
    $data['organization_id'] = $organization->id;
    loginAdmin()->post(route('admin.users.store'), $data)->assertRedirect(route('admin.users.index'))->assertSessionHas('message', 'User created successfully!');
});

test('admin can update user', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $user->name .= ' edited';
    $data = $user->toArray();
    $data['organization_id'] = $organization->id;
    $data = array_merge($data, [
        'invoice_notification' => false,
        'account_notification' => false,
        'account_notification_day' => 6,
    ]);
    
    loginAdmin()->put(route('admin.users.update', $user->id), $data)->assertSessionHas('message', 'User updated successfully!');
});

test('admin can delete user', function () {
    $user = User::factory()->create();

    loginAdmin()->delete(route('admin.users.destroy', $user->id))->assertSessionHas('message', 'User deleted successfully!');
});
