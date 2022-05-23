<?php

namespace App\Models;

use App\Traits\OptionsTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class Account
 *
 * @package App\Models
 * @property int $id
 * @property string $name
 * @property boolean $has_card
 * @property string $payment_token
 * @property float $balance
 * @property int $status
 * @property string $options
 * @property string $trial_ends_at
 * @property Carbon $created_at
 *
 * @property-read User[] $users
 * @property-read boolean $is_limited
 * @property-read string $card_type
 * @property-read string $card_last_four
 */
class Account extends Model
{
    use OptionsTrait;

    const STATUS_SUSPENDED = 1;
    const STATUS_ACTIVE = 2;
    const STATUS_BLOCKED = 3;

    const COST_PER_USER = 3;

    const SETTING_NAME_TRIAL_AMOUNT = 'trialAmount';
    const SETTING_NAME_TRIAL_TYPE = 'trialType';
    const SETTING_NAME_TRIAL_DAYS = 'trialDays';
    const SETTING_NAME_TRIAL_FREE_USERS_COUNT = 'trialFreeUsersCount';

    protected $fillable = [
        'name',
        'has_card',
        'payment_token',
        'balance',
        'status',
        'options',
    ];

    /**
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot(['role_id'])->withTimestamps();
    }

    /**
     * @return bool
     */
    public function getIsLimitedAttribute():bool
    {
        return $this->status !== self::STATUS_ACTIVE;
    }

    /**
     * @return string|null
     */
    public function getCardTypeAttribute()
    {
        return $this->getOption('card.type');
    }

    /**
     * @return string|null
     */
    public function getCardLastFourAttribute()
    {
        return $this->getOption('card.lastFourDigits');
    }

    public function isTrialActive(): bool
    {
        return Carbon::now()->lte($this->trial_ends_at);
    }

    public function finishTrial()
    {
        if (Setting::isTrialTypeTime() && $this->trial_ends_at && $this->isTrialActive()) {
            $this->trial_ends_at = now();
            $this->save();
        }
    }
}
