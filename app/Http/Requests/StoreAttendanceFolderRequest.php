<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceFolderRequest extends FormRequest
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
            'parent_id' => ['nullable','exists:attendance_folders,id'],
        ];
    }
}
