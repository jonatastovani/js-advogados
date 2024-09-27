<?php

namespace App\Services;

use App\Helpers\CurlRequest;
use Exception;

class ServicoGepenService
{
    protected $servidor;

    public function __construct()
    {
        $this->servidor = $this->getServidorUrl();
    }

    private function getServidorUrl()
    {
        $environment = env('APP_ENV');

        if ($environment === 'production') {
            return env('GEPEN_WS_URL_SERVICO_PROD');
        }

        return env('GEPEN_WS_URL_SERVICO_DEV');
    }

    public function loginGpu($usuario, $senha)
    {
        $url = $this->servidor . '/acesso_gepen/logar_no_gpu';
        $xml = $this->montaXMLLogin($usuario, $senha);

        $curl = new CurlRequest($url, 'POST');
        $curl->setHeader('Content-Type', 'application/xml');
        $curl->setPostFields($xml);

        $response = $curl->execute();

        if ($response['info']['http_code'] == 200) {
            return simplexml_load_string($response['response']);
        }

        throw new Exception("Erro ao realizar login: " . $response['response']);
    }

    public function atualizaCPF($idPreso, $matriculaSemDigito, $cpf, $usuId)
    {
        $url = $this->servidor . '/dados_basico/alterar_cpf';
        $xml = $this->montaXMLCPF($idPreso, $matriculaSemDigito, $cpf, $usuId);

        $curl = new CurlRequest($url, 'POST');
        $curl->setHeader('Content-Type', 'application/xml');
        $curl->setPostFields($xml);

        $response = $curl->execute();

        if ($response['info']['http_code'] == 200) {
            return simplexml_load_string($response['response']);
        }

        throw new Exception("Erro ao atualizar CPF: " . $response['response']);
    }

    private function montaXMLLogin($usuario, $senha)
    {
        return <<<XML
                <br.gov.sp.sap.gepenws.model.Login>
                    <login>{$usuario}</login>
                    <senha>{$senha}</senha>
                </br.gov.sp.sap.gepenws.model.Login>
                XML;
    }

    private function montaXMLCPF($idPreso, $matricula, $cpf, $idUsuario)
    {
        return <<<XML
            <br.gov.sp.sap.gepen.preso.model.Preso>
                <id>{$idPreso}</id>
                <matricula>{$matricula}</matricula>
                <cpf>{$cpf}</cpf>
                <idUsuarioAlteracao>{$idUsuario}</idUsuarioAlteracao>
            </br.gov.sp.sap.gepen.preso.model.Preso>
            XML;
    }
}
