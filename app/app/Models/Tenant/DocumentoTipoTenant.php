<?php

namespace App\Models\Tenant;

use App\Models\Referencias\DocumentoTipo;
use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Fluent;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;
use Stancl\Tenancy\Database\Concerns\HasDataColumn;

class DocumentoTipoTenant extends Model
{
    use HasFactory, HasUuids, CommonsModelsMethodsTrait, ModelsLogsTrait, BelongsToTenant, HasDataColumn;

    protected $table = 'tenant.documento_tipo_tenants';
    protected $tableAsName = 'doc_tip_ten';

    protected $fillable = [
        'nome',
        'descricao',
        'documento_tipo_id',
        'ativo_bln',
    ];

    public static function getCustomColumns(): array
    {
        return array_merge(self::getCustomColumnsDefault(), [
            'nome',
            'descricao',
            'documento_tipo_id',
            'ativo_bln',
        ]);
    }
    
    public function documento_tipo()
    {
        return $this->belongsTo(DocumentoTipo::class);
    }
    
    /**
     * Insere uma cláusula de junção com o A movimentação e a Movimentação Conta Participante.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query A instância do construtor de consultas.
     * @param array $options O array de opcões de personalização.
     *              - 'typeJoin' (opcional) => 'inner', 'left' ou 'right' para definir o tipo de junção. Padrão é 'inner'.
     *              - 'aliasTable' (opcional) Alias da tabela MovimentaçãoContaParticipante. Padrão está definido no atributo protegido 'tableAsName' da App\Models\Financeiro\MovimentaçãoContaParticipante.
     *              - 'instanceSelf' (opcional) Instância da model atual ou uma modificada. Padrão é a instância da model atual(self).
     *              - 'aliasJoin' (opcional) Alias da tabela que irá ser juntada. Padrão está definido no atributo protegido 'tableAsName' da model informada.
     * @return \Illuminate\Database\Eloquent\Builder A instância do construtor de consultas. 
     */
    public static function joinDocumentoTipo(Builder $query, array $options = [])
    {
        // Join com o Movimentação Conta Participante
        $instanceSelf = $options['instanceSelf'] ?? new self();
        $envOptions = new Fluent([]);
        $envOptions->aliasJoin = $options['aliasJoin'] ?? (new DocumentoTipo())->getTableAsName();
        $envOptions->typeJoin = $options['typeJoin'] ?? 'inner';
        $aliasTable = isset($options['aliasTable']) ? $options['aliasTable'] : $instanceSelf->getTableAsName();
        $envOptions->wheres = [
            ['column' => "{$envOptions->aliasJoin}.deleted_at", 'operator' => "is", 'value' => 'null'],
        ];

        $query = (new self())->joinWithConditions($query, (new DocumentoTipo())->getTableName() . " as {$envOptions->aliasJoin}", "$aliasTable.documento_tipo_id", "=", "{$envOptions->aliasJoin}.id", $envOptions->toArray());

        return $query;
    }
}
