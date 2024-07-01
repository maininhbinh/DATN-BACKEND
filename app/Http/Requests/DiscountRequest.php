<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DiscountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type_discount' => 'nullable|in:none,percentage,fixed',
            'discount' => ['nullable', 'numeric', function ($attribute, $value, $fail) {
                if ($this->input('type_discount') == 'percentage' && $value > 100) {
                    return $fail('không thể giảm giá quá 100%');
                }
                if ($this->input('type_discount') == 'fixed' && $value > $this->input('price')) {
                    return $fail('Giảm gía không thể vượt số tiền của sản phẩm');
                }
            }],

        ];
    }

    public function messages()
    {
        return [
            'type_discount.required' => 'The discount type is required.',
            'type_discount.in' => 'The discount type must be one of: none, percentage, fixed.',
            'discount.required' => 'The discount amount is required.',
            'discount.numeric' => 'The discount amount must be a number.',
            'discount.min' => 'The discount amount must be at least :min.',
            'discount.max' => 'The discount amount cannot be greater than the current price.',
        ];
    }
}
