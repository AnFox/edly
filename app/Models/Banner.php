<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Class Banner
 * @package App\Models
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
 * @property-read Room $room
 */
class Banner extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'id',
        'room_id',
        'product_id',
        'is_visible',
        'image',
        'media_id',
        'url',
        'title',
    ];

    /**
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
}
