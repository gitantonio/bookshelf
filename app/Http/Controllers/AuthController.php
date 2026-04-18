<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

/**
 * @group Authentication
 */
class AuthController extends Controller
{
    /**
     * Register a new user
     *
     * @unauthenticated
     *
     * @bodyParam name string required The user's full name. Example: Jane Doe
     * @bodyParam email string required Unique email address. Example: jane@example.com
     * @bodyParam password string required The password. Example: Sup3rS3cret!
     * @bodyParam password_confirmation string required Must match password. Example: Sup3rS3cret!
     *
     * @response 201 {"user":{"id":1,"name":"Jane Doe","email":"jane@example.com"},"token":"1|aBcDeFgHiJkLmNoPqRsTuVwXyZ"}
     * @response 422 scenario="Validation failed" {"message":"The email has already been taken.","errors":{"email":["The email has already been taken."]}}
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'token' => $token,
        ], 201);
    }

    /**
     * Log in an existing user
     *
     * @unauthenticated
     *
     * @bodyParam email string required The user's email. Example: jane@example.com
     * @bodyParam password string required The user's password. Example: Sup3rS3cret!
     *
     * @response 200 {"user":{"id":1,"name":"Jane Doe","email":"jane@example.com"},"token":"2|aBcDeFgHiJkLmNoPqRsTuVwXyZ"}
     * @response 422 scenario="Invalid credentials" {"message":"The provided credentials are incorrect.","errors":{"email":["The provided credentials are incorrect."]}}
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user
          || ! Hash::check($validated['password'], $user->password)
        ) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'token' => $token,
        ]);
    }

    /**
     * Log out (current token)
     *
     * Revokes the token used for this request.
     *
     * @authenticated
     *
     * @response 204 {}
     * @response 401 {"message":"Unauthenticated."}
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(null, 204);
    }

    /**
     * Log out from all devices
     *
     * Revokes every token belonging to the authenticated user.
     *
     * @authenticated
     *
     * @response 204 {}
     * @response 401 {"message":"Unauthenticated."}
     */
    public function logoutAll(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(null, 204);
    }

    /**
     * Get the authenticated user
     *
     * @authenticated
     *
     * @response 200 {"id":1,"name":"Jane Doe","email":"jane@example.com"}
     * @response 401 {"message":"Unauthenticated."}
     */
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);
    }
}
