<?php

namespace App\Models\GPU;

use App\Traits\CommonsModelsMethodsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PresoVulgoGPU extends Model
{
    use HasFactory, CommonsModelsMethodsTrait;

    protected $table = 'preso.tb_preso_vulgo';
    protected $primaryKey = 'psvg_id';
}
