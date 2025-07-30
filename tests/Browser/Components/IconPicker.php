<?php

declare(strict_types=1);

namespace Tests\Browser\Components;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Component as BaseComponent;

class IconPicker extends BaseComponent
{
    /**
     * Get the root selector for the component.
     */
    public function selector(): string
    {
        return '';
    }

    public function selectIcon(Browser $browser, string $iconName, int $iconIndex = 0): void
    {
        $browser
            ->click('@icon-picker')
            ->waitForText('Search for an icon')
            ->type('@input-icon-picker-search', $iconName)
            ->pause(500)
            ->click('@icon-picker-icon-' . $iconIndex)
            ->pause(500);
    }
}
