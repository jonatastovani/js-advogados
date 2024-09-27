<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Builder;

class CreateActivityLogTable extends Migration
{
    public function up()
    {
        Builder::$defaultMorphKeyType = 'uuid';
        Schema::connection(config('activitylog.database_connection'))->create(config('activitylog.table_name'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('log_name')->nullable();
            $table->text('description');
            // $table->nullableMorphs('subject', 'subject');
            
            // Para o campo 'subject'
            $table->string('subject_id');
            $table->string('subject_type');
            
            $table->nullableMorphs('causer', 'causer');
            $table->text('properties')->nullable();
            $table->timestamps();
            $table->index('log_name');
        });
    }

    public function down()
    {
        Schema::connection(config('activitylog.database_connection'))->dropIfExists(config('activitylog.table_name'));
    }
}
