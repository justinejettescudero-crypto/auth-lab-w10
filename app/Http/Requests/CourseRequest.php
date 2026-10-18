<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Course;

class CourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        $course = $this->route('course');

        return $course
            ? $user->can('update', $course)
            : $user->can('create', Course::class);
    }

    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('courses', 'code')
                    ->ignore($this->route('course')),
            ],
            'title' => ['required', 'string', 'max:255'],
            'units' => ['required', 'integer', 'between:1,6'],
            'is_active' => ['boolean'],
        ];
    }
}
