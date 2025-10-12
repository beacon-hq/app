<?php

declare(strict_types=1);

namespace App\Actions\Traits;

/**
 * @template TReturn
 * @method TReturn handle(mixed ... $args)
 */
trait AsAction
{
    public static function make(mixed ... $args): static
    {
        return resolve(static::class, [... $args]);
    }

    /**
     * @return TReturn
     */
    public static function run(mixed ...$arguments): mixed
    {
        return (static::make())->handle(... $arguments);
    }

    public function __invoke(mixed ... $args)
    {
        return $this->handle(... $args);
    }
}
