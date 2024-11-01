<?php

namespace Tests\App\Http\Controllers\Auth;

use App\Http\Controllers\Auth\PermissionController;
use Illuminate\Support\Fluent;
use Tests\TestCase;

#php artisan test --filter=PermissionControllerTest
class PermissionControllerTest extends TestCase
{

    // php artisan test --filter=PermissionControllerTest::test_getPermissoes
    public function test_getPermissoes(): void
    {
        $response = app(PermissionController::class)->getPermissoes();
        $fluent = new Fluent($response);
        dump($fluent->original);
        $this->assertIsArray($fluent->original);
    }

}
