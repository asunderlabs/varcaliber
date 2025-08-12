<?php

use App\Models\Issue;
use App\Models\Organization;
use App\Models\User;

describe('Admin can', function() {
    beforeEach(function() {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $this->organization = Organization::factory()->create();
    });

    test('view issues page', function() {
        $this->get(route('admin.issues.index'))->assertOk();
    });
    
    test('view create issue page', function() {
        $this->get(route('admin.issues.create'))->assertOk();
    });

    test('store issue', function() {
        $issue = Issue::factory()->make(['organization_id' => $this->organization->id]);
        $this->post(route('admin.issues.store'), $issue->toArray())
            ->assertRedirect(route('admin.issues.index'))
            ->assertSessionHas('message', 'Issue created successfully!');

        $this->assertDatabaseHas('issues', ['title' => $issue->title]);
    });

    test('view edit issue page', function() {
        $issue = Issue::factory()->create(['organization_id' => $this->organization->id]);
        $this->get(route('admin.issues.edit', $issue->id))->assertOk();
    });

    test('update issue', function() {
        $issue = Issue::factory()->create([
            'title' => 'Original Title',
            'organization_id' => $this->organization->id
        ]);

        $this->put(route('admin.issues.update', $issue->id), ['title' => 'New title'])
            ->assertRedirect()
            ->assertSessionHas('message', 'Issue updated successfully!');

        $this->assertDatabaseHas('issues', ['id' => $issue->id, 'title' => 'New title']);
    });

    test('delete issue', function() {
        $issue = Issue::factory()->create(['organization_id' => $this->organization->id]);

        $this->delete(route('admin.issues.destroy', $issue->id))
            ->assertRedirect(route('admin.issues.index'))
            ->assertSessionHas('message', 'Issue deleted successfully!');

        $this->expect(Issue::find($issue->id))->toBeNull();
    });
    
    test('archive issue', function() {
        $issue = Issue::factory()->create(['organization_id' => $this->organization->id]);
        $this->post(route('admin.issues.archive', $issue->id))
            ->assertRedirect()
            ->assertSessionHas('message', 'Issue archived successfully!');

        $this->expect(Issue::find($issue->id)->archived_at)->not->toBeNull();
    });

    test('unarchive issue', function() {
        $issue = Issue::factory()->create(['organization_id' => $this->organization->id]);
        $this->delete(route('admin.issues.unarchive', $issue->id))
            ->assertRedirect()
            ->assertSessionHas('message', 'Issue unarchived successfully!');

        $this->expect(Issue::find($issue->id)->archived_at)->toBeNull();
    });
});

describe('User CANNOT', function() {
    beforeEach(function() {
        $this->actingAs(User::factory()->create());
        $this->organization = Organization::factory()->create();
    });

    test('view issues page', function() {
        $this->get(route('admin.issues.index'))->assertStatus(403);
    });
    
    test('view create issue page', function() {
        $this->get(route('admin.issues.create'))->assertStatus(403);
    });

    test('store issue', function() {
        $issue = Issue::factory()->make(['organization_id' => $this->organization->id]);
        $this->post(route('admin.issues.store'), $issue->toArray())->assertStatus(403);
    });

    test('view edit issue page', function() {
        $issue = Issue::factory()->create(['organization_id' => $this->organization->id]);
        $this->get(route('admin.issues.edit', $issue->id))->assertStatus(403);
    });

    test('update issue', function() {
        $issue = Issue::factory()->create([
            'title' => 'Original Title',
            'organization_id' => $this->organization->id
        ]);

        $this->put(route('admin.issues.update', $issue->id), ['title' => 'New title'])->assertStatus(403);
    });

    test('delete issue', function() {
        $issue = Issue::factory()->create(['organization_id' => $this->organization->id]);

        $this->delete(route('admin.issues.destroy', $issue->id))->assertStatus(403);

        $this->expect(Issue::find($issue->id))->not->toBeNull();
    });
    
    test('archive issue', function() {
        $issue = Issue::factory()->create(['organization_id' => $this->organization->id]);
        $this->post(route('admin.issues.archive', $issue->id))->assertStatus(403);
    });

    test('unarchive issue', function() {
        $issue = Issue::factory()->create(['organization_id' => $this->organization->id]);
        $this->delete(route('admin.issues.unarchive', $issue->id))->assertStatus(403);
    });
});