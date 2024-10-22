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
            'tenant_id' => 'jsadvogados',
            'pessoa_id' => null, // Esse valor será configurado dinamicamente
            'nome' => $this->faker->name(),
            'mae' => $this->faker->name(),
            'pai' => $this->faker->name(),
            'nascimento_data' => $this->faker->optional()->date(),
            'created_user_id' => UUIDsHelpers::getAdminTenantUser(),
        ];
    }

    /**
     * Define o pessoa_id dinamicamente.
     *
     * @param int $pessoaId
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function comPessoaId(string $pessoaId): self
    {
        return $this->state(function () use ($pessoaId) {
            return [
                'pessoa_id' => $pessoaId,
            ];
        });
    }
}
