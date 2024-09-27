<?php

namespace App\Models\Financeiro;

use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimentacaoTipo extends Model
{
    use HasFactory, CommonsModelsMethodsTrait, ModelsLogsTrait;
}
