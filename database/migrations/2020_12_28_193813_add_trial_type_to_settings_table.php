<?php

use App\Models\Account;
use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTrialTypeToSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $setting = new Setting();
        $setting->name = Account::SETTING_NAME_TRIAL_TYPE;
        $setting->type = Setting::SETTING_TYPE_INTEGER;
        $setting->value = Setting::SETTING_TRIAL_TYPE_TIME;
        $setting->save();

        $setting = new Setting();
        $setting->name = Account::SETTING_NAME_TRIAL_DAYS;
        $setting->type = Setting::SETTING_TYPE_INTEGER;
        $setting->value = 30;
        $setting->save();

        $setting = new Setting();
        $setting->name = Account::SETTING_NAME_TRIAL_FREE_USERS_COUNT;
        $setting->type = Setting::SETTING_TYPE_INTEGER;
        $setting->value = 20;
        $setting->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Setting::where('name', Account::SETTING_NAME_TRIAL_TYPE)->delete();
        Setting::where('name', Account::SETTING_NAME_TRIAL_DAYS)->delete();
        Setting::where('name', Account::SETTING_NAME_TRIAL_FREE_USERS_COUNT)->delete();
    }
}
