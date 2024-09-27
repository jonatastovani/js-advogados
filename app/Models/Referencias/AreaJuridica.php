<?php

namespace App\Models\Referencias;

use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AreaJuridica extends Model
{
    use HasFactory, HasUuids, CommonsModelsMethodsTrait, ModelsLogsTrait;

    protected $table = 'referencias.area_juridicas';
    protected $tableAsName = 'ref_area_jur';
}
