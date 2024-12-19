<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle($request, Closure $next, $role)
    {
        // Verificar si el usuario está autenticado
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Verificar si el usuario tiene el rol correcto
        $user = Auth::user();
        if ($user->role !== $role) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
}