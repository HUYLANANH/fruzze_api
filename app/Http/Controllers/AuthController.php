<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('name', 'password');
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        return response()->json(compact('token'));
    }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function profile()
    {
        return response()->json(auth('api')->User());
    }
    public function registerUser(Request $request)
    {
        if (User::where('name', $request->name)->exists()) {
            return response()->json(['error' => 'Username đã tồn tại'], 400);
        }
        if (User::where('email', $request->email)->exists()) {
            return response()->json(['error' => 'Email đã tồn tại'], 400);
        }
        
        // Tạo người dùng với role_id là 2 (user)
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email, 
            'password' => Hash::make($request->password),
            'role_id' => 2,
        ]);
    
        return response()->json(['user' => $user], 201);
    }

    public function registerAdmin(Request $request)
    {
        // Kiểm tra quyền đăng ký admin (chỉ admin mới có thể tạo tài khoản admin)
        if (auth('api')->User()->role_id != 1) {
            return response()->json(['error' => 'Không có quyền tạo admin, ktra lại thông tin người dùng trong data'], 403);
        }

        if (User::where('name', $request->name)->exists()) {
            return response()->json(['error' => 'Username đã tồn tại'], 400);
        }
        if (User::where('email', $request->email)->exists()) {
            return response()->json(['error' => 'Email đã tồn tại'], 400);
        }

        // Tạo người dùng với role_id là 1 (admin)
        $admin = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => 1, 
        ]);

        return response()->json(['user' => $admin], 201);
    }
}
