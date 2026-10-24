<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Captura de la cámara: varios fotogramas JPEG en base64 y el id del reto. */
class FaceCaptureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'challenge_id' => ['nullable', 'string', 'max:64'],
            'frames' => ['required', 'array', 'min:1', 'max:'.config('biometrics.max_frames')],
            // ~1.5 MB por fotograma en base64.
            'frames.*' => ['required', 'string', 'max:2000000', 'starts_with:data:image/jpeg;base64,'],
        ];
    }

    public function frames(): array
    {
        return array_values($this->validated('frames'));
    }
}
