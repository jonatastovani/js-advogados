<?php

namespace App\Helpers;

enum EnumPermissionModulesHelper: int
{
    case ADMINISTRADOR = 1;
    case GPUONLINE_API = 2;
    case UNIDADE = 3;
    case INTELIGENCIA = 4;

    // Método para retornar os detalhes como array
    public function detalhes(): array
    {
        return match ($this) {
            self::ADMINISTRADOR => [
                'id' => 1,
                'nome' => 'Módulo Administrador',
                'descricao' => 'Administração dos Módulos',
                'slug' => '/admin',
                'tenant_id' => 'admin',
                'tipo_modulo' => 'tenant',
            ],
            self::GPUONLINE_API => [
                'id' => 2,
                'nome' => 'Módulo Api GPU Online - Legacy',
                'descricao' => 'Módulo os endpoints da API',
                'slug' => '/api/gpu-online-api',
                'tenant_id' => 'gpu-online-api',
                'tipo_modulo' => 'tenant',
            ],
            self::UNIDADE => [
                'id' => 3,
                'nome' => 'Módulo Unidade',
                'descricao' => 'Módulo para as Unidades Prisionais e seus Setores',
                'slug' => '/unidade/{tenant}',
                'tenant_type_id' => 3,
                'tipo_modulo' => 'tenant_type',
            ],
            self::INTELIGENCIA => [
                'id' => 4,
                'nome' => 'Módulo Inteligência',
                'descricao' => 'Módulo para o Sistema inteligência e suas funcionalidades',
                'slug' => '/modulo/inteligencia',
                'tenant_id' => 'inteligencia',
                'tipo_modulo' => 'tenant',
            ],
        };
    }
}
