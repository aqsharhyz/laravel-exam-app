<?php

use App\Models\User;

describe('LessonTest', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
    });

    afterEach(function () {
        $this->user->delete();
    });

    describe('lessons.showPublic', function () {
        test('an authenticated user can view public lessons', function () {
            $user = User::factory()->create();
            $this->actingAs($user);

            $response = $this->get(route('lessons.showPublic'));

            $response->assertStatus(200);
        });

        test('an unauthenticated user can view public lessons')->todo();

        test('a guest cannot view public lessons')->todo();
    });

    describe('lessons.showActive', function () {
        test('an authenticated user can view his active lessons')->todo();

        test('an unauthenticated user cannot view active lessons')->todo();

        test('a guest cannot view active lessons')->todo();
    });

    describe('lessons.show', function () {
        test('an authenticated user can view a lesson')->todo();

        test('an unauthenticated user cannot view a lesson')->todo();

        test('a guest cannot view a lesson')->todo();
    });

    describe('lessons.create', function () {
        test('admin can create a lesson')->todo();

        test('non-admin user cannot create a lesson')->todo();

        test('an unauthenticated user cannot create a lesson')->todo();

        test('a guest cannot create a lesson')->todo();
    });

    describe('lessons.store', function () {
        test('admin can store a lesson')->todo();

        test('non-admin user cannot store a lesson')->todo();

        test('an unauthenticated user cannot store a lesson')->todo();

        test('a guest cannot store a lesson')->todo();
    });

    describe('lessons.edit', function () {
        test('admin can edit a lesson')->todo();

        test('non-admin user cannot edit a lesson')->todo();

        test('an unauthenticated user cannot edit a lesson')->todo();

        test('a guest cannot edit a lesson')->todo();
    });

    describe('lessons.update', function () {
        test('admin can update a lesson')->todo();

        test('non-admin user cannot update a lesson')->todo();

        test('an unauthenticated user cannot update a lesson')->todo();

        test('a guest cannot update a lesson')->todo();
    });

    describe('lessons.destroy', function () {
        test('admin can delete a lesson')->todo();

        test('non-admin user cannot delete a lesson')->todo();

        test('an unauthenticated user cannot delete a lesson')->todo();

        test('a guest cannot delete a lesson')->todo();
    });
});
