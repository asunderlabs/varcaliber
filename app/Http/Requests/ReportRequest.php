<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->is_admin;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if ($this->report) {
            return [
                'name' => 'required|string|max:50|min:3',
            ];
        }

        return [
            'name' => 'required|string|max:50|min:3',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date',
            'organization_id' => 'required|exists:organizations,id',
            'report_type' => 'required|string|in:custom_report',
        ];
    }
}
