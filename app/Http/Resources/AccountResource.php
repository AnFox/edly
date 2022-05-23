<?php

namespace App\Http\Resources;

use App\Contracts\Services\PurchaseService;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class AccountResource
 * @package App\Http\Resources
 *
 * @property int $id
 * @property string $name
 * @property boolean $has_card
 * @property string $payment_token
 * @property float $balance
 * @property int $status
 * @property string $trial_ends_at
 * @property Carbon $created_at
 *
 */
class AccountResource extends JsonResource
{

    /**
     * AccountResource constructor.
     * @param $resource
     */
    public function __construct($resource)
    {
        parent::__construct($resource);
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
            'name' => $this->name,
            'has_card' => $this->has_card,
            'payment_token' => $this->payment_token,
            'balance' => $this->balance,
            'status' => $this->status,
            'trial_ends_at' => $this->trial_ends_at,
            'created_at' => $this->created_at,
        ];
    }
}
