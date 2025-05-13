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
        $this->model = new App\Models\Pessoa\PessoaPerfil();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table($this->model->getTableName(), function (Blueprint $table) {

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
            $table->dropColumn('ativo_bln');
        });
    }
};
