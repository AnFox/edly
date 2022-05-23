<?php

namespace App\Http\Resources;

use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class WebinarPublicResource
 * @package App\Http\Resources
 * @property integer $id
 * @property Carbon $starts_at
 * @property Carbon $finished_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Room $room
 * @property-read bool $enter_allowed
 */
class WebinarPublicResource extends JsonResource
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
            'room_id' => $this->room->id,
            'slug' => $this->room->slug,
            'name' => $this->room->name,
            'description' => $this->room->description,
            'waiting_text' => $this->room->waiting_text,
            'post_webinar_text' => $this->room->post_webinar_text,
            'is_bot_assign_required' => $this->room->is_bot_assign_required,
            'bot_url_telegram' => $this->room->bot_url_telegram,
            'bot_url_whatsapp' => $this->room->bot_url_whatsapp,
            'bot_url_viber' => $this->room->bot_url_viber,
            'starts_at' => $this->starts_at,
            'duration_minutes' => $this->room->duration_minutes,
            'fb_pixel' => $this->room->fbPixel,
            'thumbnail' => $this->room->getFirstMediaUrl(Room::MEDIA_COLLECTION_THUMBNAIL),
            'enter_allowed' => $this->enter_allowed,
        ];
    }
}
