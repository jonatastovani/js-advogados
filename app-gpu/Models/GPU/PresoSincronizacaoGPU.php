<?php

namespace App\Models\GPU;

use App\Traits\CommonsModelsMethodsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;

class PresoSincronizacaoGPU extends Model
{
    use HasFactory, CommonsModelsMethodsTrait;

    protected $table = 'preso.tb_preso_sincronizacao';
    protected $primaryKey = 'psi_id_preso';
    protected $tableAsName = 'psi';

    /**
     * Retorna todos os vulgos que foram cadastrados.;
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function vulgos()
    {
        return $this->hasMany(PresoVulgoGPU::class, 'psvg_id_preso_vl');
    }

    /**
     * Insere uma cláusula de junção de vulgo na consulta com base no tipo fornecido.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query A instância do construtor de consultas.
     * @param array $options O array de opcões de personalização.
     *              - 'tipoJoin' (opcional) => 'inner', 'left' ou 'right' para definir o tipo de junção. Padrão é 'left'.
     *              - 'aliasTabela' (opcional) Alias da tabela preso. Padrão é 'psi'.
     * 
     * @return \Illuminate\Database\Eloquent\Builder A instância do construtor de consultas. 
     */
    public static function inserirVulgo($query, array $options = [])
    {
        $tipoJoin = isset($options['tipoJoin']) ? $options['tipoJoin'] : 'left';
        $envDados = [
            'aliasTabela' => isset($options['aliasTabela']) ? $options['aliasTabela'] : (new self())::getTableAsName(),
        ];
        switch ($tipoJoin) {

            case 'inner':
                return $query->join(PresoVulgoGPU::getTableName() . " as psvg", function (JoinClause $join) use ($envDados) {
                    $join->on("{$envDados['aliasTabela']}.psi_id_preso", '=', "psvg.psvg_id_preso_vl")
                        ->where("psvg.psvg_vulgo", "<>", "");
                });
                break;

            case 'right':
                return $query->leftJoin(PresoVulgoGPU::getTableName() . " as psvg", function (JoinClause $join) use ($envDados) {
                    $join->on("{$envDados['aliasTabela']}.psi_id_preso", '=', "psvg.psvg_id_preso_vl")
                        ->where("psvg.psvg_vulgo", "<>", "");
                });
                break;

            default:
                return $query->leftJoin(PresoVulgoGPU::getTableName() . " as psvg", function (JoinClause $join) use ($envDados) {
                    $join->on("{$envDados['aliasTabela']}.psi_id_preso", '=', "psvg.psvg_id_preso_vl")
                        ->where("psvg.psvg_vulgo", "<>", "");
                });
                break;
        }
    }
}
