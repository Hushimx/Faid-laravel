<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendNotificationRequest extends FormRequest
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
            'title_ar' => 'required|string|max:100',
            'title_en' => 'required|string|max:100',
            'body_ar' => 'required|string|max:500',
            'body_en' => 'required|string|max:500',
            'target_type' => 'required|in:all,role,individual',
            'target_role' => 'required_if:target_type,role|in:user,vendor,admin',
            'target_users' => 'required_if:target_type,individual|array',
            'target_users.*' => 'exists:users,id',
            'custom_data' => 'nullable|json',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title_ar.required' => __('dashboard.The Arabic title is required'),
            'title_en.required' => __('dashboard.The English title is required'),
            'body_ar.required' => __('dashboard.The Arabic message is required'),
            'body_en.required' => __('dashboard.The English message is required'),
            'target_type.required' => __('dashboard.Please select target type'),
            'target_type.in' => __('dashboard.Invalid target type'),
            'target_role.required_if' => __('dashboard.Please select a role'),
            'target_users.required_if' => __('dashboard.Please select at least one user'),
            'target_users.*.exists' => __('dashboard.Selected user does not exist'),
        ];
    }
}
