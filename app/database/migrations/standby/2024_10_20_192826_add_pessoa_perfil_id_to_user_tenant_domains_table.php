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
        $this->model = new App\Models\Auth\UserTenantDomain();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table($this->model::getTableName(), function (Blueprint $table) {
            // Adiciona a coluna 'pessoa_perfil_id' após 'user_id'
            $table->uuid('pessoa_perfil_id')->after('user_id')->nullable();

            // Define a chave estrangeira para a tabela de PessoaPerfil
            $table->foreign('pessoa_perfil_id')
                ->references('id')
                ->on(App\Models\Pessoa\PessoaPerfil::getTableName()); // Opcional: define o comportamento ao excluir
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table($this->model::getTableName(), function (Blueprint $table) {
            // Remove a chave estrangeira e a coluna 'pessoa_perfil_id'
            $table->dropForeign(['pessoa_perfil_id']);
            $table->dropColumn('pessoa_perfil_id');
        });
    }
};
