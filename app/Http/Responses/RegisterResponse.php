<?php

declare(strict_types=1);

namespace App\Http\Responses;

use App\Features\BillingEnabled;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Session;
use Laravel\Pennant\Feature;

class RegisterResponse extends \Laravel\Fortify\Http\Responses\RegisterResponse
{
    /**
     * @inheritDoc
     */
    public function toResponse($request)
    {
        if (!Feature::driver('array')->active(BillingEnabled::class)) {
            return parent::toResponse($request);
        }

        if (Session::has('checkout.plan')) {
            return redirect()->route('checkout.show', [
                'checkout' => Session::get('checkout.plan'),
            ]);
        }

        return $request->wantsJson()
            ? new JsonResponse('', 201)
            : redirect()->route('checkout.index');
    }
}
