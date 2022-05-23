<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Chat
 * @package App\Models
 *
 * @property integer $id
 * @property integer $webinar_id
 * @property boolean $is_active
 *
 * @property-read  Webinar $webinar
 */
class Chat extends Model
{
    protected $guarded = [];

    /**
     * @return BelongsTo
     */
    public function webinar(): BelongsTo
    {
        return $this->belongsTo(Webinar::class);
    }
}
