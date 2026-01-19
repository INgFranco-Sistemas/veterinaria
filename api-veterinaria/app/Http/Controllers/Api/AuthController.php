<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct()
    {
        // Protege todo excepto login
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    public function login(Request $request)
        {
            $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // intenta autenticar y generar token
        if (! $token = Auth::guard('api')->attempt($credentials)) {
            return response()->json(['message' => 'Credenciales inválidas'], 401);
        }

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
            'user' => Auth::guard('api')->user(),
        ]);
    }

    public function me()
    {
        $user = Auth::guard('api')->user();

        return response()->json([
            'user' => $user,
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ]);
    }
    
    public function logout()
    {
        Auth::guard('api')->logout();

        return response()->json([
            'message' => 'Sesión cerrada correctamente.',
        ]);
    }

    public function refresh()
    {
        $token = Auth::guard('api')->refresh();

        return $this->respondWithToken($token);
    }

    protected function respondWithToken(string $token)
    {
        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
            'user' => Auth::guard('api')->user(),
        ]);
    }
}
