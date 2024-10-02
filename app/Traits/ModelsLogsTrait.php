<?php

namespace App\Traits;

use App\Common\CommonsFunctions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

trait ModelsLogsTrait
{
    use SoftDeletes, LogsActivity;

    protected static $recordEvents = ['updated', 'deleted'];

    public function getActivitylogOptions(): LogOptions
    {
        $logOptions = new LogOptions();
        return $logOptions->logAll()
            ->dontSubmitEmptyLogs()
            ->useLogName(strtolower(class_basename($this)));
    }

    public function getCreatedAtAttribute($value)
    {
        return CommonsFunctions::formatarDataTimeZonaAmericaSaoPaulo($value);
    }

    public function getUpdatedAtAttribute($value)
    {
        return CommonsFunctions::formatarDataTimeZonaAmericaSaoPaulo($value);
    }

    public function getDeletedAtAttribute($value)
    {
        return CommonsFunctions::formatarDataTimeZonaAmericaSaoPaulo($value);
    }
    
    // Intercepta os eventos create, update e delete
    protected static function bootModelsLogsTrait()
    {
        static::creating(function (Model $model) {
            CommonsFunctions::inserirInfoCreated($model);
        });

        static::updating(function (Model $model) {
            CommonsFunctions::inserirInfoUpdated($model);
        });

        static::deleting(function (Model $model) {
            CommonsFunctions::inserirInfoDeleted($model);
        });
    }

    // Método boot padrão da Model
    protected static function boot()
    {
        parent::boot();

        // Chama o boot da trait ModelsLogsTrait para não interferir com outros boots
        static::bootModelsLogsTrait();
    }
}
