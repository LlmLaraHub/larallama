<?php

namespace App\Http\Controllers;

use App\Http\Resources\SettingResource;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function show()
    {
        $setting = Setting::createNewSetting();

        return inertia('Settings/Show', [
            'setting' => new SettingResource($setting),
        ]);
    }

    public function updateClaude(Request $request, Setting $setting)
    {
        $validated = $request->validate([
            'api_key' => 'string|required',
        ]);

        $secrets = $setting->secrets;
        $secrets['claude'] = $validated;
        $setting->secrets = $secrets;
        $setting->save();
        $setting->updateStep($setting);

        return back();
    }

    public function updateOllama(Request $request, Setting $setting)
    {
        $validated = $request->validate([
            'api_key' => 'string|nullable',
            'api_url' => 'string|nullable',
        ]);

        $secrets = $setting->secrets;
        $secrets['ollama'] = $validated;
        $setting->secrets = $secrets;
        $setting->save();
        $setting->updateStep($setting);

        return back();
    }

    public function updateGroq(Request $request, Setting $setting)
    {
        $validated = $request->validate([
            'api_key' => 'string|required',
            'api_url' => 'string|required',
        ]);

        $secrets = $setting->secrets;
        $secrets['groq'] = $validated;
        $setting->secrets = $secrets;
        $setting->save();
        $setting->updateStep($setting);

        return back();
    }

    public function updateOpenAi(Request $request, Setting $setting)
    {
        $validated = $request->validate([
            'api_key' => 'string|required',
            'api_url' => 'string|required',
            'organization' => 'string|nullable',
            'request_timeout' => 'nullable',
        ]);

        $secrets = $setting->secrets;
        $secrets['openai'] = $validated;
        $setting->secrets = $secrets;
        $setting->save();
        $setting->updateStep($setting);

        return back();
    }
}
