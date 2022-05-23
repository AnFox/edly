<?php

namespace App\Http\Resources;

use App\Models\Account;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class WebinarOrderResource
 * @package App\Http\Resources
 *
 * @property int $id
 * @property int $account_id
 * @property int $user_id
 * @property int $product_id
 * @property int $webinar_id
 * @property float $amount
 *
 * @property-read Account $account
 * @property-read Payment $payment
 * @property-read Product $product
 * @property-read User $user
 */
class WebinarOrderResource extends JsonResource
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
            'description' => $this->product->name,
            'amount' => (float)$this->product->price,
            'currency' => $this->product->currency->code,
            'publicId' => $this->account->getOption('payment.CloudPayments.public_key'),
        ];
    }
}
