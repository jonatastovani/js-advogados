<?php

namespace App\Models\Servico;

use App\Models\Pessoa\PessoaPerfil;
use App\Traits\BelongsToDomain;
use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class ServicoCliente extends Model
{
    use HasFactory,
        HasUuids,
        CommonsModelsMethodsTrait,
        ModelsLogsTrait,
        BelongsToTenant,
        BelongsToDomain;

    protected $table = 'servico.servico_clientes';
    protected $tableAsName = 'serv_cli';

    protected $fillable = [
        'id',
        'servico_id',
        'perfil_id',
    ];

    public function servico()
    {
        return $this->belongsTo(Servico::class);
    }

    public function perfil()
    {
        return $this->belongsTo(PessoaPerfil::class);
    }
}
