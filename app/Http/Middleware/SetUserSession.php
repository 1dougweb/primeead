<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SetUserSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Se o usuário está autenticado e a sessão não está definida, definir a sessão
        if (Auth::check() && !session('admin_id')) {
            $user = Auth::user();
            
            session([
                'admin_id' => $user->id,
                'admin_name' => $user->name,
                'admin_email' => $user->email,
                'admin_tipo' => $user->tipo_usuario,
                'admin_logged_in' => $user->isAdmin(),
            ]);
            
            \Log::info('User session set automatically', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'tipo_usuario' => $user->tipo_usuario,
                'is_admin' => $user->isAdmin()
            ]);
        }
        
        return $next($request);
    }
} 