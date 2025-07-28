<?php

namespace App\Http\Requests;

use App\Rules\ValidActionJson;
use App\Rules\ValidConditionJson;
use Illuminate\Foundation\Http\FormRequest;

class StoreRuleRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:150',
                'unique:rules,name'
            ],
            'salience' => [
                'required',
                'integer',
                'min:0',
                'max:999'
            ],
            'stackable' => [
                'required',
                'boolean'
            ],
            'condition_json' => [
                'required',
                'array',
                new ValidConditionJson()
            ],
            'action_json' => [
                'required',
                'array',
                new ValidActionJson()
            ],
            'is_active' => [
                'sometimes',
                'boolean'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Rule name is required.',
            'name.unique' => 'A rule with this name already exists.',
            'name.max' => 'Rule name cannot exceed 150 characters.',
            'salience.required' => 'Salience (priority) is required.',
            'salience.integer' => 'Salience must be an integer.',
            'salience.min' => 'Salience must be at least 0.',
            'salience.max' => 'Salience cannot exceed 999.',
            'stackable.required' => 'Stackable field is required.',
            'stackable.boolean' => 'Stackable must be true or false.',
            'condition_json.required' => 'Condition JSON is required.',
            'condition_json.array' => 'Condition must be a valid JSON object.',
            'action_json.required' => 'Action JSON is required.',
            'action_json.array' => 'Action must be a valid JSON object.',
            'is_active.boolean' => 'Active status must be true or false.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'condition_json' => 'condition',
            'action_json' => 'action',
            'is_active' => 'active status',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default value for is_active if not provided
        if (!$this->has('is_active')) {
            $this->merge(['is_active' => true]);
        }

        // Ensure salience is treated as integer
        if ($this->has('salience')) {
            $this->merge(['salience' => (int) $this->salience]);
        }
    }
}
