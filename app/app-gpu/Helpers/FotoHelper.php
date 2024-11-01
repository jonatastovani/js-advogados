<?php

namespace App\Helpers;

use App\Enums\TipoFotoEnum;
use App\Enums\SubTipoFotoEnum;
use App\Services\Foto\FotoManagerGPUService;

class FotoHelper
{
    /**
     * Construtor da classe FotoHelper.
     * Injeta o serviço FotoManagerGPUService para gerenciamento das operações de foto.
     *
     * @param FotoManagerGPUService $fotoManagerGPUService Serviço responsável por gerenciar as operações de foto.
     */
    public function __construct(public FotoManagerGPUService $fotoManagerGPUService) {}

    /**
     * Busca uma única foto de um preso com base no ID e, opcionalmente, no subtipo.
     *
     * @param int $idPreso O ID do preso para buscar a foto.
     * @param string|null $subTipo O subtipo da foto (opcional).
     * @return mixed A foto encontrada, ou null se não houver foto correspondente.
     */
    public function buscarFotoPreso(int $idPreso, string $subTipo = null)
    {
        return $this->listarFotoPorTipoESubTipo($idPreso, TipoFotoEnum::PRESO, $subTipo);
    }

    /**
     * Busca todas as fotos de um preso com base no ID e, opcionalmente, no subtipo.
     *
     * @param int $idPreso O ID do preso para buscar as fotos.
     * @param string|null $subTipo O subtipo das fotos (opcional).
     * @return mixed As fotos encontradas, ou null se não houver fotos correspondentes.
     */
    public function buscarFotosPreso(int $idPreso, string $subTipo = null)
    {
        return $this->listarFotosPorTipoESubTipo($idPreso, TipoFotoEnum::PRESO, $subTipo);
    }

    /**
     * Método privado para listar uma única foto com base no tipo e subtipo.
     *
     * @param int $id O ID do objeto para buscar a foto.
     * @param string $tipo O tipo do objeto (ex: PRESO, FUNCIONARIO).
     * @param string|null $subTipo O subtipo da foto (opcional).
     * @return mixed A foto encontrada, ou null se não houver foto correspondente.
     */
    private function listarFotoPorTipoESubTipo(int $id, string $tipo, string $subTipo = null)
    {
        switch ($subTipo) {
            case SubTipoFotoEnum::FRONTAL:
                return $this->fotoManagerGPUService->getFoto($id, $tipo, SubTipoFotoEnum::FRONTAL);
            case SubTipoFotoEnum::PERFILESQUERDO:
                return $this->fotoManagerGPUService->getFoto($id, $tipo, SubTipoFotoEnum::PERFILESQUERDO);
            case SubTipoFotoEnum::PERFILDIREITO:
                return $this->fotoManagerGPUService->getFoto($id, $tipo, SubTipoFotoEnum::PERFILDIREITO);
            case SubTipoFotoEnum::SINAIS:
                return $this->fotoManagerGPUService->getFoto($id, $tipo, SubTipoFotoEnum::SINAIS);
            default:
                return $this->fotoManagerGPUService->getFoto($id, $tipo, SubTipoFotoEnum::FRONTAL);
        }
    }

    /**
     * Método privado para listar todas as fotos com base no tipo e subtipo.
     *
     * @param int $id O ID do objeto para buscar as fotos.
     * @param string $tipo O tipo do objeto (ex: PRESO, FUNCIONARIO).
     * @param string|null $subTipo O subtipo das fotos (opcional).
     * @return mixed As fotos encontradas, ou null se não houver fotos correspondentes.
     */
    private function listarFotosPorTipoESubTipo(int $id, string $tipo, string $subTipo = null)
    {
        switch ($subTipo) {
            case SubTipoFotoEnum::FRONTAL:
                return $this->fotoManagerGPUService->getFotos($id, $tipo, SubTipoFotoEnum::FRONTAL);
            case SubTipoFotoEnum::PERFILESQUERDO:
                return $this->fotoManagerGPUService->getFotos($id, $tipo, SubTipoFotoEnum::PERFILESQUERDO);
            case SubTipoFotoEnum::PERFILDIREITO:
                return $this->fotoManagerGPUService->getFotos($id, $tipo, SubTipoFotoEnum::PERFILDIREITO);
            case SubTipoFotoEnum::SINAIS:
                return $this->fotoManagerGPUService->getFotos($id, $tipo, SubTipoFotoEnum::SINAIS);
            default:
                return $this->fotoManagerGPUService->getFotos($id, $tipo);
        }
    }

    public function buscarFotoVisitantePreso(int $idVisitaPreso)
    {
        return $this->listarFotosPorTipoESubTipo($idVisitaPreso, TipoFotoEnum::VISITANTEPRESO);
    }

    public function buscarFotoPorNome(int $id, string $tipo, string $nomeImagem, string $subTipo = null)
    {
        // $fotoManagerGPUService = new FotoManagerGPUService();
        return $this->fotoManagerGPUService->getFotoPorNome($id, $tipo, $nomeImagem, $subTipo);
    }
}
