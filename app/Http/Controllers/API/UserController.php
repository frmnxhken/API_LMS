<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\ChangeProfilePhotoRequest;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function changePassword(ChangePasswordRequest $request)
    {
        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'errors' => [
                    'current_password' => ["Password saat ini tidak sesuai"]
                ],
            ], 422);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return response()->json(['message' => 'success']);
    }

    public function changePhoto(ChangeProfilePhotoRequest $request)
    {
        $user = $request->user();
        $file = $request->file('photo');

        $this->deleteOldPhoto($user->photo);

        $filename = $this->storePhoto($file);
        $user->update(['photo' => $filename]);

        return response()->json([
            'message' => 'success',
            'photo_url' => $filename,
        ]);
    }

    private function storePhoto($file): string
    {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('profiles'), $filename);

        return $filename;
    }

    private function deleteOldPhoto(?string $photo): void
    {
        if (!$photo) return;

        $path = public_path('profiles/' . $photo);

        if (File::exists($path)) {
            File::delete($path);
        }
    }
}
