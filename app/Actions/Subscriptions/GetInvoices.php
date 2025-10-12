<?php

declare(strict_types=1);

namespace App\Actions\Subscriptions;

use App\Actions\Traits\AsAction;
use App\Models\Organization as OrganizationModel;
use App\Values\Organization;
use Illuminate\Support\Collection;
use Laravel\Cashier\Invoice;

class GetInvoices
{
    use AsAction;

    public function handle(Organization $organization): Collection
    {
        $organizationModel = OrganizationModel::find($organization->id);

        return $organizationModel->invoices()->map(function (Invoice $invoice) {
            return [
                'id' => $invoice->id,
                'number' => $invoice->number,
                'date' => $invoice->date()->toFormattedDateString(),
                'total' => $invoice->total(),
                'status' => $invoice->asStripeInvoice()->paid ? 'Paid' : 'Outstanding',
            ];
        });
    }
}
