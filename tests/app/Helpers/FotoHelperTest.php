<?php

namespace Tests\App\Helpers;

use App\Helpers\FotoHelper;
use Illuminate\Support\Fluent;
use Tests\TestCase;

#php artisan test --filter=FotoHelperTest
class FotoHelperTest extends TestCase
{

    // php artisan test --filter=FotoHelperTest::test_buscarFotoPreso
    public function test_buscarFotoPreso(): void
    {
        $response = app(FotoHelper::class)->buscarFotoPreso(169162);
        $fluent = new Fluent($response);
        dump($fluent);
        $this->assertIsArray($fluent->toArray());
    }
}
