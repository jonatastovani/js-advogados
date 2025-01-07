<?php

namespace App\Traits;

use App\Enums\PessoaPerfilTipoEnum;
use App\Helpers\LogHelper;
use App\Helpers\ValidationRecordsHelper;
use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use App\Mail\FirstAccessMail;
use App\Mail\PasswordResetRequestMail;
use App\Mail\EmailChangedNotificationMail;

trait PessoaFisicaUsuarioMethodsTrait
{

    public function atualizarOuCriarUsuarioEnviado($resource, $user, $options = [])
    {
        if ($user->id) {
            $user->save();
        } else {
            $perfilUsuario = $this->modelPessoaPerfil::where('pessoa_id', $resource->pessoa->id)->where('perfil_tipo_id', PessoaPerfilTipoEnum::USUARIO->value)->first();

            $user->pessoa_perfil_id = $perfilUsuario->id;
            $user->tenant_id = tenant('id');
            $user = $this->salvarUsuarioEEnviarEmail($user, 'first_access', $options);
        }

        return $user;
    }

    /**
     * Verifica e preenche dados do usuário para perfis do tipo USUARIO.
     */
    protected function verificarUsuario(Fluent $requestData, Model $resource, Fluent $arrayErrors): Fluent
    {
        // Verifica se os dados do usuário foram enviados
        if (empty($requestData->user) || !isset($requestData->user['email']) || !isset($requestData->user['name'])) {
            $arrayErrors->user = LogHelper::gerarLogDinamico(404, 'Os dados do usuário devem ser informados.', $requestData)->error;

            $retorno = new Fluent();
            $retorno->user = null;
            $retorno->arrayErrors = $arrayErrors;
            return $retorno;
        }

        // Busca ou cria o usuário
        $user = isset($requestData->user['id'])
            ? User::find($requestData->user['id'])
            : ($resource->id ? $resource->pessoa->perfil_usuario->user ?? null : null);

        // Verifica duplicidade de email no mesmo tenant
        $validacaoRecursoExistente = ValidationRecordsHelper::validarRecursoExistente(
            User::class,
            ['email' => $requestData->user['email']],
            $user->id ?? null
        );

        if ($validacaoRecursoExistente->count()) {
            $arrayErrors->user_email = LogHelper::gerarLogDinamico(404, "O email informado já existe cadastrado para outra pessoa.", $requestData)->error;
        } else {

            if (!$user) {
                $user = new User();
            }

            $user->fill(collect($requestData->user)->toArray());

            // // Adiciona a senha se necessário
            // if (!$user->id || !empty($requestData->user['password'])) {
            //     $user->password = Hash::make($requestData->user['password']);
            // }
        }

        $retorno = new Fluent();
        $retorno->user = $user;
        $retorno->arrayErrors = $arrayErrors;

        return $retorno;
    }

    private function salvarUsuarioEEnviarEmail($user, $tipoNotificacao = null, $options = [])
    {
        if (in_array($tipoNotificacao, ['first_access'])) {
            // Gerar uma nova senha temporária
            $user->password = Hash::make(Str::random(12));
        }

        // Salvar o usuário
        $user->save();

        // Escolher o e-mail baseado no tipo de notificação
        switch ($tipoNotificacao) {
            case 'first_access':
                // Gerar o link de redefinição de senha para o primeiro acesso
                $token = Password::createToken($user);
                $resetLink = url(route('password.reset', ['token' => $token, 'email' => $user->email], false));
                $mailable = new FirstAccessMail($resetLink);
                break;

            case 'password_reset_request':
                // Link de redefinição de senha para solicitação de alteração
                $token = Password::createToken($user);
                $resetLink = url(route('password.reset', ['token' => $token, 'email' => $user->email], false));
                $mailable = new PasswordResetRequestMail($resetLink);
                break;

            case 'email_changed':
                // Notificar o usuário que o e-mail foi alterado
                $oldEmail = $options['oldEmail'];
                $mailable = new EmailChangedNotificationMail($oldEmail);
                break;

            default:
                return response()->json(['message' => 'Tipo de notificação inválido.'], 400);
        }

        // // Enviar o e-mail
        // try {
        Mail::to($user->email)->send($mailable);
        //     return response()->json(['message' => 'Usuário atualizado e e-mail enviado com sucesso!']);
        // } catch (\Exception $e) {
        //     return response()->json(['message' => 'Usuário atualizado, mas houve um erro ao enviar o e-mail.', 'error' => $e->getMessage()], 500);
        // }

        return $user;
    }
}
