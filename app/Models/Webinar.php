<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Class Webinar
 * @package App\Models
 *
 * @property integer $id
 * @property integer $room_id
 * @property boolean $is_started
 * @property boolean $is_scheduled
 * @property boolean $is_recordable
 * @property boolean $is_limit_reached_notified
 * @property boolean $is_limit_reaching_notified
 * @property Carbon $starts_at
 * @property Carbon $finished_at
 * @property string $layout
 * @property string $tab
 * @property integer $current_slide_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Room $room
 * @property-read Chat $chat
 * @property-read User[] $visitors
 * @property-read int $visitorsOnline
 * @property-read int $visitorsSubscribed
 * @property-read User[] $visitorsActive
 * @property-read User[] $visitorsBanned
 * @property-read User[] $visitorsBlocked
 * @property-read User[] $visitorsByIdList
 * @property-read User[] $visitorsUnpaid
 * @property-read Media $current_slide
 * @property-read bool $enter_allowed
 */
class Webinar extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $guarded = [];

    protected $dates = [
        'starts_at',
        'finished_at',
    ];

    /**
     * @return HasOne
     */
    public function chat(): HasOne
    {
        return $this->hasOne(Chat::class);
    }

    /**
     * @return BelongsTo
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * @return BelongsTo
     */
    public function current_slide(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'current_slide_id');
    }

    /**
     * Users who visited this webinar
     *
     * @return BelongsToMany
     */
    public function visitors(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot(['is_user_online'])->withTimestamps();
    }

    /**
     * Users who visited this webinar
     *
     * @param array $idList
     * @return BelongsToMany
     */
    public function visitorsByIdList(array $idList): BelongsToMany
    {
        return $this->belongsToMany(User::class)->whereIn('user_id', $idList);
    }

    /**
     * Not blocked and not banned webinar visitors
     *
     * @return BelongsToMany
     */
    public function visitorsActive(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->wherePivot('user_status', User::STATUS_ACTIVE)->withTimestamps();
    }

    /**
     * Blocked webinar visitors
     *
     * Read-only access to webinar chat
     *
     * @return BelongsToMany
     */
    public function visitorsBlocked(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->wherePivot('user_status', User::STATUS_CHAT_BLOCKED)->withTimestamps();
    }

    /**
     * Banned webinar visitors
     *
     * Banned access to webinar
     *
     * @return BelongsToMany
     */
    public function visitorsBanned(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->wherePivot('user_status', User::STATUS_BANNED)->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function visitorsUnpaid(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->wherePivot('is_paid', 0)
            ->wherePivot('is_charging', 0)
            ->where(function (Builder $query) {
                $query->where('user_webinar.joined_at', '>=', $this->starts_at);
                $query->orWhere('user_webinar.left_at', '>=', $this->starts_at);
            })
            ->withTimestamps();
    }

    /**
     * Online users counter
     *
     * @return integer
     */
    public function getVisitorsOnlineAttribute(): int
    {
        $count = $this->belongsToMany(User::class)->wherePivot('is_user_online', true)->count();

        return $this->room->type_id === Room::TYPE_AUTO ? $count + $this->room->commands()->count() : $count;
    }

    /**
     * Online users counter
     *
     * @return integer
     */
    public function getVisitorsSubscribedAttribute(): int
    {
        return $this->belongsToMany(User::class)->count();
    }

    /**
     * @return bool
     */
    public function getChatEnabledAttribute()
    {
        return $this->chat->is_active && Auth::user()->can('post-message', $this);
    }

    /**
     * @param Builder $query
     * @param string $type
     * @return Builder
     */
    public function scopeType(Builder $query, string $type): Builder
    {
        switch ($type) {
            case 'current':
                return $query->whereNull('finished_at');
                break;
            case 'completed':
                return $query->whereNotNull('finished_at');
                break;
        }
    }

    public function getEnterAllowedAttribute(): bool
    {
        if (Setting::isTrialTypeTime()) {
            if (!$this->isTrialUsersLimitReached()) {
                return true;
            }

            /** @var User $user */
            $user = Auth::user();

            if ($this->getOwner()->account->balance >= Account::COST_PER_USER) {
                return true;
            }

            if ($user && $this->visitors()->where('user_id', $user->id)->withPivot(['is_paid'])->where('is_paid', 1)->count()) {
                return true;
            }

            return false;
        }

        return true;
    }

    public function isTrialUsersLimitReached(): bool
    {
        if (Setting::isTrialTypeTime()) {
            $owner = $this->getOwner();

            if ($owner->account->isTrialActive() && $this->visitorsSubscribed < Setting::getTrialUsersCount()) {
                return false;
            }

            return true;
        }

        return false;
    }

    public function isTrialUsersLimitAlmostReached(): bool
    {
        if (Setting::isTrialTypeTime()) {
            $owner = $this->getOwner();

            if ($owner->account->isTrialActive() &&
                $this->visitorsSubscribed == Setting::getTrialUsersCount() - Setting::SETTING_TRIAL_USERS_THRESHOLD) {
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * @return User
     */
    public function getOwner()
    {
        return $this->room->owner;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->room->name;
    }
}
