<?php

use App\Models\Organization;
use App\Models\User;

test('admin can create organization', function () {
    $organization = Organization::factory()->make();
    loginAdmin()->post(route('admin.organizations.store'), $organization->toArray())->assertRedirect(route('admin.organizations.index'))->assertSessionHas('message', 'Organization created successfully!');
});

test('admin edit organization', function () {
    $organization = Organization::factory()->create();
    $organization->name .= ' edited';
    loginAdmin()->put(route('admin.organizations.update', $organization->id), $organization->toArray())->assertSessionHas('message', 'Organization updated successfully!');
});
