<?php

namespace App\Models;

use App\Traits\ClearsResponseCache;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ChatMessage
 * @package App\Models
 *
 * @property integer $id
 * @property integer $chat_id
 * @property integer $banner_id
 * @property integer $sender_user_id
 * @property integer $recipient_user_id
 * @property string $message
 * @property Carbon $deleted_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Chat $chat
 * @property-read Banner $banner
 * @property-read User $sender
 * @property-read User $recipient
 * @property-read string $sender_name
 * @property-read string $recipient_name
 */
class ChatMessage extends Model
{
    use SoftDeletes;
    use ClearsResponseCache;

    protected $guarded = [];

    /**
     * @return BelongsTo
     */
    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    /**
     * @return BelongsTo
     */
    public function banner(): BelongsTo
    {
        return $this->belongsTo(Banner::class);
    }

    /**
     * @return BelongsTo
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    /**
     * @return BelongsTo
     */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    public function getSenderNameAttribute()
    {
        return $this->is_fake ? $this->fake_sender_user_name : $this->sender->name;
    }

    public function getRecipientNameAttribute()
    {
        return $this->is_fake ? null : ($this->recipient ? $this->recipient->name : null);
    }
}
