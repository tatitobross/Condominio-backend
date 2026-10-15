<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate(['username' => ['required', 'string'], 'password' => ['required', 'string'], 'device_name' => ['nullable', 'string', 'max:100']]);
        $user = \App\Models\User::with('role')->where('username', $data['username'])->first();
        if (! $user || ! Hash::check($data['password'], $user->password)) return response()->json(['message' => 'Credenciales inválidas.'], 401);
        if ($user->status !== 'active') return response()->json(['message' => 'La cuenta está inactiva.'], 403);
        return response()->json(['message' => 'Inicio de sesión correcto.', 'token' => $user->createToken($data['device_name'] ?? 'flutter-web')->plainTextToken, 'user' => $user]);
    }
    public function user(Request $request) { return response()->json(['user' => $request->user()->load('role')]); }
    public function updateProfile(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:150'], 'email' => ['required', 'email', 'max:150']]);
        $request->user()->update($data);
        return response()->json(['message' => 'Perfil actualizado.', 'user' => $request->user()->fresh()->load('role')]);
    }
    public function changePassword(Request $request)
    {
        $data = $request->validate(['current_password' => ['required', 'string'], 'password' => ['required', 'string', 'min:8', 'confirmed']]);
        if (! Hash::check($data['current_password'], $request->user()->password)) return response()->json(['message' => 'La contraseña actual no coincide.'], 422);
        $request->user()->update(['password' => $data['password']]);
        return response()->json(['message' => 'Contraseña actualizada.']);
    }
    public function logout(Request $request) { $request->user()->currentAccessToken()->delete(); return response()->json(['message' => 'Sesión cerrada correctamente.']); }
}
