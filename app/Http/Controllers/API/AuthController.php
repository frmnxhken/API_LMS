<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Fragment\RoutableFragmentRenderer;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Username atau password salah'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;


        if ($user->role === 'teacher') {
            $user->teacher;
        } elseif ($user->role === 'student') {
            $user->student;
        }

        return response()->json([
            'token' => $token,
            'user' => $user
        ]);
    }
}
