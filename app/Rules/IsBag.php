<?php

declare(strict_types=1);

namespace App\Rules;

use Bag\Bag;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IsBag implements ValidationRule
{
    public function __construct(protected string $bagClass = Bag::class, protected bool $strict = false)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof $this->bagClass && (!$this->strict || is_subclass_of($value, $this->bagClass))) {
            $fail($attribute.' is not an instance of '.$this->bagClass);
        }
    }
}
