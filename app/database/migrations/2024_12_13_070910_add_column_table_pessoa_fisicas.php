<?php

declare(strict_types=1);

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
        $this->model = new App\Models\Pessoa\PessoaFisica();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table($this->model->getTableName(), function (Blueprint $table) {
            $table->string('nascimento_cidade')->nullable();
            $table->string('nascimento_estado')->nullable();
            $table->string('nacionalidade')->nullable();
            $table->string('profissao')->nullable();

            $table->uuid('estado_civil_id')->nullable();
            $table->foreign('estado_civil_id')->references('id')->on((new App\Models\Tenant\EstadoCivilTenant())->getTableName());

            $table->uuid('escolaridade_id')->nullable();
            $table->foreign('escolaridade_id')->references('id')->on((new App\Models\Tenant\EscolaridadeTenant())->getTableName());

            $table->uuid('sexo_id')->nullable();
            $table->foreign('sexo_id')->references('id')->on((new App\Models\Tenant\SexoTenant())->getTableName());

            $table->text('observacao')->nullable();
            $table->boolean('ativo_bln')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table($this->model->getTableName(), function (Blueprint $table) {
            $table->dropForeign(['estado_civil_id']);
            $table->dropForeign(['escolaridade_id']);
            $table->dropForeign(['sexo_id']);

            $table->dropColumn('nascimento_cidade');
            $table->dropColumn('nascimento_estado');
            $table->dropColumn('nacionalidade');

            $table->dropColumn('estado_civil_id');
            $table->dropColumn('escolaridade_id');
            $table->dropColumn('sexo_id');
            
            $table->dropColumn('observacao');
            $table->dropColumn('ativo_bln');
        });
    }
};
