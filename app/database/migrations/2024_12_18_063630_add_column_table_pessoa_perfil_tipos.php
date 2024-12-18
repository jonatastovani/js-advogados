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
        $this->model = new App\Models\Referencias\PessoaPerfilTipo();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table($this->model->getTableName(), function (Blueprint $table) {
            $table->json('configuracao')->nullable()->after('descricao');
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
                'configuracao',
            ]);
        });
    }
};
