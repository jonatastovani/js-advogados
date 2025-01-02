<?php

use App\Traits\MigrateTrait;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use MigrateTrait;
    protected $model;

    public function __construct()
    {
        $this->model = new App\Models\Pessoa\PessoaPerfil();
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->createSchemaIfNotExists($this->model::getSchemaName());
        Schema::create($this->model->getTableName(), function (Blueprint $table) {
            $this->addIDFieldAsUUID($table);
            $this->addTenantIDField($table);

            $table->uuid('pessoa_id')->nullable();
            $table->foreign('pessoa_id')->references('id')->on((new App\Models\Pessoa\Pessoa)->getTableName());

            $table->unsignedSmallInteger('perfil_tipo_id');
            $table->foreign('perfil_tipo_id')->references('id')->on((new App\Models\Referencias\PessoaPerfilTipo)->getTableName());

            $table->string('observacao')->nullable();
            
            $this->addCommonFieldsCreatedUpdatedDeleted($table);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists($this->model->getTableName());
    }
};
