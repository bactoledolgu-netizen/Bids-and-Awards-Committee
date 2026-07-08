<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMinutesFolderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => ['required','string','max:255'],
            'description' => ['nullable','string','max:2000'],
            'start_month' => ['nullable','integer','between:1,12'],
            'start_year' => ['nullable','integer','digits:4'],
            'end_month' => ['nullable','integer','between:1,12'],
            'end_year' => ['nullable','integer','digits:4'],
        ];
    }
}
