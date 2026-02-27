<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateSettingRequest;
use App\Services\SettingService;

class SettingController extends Controller
{
    protected $settingService;

    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    public function index()
    {
        $settings = $this->settingService->getAllSettings();
        return $this->successResponse($settings, 'Settings retrieved successfully.');
    }

    public function update(UpdateSettingRequest $request)
    {
        $updatedSettings = $this->settingService->updateSettings($request->validated('settings'));
        return $this->successResponse($updatedSettings, 'Settings updated successfully.');
    }
}
