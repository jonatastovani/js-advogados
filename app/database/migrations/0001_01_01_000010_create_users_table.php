<?php

use App\Traits\SchemaTrait;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use SchemaTrait;

    protected $model;
    protected $schema;

    public function __construct()
    {
        $this->model = new App\Models\Auth\User();
        $this->schema = $this->model::getSchemaName();
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->createSchemaIfNotExists($this->schema);
        Schema::create("{$this->schema}.users", function (Blueprint $table) {
            $this->addIDFieldAsUUID($table);

            $table->string('nome');
            $table->string('username')->unique();
            $table->string('password');


            $this->addTenantIDField($table);

            // $table->string('email')->unique();
            // $table->timestamp('email_verified_at')->nullable();

            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create("{$this->schema}.password_reset_tokens", function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create("{$this->schema}.sessions", function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignUuid('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("{$this->schema}.users");
        Schema::dropIfExists("{$this->schema}.password_reset_tokens");
        Schema::dropIfExists("{$this->schema}.sessions");
    }
};
