<?php

namespace App\Http\Resources\Admin;

use App\Models\Account;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Models\Webinar;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class OrderResource
 * @package App\Http\Resources
 *
 * @property int $id
 * @property int $account_id
 * @property int $status_id
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
 * @property-read Webinar $webinar
 */
class OrderResource extends JsonResource
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
            'id' => $this->id,
            'user_id' => $this->user_id,
            'type' => $this->description,
            'status' => $this->status->name,
            'price' => $this->amount,
            'currency' => new CurrencyResource($this->currency),
            'date' => $this->created_at,
        ];
    }
}
