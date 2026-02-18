<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function rules()
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ];
    }

    public function messages()
    {
        return [
            'email.required' => __('validation.email_required'),
            'email.email' => __('validation.email_email'),
            'password.required' => __('validation.password_required'),
            'password.min' => __('validation.password_min'),
        ];
    }
}
