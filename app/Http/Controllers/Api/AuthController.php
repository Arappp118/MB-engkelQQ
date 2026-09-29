<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Login via email+password. Role SELALU dari database (User::role),
     * tidak pernah dari input request — request cuma kirim email/password.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        if (!Auth::attempt($credentials)) {
            return $this->error('Email atau password salah.', null, 401);
        }

        $user = User::where('email', $credentials['email'])->firstOrFail();

        if (!$user->is_active) {
            return $this->error('Akun tidak aktif.', null, 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return $this->success('Login berhasil', [
            'user'  => new UserResource($user),
            'token' => $token,
        ]);
    }

    /**
     * Revoke HANYA token yang sedang dipakai request ini, bukan semua
     * token milik user (supaya sesi device lain tidak ikut ke-logout).
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success('Logout berhasil');
    }

    public function me(Request $request): JsonResponse
    {
        return $this->success('Data user', new UserResource($request->user()));
    }
}
