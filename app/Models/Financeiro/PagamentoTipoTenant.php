<?php

namespace App\Models\Financeiro;

use App\Models\Referencias\PagamentoTipo;
use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class PagamentoTipoTenant extends Model
{
    use HasFactory, HasUuids, CommonsModelsMethodsTrait, ModelsLogsTrait, BelongsToTenant;

    protected $table = 'financeiro.pagamento_tipo_tenants';
    protected $tableAsName = 'pag_tip_ten';

    protected $casts = [
        // O campo configuracao será tratado como um array
        'configuracao' => 'array',
    ];

    public function pagamento_tipo()
    {
        return $this->belongsTo(PagamentoTipo::class);
    }
}
