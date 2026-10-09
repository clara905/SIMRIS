<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\AuditLog;
use App\Services\CaptchaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(private CaptchaService $captcha) {}

    public function captcha(): JsonResponse
    {
        return response()->json(['data' => $this->captcha->make()]);
    }

    public function login(LoginRequest $request): UserResource
    {
        // Captcha dicek lebih dulu & sekali pakai, jadi tiap percobaan butuh captcha baru
        if (! $this->captcha->verify($request->input('captcha_key'), $request->input('captcha'))) {
            throw ValidationException::withMessages([
                'captcha' => 'Captcha salah atau sudah kedaluwarsa.',
            ]);
        }

        $credentials = [
            'username'  => $request->input('username'),
            'password'  => $request->input('password'),
            'is_active' => true,
        ];

        if (! Auth::guard('web')->attempt($credentials)) {
            throw ValidationException::withMessages([
                'username' => 'Username atau password salah.',
            ]);
        }

        $request->session()->regenerate();

        $user = Auth::guard('web')->user();
        AuditLog::record('login', 'users', $user->users_id);

        return new UserResource($user);
    }

    public function me(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    public function logout(Request $request): Response
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->noContent();
    }
}