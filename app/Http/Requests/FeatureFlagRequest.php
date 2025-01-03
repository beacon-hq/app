<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FeatureFlagRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required'],
            'description' => ['nullable'],
            'feature_type_id' => ['required', 'exists:feature_types,id'],
            'tags' => ['array', 'exists:tags,id'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
