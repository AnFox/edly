<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class AccountResource
 * @package App\Http\Resources\Admin
 *
 * @property int $id
 * @property string $name
 * @property boolean $has_card
 * @property string $payment_token
 * @property float $balance
 * @property int $status
 * @property string $options
 *
 * @property-read boolean $is_limited
 * @property-read string $card_type
 * @property-read string $card_last_four
 */
class AccountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'is_limited' => (bool)$this->is_limited,
            'name' => $this->name,
            'has_card' => (bool)$this->has_card,
            'cards' => $this->card_type ? [
                [
                    'type' => $this->card_type,
                    'last_four' => $this->card_last_four,
                ],
            ] : null,
            'balance' => $this->balance,
            'cashbox' => $this->getOption('cashbox'),
            'payment' => $this->getOption('payment'),
            'fb_pixel' => $this->getOption('fb_pixel'),
            'is_trial_active' => $this->isTrialActive(),
        ];
    }
}
