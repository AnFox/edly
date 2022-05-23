<?php

namespace App\Http\Resources\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class ConversionResource
 * @package App\Http\Resources
 *
 * @property string $model_type
 * @property int $id
 * @property int $model_id
 * @property int $status
 * @property float $progress
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class ConversionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        if (!$this->resource) {
            return [
                'status' => null,
                'progress' => null,
            ];
        }

        return [
            'id' => $this->id,
            'status' => $this->status,
            'progress' => $this->progress,
        ];
    }
}
