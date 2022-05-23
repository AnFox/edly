<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\ChatResource;
use App\Models\Banner;
use App\Models\Chat;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class WebinarResource
 * @package App\Http\Resources
 * @property integer $id
 * @property Carbon $starts_at
 * @property boolean $is_started
 * @property boolean $is_scheduled
 * @property boolean $is_recordable
 * @property Carbon $finished_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Chat $chat
 * @property-read Banner $banners
 * @property-read User[] $visitorsBlocked
 * @property-read integer $visitorsOnline
 * @property-read integer $visitorsSubscribed
 */
class WebinarResource extends JsonResource
{
    /**
     * @var bool
     */
    private $displayAmountOnline;

    /**
     * WebinarResource constructor.
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
            'starts_at' => $this->starts_at,
            'is_started' => (bool)$this->is_started,
            'finished_at' => $this->finished_at,
            'chat_id' => $this->chat ? $this->chat->id : null,
            'chat' => new ChatResource($this->chat),
            'blockedUsers' => BannedUserResource::collection($this->visitorsBlocked),
            'url' => config('app.url_front') . '/webinar/' . $this->id . '/' . $this->slug,
            'amountSubscribed' => $this->visitorsSubscribed,
            'is_recordable' => $this->is_recordable,
        ];

        if ($this->displayAmountOnline) {
            $attributes['amountOnline'] = $this->visitorsOnline;
        }

        return $attributes;
    }
}
