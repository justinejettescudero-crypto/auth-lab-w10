<?php

namespace App\Http\Controllers;

use App\Http\Requests\CourseRequest;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Course::class);

        $courses = Course::with('owner')
            ->where('is_active', true)
            ->when($request->user(), fn ($query, $user) => $query
                ->orWhere('user_id', $user->id))
            ->orderBy('code')
            ->get();

        return view('courses.index', compact('courses'));
    }

    public function create()
    {
        Gate::authorize('create', Course::class);

        $course = new Course([
            'units' => 3,
            'is_active' => true,
        ]);

        return view('courses.form', compact('course'));
    }

    public function store(CourseRequest $request)
    {
        Gate::authorize('create', Course::class);

        $course = $request->user()
            ->courses()
            ->create($request->validated());

        return redirect()
            ->route('courses.show', $course)
            ->with('status', 'Course created.');
    }

    public function show(Course $course)
    {
        Gate::authorize('view', $course);

        $course->load('owner')->loadCount('students');

        return view('courses.show', compact('course'));
    }

    public function edit(Course $course)
    {
        Gate::authorize('update', $course);

        return view('courses.form', compact('course'));
    }

    public function update(
        CourseRequest $request,
        Course $course
    ) {
        Gate::authorize('update', $course);

        $course->update($request->validated());

        return redirect()
            ->route('courses.show', $course)
            ->with('status', 'Course updated.');
    }

    public function destroy(Course $course)
    {
        Gate::authorize('delete', $course);

        $course->delete();

        return redirect()
            ->route('courses.index')
            ->with('status', 'Course deleted.');
    }
}
