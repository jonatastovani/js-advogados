<?php

namespace App\Services\Auth;

use App\Common\RestResponse;
use App\Helpers\LogHelper;
use App\Models\Auth\User;
use App\Traits\CommonServiceMethodsTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class LoginService
{
    use CommonServiceMethodsTrait;

    public function session_start(Request $request)
    {
        try {
            $credentials = $request->only('email', 'password');
            $email = $credentials['email'];
            $password = $credentials['password'];

            // Verifica se o usuário é "admin"
            if ($email === 'admin@byteforge.com.br') {
                // $user = User::withoutTenancy()->where('email', 'admin@byteforge.com.br')->first();
                $user = User::where('email', 'admin@byteforge.com.br')->first();

                // Valida a senha do administrador
                if (!$user || !Hash::check($password, $user->password)) {
                    return $this->invalidCredentialsResponse($request);
                }
            } else {
                // Tenta autenticar o usuário normalmente
                if (!Auth::attempt($credentials)) {
                    return $this->invalidCredentialsResponse($request);
                }

                $user = Auth::user();
            }

            // Realiza o login e regenera a sessão
            Auth::login($user);
            $request->session()->regenerate();

            return redirect()->intended(route('servico.index'));
        } catch (Exception $e) {
            Log::error('Erro ao processar login: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao processar login.');
        }
    }

    /**
     * Retorna uma resposta de erro para credenciais inválidas.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    private function invalidCredentialsResponse(Request $request)
    {
        $responseErroLogin = RestResponse::createErrorResponse(401, 'Email ou senha inválidos.');
        return redirect()->back()->with('error_login', [
            'error' => $responseErroLogin->toArray(),
            'email' => $request->input('email'),
        ]);
    }
}
