<?php

namespace App\Http\Resources\Admin;

use App\Models\Banner;
use App\Models\Chat;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\MediaLibrary\MediaCollections\MediaCollection;

/**
 * Class RoomResource
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
 * @property boolean $is_bot_assign_required
 * @property string $bot_url_telegram
 * @property string $bot_url_whatsapp
 * @property string $bot_url_viber
 * @property Carbon $starts_at
 * @property boolean $is_started
 * @property integer $duration_minutes
 * @property Carbon $finished_at
 * @property Carbon $scheduled_at
 * @property Carbon $schedule_interval
 * @property boolean $request_record
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read  Chat $chat
 * @property-read  Webinar[] $webinars
 * @property-read  Banner $banners
 * @property-read User[] $visitorsBanned
 * @property-read User[] $visitorsBlocked
 * @property-read  integer $visitorsOnline
 * @property-read User $owner
 * @property-read User[] $visitors
 * @property-read Collection $bannersVisible
 * @property-read boolean $adminable
 * @property-read boolean $moderatable
 * @property-read boolean $canChat
 * @property-read string $fbPixel
 * @property-read boolean $access_allowed
 * @property-read boolean $chat_enabled
 * @property-read bool $has_presentation
 * @property-read bool $presentation
 * @property-read bool $pdf_filename
 * @property-read MediaCollection $slides
 */
class RoomResource extends JsonResource
{
    /**
     * @var bool
     */
    private $displayAmountOnline;

    /**
     * RoomResource constructor.
     * @param $resource
     * @param int $key
     * @param bool $displayAmountOnline
     */
    public function __construct($resource, int $key = 0, bool $displayAmountOnline = true)
    {
        parent::__construct($resource);

        $this->displayAmountOnline = $displayAmountOnline;
    }
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $attributes = [
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
            'is_bot_assign_required' => $this->is_bot_assign_required,
            'bot_url_telegram' => $this->bot_url_telegram,
            'bot_url_whatsapp' => $this->bot_url_whatsapp,
            'bot_url_viber' => $this->bot_url_viber,
            'duration_minutes' => $this->duration_minutes,
            'webinars' => WebinarResource::collection($this->webinars),
            'banners' => BannerResource::collection($this->banners),
            'url' => config('app.url_front') . '/webinar/' . $this->id . '/' . $this->slug,
            'thumbnail' => $this->getFirstMediaUrl(Room::MEDIA_COLLECTION_THUMBNAIL, Room::MEDIA_CONVERSION_WEB),
            'presentation' => $this->has_presentation ? [
                'name' => $this->pdf_filename,
                'slides_total' => $this->slides()->count(),
            ] : null,
            'scheduled_at' => $this->scheduled_at,
            'schedule_interval' => $this->schedule_interval,
            'request_record' => $this->request_record,
        ];

        return $attributes;
    }
}
