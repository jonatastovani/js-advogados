<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Traits\AuthControllerTrait;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Http\JsonResponse;

class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails, AuthControllerTrait;

    /**
     * Obtém as credenciais para redefinição de senha.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        return [
            'email' => $request->get('email'),
            'tenant_id' => tenant('id'),
            'ativo_bln' => true,
        ];
    }

    /**
     * Envia o link de redefinição de senha.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        $this->validateEmail($request);

        // Verifica se o usuário está ativo e se o perfil está ativo
        $user = User::where('email', $request->get('email'))
            ->where('tenant_id', tenant('id'))
            ->first();

        if (!$user) {
            return $this->sendResetLinkFailedResponse($request, Password::INVALID_USER);
        }

        // Verificação se o usuário está inativo
        if (!$user->ativo_bln) {
            return $this->sendResetLinkFailedResponse($request, 'auth.user_inactive');
        }

        // Verificação se o perfil está inativo
        if (!$this->isPerfilUsuarioAtivo($user)) {
            return $this->sendResetLinkFailedResponse($request, 'auth.profile_inactive');
        }

        // Envia o link de redefinição se as verificações forem bem-sucedidas
        $response = $this->broker()->sendResetLink(
            $this->credentials($request)
        );

        return $response == Password::RESET_LINK_SENT
            ? $this->sendResetLinkResponse($request, $response)
            : $this->sendResetLinkFailedResponse($request, $response);
    }
}
