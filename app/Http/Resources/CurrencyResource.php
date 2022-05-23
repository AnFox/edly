<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class CurrencyResource
 * @package App\Http\Resources
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $sign
 */
class CurrencyResource extends JsonResource
{
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
            'code' => $this->code,
            'sign' => $this->sign,
        ];
    }
}
