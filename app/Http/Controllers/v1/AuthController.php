<?php

namespace App\Http\Controllers\v1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use DefStudio\Telegraph\Keyboard\ReplyButton;
use DefStudio\Telegraph\Keyboard\ReplyKeyboard;

class AuthController extends Controller
{

    public function login(Request $request): JsonResponse
    {

        $request->validate([
            'phone' => 'required',
            'password' => 'required|string',
        ]);

        $user = User::where('phone', $request->phone)->first();


        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email yoki parol noto‘g‘ri'
            ], 401);
        }

        // Avtorizatsiya qilish (token yaratish)
        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'token' => $token,
            'user' => $user,
        ], "User logged in successfully");

    }

    public function logout(Request $request)
    {
        return $request->user()->token()->revoke();

    }


    public function user(Request $request) : JsonResponse
    {
        return $this->successResponse($request->user(), "User logged in successfully");
    }
}
