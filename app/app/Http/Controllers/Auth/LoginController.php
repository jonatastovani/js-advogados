<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\UUIDsHelpers;
use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Get the needed authorization credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        $userAdmin = User::find(UUIDsHelpers::getAdmin());

        // Verifica se o usuário é o administrador pelo ID
        if ($request->get('email') === $userAdmin->email) {
            return [
                'email' => $request->get('email'),
                'password' => $request->get('password'),
            ];
        }

        // Adiciona o filtro de tenant_id para outros usuários
        return [
            'email' => $request->get('email'),
            'password' => $request->get('password'),
            'tenant_id' => tenant('id'),
        ];
    }
}
