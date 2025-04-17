<?php

namespace App\Http\Controllers;

use App\Models\InstitusiUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Username atau Password salah! Silakan coba kembali.'], 400);
        }

        $user = JWTAuth::user();
        $token = JWTAuth::fromUser($user, [
            'kode_pegawai' => $user->kode_pegawai,
            // 'category' => $user->category
        ]);
        $refreshToken = $this->generateRefreshToken($user);

        return $this->respondWithToken($token, $refreshToken);
    }

    public function logout()
    {
        auth('api')->logout();
    }

    public function refresh(Request $request)
    {
        $refreshToken = $request->input('refresh_token');
        $user = User::where('refresh_token', $refreshToken)->first();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Extend the expiration time for the token
        $customClaims = [
            'category' => $user->category ?? null,
            'kode_pegawai' => $user->kode_pegawai,
        ];

        // Set a longer expiration time (e.g., 2 hours instead of the default)
        JWTAuth::factory()->setTTL(120); // TTL in minutes
        $token = JWTAuth::fromUser($user, $customClaims);

        // Generate a new refresh token
        $newRefreshToken = $this->generateRefreshToken($user);

        return $this->respondWithToken($token, $newRefreshToken);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'kode_pegawai' => ['required', 'string', 'unique:users'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'img_profile' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ]);

        $path = null;
        if ($request->hasFile('img_profile')) {
            $path = $request->file('img_profile')->store('profiles');
        }

        $user = User::create([
            'kode_pegawai' => $validated['kode_pegawai'],
            'name' => $validated['name'],
            // 'category' => $validated['category'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'img_profile' => $path,
        ]);

        return response()->json([
            'message' => 'Akun user berhasil dibuat!',
        ], 201);
    }


    public function getProfileImage($filename)
    {
        if (!Storage::exists("profiles/$filename")) {
            return response()->json(['error' => 'File not found.'], 404);
        }

        $file = Storage::get("profiles/$filename");
        $mimetype = Storage::mimeType("profiles/$filename");

        return response($file, Response::HTTP_OK, [
            'Content-Type' => $mimetype,
        ]);
    }

    protected function respondWithToken($token, $refreshToken)
    {
        return response()->json([
            'token' => $token,
            'expires_in' => JWTAuth::factory()->getTTL(120) * 60, # valid for 2 hours
            'refresh_token' => $refreshToken
        ]);
    }

    private function generateRefreshToken($user)
    {
        $refreshToken = Str::random(60);

        $user->refresh_token = $refreshToken;
        $user->save();

        return $refreshToken;
    }

}