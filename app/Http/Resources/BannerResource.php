<?php

namespace App\Http\Resources;

use App\Models\Product;
use App\Models\Webinar;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class BannerResource
 * @package App\Http\Resources
 *
 * @property int $id
 * @property int $room_id
 * @property int $product_id
 * @property boolean $is_visible
 * @property string $image
 * @property string $url
 * @property string $title
 *
 * @property-read Product $product
 * @property-read Webinar $webinar
 */
class BannerResource extends JsonResource
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
            'room_id' => $this->room_id,
            'is_visible' => (bool)$this->is_visible,
            'product' => new ProductResource($this->product),
            'image_url' => $this->media ? $this->media->getUrl() : $this->image,
            'url' => $this->url,
            'title' => $this->title,
        ];
    }
}
