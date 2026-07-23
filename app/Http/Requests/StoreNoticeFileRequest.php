<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNoticeFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'files' => ['required','array'],
            'files.*' => ['file','mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,ppt,pptx,txt,csv'],
        ];
    }
}
