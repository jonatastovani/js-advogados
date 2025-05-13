<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\UUIDsHelpers;
use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Traits\AuthControllerTrait;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;

class ResetPasswordController extends Controller
{
    use ResetsPasswords, AuthControllerTrait;

    protected $redirectTo = '/home';

    /**
     * Recupera as credenciais de redefinição de senha.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        return array_merge($request->only(
            'email',
            'password',
            'password_confirmation',
            'token'
        ), [
            'tenant_id' => tenant('id'),  // Garantir que o tenant_id esteja presente
        ]);
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @param  string  $password
     * @return void
     */
    protected function resetPassword($user, $password)
    {
        // Faz login do administrador para registrar a alteração
        $admin = $this->loginAdmin();
        if (!$admin) {
            abort(403, 'Ação não autorizada. Admin não autenticado.');
        }

        // Define a nova senha do usuário
        $this->setUserPassword($user, $password);
        /** @var \App\Models\Auth\User $user */
        $user->setRememberToken(Str::random(60));
        $user->save();

        event(new PasswordReset($user));
        $this->guard()->login($user);
    }

    /**
     * Reset the given user's password after validation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function reset(Request $request)
    {
        $request->validate($this->rules(), $this->validationErrorMessages());

        // Verifica se o usuário está ativo e se o perfil está ativo
        $user = User::where('email', $request->get('email'))
            ->where('tenant_id', tenant('id'))
            ->first();

        if (!$user) {
            return $this->sendResetFailedResponse($request, Password::INVALID_USER);
        }

        // Verifica se o usuário está inativo
        if (!$user->ativo_bln) {
            return $this->sendResetFailedResponse($request, 'auth.user_inactive');
        }

        // Verifica se o perfil está inativo
        if (!$this->isPerfilUsuarioAtivo($user)) {
            return $this->sendResetFailedResponse($request, 'auth.profile_inactive');
        }

        // Reseta a senha se todas as verificações forem bem-sucedidas
        $response = $this->broker()->reset(
            $this->credentials($request),
            function ($user, $password) {
                $this->resetPassword($user, $password);
            }
        );

        return $response == Password::PASSWORD_RESET
            ? $this->sendResetResponse($request, $response)
            : $this->sendResetFailedResponse($request, $response);
    }

    /**
     * Send the response for a failed password reset.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetFailedResponse(Request $request, $response)
    {
        if ($request->wantsJson()) {
            return new JsonResponse(['message' => trans($response)], 403);
        }

        return redirect()->back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => trans($response)]);
    }
}
