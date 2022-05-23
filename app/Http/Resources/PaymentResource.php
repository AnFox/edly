<?php

namespace App\Http\Resources;

use App\Contracts\Services\PurchaseService;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class PaymentResource
 * @package App\Http\Resources
 *
 * @property int id
 * @property int order_id
 * @property int payment_id
 * @property string status
 * @property bool paid
 * @property float amount
 * @property string payment_method
 * @property string description
 * @property string metadata
 * @property Carbon expires_at
 * @property bool test
 * @property int fail_reason_code
 * @property string fail_reason_text
 * @property string payment_data
 * @property Carbon payment_ts
 * @property Carbon synced_at
 *
 * @property-read Order $order
 */
class PaymentResource extends JsonResource
{
    /**
     * @var PurchaseService
     */
    private $purchaseService;

    /**
     * PaymentResource constructor.
     * @param $resource
     */
    public function __construct($resource)
    {
        parent::__construct($resource);

        $this->purchaseService = app(PurchaseService::class);
    }
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'status' => $this->status,
            'paid' => (bool)$this->paid,
            'amount' => $this->amount,
            'description' => $this->description,
            'fail_reason_code' => $this->fail_reason_code,
            'fail_reason_text' => $this->fail_reason_text,
            'fail_message' => $this->purchaseService->getFailReasonCodeMessage($this->fail_reason_code),
        ];
    }
}
