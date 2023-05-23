<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Users;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ApiAuthController extends Controller
{
    public function register(Request $request)
    {
        // Validate the Registeration Payload
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return response(['code' => 40001, 'message' => $validator->errors()->all()], 400);
        }
        // Hashing the password to be saved in the database
        $request['password'] = Hash::make($request['password']);
        $request['remember_token'] = Str::random(10);
        $user = Users::create($request->toArray());
        return response(['code' => 20101, 'message' => 'User Created'], 201);
    }
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response(['code' => 40001, 'message' => $validator->errors()->all()], 400);
        }
        $user = Users::where('email', $request->email)->first();
        if ($user) {
            if (Hash::check($request->password, $user->password)) {
                $token = $user->createToken('Laravel Password Grant Client')->plainTextToken;
                $token = explode('|', $token);
                $response = ['code' => 20001, 'token' => $token[1]];
                return response($response, 200);
            } else {
                $response = ['code' => 40001, "message" => "Password mismatch"];
                return response($response, 400);
            }
        } else {
            $response = ['code' => 40001, "message" => 'User does not exist'];
            return response($response, 400);
        }
    }
    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken()->delete();
        $response = ['code' => 20002, 'message' => 'You have been successfully logged out!'];
        return response($response, 200);
    }
}