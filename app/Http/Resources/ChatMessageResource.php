<?php

namespace App\Http\Resources;

use App\Models\Banner;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class ChatMessageResource
 * @package App\Http\Resources
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
 * @property-read Banner $banner
 * @property-read User $sender
 * @property-read User $recipient
 * @property-read string $sender_name
 * @property-read string $recipient_name
 */
class ChatMessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'text' => $this->message,
            'author' => $this->sender_name,
            'sender_user_id' => $this->sender_user_id,
            'recipient_user_id' => $this->recipient_user_id,
            'recipient_name' => $this->recipient_name,
            'date' => $this->created_at,
            'is_banner' => (bool)$this->banner_id,
            'banner' => $this->banner ? new BannerResource($this->banner) : null,
        ];
    }
}
