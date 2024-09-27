<?php

namespace App\Services\Auth;

use App\Common\CommonsFunctions;
use App\Common\RestResponse;
use App\Helpers\EnumPermissionModulesHelper;
use App\Helpers\LogHelper;
use App\Helpers\PermissionHelper;
use App\Helpers\ValidationRecordsHelper;
use App\Helpers\XMLHelper;
use App\Models\Auth\Permission;
use App\Models\Auth\PermissionConfig;
use App\Models\Auth\PermissionGroup;
use App\Services\ServicoGepenService;
use App\Traits\CommonsConsultaServiceTrait;
use App\Traits\CommonServiceMethodsTrait;
use App\Traits\EnumRenderTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;

class LoginService
{
    use CommonServiceMethodsTrait;

    public function __construct(public Permission $model) {}

    public function session_start($request)
    {
        try {
            // if ($request->input('username') == 'admin') {

                $credentials = $request->only('username', 'password');
                if (!Auth::attempt($credentials)) {
                    $responseErroLogin = RestResponse::createErrorResponse(401, 'Usuário ou senha inválidos.');
                } else {
                    $request->session()->regenerate();
                    return redirect()->intended(route('advocacia.servico.index'));
                }
            // } else {

            //     $gepenService = new ServicoGepenService();
            //     $dadosUsuario = $gepenService->loginGpu($request->input('username'), $request->input('password'));
            //     $dadosUsuario = new Fluent(XMLHelper::xmlToArray($dadosUsuario));

            //     if ($dadosUsuario->status == 200) {
            //         if ($dadosUsuario->id) {
            //             if ($dadosUsuario->habilitado == 'S') {
            //                 $request->session()->regenerate();
            //                 return redirect()->intended(route('lobby'));
            //             }
            //         }
            //     }

            //     // Processa a "observacao" para remover o texto antes de "==>"
            //     if (isset($dadosUsuario->observacao)) {
            //         $dadosUsuario->observacao = trim(preg_replace('/^.*==>\s*/', '', $dadosUsuario->observacao));
            //     }
            //     $responseErroLogin = RestResponse::createErrorResponse($dadosUsuario->status, $dadosUsuario->observacao);
            // }
            
            return redirect()->back()->with('error_login', ['error' => $responseErroLogin->toArray(), 'username' => $request->input('username'), 'password' => $request->input('password')]);
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e);
        }
    }
}
