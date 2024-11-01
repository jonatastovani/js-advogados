<?php

namespace App\Models\GPU\Inteligencia;

use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InformacaoSubjetivaCategoria extends Model
{
    use HasFactory, HasUuids, CommonsModelsMethodsTrait, ModelsLogsTrait;

    protected $table = 'inteligencia.informacao_subjetiva_categorias';
    protected $tableAsName = 'inf_sub_cat';
}
