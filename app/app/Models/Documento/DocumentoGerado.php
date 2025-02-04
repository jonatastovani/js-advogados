<?php

namespace App\Models\Documento;

use App\Helpers\NumeracaoSequencialHelper;
use App\Models\Referencias\DocumentoGeradoTipo;
use App\Traits\BelongsToDomain;
use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class DocumentoGerado extends Model
{
    use HasFactory, HasUuids, CommonsModelsMethodsTrait, ModelsLogsTrait, BelongsToTenant, BelongsToDomain;

    protected $table = 'documento.documento_gerados';
    protected $tableAsName = 'doc_ger';
    // Variável estática para armazenar a sequência temporariamente
    protected static $sequenciaTemporaria;

    protected $casts = [
        'dados' => 'array',
    ];

    protected $hidden = [
        'tenant_id',
        'domain_id',
        'created_user_id',
        'created_ip',
        // 'created_at',
        'updated_user_id',
        'updated_ip',
        'updated_at',
        'deleted_user_id',
        'deleted_ip',
        'deleted_at',
    ];

    public function documento_gerado_tipo()
    {
        return $this->belongsTo(DocumentoGeradoTipo::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Model $model) {
            // Verifica se já foi informado um número e ano
            if (!$model->numero_documento) {
                $tenantId = $model->tenant_id ?? tenant('id');
                $sequencia = NumeracaoSequencialHelper::obterProximoNumero('documento_gerado', $tenantId);
                // Preenche o campo numero_documento com o número obtido
                $model->numero_documento = $sequencia['numero'];

                // Armazena a sequência temporariamente para ser usada após a criação
                self::$sequenciaTemporaria = $sequencia;
            }
        });

        static::created(function (Model $model) {
            // Após a criação bem-sucedida, confirma a numeração sequencial
            if (self::$sequenciaTemporaria) {
                NumeracaoSequencialHelper::confirmarNumeracao(self::$sequenciaTemporaria['registroNumeracao']);
                // Limpa a sequência temporária
                self::$sequenciaTemporaria = null;
            }
        });
    }
}
