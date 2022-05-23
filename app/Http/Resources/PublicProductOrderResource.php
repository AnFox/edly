<?php

namespace App\Http\Resources;

use App\Models\Account;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class PublicProductOrderResource
 * @package App\Http\Resources
 *
 * @property int $id
 * @property int $account_id
 * @property int $product_id
 * @property int $webinar_id
 * @property float $amount
 *
 * @property-read Account $account
 * @property-read Payment $payment
 * @property-read Product $product
 */
class PublicProductOrderResource extends JsonResource
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
            'description' => $this->product->name,
            'amount' => (float)$this->product->price,
            'currency' => $this->product->currency->code,
            'publicId' => $this->account->getOption('payment.CloudPayments.public_key'),
        ];
    }
}
