<?php

namespace Database\Seeders\Pessoa;

use App\Enums\DocumentoTipoEnum;
use App\Enums\PessoaPerfilTipoEnum;
use App\Enums\PessoaTipoEnum;
use App\Helpers\UUIDsHelpers;
use App\Models\Pessoa\Pessoa;
use App\Models\Pessoa\PessoaDocumento;
use App\Models\Pessoa\PessoaJuridica;
use App\Models\Pessoa\PessoaPerfil;
use App\Models\Tenant\DocumentoTipoTenant;
use Illuminate\Database\Seeder;

class PessoaPerfilEmpresaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminTenantUserId = UUIDsHelpers::getAdminTenantUser();
        $tenantId = 'jsadvogados';

        $pessoaJuridica = PessoaJuridica::create([
            "id" => "9de45a89-bc08-4fba-806f-62df4657bd53",
            "razao_social" => "JORGE SILVA SOCIEDADE INDIVIDUAL DE ADVOCACIA",
            "nome_fantasia" => "JS Advogados",
            "tenant_id" => $tenantId,
            'created_user_id' => $adminTenantUserId
        ]);

        $pessoa = Pessoa::create([
            "id" => "9de45a89-c38e-4236-a0b8-947fbf23ce86",
            "pessoa_dados_type" => PessoaTipoEnum::PESSOA_JURIDICA->value,
            "pessoa_dados_id" => $pessoaJuridica->id,
            "tenant_id" => $tenantId,
            'created_user_id' => $adminTenantUserId
        ]);

        $documentoTipoTenant = DocumentoTipoTenant::where('tenant_id', $tenantId)->where('documento_tipo_id', DocumentoTipoEnum::CNPJ->value)->first();

        PessoaDocumento::create([
            "id" => "9de45a89-c7dd-4ed0-a48e-ff8e9b0f4b7a",
            "pessoa_id" => $pessoa->id,
            "documento_tipo_tenant_id" => $documentoTipoTenant->id,
            "numero" => "40.910.109/0001-80",
            "tenant_id" => $tenantId,
            'created_user_id' => $adminTenantUserId
        ]);

        PessoaPerfil::create([
            "id" => "9de45a89-cca7-4b82-9cc7-88f2d3f94454",
            "pessoa_id" => $pessoa->id,
            "perfil_tipo_id" => PessoaPerfilTipoEnum::EMPRESA->value,
            "tenant_id" => $tenantId,
            'created_user_id' => $adminTenantUserId
        ]);
    }
}
