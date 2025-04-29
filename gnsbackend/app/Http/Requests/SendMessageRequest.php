<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'number' => 'required|string',
            'message' => 'required|string',
            'media' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,mp4,mp3,ogg,aac,m4a,opus,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,zip,rar|max:20480',

        ];
    }
}
