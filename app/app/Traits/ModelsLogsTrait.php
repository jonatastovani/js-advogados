<?php

namespace App\Traits;

use App\Common\CommonsFunctions;
use App\Common\RestResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Fluent;
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
    }

    protected function runSoftDelete()
    {
        $query = $this->setKeysForSaveQuery($this->newModelQuery());

        // Desativa temporariamente os timestamps (updated_at e created_at)
        static::withoutTimestamps(function () use ($query) {

            $fluentData = new Fluent();
            CommonsFunctions::inserirInfoDeleted($fluentData);

            $columns = $fluentData->toArray();

            foreach ($fluentData->toArray() as $key => $value) {
                $this->{$key} = $value;
            }

            // Executa a query sem modificar o updated_at
            $query->update($columns);

            // Sincroniza os atributos originais com os novos valores
            $this->syncOriginalAttributes(array_keys($columns));

            // Dispara o evento trashed para soft delete
            $this->fireModelEvent('trashed', false);
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
