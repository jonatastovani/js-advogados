<?php

namespace App\Models\Financeiro;

use App\Helpers\NumeracaoSequencialHelper;
use App\Models\Comum\IdentificacaoTags;
use App\Models\Comum\ParticipacaoParticipante;
use App\Models\Referencias\LancamentoStatusTipo;
use App\Models\Referencias\MovimentacaoContaTipo;
use App\Models\Tenant\ContaTenant;
use App\Models\Tenant\LancamentoCategoriaTipoTenant;
use App\Models\Tenant\TagTenant;
use App\Traits\BelongsToDomain;
use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Fluent;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;
use Stancl\Tenancy\Database\Concerns\HasDataColumn;

class LancamentoRessarcimento extends Model
{
    use HasFactory,
        HasUuids,
        CommonsModelsMethodsTrait,
        ModelsLogsTrait,
        BelongsToTenant,
        BelongsToDomain,
        HasDataColumn;

    protected $table = 'financeiro.lancamento_ressarcimentos';
    protected $tableAsName = 'lanc_ressar';
    // Variável estática para armazenar a sequência temporariamente
    protected static $sequenciaTemporaria;

    protected $casts = [
        'valor_esperado' => 'float',
        'valor_quitado' => 'float',
    ];

    protected $fillable = [
        'movimentacao_tipo_id',
        'descricao',
        'valor_esperado',
        'data_vencimento',
        'categoria_id',
        'conta_id',
        'observacao',
        'status_id',
        'tenant_id',
        'domain_id',
        'created_user_id',
    ];

    protected $exceptHidden = [
        'created_at'
    ];

    public static function getCustomColumns(): array
    {
        return array_merge(self::getCustomColumnsDefault(), [
            'numero_lancamento',
            'movimentacao_tipo_id',
            'descricao',
            'valor_esperado',
            'data_vencimento',
            'valor_quitado',
            'data_quitado',
            'categoria_id',
            'conta_id',
            'status_id',
            'observacao',
        ]);
    }

    public function movimentacao_tipo()
    {
        return $this->belongsTo(MovimentacaoContaTipo::class);
    }

    public function categoria()
    {
        return $this->belongsTo(LancamentoCategoriaTipoTenant::class);
    }

    public function conta()
    {
        return $this->belongsTo(ContaTenant::class);
    }

    public function status()
    {
        return $this->belongsTo(LancamentoStatusTipo::class);
    }

    public function participantes()
    {
        return $this->morphMany(ParticipacaoParticipante::class, 'parent');
    }

    public function movimentacao_conta_participante()
    {
        return $this->morphMany(MovimentacaoContaParticipante::class, 'parent');
    }

    public function tags()
    {
        return $this->morphMany(IdentificacaoTags::class, 'parent');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Model $model) {
            // Verifica se já foi informado um número e ano
            if (!$model->numero_lancamento) {
                $tenantId = $model->tenant_id ?? tenant('id');
                $sequencia = NumeracaoSequencialHelper::obterProximoNumero('lancamento_ressarcimento', $tenantId);
                // Preenche o campo numero_lancamento com o número obtido
                $model->numero_lancamento = $sequencia['numero'];

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

    /**
     * Insere uma cláusula de junção do Lançamento até a TagTenant.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query A instância do construtor de consultas.
     * @param array $options O array de opcões de personalização.
     *              - 'typeJoin' (opcional) => 'inner', 'left' ou 'right' para definir o tipo de junção. Padrão é 'left'.
     *              - 'aliasTable' (opcional) Alias da tabela IdentificacaoTags. Padrão está definido no atributo protegido 'tableAsName' da App\Models\Comum\IdentificacaoTags.
     *              - 'aliasJoin' (opcional) Alias da tabela que irá ser juntada. Padrão está definido no atributo protegido 'tableAsName' da model informada.
     *              - 'typeTagTenant' (opcional) => 'inner', 'left' ou 'right' para definir o tipo de junção da tabela Servico. Padrão é 'left'.
     *              - 'aliasTagTenant' (opcional) Alias da tabela App\Models\Tenant\TagTenant que irá ser juntada. Padrão está definido no atributo protegido 'tableAsName' da model informada.
     * @return \Illuminate\Database\Eloquent\Builder A instância do construtor de consultas. 
     */
    public static function joinTagTenant(Builder $query, array $options = [])
    {
        // Join com o IdentificacaoTags
        $envOptions = new Fluent([]);
        $envOptions->aliasJoin = $options['aliasJoin'] ?? (new IdentificacaoTags())->getTableAsName();
        $envOptions->typeJoin = $options['typeJoin'] ?? 'left';
        $aliasTable = isset($options['aliasTable']) ? $options['aliasTable'] : (new self())->getTableAsName();
        $envOptions->wheres = [
            ['column' => "{$envOptions->aliasJoin}.parent_type", 'operator' => "=", 'value' => (new self())->getMorphClass()],
            ['column' => "{$envOptions->aliasJoin}.deleted_at", 'operator' => "is", 'value' => 'null'],
        ];

        $query = (new self())->joinWithConditions($query, (new IdentificacaoTags())->getTableName() . " as {$envOptions->aliasJoin}", "$aliasTable.id", "=", "{$envOptions->aliasJoin}.parent_id", $envOptions->toArray());

        // Join com a TagTenant
        $aliasTable = $envOptions->aliasJoin;
        $envOptions->aliasJoin = $options['aliasTagTenant'] ?? (new TagTenant())->getTableAsName();
        $envOptions->typeJoin = $options['typeTagTenant'] ?? 'left';
        $envOptions->wheres = [
            ['column' => "{$envOptions->aliasJoin}.deleted_at", 'operator' => "is", 'value' => 'null'],
        ];

        $query = (new self())->joinWithConditions($query, (new TagTenant())->getTableName() . " as {$envOptions->aliasJoin}", "$aliasTable.tag_id", "=", "{$envOptions->aliasJoin}.id", $envOptions->toArray());

        return $query;
    }
}
