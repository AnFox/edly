<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Script
 * @package App\Model
 *
 * @property-read Room $room
 */
class Script extends Model
{
    const ACTION_START_RECORD = 'startRecord';
    const ACTION_STOP_RECORD = 'stopRecord';
    const ACTION_START_STREAM = 'startStream';
    const ACTION_STOP_STREAM = 'stopStream';
    const ACTION_WEBINAR_LAYOUT = 'setWebinarLayout';
    const ACTION_WEBINAR_TAB = 'setWebinarTab';
    const ACTION_SET_PRESENTATION_PAGE = 'setCurrentSlide';
    const ACTION_POST_MESSAGE = 'postMessage';
    const ACTION_POST_BANNER = 'postBanner';
    const ACTION_CHAT_BLOCK = 'chatBlock';
    const ACTION_CHAT_UNBLOCK = 'chatUnblock';

    const ACCEPTABLE_MIME = [
        'application/json',
        'text/plain',
    ];

    protected $guarded = [];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
