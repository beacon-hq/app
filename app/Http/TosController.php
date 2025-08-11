<?php

declare(strict_types=1);

namespace App\Http;

use Inertia\Inertia;
use Inertia\Response;

class TosController
{
    public function __invoke(): Response
    {
        return Inertia::render('Company/TermsOfService');
    }
}
