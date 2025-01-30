<?php

declare(strict_types=1);

namespace App\Rules;

use Bag\Collection;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IsBagCollection implements ValidationRule
{
    public function __construct(protected string $collectionClass = Collection::class, protected bool $strict = false)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof $this->collectionClass && (!$this->strict || is_subclass_of($value, $this->collectionClass))) {
            $fail($attribute.' is not an instance of '.$this->collectionClass);
        }
    }
}
