<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FeatureTypeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required'],
            'description' => ['nullable'],
            'temporary' => ['boolean'],
            'color' => ['present'],
            'icon' => ['required'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
