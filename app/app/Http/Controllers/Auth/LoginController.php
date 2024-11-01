<?php

namespace App\Http\Controllers\Auth;

use App\Common\CommonsFunctions;
use App\Common\RestResponse;
use App\Helpers\XMLHelper;
use App\Http\Controllers\Controller;
use App\Services\Auth\LoginService;
use App\Services\ServicoGepenService;
use App\Traits\CommonsControllerMethodsTrait;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Fluent;
use Laravel\Sanctum\PersonalAccessToken;

class LoginController extends Controller
{
    use CommonsControllerMethodsTrait;

    public function __construct(public LoginService $service) {}

    public function index()
    {
        return view('auth.login');
    }

    public function session_start(Request $request)
    {
        // Regras de validação
        $rules = [
            'username' => 'required',
            'password' => 'required',
        ];

        $validacao = CommonsFunctions::validacaoRequest($request, $rules);
        if ($validacao instanceof RestResponse) {
            return redirect()->back()->with('erro_login', $validacao->toArray());
        }

        return $this->service->session_start($request);
    }

    public function session_start_post(Request $request)
    {
        // Regras de validação
        $rules = [
            'username' => 'required',
            'password' => 'required',
        ];

        $validacao = CommonsFunctions::validacaoRequest($request, $rules);
        if ($validacao instanceof RestResponse) {
            return $validacao->throwResponse();
        }

        try {
            if ($request->input('username') == 'admin') {

                $credentials = $request->only('username', 'password');
                if (!Auth::attempt($credentials)) {
                    RestResponse::createErrorResponse(401, 'Usuário ou senha inválidos.')->throwResponse();
                } else {
                    return RestResponse::createSuccessResponse(['redirect' => route('admin.index')])->autoResponse();
                }
            } else {

                $gepenService = new ServicoGepenService();
                $dadosUsuario = $gepenService->loginGpu($request->input('username'), $request->input('password'));
                $dadosUsuario = new Fluent(XMLHelper::xmlToArray($dadosUsuario));

                if ($dadosUsuario->status == 200) {
                    if ($dadosUsuario->id) {
                        if ($dadosUsuario->habilitado == 'S') {
                            $request->session()->regenerate();
                            return redirect()->intended('lobby');
                        }
                    }
                }

                // Processa a "observacao" para remover o texto antes de "==>"
                if (isset($dadosUsuario->observacao)) {
                    $dadosUsuario->observacao = trim(preg_replace('/^.*==>\s*/', '', $dadosUsuario->observacao));
                }
                $responseErroLogin = RestResponse::createErrorResponse($dadosUsuario->status, $dadosUsuario->observacao);
            }
            return redirect()->back()->with('error_login', ['error' => $responseErroLogin->toArray(), 'username' => $request->input('username'), 'password' => $request->input('password')]);
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e);
        }
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function lobby()
    {
        return view('auth.lobby');
    }

    public function loginFrontApi(Request $request)
    {
        // Regras de validação
        $rules = [
            'username' => 'required',
            'password' => 'required',
            // 'session_user_data' => 'required|array',
            // 'session_user_data.user_gepen_id' => 'required|integer',
            // 'session_user_data.tenancy_id' => 'required|integer',
        ];

        CommonsFunctions::validacaoRequest($request, $rules);

        $credentials = $request->only('username', 'password');

        if (!Auth::attempt($credentials)) {
            $response = RestResponse::createErrorResponse(401, 'Credenciais inválidas');
            return response()->json($response->toArray(), $response->getStatusCode());
        }

        $user = Auth::user();
        // $sessionUserData = $request->input('session_user_data');

        // $token = $user->createTokenFront('auth_token_front', $sessionUserData)->plainTextToken;
        $token = $user->createToken('auth_token_front')->plainTextToken;

        $response = RestResponse::createSuccessResponse([
            'access_token' => $token,
            'token_type' => 'Bearer',
            // 'session_user_data' => $sessionUserData
        ]);
        return response()->json($response->toArray(), $response->getStatusCode());
    }

    public function checkTokenApi(Request $request)
    {
        // Obtem o token do header da requisição
        $token = $request->bearerToken();

        // Autenticar o usuário com base no token
        $user = Auth::guard('sanctum')->user();

        if ($user) {
            $sessionUserData = $request->input('session_user_data');
            $response = RestResponse::createSuccessResponse([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'session_user_data' => $sessionUserData
            ]);
            return response()->json($response->toArray(), $response->getStatusCode());
        } else {
            $response = RestResponse::createErrorResponse(401, 'Token inválido ou expirado');
            return response()->json($response->toArray(), $response->getStatusCode());
        }
    }

    public function logoutApi(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        $response = RestResponse::createSuccessResponse([], 204);
        return response()->json($response->toArray(), $response->getStatusCode());
    }

    public function logoutListApi(Request $request)
    {
        $rules = [
            'tokens' => 'required|array',
        ];

        CommonsFunctions::validacaoRequest($request, $rules);

        $user = $request->user();
        $userId = $user->id;

        foreach ($request->input('tokens') as $tokenId) {
            $token = PersonalAccessToken::findToken($tokenId);

            if ($token) {
                if ($token->tokenable_id === $userId || $userId === 1) {
                    $token->delete();
                }
            }
        }

        $response = RestResponse::createSuccessResponse([], 204);
        return response()->json($response->toArray(), $response->getStatusCode());
    }
}
