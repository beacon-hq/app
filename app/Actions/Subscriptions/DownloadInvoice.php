<?php

declare(strict_types=1);

namespace App\Actions\Subscriptions;

use App\Actions\Traits\AsAction;
use App\Models\Organization as OrganizationModel;
use App\Values\Organization;
use Symfony\Component\HttpFoundation\Response;

class DownloadInvoice
{
    use AsAction;

    public function handle(Organization $organization, string $invoiceId): Response
    {
        return OrganizationModel::findOrFail($organization->id)->downloadInvoice($invoiceId);
    }
}
