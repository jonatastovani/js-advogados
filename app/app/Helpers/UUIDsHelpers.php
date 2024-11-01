<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class UUIDsHelpers
{
    private function get(string $path): string | null
    {
        $pathStorage = $this->getPathStorage();
        $path = "$pathStorage/$path";
        return File::exists($path) ? File::get($path) : null;
    }

    private function getPathStorage(): string
    {
        // Verifica se a última pasta não é 'storage' e ajusta o caminho
        // O Tenant joga uma pasta personalizada para cada tenant no Storage quando está executando.
        $pathStorage = storage_path();
        $folderVerification = basename($pathStorage);
        while ($folderVerification != 'storage') {
            $pathStorage = dirname($pathStorage);
            $folderVerification = basename($pathStorage);
        }

        return $pathStorage;
    }
    private function set(string $path, string $value): string
    {
        $pathStorage = $this->getPathStorage();
        $path = "$pathStorage/$path";
        File::put($path, $value);
        return $value;
    }

    private function newUuid(): string
    {
        return (string) Str::uuid();
    }

    private function execute(string $path): string
    {
        $id = $this->get($path);
        if (!Str::isUuid($id)) {
            $id = $this->set($path, $this->newUuid());
        }
        return $id;
    }

    public static function getAdmin()
    {
        return (new self())->execute('app/admin_uuid.txt');
    }


    public static function getAdminTenantUser()
    {
        return (new self())->execute('app/admin_tenant_user_uuid.txt');
    }

    public static function getGpuOnlineApi()
    {
        return (new self())->execute('app/gpu_online_api_uuid.txt');
    }

}
