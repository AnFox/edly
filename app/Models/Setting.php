<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Setting
 * @package App\Models
 *
 * @property string $name
 * @property string $type
 * @property string $value
 */
class Setting extends Model
{
    const SETTING_TYPE_STRING = 'string';
    const SETTING_TYPE_INTEGER = 'integer';
    const SETTING_TYPE_FLOAT = 'float';
    const SETTING_TYPE_BOOLEAN = 'boolean';
    const SETTING_TYPE_ARRAY = 'array';
    const SETTING_TYPE_OBJECT = 'object';

    const SETTING_TRIAL_TYPE_SUM = 0;
    const SETTING_TRIAL_TYPE_TIME = 1;

    const SETTING_TRIAL_USERS_THRESHOLD = 5;

    /**
     * @return int
     */
    public static function isTrialTypeTime()
    {
        return self::query()->where('name', Account::SETTING_NAME_TRIAL_TYPE)
            ->where('value', self::SETTING_TRIAL_TYPE_TIME)
            ->count();
    }

    /**
     * @return int
     */
    public static function getTrialDays()
    {
        return self::query()->where('name', Account::SETTING_NAME_TRIAL_DAYS)
            ->pluck('value')->first();
    }

    /**
     * @return int
     */
    public static function getTrialUsersCount()
    {
        return self::query()->where('name', Account::SETTING_NAME_TRIAL_FREE_USERS_COUNT)
            ->pluck('value')->first();
    }
}
