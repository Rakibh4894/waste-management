<?php

namespace App\Http\Controllers;

use App\Models\UserConfig;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;

class UserConfigController extends Controller
{
    public function toggleDarkMode(Request $request)
    {

        $user_id = Auth::id();
        $darkMode = UserConfig::where('user_id', $user_id)
            ->where('type', 'dark_mode_on_off')
            ->first();

        if ($darkMode) {
            $darkMode->value = $darkMode->value == 1 ? 0 : 1;
        } else {
            $darkMode = new UserConfig();
            $darkMode->user_id = $user_id;
            $darkMode->type = 'dark_mode_on_off';
            $darkMode->value = 1;
        }

        $darkMode->save();

        return response()->json([
            'status' => 'success',
            'value' => $darkMode->value
        ]);
    }

    public function fullScreenMode(Request $request)
    {

        $user_id = Auth::id();
        $fullScreenMode = UserConfig::where('user_id', $user_id)
            ->where('type', 'full_screen_on_off')
            ->first();

        if ($fullScreenMode) {
            $fullScreenMode->value = $fullScreenMode->value == 1 ? 0 : 1;
        } else {
            $fullScreenMode = new UserConfig();
            $fullScreenMode->user_id = $user_id;
            $fullScreenMode->type = 'full_screen_on_off';
            $fullScreenMode->value = 1;
        }

        $fullScreenMode->save();

        return response()->json([
            'status' => 'success',
            'value' => $fullScreenMode->value
        ]);
    }
}
