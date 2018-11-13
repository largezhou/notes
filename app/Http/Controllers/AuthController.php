<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use AuthenticatesUsers;

    public function __construct()
    {
    }

    public function login(Request $request)
    {
        $credentials = $request->only($this->username(), 'password');

        if (!$token = auth()->attempt($credentials)) {
            return $this->sendFailedLoginResponse($request);
        }

        return $this->respondWithToken($token);
    }

    public function username()
    {
        return 'username';
    }

    public function info()
    {
        return UserResource::make(auth()->user());
    }

    public function logout()
    {
        auth()->logout();

        return $this->noContent();
    }

    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'token'      => "bearer {$token}",
            'expires_in' => auth()->factory()->getTTL() * 60,
        ]);
    }
}
