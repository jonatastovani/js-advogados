<?php

use App\Traits\SchemaTrait;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use SchemaTrait;

    protected $model;

    public function __construct()
    {
        $this->model = new App\Models\Auth\Permission();
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create($this->model::getTableName(), function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('nome_completo')->nullable();
            $table->string('descricao');
            $table->boolean('ativo')->default(true);
            $this->addCommonFieldsCreatedUpdatedDeleted($table);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists($this->model::getTableName());
    }
};