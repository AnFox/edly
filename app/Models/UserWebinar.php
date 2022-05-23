<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Class UserWebinar
 * @package App\Models
 */
class UserWebinar extends Pivot
{
    protected $dates = [
        'joined_at',
        'left_at'
    ];

    public function webinar()
    {
        return $this->belongsTo(Webinar::class, 'webinar_id');
    }
}
