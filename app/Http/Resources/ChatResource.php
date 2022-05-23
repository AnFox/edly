<?php

namespace App\Http\Resources;

use App\Models\Webinar;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class ChatResource
 * @package App\Http\Resources
 *
 * @property integer $id
 * @property integer $webinar_id
 * @property boolean $is_active
 *
 * @property-read  Webinar $webinar
 */
class ChatResource extends JsonResource
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
            'webinar_id' => $this->webinar_id,
            'is_active' => (bool)$this->is_active,
        ];
    }
}
