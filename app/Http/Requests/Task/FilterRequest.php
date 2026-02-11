<?php

namespace App\Http\Requests\Task;

use App\Enums\Task\PriorityEnum;
use App\Enums\Task\StatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterRequest extends FormRequest
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
            'search' => 'nullable|string|max:200',
            'status' => ['nullable', Rule::enum(StatusEnum::class)],
            'priority' => ['nullable', Rule::enum(PriorityEnum::class)],
            'category' => ['nullable', 'integer', 'exists:categories,id']
        ];
    }
}
