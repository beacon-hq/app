<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApplicationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required'],
            'display_name' => ['required'],
            'description' => ['nullable'],
            'color' => ['present'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
