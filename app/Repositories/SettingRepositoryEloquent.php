<?php

namespace App\Repositories;

use App\Contracts\Repositories\SettingRepository;
use App\Models\Setting;

/**
 * Class SettingRepositoryEloquent
 * @package App\Repositories
 */
class SettingRepositoryEloquent extends BaseRepositoryEloquent implements SettingRepository
{
    /** @var Setting */
    protected $model;

    /**
     * SettingRepositoryEloquent constructor.
     * @param Setting $model
     */
    public function __construct(Setting $model)
    {
        $this->setModel($model);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getValueByName(string $name)
    {
        /** @var Setting $setting */
        $setting = $this->model->where('name', $name)->first();

        return $setting ? $this->getValue($setting) : null;
    }

    /**
     * @param Setting $setting
     * @return bool|float|int|mixed|string
     */
    protected function getValue(Setting $setting)
    {
        switch ($setting->type) {
            case Setting::SETTING_TYPE_STRING:
                return $setting->value;
                break;
            case Setting::SETTING_TYPE_INTEGER:
                return intval($setting->value);
                break;
            case Setting::SETTING_TYPE_FLOAT:
                return floatval($setting->value);
                break;
            case Setting::SETTING_TYPE_BOOLEAN:
                return boolval($setting->value);
                break;
            case Setting::SETTING_TYPE_ARRAY:
                return json_decode($setting->value, true);
                break;
            case Setting::SETTING_TYPE_OBJECT:
                return json_decode($setting->value);
                break;
            default:
                return $setting->value;
                break;
        }
    }
}
