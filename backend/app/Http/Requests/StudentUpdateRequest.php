<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rule;

class StudentUpdateRequest extends FormRequest
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
            'name'       => ['sometimes', 'required', 'string', 'max:255'],
            'student_id' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('students', 'student_id')->ignore($this->student),
            ],
            'class'      => ['sometimes', 'required', 'string', 'max:50'],
            'section'    => ['sometimes', 'required', 'string', 'max:50'],
            'photo'      => ['nullable', 'string', 'max:255'],
        ];
    }
}
