<?php

namespace App\Common;

use App\Models\RefCabeloCor;
use App\Models\RefCabeloTipo;
use App\Models\RefCidade;
use App\Models\RefCrenca;
use App\Models\RefCutis;
use App\Models\RefEscolaridade;
use App\Models\RefEstadoCivil;
use App\Models\RefGenero;
use App\Models\RefIncOrigem;
use App\Models\RefOlhoCor;
use App\Models\RefOlhoTipo;
use App\Models\RefPresoConvivioTipo;

class ValidacoesReferenciasId
{

    public static function incOrigem($resource, $request, &$arrErrors)
    {
        $resource->origem_id = $request->input('origem_id');

        $resource = RefIncOrigem::find($resource->origem_id);

        // Verifique se o modelo foi encontrado e não foi excluído
        if (!$resource || $resource->trashed()) {
            // Gerar um log
            $mensagem = "A origem de inclusão informada não existe ou foi excluída.";
            $traceId = CommonsFunctions::generateLog($mensagem . "| Request: " . json_encode($request->input()));

            $arrErrors['origem'] = [
                'error' => $mensagem,
                'trace_id' => $traceId
            ];
        }
    }

    public static function cidade($resource, $request, &$arrErrors, $options = [])
    {
        $input = isset($options['input']) ? $options['input'] : 'cidade_id';
        $nome = isset($options['nome']) ? $options['nome'] : 'cidade';

        $resource->$input = $request->input($input);

        $resource = RefCidade::find($resource->$input);

        // Verifique se o modelo foi encontrado e não foi excluído
        if (!$resource || $resource->trashed()) {
            // Gerar um log
            $mensagem = "A $nome informada não existe ou foi excluída.";
            $traceId = CommonsFunctions::generateLog($mensagem . "| Request: " . json_encode($request->input()));

            $arrErrors[$input] = [
                'error' => $mensagem,
                'trace_id' => $traceId
            ];
        }
    }

    public static function genero($resource, $request, &$arrErrors)
    {
        $resource->genero_id = $request->input('genero_id');

        $resource = RefGenero::find($resource->genero_id);

        // Verifique se o modelo foi encontrado e não foi excluído
        if (!$resource || $resource->trashed()) {
            // Gerar um log
            $mensagem = "O gênero informado não existe ou foi excluído.";
            $traceId = CommonsFunctions::generateLog($mensagem . "| Request: " . json_encode($request->input()));

            $arrErrors['genero'] = [
                'error' => $mensagem,
                'trace_id' => $traceId
            ];
        }
    }

    public static function escolaridade($resource, $request, &$arrErrors)
    {
        $resource->escolaridade_id = $request->input('escolaridade_id');

        $resource = RefEscolaridade::find($resource->escolaridade_id);

        // Verifique se o modelo foi encontrado e não foi excluído
        if (!$resource || $resource->trashed()) {
            // Gerar um log
            $mensagem = "A escolaridade informada não existe ou foi excluída.";
            $traceId = CommonsFunctions::generateLog($mensagem . "| Request: " . json_encode($request->input()));

            $arrErrors['escolaridade'] = [
                'error' => $mensagem,
                'trace_id' => $traceId
            ];
        }
    }

    public static function estadoCivil($resource, $request, &$arrErrors)
    {
        $resource->estado_civil_id = $request->input('estado_civil_id');

        $resource = RefEstadoCivil::find($resource->estado_civil_id);

        // Verifique se o modelo foi encontrado e não foi excluído
        if (!$resource || $resource->trashed()) {
            // Gerar um log
            $mensagem = "O estado civil informado não existe ou foi excluído.";
            $traceId = CommonsFunctions::generateLog($mensagem . "| Request: " . json_encode($request->input()));

            $arrErrors['estado_civil'] = [
                'error' => $mensagem,
                'trace_id' => $traceId
            ];
        }
    }

    public static function cutis($resource, $request, &$arrErrors)
    {
        $resource->cutis_id = $request->input('cutis_id');

        $resource = RefCutis::find($resource->cutis_id);

        // Verifique se o modelo foi encontrado e não foi excluído
        if (!$resource || $resource->trashed()) {
            // Gerar um log
            $mensagem = "A cutis informada não existe ou foi excluída.";
            $traceId = CommonsFunctions::generateLog($mensagem . "| Request: " . json_encode($request->input()));

            $arrErrors['cutis'] = [
                'error' => $mensagem,
                'trace_id' => $traceId
            ];
        }
    }

    public static function cabeloTipo($resource, $request, &$arrErrors)
    {
        $resource->cabelo_tipo_id = $request->input('cabelo_tipo_id');

        $resource = RefCabeloTipo::find($resource->cabelo_tipo_id);

        // Verifique se o modelo foi encontrado e não foi excluído
        if (!$resource || $resource->trashed()) {
            // Gerar um log
            $mensagem = "O tipo de cabelo informado não existe ou foi excluído.";
            $traceId = CommonsFunctions::generateLog($mensagem . "| Request: " . json_encode($request->input()));

            $arrErrors['cabelo_tipo'] = [
                'error' => $mensagem,
                'trace_id' => $traceId
            ];
        }
    }

    public static function cabeloCor($resource, $request, &$arrErrors)
    {
        $resource->cabelo_cor_id = $request->input('cabelo_cor_id');

        $resource = RefCabeloCor::find($resource->cabelo_cor_id);

        // Verifique se o modelo foi encontrado e não foi excluído
        if (!$resource || $resource->trashed()) {
            // Gerar um log
            $mensagem = "A cor de cabelo informada não existe ou foi excluída.";
            $traceId = CommonsFunctions::generateLog($mensagem . "| Request: " . json_encode($request->input()));

            $arrErrors['cabelo_cor'] = [
                'error' => $mensagem,
                'trace_id' => $traceId
            ];
        }
    }

    public static function olhoTipo($resource, $request, &$arrErrors)
    {
        $resource->olho_tipo_id = $request->input('olho_tipo_id');

        $resource = RefOlhoTipo::find($resource->olho_tipo_id);

        // Verifique se o modelo foi encontrado e não foi excluído
        if (!$resource || $resource->trashed()) {
            // Gerar um log
            $mensagem = "O tipo de olhos informado não existe ou foi excluído.";
            $traceId = CommonsFunctions::generateLog($mensagem . "| Request: " . json_encode($request->input()));

            $arrErrors['olho_tipo'] = [
                'error' => $mensagem,
                'trace_id' => $traceId
            ];
        }
    }

    public static function olhoCor($resource, $request, &$arrErrors)
    {
        $resource->olho_cor_id = $request->input('olho_cor_id');

        $resource = RefOlhoCor::find($resource->olho_cor_id);

        // Verifique se o modelo foi encontrado e não foi excluído
        if (!$resource || $resource->trashed()) {
            // Gerar um log
            $mensagem = "A cor de olhos informada não existe ou foi excluída.";
            $traceId = CommonsFunctions::generateLog($mensagem . "| Request: " . json_encode($request->input()));

            $arrErrors['olho_cor'] = [
                'error' => $mensagem,
                'trace_id' => $traceId
            ];
        }
    }

    public static function crenca($resource, $request, &$arrErrors)
    {
        $resource->crenca_id = $request->input('crenca_id');

        $resource = RefCrenca::find($resource->crenca_id);

        // Verifique se o modelo foi encontrado e não foi excluído
        if (!$resource || $resource->trashed()) {
            // Gerar um log
            $mensagem = "A crença informada não existe ou foi excluída.";
            $traceId = CommonsFunctions::generateLog($mensagem . "| Request: " . json_encode($request->input()));

            $arrErrors['crenca'] = [
                'error' => $mensagem,
                'trace_id' => $traceId
            ];
        }
    }

    public static function presoTipo($resource, $request, &$arrErrors)
    {
        $resource->preso_tipo_id = $request->input('preso_tipo_id');

        $resource = RefPresoConvivioTipo::find($resource->preso_tipo_id);

        // Verifique se o modelo foi encontrado e não foi excluído
        if (!$resource || $resource->trashed()) {
            // Gerar um log
            $mensagem = "O tipo de preso informado não existe ou foi excluído.";
            $traceId = CommonsFunctions::generateLog($mensagem . "| Request: " . json_encode($request->input()));

            $arrErrors['preso_tipo'] = [
                'error' => $mensagem,
                'trace_id' => $traceId
            ];
        }
    }

}
