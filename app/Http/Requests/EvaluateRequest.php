<?php

namespace App\Http\Requests;

use App\Enums\CustomerType;
use App\Enums\LoyaltyTier;
use Illuminate\Foundation\Http\FormRequest;

class EvaluateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // For now, allow all users. In production, implement proper authorization
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Line item validation
            'line' => 'required|array',
            'line.productId' => 'required|integer|exists:products,id',
            'line.quantity' => 'required|integer|min:1|max:9999',
            'line.unitPrice' => 'required|numeric|min:0.01',
            'line.categoryId' => 'sometimes|integer|exists:categories,id',

            // Customer validation
            'customer' => 'required|array',
            'customer.email' => 'required|email|max:150',
            'customer.type' => ['required', 'string', CustomerType::rules()],
            'customer.loyaltyTier' => ['sometimes', 'string', LoyaltyTier::rules()],
            'customer.ordersCount' => 'sometimes|integer|min:0',
            'customer.city' => 'sometimes|string|max:100',

            // Optional evaluation parameters
            'options' => 'sometimes|array',
            'options.includeInactive' => 'sometimes|boolean',
            'options.maxRules' => 'sometimes|integer|min:1|max:100',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            // Line item messages
            'line.required' => 'Line item data is required.',
            'line.array' => 'Line item must be an object.',
            'line.productId.required' => 'Product ID is required.',
            'line.productId.integer' => 'Product ID must be an integer.',
            'line.productId.exists' => 'The specified product does not exist.',
            'line.quantity.required' => 'Quantity is required.',
            'line.quantity.integer' => 'Quantity must be an integer.',
            'line.quantity.min' => 'Quantity must be at least 1.',
            'line.quantity.max' => 'Quantity cannot exceed 9999.',
            'line.unitPrice.required' => 'Unit price is required.',
            'line.unitPrice.numeric' => 'Unit price must be a number.',
            'line.unitPrice.min' => 'Unit price must be greater than 0.',
            'line.categoryId.integer' => 'Category ID must be an integer.',
            'line.categoryId.exists' => 'The specified category does not exist.',

            // Customer messages
            'customer.required' => 'Customer data is required.',
            'customer.array' => 'Customer must be an object.',
            'customer.email.required' => 'Customer email is required.',
            'customer.email.email' => 'Customer email must be a valid email address.',
            'customer.email.max' => 'Customer email cannot exceed 150 characters.',
            'customer.type.required' => 'Customer type is required.',
            'customer.type.in' => 'Customer type must be one of: ' . implode(', ', CustomerType::values()),
            'customer.loyaltyTier.in' => 'Loyalty tier must be one of: ' . implode(', ', LoyaltyTier::values()),
            'customer.ordersCount.integer' => 'Orders count must be an integer.',
            'customer.ordersCount.min' => 'Orders count cannot be negative.',
            'customer.city.string' => 'City must be a string.',
            'customer.city.max' => 'City name cannot exceed 100 characters.',

            // Options messages
            'options.array' => 'Options must be an object.',
            'options.includeInactive.boolean' => 'Include inactive must be true or false.',
            'options.maxRules.integer' => 'Max rules must be an integer.',
            'options.maxRules.min' => 'Max rules must be at least 1.',
            'options.maxRules.max' => 'Max rules cannot exceed 100.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'line.productId' => 'product ID',
            'line.unitPrice' => 'unit price',
            'line.categoryId' => 'category ID',
            'customer.email' => 'email',
            'customer.type' => 'customer type',
            'customer.loyaltyTier' => 'loyalty tier',
            'customer.ordersCount' => 'orders count',
            'options.includeInactive' => 'include inactive rules',
            'options.maxRules' => 'maximum rules',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default values for customer if not provided
        $customerData = $this->customer ?? [];

        if (!isset($customerData['loyaltyTier'])) {
            $customerData['loyaltyTier'] = LoyaltyTier::NONE->value;
        }

        if (!isset($customerData['ordersCount'])) {
            $customerData['ordersCount'] = 0;
        }

        $this->merge(['customer' => $customerData]);

        // Set default options
        if (!$this->has('options')) {
            $this->merge(['options' => [
                'includeInactive' => false,
                'maxRules' => 50
            ]]);
        }
    }
}
