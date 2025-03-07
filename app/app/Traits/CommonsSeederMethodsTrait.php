<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait CommonsSeederMethodsTrait
{

    protected $tenantId;
    protected $domainId;
    protected $atualizarIdIncremental = false;

    protected function atualizaIdIncrementalNumerico(): void
    {
        $maxId = $this->model::max('id');  // Obtém o maior ID atual na tabela
        if ($maxId) {
            // Substitua "tenant_types_id_seq" pelo nome correto da sequência para sua tabela e coluna
            $sequenceName = $this->model->getTableName() . '_id_seq';  // Nome da sequência associada à coluna ID da tabela
            DB::statement('SELECT setval(\'' . $sequenceName . '\', ' . ($maxId + 1) . ', false)');
        }
    }

    /**
     * Realiza inserções ou atualizações dinâmicas com base nos dados fornecidos.
     * Se um registro com o mesmo `id` existir, ele será atualizado.
     * Caso contrário, será criado um novo registro.
     *
     * @param array $dataList Lista de dados a serem inseridos ou atualizados.
     * @param callable|null $beforeUpdateCallback Callback opcional para processar dados antes do update.
     * @param callable|null $beforeCreateCallback Callback opcional para processar dados antes do create.
     * @return void
     */
    public function upsertData(array $dataList, ?callable $beforeUpdateCallback = null, ?callable $beforeCreateCallback = null): void
    {
        $adminTenantUserId = \App\Helpers\UUIDsHelpers::getAdminTenantUser();

        foreach ($dataList as $data) {
            // Tenta encontrar o registro com base no ID
            $resource = isset($data['id']) ? $this->model::find($data['id']) : null;

            if ($resource) {
                // Chamada do callback antes do update (opcional)
                if ($beforeUpdateCallback) {
                    $data = $beforeUpdateCallback($data, $resource);
                }

                if ($this->verificaUsoDoLogsActivity()) {
                    // Adiciona campos de atualização
                    $data['updated_user_id'] = $adminTenantUserId;
                }

                // Atualiza o registro
                $resource->update($data);
            } else {
                // Chamada do callback antes do create (opcional)
                if ($beforeCreateCallback) {
                    $data = $beforeCreateCallback($data);
                }

                if ($this->verificaUsoDoLogsActivity()) {
                    // Adiciona campos de criação
                    $data['created_user_id'] = $adminTenantUserId;
                }

                // Verifica se a trait BelongsToTenant esta sendo utilizada no modelo
                if ($this->verificaUsoDoBelongsToTenant() && !isset($data['tenant_id']) && $this->tenantId) {
                    $data['tenant_id'] = $this->tenantId;
                }

                // Verifica se a trait BelongsToDomain esta sendo utilizada no modelo
                if ($this->verificaUsoDoBelongsToDomain() && !isset($data['domain_id'])) {
                    $data['domain_id'] = $this->domainId;
                }

                // Cria um novo registro
                $this->model::create($data);
            }
        }

        if ($this->atualizarIdIncremental) {
            // Atualiza o ID incremental se requisitado
            $this->atualizaIdIncrementalNumerico();
        }
    }

    private function verificaUsoDoLogsActivity(): bool
    {
        // Verifica se a trait LogsActivity esta sendo utilizada no modelo
        if (in_array(\Spatie\Activitylog\Traits\LogsActivity::class, class_uses_recursive($this->model))) {
            return true;
        }
        return false;
    }

    private function verificaUsoDoBelongsToTenant(): bool
    {
        // Verifica se a trait BelongsToTenant esta sendo utilizada no modelo
        if (in_array(\Stancl\Tenancy\Database\Concerns\BelongsToTenant::class, class_uses_recursive($this->model))) {
            return true;
        }
        return false;
    }

    private function verificaUsoDoBelongsToDomain(): bool
    {
        // Verifica se a trait BelongsToDomain esta sendo utilizada no modelo
        if (in_array(\App\Traits\BelongsToDomain::class, class_uses_recursive($this->model))) {
            return true;
        }
        return false;
    }

    public function setTenantId($tenantId)
    {
        $this->tenantId = $tenantId;
        return $this;
    }

    public function setDomainId($domainId)
    {
        $this->domainId = $domainId;
        return $this;
    }

    public function setDefaultTenantId()
    {
        $this->tenantId = 'jsadvogados';
        return $this;
    }

    public function setAtualizaIdIncrementalBln($atualizarIdIncremental)
    {
        $this->atualizarIdIncremental = $atualizarIdIncremental;
        return $this;
    }
}
