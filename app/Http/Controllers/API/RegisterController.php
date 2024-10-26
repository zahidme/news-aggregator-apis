<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;

class RegisterController extends BaseController
{
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => 'required|string|min:8|confirmed', // Ensure this checks for confirmation
            // 'c_password' is not needed as we use 'confirmed'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        if (User::where('email', $request->email)->exists()) {
            return $this->sendError('Registration Error.', ['email' => 'This email is already registered.']);
        }

        try {
            $input = $request->all();
            $input['password'] = bcrypt($input['password']);
            $user = User::create($input);

            $success['token'] = $user->createToken('MyApp')->plainTextToken;
            $success['name'] = $user->name;

            return $this->sendResponse($success, 'User registered successfully.');
        } catch (QueryException $e) {
            // Handle database errors
            return $this->sendError('Database Error.', ['error' => 'Unable to register user. Please try again later.']);
        } catch (\Exception $e) {
            // Handle other exceptions
            return $this->sendError('Unexpected Error.', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return $this->sendError('Login Error.', ['error' => 'User with this email does not exist.']);
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $success['token'] = $user->createToken('MyApp')->plainTextToken;
            $success['name'] = $user->name;

            return $this->sendResponse($success, 'User login successfully.');
        } else {
            return $this->sendError('Unauthorized.', ['error' => 'Invalid credentials.']);
        }
    }

    public function logout(Request $request)
    {
        if (!$request->user()) {
            return $this->sendError('Unauthorized.', ['error' => 'User is not authenticated.']);
        }

        try {
            $request->user()->tokens()->delete();

            return response()->json(['message' => 'Successfully logged out'], 200);
        } catch (\Exception $e) {
            return $this->sendError('Logout Error.', ['error' => 'An error occurred while logging out. Please try again later.']);
        }
    }
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'No user found with this email.'], 422);
        } else {
            return response()->json(['message' => 'Password reset successfully.'], 422);
        }

    }
}
