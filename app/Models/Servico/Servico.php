<?php

namespace App\Models\Servico;

use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\DomainIdentificationTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Servico extends Model
{
    use HasFactory, HasUuids, CommonsModelsMethodsTrait, ModelsLogsTrait, BelongsToTenant, DomainIdentificationTrait;

    protected $table = 'servico.servicos';
    protected $tableAsName = 'serv';
}
