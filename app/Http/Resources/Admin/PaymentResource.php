<?php

namespace App\Http\Resources\Admin;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class PaymentResource
 * @package App\Http\Resources
 *
 * @property int $order_id
 * @property int $payment_id
 * @property string $status
 * @property bool $paid
 * @property float $amount
 * @property string $payment_method
 * @property int $payment_method_id
 * @property string $description
 * @property string $metadata
 * @property Carbon $expires_at
 * @property bool $test
 * @property int $fail_reason_code
 * @property string $fail_reason_text
 * @property string $payment_data
 * @property Carbon $payment_ts
 * @property Carbon $synced_at
 *
 * @property-read Order $order
 */
class PaymentResource extends JsonResource
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
            'description' => $this->description,
            'status' => $this->status,
            'amount' => $this->amount,
            'currency' => new CurrencyResource($this->currency),
            'created_at' => $this->created_at,
        ];
    }
}
