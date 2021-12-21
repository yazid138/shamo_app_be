<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Rules\Password;

class UserController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'username' => 'required|string|max:255|unique:users',
                'email' => 'required|string|email:dns|max:255|unique:users',
                'phone_number' => 'string|max:15',
                'password' => ['required', 'string', new Password]
            ]);

            $validated['password'] = Hash::make($validated['password']);
            User::create($validated);

            $user = User::where('email', $validated['email'])->first();

            $tokenResult = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success(
                [
                    'access_token' => $tokenResult,
                    'token_type' => 'Bearer',
                    'user' => $user
                ],
                'Berhasil register user'
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something went wrong',
                    'error' => $error->validator->errors()
                ],
                'Gagal register user',
                500
            );
        }
    }

    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|string|email:dns',
                'password' => 'required|string'
            ]);

            if (!Auth::attempt($validated)) {
                return ResponseFormatter::error(
                    [
                        'message' => 'Unauthorized',
                    ],
                    'Authentication Failed',
                    500
                );
            }

            $user = User::where('email', $validated['email'])->first();

            if (!Hash::check($validated['password'], $user->password)) {
                throw new Exception('Invalid Credentials');
            }

            $tokenResult = $user->createToken('authToken')->plainTextToken;
            return ResponseFormatter::success(
                [
                    'access_token' => $tokenResult,
                    'token_type' => 'Bearer',
                    'user' => $user,
                ],
                'Authenticated'
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something went wrong',
                    'error' => $error
                ],
                'Authentication Failed',
                500
            );
        }
    }

    public function fetch(Request $request)
    {
        return ResponseFormatter::success(
            Auth::user(),
            'Data profile user berhasil diambil'
        );
    }

    public function updateProfile(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'string|max:255',
                'username' => 'string|max:255|unique:users',
                'email' => 'string|email:dns|max:255|unique:users',
                'phone_number' => 'string|max:15',
            ]);

            $user = $request->user();
            $user->update($validated);

            return ResponseFormatter::success(
                $user,
                'Profile updated'
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something went wrong',
                    'error' => $error->validator->errors()
                ],
                'Update Failed',
                400
            );
        }
    }

    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken()->delete();

        return ResponseFormatter::success(
            $token,
            'Token revoked'
        );
    }
}
