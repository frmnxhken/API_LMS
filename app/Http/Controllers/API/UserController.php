<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\ChangeProfilePhotoRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function changePassword(ChangePasswordRequest $request)
    {
        $user = Auth::user();

        if (!Hash::check(
            $request->current_password,
            $user->password
        )) {
            return response()->json([
                'errors' => [
                    'current_password' => ["Password saat ini tidak sesuai"]
                ],
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'Password berhasil diubah',
        ]);
    }

    public function changePhoto(ChangeProfilePhotoRequest $request)
    {
        $user = Auth::user();

        if ($user->photo && File::exists(public_path($user->photo))) {
            File::delete(public_path($user->photo));
        }

        $file = $request->file('photo');

        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();

        $file->move(
            public_path('profiles'),
            $filename
        );

        $user->update([
            'photo' => $filename,
        ]);

        return response()->json([
            'message' => 'success',
            'photo_url' =>  $filename,
        ]);
    }
}
