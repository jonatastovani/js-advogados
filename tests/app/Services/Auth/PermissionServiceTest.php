<?php

namespace Tests\App\Services\Auth;

use App\Services\Auth\PermissionService;
use Illuminate\Support\Fluent;
use Tests\TestCase;

#php artisan test --filter=PermissionServiceTest
class PermissionServiceTest extends TestCase
{

    // php artisan test --filter=PermissionServiceTest::test_getPermissoes
    public function test_getPermissoes(): void
    {
        $response =  app(PermissionService::class)->getPermissoes();
        $fluent = new Fluent($response);
        dump($fluent);
        $this->assertIsArray($fluent->toArray(),'Não é um array');
    }

}
