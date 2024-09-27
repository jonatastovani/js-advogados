<?php

use App\Traits\SchemaTrait;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use SchemaTrait;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('system.users', function (Blueprint $table) {
            $this->addIDFieldAsUUID($table);

            $table->string('nome');
            $table->string('username')->unique();
            $table->string('password');

            // $table->string('descricao')->nullable();

            $table->unsignedBigInteger('gepen_pessoa_id')->nullable()->unique();
            $table->unsignedBigInteger('gepen_rh_id')->nullable()->unique();

            // $table->string('email')->unique();
            // $table->timestamp('email_verified_at')->nullable();

            // $table->unsignedBigInteger('domain_id')->nullable()->after('tenant_id');
            // $table->foreign('domain_id')->references('id')->on('domains');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('system.password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('system.sessions', function (Blueprint $table) {
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
        Schema::dropIfExists('system.users');
        Schema::dropIfExists('system.password_reset_tokens');
        Schema::dropIfExists('system.sessions');
    }
};
