<?php

use App\Models\Account;
use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Class SettingsSeeder
 */
class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $setting = new Setting();
        $setting->name = Account::SETTING_NAME_TRIAL_AMOUNT;
        $setting->type = Setting::SETTING_TYPE_INTEGER;
        $setting->value = env('TRIAL_AMOUNT', 100);
        $setting->save();
    }
}
