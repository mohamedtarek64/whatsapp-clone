<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Controller for handling authentication and users.
 */
class AuthController extends Controller
{
    use ApiResponse;

    /**
     * Register a new user.
     */
    public function create(RegisterRequest $request)
    {
        try {
            $user = User::create([
                'name' => $request->validated()['name'],
                'email' => $request->validated()['email'],
                'password' => Hash::make($request->validated()['password']),
            ]);

            $token = $user->createToken('API TOKEN')->plainTextToken;

            return $this->success(
                [
                    'user' => new UserResource($user),
                    'token' => $token,
                ],
                'User registered successfully',
                201
            );
        } catch (\Exception $e) {
            return $this->error(
                'Registration failed',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Login a user.
     */
    public function login(LoginRequest $request)
    {
        if (!Auth::attempt($request->validated())) {
            return $this->error(
                'Invalid email or password',
                401
            );
        }

        $user = User::where('email', $request->validated()['email'])->first();
        $token = $user->createToken('API TOKEN')->plainTextToken;

        return $this->success(
            [
                'user' => new UserResource($user),
                'token' => $token,
            ],
            'User logged in successfully'
        );
    }

    /**
     * Logout the current user.
     */
    public function logout()
    {
        try {
            auth()->user()->tokens()->delete();

            return $this->success(
                null,
                'User logged out successfully'
            );
        } catch (\Exception $e) {
            return $this->error(
                'Logout failed',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }
}
