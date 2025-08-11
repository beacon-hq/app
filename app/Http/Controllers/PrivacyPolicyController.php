<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class PrivacyPolicyController
{
    public function __invoke(): Response
    {
        return Inertia::render('Company/PrivacyPolicy');
    }
}
