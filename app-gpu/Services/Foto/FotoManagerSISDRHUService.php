<?php

namespace App\Services\Foto;

use App\Helpers\CurlRequest;
use App\Services\Cache\CacheManager;
use Illuminate\Support\Facades\Session;

class FotoManagerSISDRHUService
{
    protected $servidorAuth;
    protected $servidorImage;
    protected CacheManager $cacheManager;

    public function __construct(CacheManager $cacheManager)
    {
        $this->servidorAuth = env('APP_ENV') === 'production' ?  env('WEB_SERVICE_AUTH_PROD') : env('WEB_SERVICE_AUTH_DEV');
        $this->servidorImage = env('APP_ENV') === 'production' ?  env('SISDRHU_WS_IMAGE_PROD') : env('SISDRHU_WS_IMAGE_DEV');
        $this->cacheManager = $cacheManager;
    }

    /**
     * Consulta a imagem de um funcionário ou assinatura no servidor SISDRHU.
     *
     * @param string $tpoLocal O tipo de local (ex: 'ASSINATURAS')
     * @param string $nome O nome ou identificador do funcionário
     * @return string HTML com a imagem ou uma imagem padrão de erro
     */
    public function getConsultarImagem(string $tpoLocal, string $nome): string
    {
        try {
            // Definindo chave de cache
            $cacheKey = "imagem_{$tpoLocal}_{$nome}";

            // Verifica se a imagem está no cache
            $imagemCache = $this->cacheManager->get($cacheKey);

            if ($imagemCache) {
                return $imagemCache;
            }

            // Recupera o token da sessão ou do cache
            $sessaoWsImagem = $_SESSION['TOKEN_WS_IMAGE'] ?? $this->cacheManager->get('TOKEN_WS_IMAGE');

            // Se o token não existe, gera um novo token
            if (!$sessaoWsImagem) {
                $sessaoWsImagem = $this->postGeraTokenWs(); // Método para gerar novo token
                $this->cacheManager->set('TOKEN_WS_IMAGE', $sessaoWsImagem);
                Session::put('TOKEN_WS_IMAGE', $sessaoWsImagem);
            }

            // Monta a URL de consulta
            $url = "{$this->servidorImage}/rest/api/v2/image/?nome={$nome}&tpoLocal={$tpoLocal}";

            // Faz a requisição CURL com headers
            $curl = new CurlRequest($url, 'GET');
            $curl->setHeaders([
                'Content-Type: application/json',
                'Accept: application/json',
                "Authorization: {$sessaoWsImagem}"
            ]);

            $retorno = $curl->execute();

            // Se a requisição falhar ou não houver retorno válido
            if (!$retorno || isset($retorno['error'])) {
                return $this->imagemNaoLocalizada();
            }

            // Verifica se o token é válido
            $tokenValido = $this->postConsultaTokenWs($sessaoWsImagem);
            if (!$tokenValido) {
                // Token inválido, gera um novo token e refaz a consulta
                $sessaoWsImagem = $this->postGeraTokenWs();
                $this->cacheManager->set('TOKEN_WS_IMAGE', $sessaoWsImagem);
                $_SESSION['TOKEN_WS_IMAGE'] = $sessaoWsImagem;
                return $this->getConsultarImagem($tpoLocal, $nome);
            }

            // Formata a imagem como base64
            $srcWs = 'data: ;base64,' . base64_encode($retorno['response']);

            // Armazena a imagem no cache
            $this->cacheManager->set($cacheKey, $srcWs);

            return $srcWs;
        } catch (\Exception $e) {
            return $this->imagemNaoLocalizada();
        }
    }

    /**
     * Gera um novo token no WebService.
     *
     * @return string O novo token gerado
     */
    public function postGeraTokenWs(): string
    {
        try {
            $url = $this->servidorAuth . '/rest/api/v2/token/';
            $headers = [
                'Content-Type: application/json',
                'Accept: application/json',
            ];
            $fields = json_encode([
                'login' => env('APP_ENV') === 'production' ?  env('SISDRHU_WS_IMAGE_USER_PROD') : env('SISDRHU_WS_IMAGE_USER_DEV'),
                'senha' => env('APP_ENV') === 'production' ?  env('SISDRHU_WS_IMAGE_PASSWORD_PROD') : env('SISDRHU_WS_IMAGE_PASSWORD_DEV'),
                'sigla' => 'IMS',
            ]);

            $curl = new CurlRequest($url, 'POST');
            $curl->setHeaders($headers);
            $curl->setPostFields($fields);
            $response = $curl->execute();

            if (isset($response['token'])) {
                Session::put('TOKEN_WS_IMAGE', $response['token']);
                return $response['token'];
            }

            throw new \Exception('Erro ao gerar o token.');
        } catch (\Exception $e) {
            throw new \Exception('Erro na geração do token: ' . $e->getMessage());
        }
    }

    /**
     * Consulta a validade do token no WebService.
     *
     * @param string $tokenWsGerada O token a ser validado
     * @return bool Se o token é válido
     */
    public function postConsultaTokenWs(string $tokenWsGerada): bool
    {
        try {
            $url = $this->servidorAuth . '/rest/api/v2/token/app/';
            $headers = [
                'Content-Type: application/json',
                'Accept: application/json',
            ];
            $fields = json_encode(['token' => $tokenWsGerada]);

            $curl = new CurlRequest($url, 'POST');
            $curl->setHeaders($headers);
            $curl->setPostFields($fields);
            $response = $curl->execute();

            if (isset($response['auth2']) && $response['auth2'] === true) {
                Session::put('TOKEN_WS_IMAGE', $response['token']);
                return true;
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Formata a imagem para HTML.
     *
     * @param string $tpoLocal O tipo de local (ex: 'ASSINATURAS')
     * @param string $nome O nome ou identificador
     * @param string $srcWs O src base64 da imagem
     * @return string HTML da imagem
     */
    private function formatarImagem(string $tpoLocal, string $nome, string $srcWs): string
    {
        if ($tpoLocal === 'ASSINATURAS') {
            return "<img alt='Assinatura do Servidor' id='fass' border='0' value='{$nome}' src='{$srcWs}' onClick='return abreImg(this);'/>";
        } else {
            return "<img alt='Foto do Servidor' height='136' id='fserv' border='0' width='105' value='{$nome}' src='{$srcWs}' onClick='return abreImg(this);'/>";
        }
    }

    /**
     * Retorna uma imagem padrão quando não encontrada.
     *
     * @return string HTML da imagem não localizada
     */
    private function imagemNaoLocalizada(): string
    {
        return "<img alt='Imagem não localizada' height='136' id='fserv' border='0' width='105' src='../icons/faltafoto.jpg' onClick='return abreImg(this);'/>";
    }
}
