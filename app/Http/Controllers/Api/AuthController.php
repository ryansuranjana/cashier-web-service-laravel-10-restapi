<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $validate = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required'
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'code' => 400,
                    'status' => 'Bad Request',
                    'errors' => $validate->errors()
                ], 400);
            }

            if (!Auth::attempt($request->only(['email', 'password']))) {
                return response()->json([
                    'code' => 401,
                    'status' => 'Unauthorized.'
                ], 401);
            }

            $user = User::where('email', $request->email)->first();
            return response()->json([
                'code' => 200,
                'status' => 'OK',
                'data' => [
                    'token' => $user->createToken(str()->random(40), [$user->role])->plainTextToken,
                    'user' => $user
                ]
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'code' => 500,
                'status' => 'Internal Server Error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'code' => 200,
            'status' => 'OK',
        ], 200);
    }
}