<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SettingRequest;
use App\Models\Setting;

class SettingController extends Controller
{
    public function show()
    {
        $data = Setting::first();
        return response()->json($data);
    }

    public function upsert(SettingRequest $request)
    {
        $validatedData = $request->only(['latitude', 'longitude', 'radius', 'start_time']);
        Setting::updateOrCreate(['id' => 1], $validatedData);

        return response()->json(['message' => 'success'], 201);
    }
}
