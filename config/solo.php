<?php

declare(strict_types=1);

use SoloTerm\Solo\Commands\Command;
use SoloTerm\Solo\Commands\EnhancedTailCommand;
use SoloTerm\Solo\Hotkeys as Hotkeys;
use SoloTerm\Solo\Themes as Themes;

// Solo may not (should not!) exist in prod, so we have to
// check here first to see if it's installed.
if (!class_exists('\SoloTerm\Solo\Manager')) {
    return [
        //
    ];
}

return [
    /*
    |--------------------------------------------------------------------------
    | Themes
    |--------------------------------------------------------------------------
    */
    'theme' => env('SOLO_THEME', 'light'),

    'themes' => [
        'light' => Themes\LightTheme::class,
        'dark' => Themes\DarkTheme::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Keybindings
    |--------------------------------------------------------------------------
    */
    'keybinding' => env('SOLO_KEYBINDING', 'default'),

    'keybindings' => [
        'default' => Hotkeys\DefaultHotkeys::class,
        'vim' => Hotkeys\VimHotkeys::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Commands
    |--------------------------------------------------------------------------
    |
    */
    'commands' => [
        'Sail' => './vendor/bin/sail up',
        'Logs' => EnhancedTailCommand::file(storage_path('logs/laravel.log')),
        'Pest' => Command::from('./vendor/bin/sail pest')->lazy(),
        'Vite' => './vendor/bin/sail npm run dev; ./vendor/bin/sail exec beacon killall -9 node',
        // Lazy commands do no automatically start when Solo starts.
        // 'Dumps' => Command::from('./vendor/bin/sail artisan solo:dumps')->lazy(),
        'Pint' => Command::from('./vendor/bin/sail pint')->lazy(),
        'Queue' => Command::from('./vendor/bin/sail artisan queue:work')->lazy(),
        'Typescript' => Command::from('./vendor/bin/sail artisan typescript:transform')->lazy(),

    ],

    /*
    |--------------------------------------------------------------------------
    | Miscellaneous
    |--------------------------------------------------------------------------
    */

    /*
     * If you run the solo:dumps command, Solo will start a server to receive
     * the dumps. This is the address. You probably don't need to change
     * this unless the default is already taken for some reason.
     */
    'dump_server_host' => env('SOLO_DUMP_SERVER_HOST', 'tcp://127.0.0.1:9984')
];
