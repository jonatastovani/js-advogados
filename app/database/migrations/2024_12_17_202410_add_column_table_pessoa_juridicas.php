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
        $this->model = new App\Models\Pessoa\PessoaJuridica();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table($this->model->getTableName(), function (Blueprint $table) {
            // // Informações de contato
            // $table->string('email')->nullable()->after('data_fundacao');
            // $table->string('telefone')->nullable()->after('email');
            // $table->string('celular')->nullable()->after('telefone');
            // $table->string('site')->nullable()->after('celular');

            // // Endereço
            // $table->string('logradouro')->nullable()->after('site');
            // $table->string('numero')->nullable()->after('logradouro');
            // $table->string('complemento')->nullable()->after('numero');
            // $table->string('bairro')->nullable()->after('complemento');
            // $table->string('cidade')->nullable()->after('bairro');
            // $table->string('estado', 2)->nullable()->after('cidade');
            // $table->string('cep')->nullable()->after('estado');
            
            // Informações financeiras
            $table->decimal('capital_social', 15, 2)->nullable()->after('data_fundacao');
            $table->string('regime_tributario')->nullable()->after('capital_social');

            // Informações do responsável
            $table->string('responsavel_legal')->nullable()->after('regime_tributario');
            $table->string('cpf_responsavel', 20)->nullable()->after('responsavel_legal');

            $table->text('observacao')->nullable()->after('cpf_responsavel');
            $table->boolean('ativo_bln')->default(true)->after('observacao');
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
            $table->dropColumn([
                'capital_social',
                'regime_tributario',
                'responsavel_legal',
                'cpf_responsavel',
                'observacao',
                'ativo_bln',
            ]);
        });
    }
};
