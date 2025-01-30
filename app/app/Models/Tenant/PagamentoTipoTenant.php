<?php

namespace App\Models\Tenant;

use App\Models\Referencias\PagamentoTipo;
use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;
use Stancl\Tenancy\Database\Concerns\HasDataColumn;

class PagamentoTipoTenant extends Model
{
    use HasFactory, HasUuids, CommonsModelsMethodsTrait, ModelsLogsTrait, BelongsToTenant, HasDataColumn;

    protected $table = 'tenant.pagamento_tipo_tenants';
    protected $tableAsName = 'pag_tip_ten';

    public static function getCustomColumns(): array
    {
        return array_merge(self::getCustomColumnsDefault(), [
            'nome',
            'descricao',
            'pagamento_tipo_id',
            'ativo_bln',
        ]);
    }

    public function pagamento_tipo()
    {
        return $this->belongsTo(PagamentoTipo::class);
    }
}
