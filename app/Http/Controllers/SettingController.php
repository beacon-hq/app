<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class SettingController
{
    public function index(): Response
    {
        return Inertia::render('Settings/Index');
    }
}
