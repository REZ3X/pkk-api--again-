<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,basic',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'status' => true,
            'message' => 'User registered successfully',
            'token' => $token,
            'user' => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['status' => false, 'message' => 'Invalid credentials'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['status' => false, 'message' => 'Could not create token'], 500);
        }

        return response()->json([
            'status' => true,
            'token' => $token,
            'user' => Auth::user(),
        ]);
    }

    public function me()
    {
        return response()->json([
            'status' => true,
            'user' => Auth::user(),
        ]);
    }

    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
        } catch (\Exception $e) {
            // ignore
        }

        return response()->json(['status' => true, 'message' => 'Successfully logged out']);
    }
}
