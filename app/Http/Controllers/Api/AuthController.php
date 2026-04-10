<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:6',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // 🔥 مهم جداً: إنشاء توكن مثل login
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'تم إنشاء الحساب',
                'token' => $token,
                'user' => $user
            ]);

        } catch (\Exception $e) {
            \Log::error($e->getMessage());

            return response()->json([
                'message' => 'خطأ بالسيرفر ❌',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function login(Request $request)
    {
        try {
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'message' => 'بيانات غلط'
                ], 401);
            }

            // 🔥 إنشاء Token حقيقي
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'تم تسجيل الدخول',
                'token' => $token,
                'user' => $user
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'خطأ بالسيرفر ❌',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // تسجيل خروج
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'تم تسجيل الخروج'
        ]);
    }

    public function deleteAccount(Request $request)
    {
        $user = $request->user();

        $user->tokens()->delete(); // حذف كل التوكنات
        $user->delete();

        return response()->json([
            'message' => 'Account deleted'
        ]);
    }
}