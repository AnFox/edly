<?php

namespace App\Http\Resources\Admin;

use App\Models\Account;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class AccountRefillOrderResource
 * @package App\Http\Resources
 *
 * @property int $id
 * @property int $account_id
 * @property int $user_id
 * @property int $product_id
 * @property int $webinar_id
 * @property float $amount
 * @property string $currency_code
 * @property string $description
 *
 * @property-read Account $account
 * @property-read Payment $payment
 * @property-read Product $product
 * @property-read User $user
 */
class AccountRefillOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'invoiceId' => $this->id,
            'accountId' => $request->user()->email,
            'description' => $this->description,
            'amount' => (float)$this->amount,
            'currency' => $this->currency_code,
            'publicId' => config('services.cloud-payments.public-key'),
        ];
    }
}
