<?php

namespace App\Http\Resources;

use App\Models\Banner;
use App\Models\Chat;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class RoomWebsocketResource
 * @package App\Http\Resources
 * @property integer $id
 * @property integer $type_id
 * @property boolean $is_published
 * @property integer $user_id
 * @property string $slug
 * @property string $name
 * @property string $description
 * @property string $waiting_text
 * @property string $post_webinar_text
 * @property string $video_id
 * @property string $video_src
 * @property Carbon $starts_at
 * @property Carbon $finished_at
 * @property integer $duration_minutes
 * @property integer $users_online
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Chat $chat
 * @property-read Banner $banners
 * @property-read integer $visitorsOnline
 * @property-read Collection $bannersVisible
 * @property-read boolean $adminable
 * @property-read boolean $moderatable
 */
class RoomWebsocketResource extends JsonResource
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
            'type_id' => $this->type_id,
            'is_published' => (bool)$this->is_published,
            'author_user_id' => $this->user_id,
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description,
            'waiting_text' => $this->waiting_text,
            'post_webinar_text' => $this->post_webinar_text,
            'video_id' => $this->video_id,
            'video_src' => $this->video_src,
            'starts_at' => $this->starts_at,
            'finished_at' => $this->finished_at,
            'duration_minutes' => $this->duration_minutes,
            'chat_id' => $this->chat ? $this->chat->id : null,
            'chat' => new ChatResource($this->chat),
            'banners' => BannerResource::collection($this->banners),
            'url' => config('app.url_front') . '/webinar/' . $this->id . '/' . $this->slug,
            'thumbnail' => $this->getFirstMediaUrl(Room::MEDIA_COLLECTION_THUMBNAIL, Room::MEDIA_CONVERSION_WEB)
        ];
    }
}
