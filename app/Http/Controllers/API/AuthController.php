<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(AuthRequest $request)
    {
        $user = User::where('username', $request->username)->first();

        $this->matchCredential($user, $request);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->dataByRole($user)
        ]);
    }

    private function matchCredential($user, $request)
    {
        if (!$user || !Hash::check($request->password, $user->password)) {
            abort(401, 'Username atau password salah');
        }
    }

    private function dataByRole($user)
    {
        return match ($user->role) {
            'teacher' => $user->load('teacher'),
            'student' => $user->load('student'),
            default => $user,
        };
    }
}
