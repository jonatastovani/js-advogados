<?php

namespace App\Models;

use App\Common\CommonsFunctions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class UserPermissao extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'user_permissao';

    public function user()
    {

        return $this->belongsTo(User::class, 'created_user_id');
    }

    public function permissao()
    {

        return $this->belongsTo(RefPermissao::class, 'permissao_id');
    }

    // protected $appends = ['formatted_created_at'];

    // public function getFormattedCreatedAtAttribute()
    // {
    //     return Carbon::parse($this->attributes['created_at'])->timezone(config('app.timezone'))->toDateTimeString();
    // }

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

    public function getActivitylogOptions(): LogOptions
    {
        $logOptions = new LogOptions();
        return $logOptions->logAll()
            ->dontSubmitEmptyLogs()
            ->useLogName(strtolower(class_basename($this)));
    }
}
