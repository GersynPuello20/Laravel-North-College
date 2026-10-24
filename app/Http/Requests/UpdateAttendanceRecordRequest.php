<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\Course;

class UpdateAttendanceRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->isTeacher();
    }

    public function rules(): array
    {
        return [
            'course_id' => [
                'required',
                Rule::exists(Course::class, 'id')->where(fn ($query) => $query->where('professor_id', Auth::id())),
            ],
            'attendance_date' => ['required', 'date', 'before_or_equal:today'],
            'class_time' => ['required', 'date_format:H:i'],
            'students_present' => ['required', 'integer', 'min:0'],
            'observations' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
