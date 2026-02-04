<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Only administrator can register new users.
     * Note: This is a secondary check - the administrator middleware also enforces this.
     */
    public function authorize(): bool
    {
        // The administrator middleware already ensures only administrator can access this endpoint
        // This is kept as a defense-in-depth measure
        return $this->user()?->role === 'administrator' ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'string', 'regex:/^\d{5}$/', 'unique:users,user_id'],
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'password' => [
                'required',
                'confirmed',
                Password::min(4)
                    ->numbers(),

            ],
            'password_confirmation' => ['required', 'string'],
            'card_id' => ['nullable', 'string', 'max:255', 'unique:users,card_id'],
             // Administrator can create trainer or user roles
            'role' => ['required', 'in:trainer,user'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'The user id is required.',
            'name.regex' => 'The name field may only contain letters and spaces.',
            'password.confirmed' => 'The password confirmation does not match.',
            'password.min' => 'The password must be at least 4 characters.',
            'password.numbers' => 'The password must contain at least 4 number.',
            'phone.unique' => 'The phone number has already been taken.',
            'user_id.regex' => 'The user id must be exactly 5 digits.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Normalize email to lowercase
        if ($this->has('email')) {
            $this->merge([
                'email' => strtolower(trim($this->email)),
            ]);
        }

        if ($this->has('phone')) {
            $this->merge([
                'phone' => trim($this->phone),
            ]);
        }


        // Trim and sanitize name
        if ($this->has('name')) {
            $this->merge([
                'name' => trim($this->name),
            ]);
        }
    }
}
