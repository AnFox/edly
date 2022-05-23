<?php

namespace App\Http\Resources\Admin;

use App\Models\Currency;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class ProductResource
 * @package App\Http\Resources
 *
 * @property int $id
 * @property int $type
 * @property string $name
 * @property string $description
 * @property int $currency_id
 * @property float $price
 *
 * @property-read Currency $currency
 */
class ProductResource extends JsonResource
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
            'type' => $this->type,
            'name' => $this->name,
            'description' => $this->description,
            'currency' => new CurrencyResource($this->currency),
            'price' => $this->price,
        ];
    }
}
