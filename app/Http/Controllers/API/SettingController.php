<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends BaseController
{
    public function index(Request $request)
    {
        $setting = Setting::all()
        return $this->sendResponse($setting, 'Settings retrieved successfully.');
    }

    public function update(Request $request, $id)
    {
        $setting = Setting::find($id);
        $setting->update($request->all());
        return $this->sendResponse($setting, 'Settings updated successfully.');
    }
}
