<?php

namespace App\Models\Servico;

use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicoPagamentoTarefaPresetRelacaoGrupo extends Model
{
    use HasFactory, HasUuids, CommonsModelsMethodsTrait, ModelsLogsTrait;

    protected $table = 'servico.servico_pagamento_tarefa_preset_relacao_grupos';
    protected $tableAsName = 'serv_pag_tar_pres_rel_gru';
}
