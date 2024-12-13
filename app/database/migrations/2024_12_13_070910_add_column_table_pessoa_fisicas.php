<?php

declare(strict_types=1);

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
            $table->string('nacionalidade')->nullable();

            $table->uuid('estado_civil_id')->nullable();
            $table->foreign('estado_civil_id')->references('id')->on((new App\Models\Tenant\EstadoCivilTenant())->getTableName());

            $table->uuid('escolaridade_id')->nullable();
            $table->foreign('escolaridade_id')->references('id')->on((new App\Models\Tenant\EscolaridadeTenant())->getTableName());

            $table->uuid('genero_id')->nullable();
            $table->foreign('genero_id')->references('id')->on((new App\Models\Tenant\GeneroTenant())->getTableName());

            $table->text('observacoes')->nullable();
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
            $table->dropForeign(['genero_id']);

            $table->dropColumn('nascimento_cidade');
            $table->dropColumn('nacionalidade');

            $table->dropColumn('estado_civil_id');
            $table->dropColumn('escolaridade_id');
            $table->dropColumn('genero_id');
            
            $table->dropColumn('observacoes');
            $table->dropColumn('ativo_bln');
        });
    }
};
