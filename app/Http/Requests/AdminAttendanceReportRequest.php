<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\User;

class AdminAttendanceReportRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'teacher_id' => $this->input('teacher_id') === '' ? null : $this->input('teacher_id'),
            'period_key' => $this->input('period_key') === '' ? null : $this->input('period_key'),
        ]);
    }

    public function authorize(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return $user !== null && ($user->isAdmin() || $user->isSuperAdmin());
    }

    public function rules(): array
    {
        return [
            'teacher_id' => [
                'nullable',
                'integer',
                Rule::in(User::whereHas('role', fn ($query) => $query->where('slug', 'teacher'))->pluck('id')->toArray()),
            ],
            'period_key' => ['nullable', 'string', 'regex:/^current$|^\d{4}-\d{2}-\d{2}_\d{4}-\d{2}-\d{2}$/'],
        ];
    }
}
