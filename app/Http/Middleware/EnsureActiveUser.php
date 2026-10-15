<?php
namespace App\Http\Middleware;
use Closure; use Illuminate\Http\Request;
class EnsureActiveUser { public function handle(Request $request, Closure $next) { if ($request->user()?->status !== 'active') return response()->json(['message' => 'La cuenta está inactiva.'], 403); return $next($request); } }
