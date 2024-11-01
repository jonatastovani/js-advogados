<?php

namespace App\Models\GPU;

use App\Traits\CommonsModelsMethodsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentoGPU extends Model
{
    use HasFactory, CommonsModelsMethodsTrait;

    protected $table = 'documentos.tb_documentos';
    // Definindo a chave primária personalizada
    protected $primaryKey = 'docm_id';

    /**
     * Retorna o tipo de documento, consultando o modelo 'DocumentoTipoGPU' com condições específicas.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function tipo()
    {
        return $this->hasOne(DocumentoTipoGPU::class, 'tipd_id', 'docm_id_tipo_documento');
    }
}
