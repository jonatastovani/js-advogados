<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\UUIDsHelpers;
use App\Http\Controllers\Controller;
use App\Traits\AuthFuncoesControllerTrait;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    use ResetsPasswords, AuthFuncoesControllerTrait;

    protected $redirectTo = '/home';

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

        // Faz o restante igualmente a função original
        $this->setUserPassword($user, $password);

        $user->setRememberToken(Str::random(60));

        $user->save();

        event(new PasswordReset($user));

        $this->guard()->login($user);
    }
}
