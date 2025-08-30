<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    /**
     * Exibe o formulário de login
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }
        
        return view('auth.login');
    }

    /**
     * Processa a tentativa de login
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();
        
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            // Definir variáveis de sessão para compatibilidade
            session([
                'admin_logged_in' => auth()->user()->isAdmin(),
                'admin_id' => auth()->id(),
                'admin_name' => auth()->user()->name,
                'admin_email' => auth()->user()->email,
                'admin_tipo' => auth()->user()->tipo_usuario
            ]);
            
            // Log para depuração
            \Log::info('Login realizado', [
                'user_id' => auth()->id(),
                'name' => auth()->user()->name,
                'email' => auth()->user()->email,
                'tipo_usuario' => auth()->user()->tipo_usuario,
                'session_admin_tipo' => session('admin_tipo')
            ]);
            
            // Verificar se o usuário está ativo
            if (!auth()->user()->ativo) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Sua conta está desativada. Entre em contato com o administrador.',
                ])->withInput($request->except('password'));
            }
            
            // Redirecionar para o dashboard
            return redirect()->route('dashboard');
        }
        
        return back()->withErrors([
            'email' => 'As credenciais fornecidas não correspondem aos nossos registros.',
        ])->withInput($request->except('password'));
    }

    /**
     * Realiza o logout do usuário
     */
    public function logout(Request $request): RedirectResponse
    {
        $this->authService->logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login')
            ->with('success', 'Logout realizado com sucesso!');
    }

    public function loginAdmin(Request $request)
    {
        // ... existing code ...
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('dashboard');
        }
        // ... existing code ...
    }
}
