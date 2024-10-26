<?php

namespace Database\Factories\Pessoa;

use App\Helpers\UUIDsHelpers;
use App\Models\Pessoa\PessoaFisica;
use Illuminate\Database\Eloquent\Factories\Factory;

class PessoaFisicaFactory extends Factory
{
    protected $model = PessoaFisica::class;

    public function definition(): array
    {
        return [
            'tenant_id' => null,
            'nome' => $this->faker->name(),
            'mae' => $this->faker->name(),
            'pai' => $this->faker->name(),
            'nascimento_data' => $this->faker->optional()->date(),
            'created_user_id' => UUIDsHelpers::getAdminTenantUser(),
        ];
    }

    /**
     * Define o tenant_id dinamicamente.
     *
     * @param int $tenantId
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function comTenantId(string $tenantId): self
    {
        return $this->state(function () use ($tenantId) {
            return [
                'tenant_id' => $tenantId,
            ];
        });
    }
}
