<?php

namespace App\Http\Resources;

use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Class WebinarWebsocketResource
 * @package App\Http\Resources
 *
 * @property integer $id
 * @property integer $room_id
 * @property boolean $is_started
 * @property Carbon $starts_at
 * @property Carbon $finished_at
 * @property string $layout
 * @property integer $current_slide_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Room $room
 * @property-read Media $current_slide
 */
class WebinarWebsocketResource extends JsonResource
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
            'starts_at' => $this->starts_at,
            'finished_at' => $this->finished_at,
            'layout' => $this->layout,
            'current_slide_id' => $this->current_slide_id,
            'type_id' => $this->room->type_id,
            'is_published' => (bool)$this->room->is_published,
            'author_user_id' => $this->room->user_id,
            'slug' => $this->room->slug,
            'name' => $this->room->name,
            'description' => $this->room->description,
            'waiting_text' => $this->room->waiting_text,
            'post_webinar_text' => $this->room->post_webinar_text,
            'video_id' => $this->room->video_id,
            'video_src' => $this->room->video_src,
            'is_bot_assign_required' => $this->room->is_bot_assign_required,
            'bot_url_telegram' => $this->room->bot_url_telegram,
            'bot_url_whatsapp' => $this->room->bot_url_whatsapp,
            'bot_url_viber' => $this->room->bot_url_viber,
            'is_started' => (bool)$this->is_started,
            'duration_minutes' => $this->room->duration_minutes,
            'chat_id' => $this->chat ? $this->chat->id : null,
            'chat' => new ChatResource($this->chat),
            'banners' => BannerResource::collection($this->room->banners),
            'amountOnline' => $this->visitorsOnline,
            'url' => config('app.url_front') . '/webinar/' . $this->room->id . '/' . $this->room->slug,
            'thumbnail' => $this->room->getFirstMediaUrl(Room::MEDIA_COLLECTION_THUMBNAIL, Room::MEDIA_CONVERSION_WEB),
            'presentation' => $this->room->has_presentation ? [
                'layout' => $this->layout,
                'slide' => new SlideResource($this->current_slide),
            ] : null,
        ];
    }
}
