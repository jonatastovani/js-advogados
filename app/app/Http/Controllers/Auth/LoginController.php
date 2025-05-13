<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\UUIDsHelpers;
use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Traits\AuthControllerTrait;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class LoginController extends Controller
{
    use AuthenticatesUsers, AuthControllerTrait;

    /**
     * Redirecionamento após login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Construtor do controlador.
     *
     * Define os middlewares necessários para as rotas de login e logout.
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Obtém as credenciais de login a partir da requisição.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        $userAdmin = User::find(UUIDsHelpers::getAdmin());

        // Verifica se o usuário é o administrador pelo e-mail
        if ($userAdmin && $request->get('email') === $userAdmin->email) {
            return [
                'email' => $request->get('email'),
                'password' => $request->get('password'),
            ];
        }

        // Retorna as credenciais padrão com verificação de tenant e usuário ativo
        return [
            'email' => $request->get('email'),
            'password' => $request->get('password'),
            'tenant_id' => tenant('id'),
            'ativo_bln' => true,
        ];
    }

    /**
     * Ação realizada após o usuário ser autenticado.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        // Verifica se o perfil do usuário está ativo após a autenticação
        if (!$this->isPerfilUsuarioAtivo($user)) {
            $this->guard()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $message = trans('auth.profile_inactive');

            return $request->wantsJson()
                ? new JsonResponse(['message' => $message], 403)
                : redirect()->route('login')->withErrors(['email' => $message]);
        }
    }

    /**
     * Envia a resposta após uma tentativa de login malsucedida.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        $user = User::where('email', $request->get('email'))
            ->where('tenant_id', tenant('id'))
            ->first();

        // Verifica se o usuário está inativo
        if ($user && !$user->ativo_bln) {
            return back()->withErrors([
                'email' => trans('auth.user_inactive'),
            ]);
        }

        // Verifica se o perfil do usuário está inativo
        if ($user && $user->pessoa_perfil && !$user->pessoa_perfil->ativo_bln) {
            return back()->withErrors([
                'email' => trans('auth.profile_inactive'),
            ]);
        }

        // Retorno padrão para falha de login
        return back()->withErrors([
            'email' => trans('auth.failed'),
        ]);
    }
}
