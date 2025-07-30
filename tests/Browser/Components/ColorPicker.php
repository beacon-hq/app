<?php

declare(strict_types=1);

namespace Tests\Browser\Components;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Component as BaseComponent;

class ColorPicker extends BaseComponent
{
    /**
     * Get the root selector for the component.
     */
    public function selector(): string
    {
        return '';
    }

    public function selectColor(Browser $browser, string $color, ?string $screenshotElement = null, ?string $screenshotName = null): void
    {
        $browser
            ->mouseover('.bg-' . $color . '-400')
            ->pause(500);

        if ($screenshotName && $screenshotElement) {
            $browser->screenshotElement($screenshotElement, $screenshotName);
        }

        if ($screenshotName) {
            $browser->screenshot($screenshotName);
        }

        $browser->click('.bg-' . $color . '-400')
            ->pause(500);
    }
}
