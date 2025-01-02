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
        $this->model = new App\Models\Servico\ServicoPagamentoLancamento();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table($this->model->getTableName(), function (Blueprint $table) {
            $table->uuid('parent_id')->nullable(); // Quando um lançamento deriva-se de outro (Casos de liquidação parcial, reagendamento, etc.)
            $table->foreign('parent_id')->references('id')->on($this->model->getTableName());
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
            // // Remove a chave estrangeira e a coluna 'parent_id'
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
        });
    }
};
