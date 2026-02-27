<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    /**
     * جلب كل الإعدادات (مع استخدام الكاش لتقليل الضغط على الداتا بيز)
     */
    public function getAllSettings()
    {
        return Cache::rememberForever('app_settings', function () {
            return Setting::pluck('value', 'key')->toArray();
        });
    }


    public function updateSettings(array $settingsData)
    {
        foreach ($settingsData as $setting) {
            Setting::where('key', $setting['key'])->update(['value' => $setting['value']]);
        }

        Cache::forget('app_settings');

        return $this->getAllSettings();
    }
}
