<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $userId = $this->route('id');
        
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($userId)
            ],
            'phone' => 'nullable|string|max:20',
            'mobile_number' => 'nullable|string|max:15',
            'country_code' => 'nullable|string|max:5',
            'password' => $userId ? 'nullable|min:8|confirmed' : 'required|min:8|confirmed',
            'role' => 'required|in:admin,manager,user',
            'status' => 'required|in:active,inactive,suspended',
            'date_of_birth' => 'nullable|date|before:today',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'bio' => 'nullable|string|max:1000',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'User name is required',
            'email.required' => 'Email address is required',
            'email.email' => 'Please enter a valid email address',
            'email.unique' => 'This email address is already registered',
            'password.required' => 'Password is required for new users',
            'password.min' => 'Password must be at least 8 characters long',
            'password.confirmed' => 'Password confirmation does not match',
            'role.required' => 'User role is required',
            'role.in' => 'Invalid user role selected',
            'status.required' => 'User status is required',
            'status.in' => 'Invalid user status selected',
            'date_of_birth.before' => 'Date of birth must be before today',
            'avatar.image' => 'Avatar must be an image file',
            'avatar.mimes' => 'Avatar must be a JPEG, PNG, JPG, or GIF file',
            'avatar.max' => 'Avatar file size must not exceed 2MB'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean phone number
        if ($this->has('mobile_number')) {
            $this->merge([
                'mobile_number' => preg_replace('/[^0-9+]/', '', $this->mobile_number)
            ]);
        }

        // Ensure email is lowercase
        if ($this->has('email')) {
            $this->merge([
                'email' => strtolower(trim($this->email))
            ]);
        }
    }
}