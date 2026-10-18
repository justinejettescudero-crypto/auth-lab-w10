<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Course $course): bool
    {
        return $course->is_active || ($user && $user->id === $course->user_id);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('instructor', 'admin');
    }

    public function update(User $user, Course $course): bool
    {
        return $user->id === $course->user_id;
    }

    public function delete(User $user, Course $course): bool
    {
        return $user->id === $course->user_id
            && $course->students()->doesntExist();
    }
}