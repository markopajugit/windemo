<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLotteryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasVerifiedEmail();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'product_value' => ['required', 'numeric', 'min:1', 'max:100000'],
            'ticket_price' => ['required', 'numeric', 'min:0.50', 'max:1000'],
            'total_tickets' => ['required', 'integer', 'min:10', 'max:10000'],
            'charity_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'starts_at' => ['required', 'date', 'after:now'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'images' => ['required', 'array', 'min:1', 'max:5'],
            'images.*' => ['required', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'images.required' => 'Please upload at least one image.',
            'images.*.max' => 'Each image must be less than 5MB.',
            'images.*.mimes' => 'Images must be JPEG, PNG, or WebP format.',
            'starts_at.after' => 'Start date must be in the future.',
            'ends_at.after' => 'End date must be after the start date.',
        ];
    }
}

