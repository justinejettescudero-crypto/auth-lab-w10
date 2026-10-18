<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_browse_active_courses_but_not_inactive_courses(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $activeCourse = Course::factory()->for($instructor, 'owner')->create();
        $inactiveCourse = Course::factory()
            ->inactive()
            ->for($instructor, 'owner')
            ->create();

        $this->get(route('courses.index'))
            ->assertOk()
            ->assertSee($activeCourse->code)
            ->assertDontSee($inactiveCourse->code);

        $this->get(route('courses.show', $activeCourse))->assertOk();
        $this->get(route('courses.show', $inactiveCourse))->assertForbidden();
        $this->get(route('courses.create'))->assertRedirect(route('login'));
    }

    public function test_students_cannot_create_or_edit_courses(): void
    {
        /** @var User $student */
        $student = User::factory()->create(['role' => 'student']);
        /** @var User $owner */
        $owner = User::factory()->create(['role' => 'instructor']);
        $course = Course::factory()->for($owner, 'owner')->create();

        $this->actingAs($student)
            ->get(route('courses.create'))
            ->assertForbidden();

        $this->actingAs($student)
            ->get(route('courses.edit', $course))
            ->assertForbidden();
    }

    public function test_course_owner_is_always_the_authenticated_instructor(): void
    {
        /** @var User $instructor */
        $instructor = User::factory()->create(['role' => 'instructor']);
        /** @var User $otherInstructor */
        $otherInstructor = User::factory()->create(['role' => 'instructor']);

        $this->actingAs($instructor)
            ->post(route('courses.store'), [
                'code' => 'WEB101',
                'title' => 'Web Development',
                'units' => 3,
                'is_active' => 1,
                'user_id' => $otherInstructor->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('courses', [
            'code' => 'WEB101',
            'user_id' => $instructor->id,
        ]);
    }

    public function test_instructors_can_manage_only_their_courses_and_cannot_delete_enrolled_courses(): void
    {
        /** @var User $instructor */
        $instructor = User::factory()->create(['role' => 'instructor']);
        /** @var User $otherInstructor */
        $otherInstructor = User::factory()->create(['role' => 'instructor']);
        $ownedCourse = Course::factory()->for($instructor, 'owner')->create();
        $otherCourse = Course::factory()->for($otherInstructor, 'owner')->create();
        /** @var User $student */
        $student = User::factory()->create(['role' => 'student']);
        $ownedCourse->students()->attach($student);

        $this->actingAs($instructor)
            ->get(route('courses.edit', $ownedCourse))
            ->assertOk();

        $this->get(route('courses.edit', $otherCourse))->assertForbidden();
        $this->delete(route('courses.destroy', $ownedCourse))->assertForbidden();
        $this->delete(route('courses.destroy', $otherCourse))->assertForbidden();
    }

    public function test_admin_can_manage_courses_and_only_admin_can_open_admin_page(): void
    {
        $this->get(route('admin.index'))->assertRedirect(route('login'));

        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);
        /** @var User $instructor */
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::factory()->for($instructor, 'owner')->create();

        $this->actingAs($admin)
            ->get(route('courses.edit', $course))
            ->assertOk();

        $this->get(route('admin.index'))->assertOk();

        $this->actingAs($instructor)
            ->get(route('admin.index'))
            ->assertForbidden();

    }
}