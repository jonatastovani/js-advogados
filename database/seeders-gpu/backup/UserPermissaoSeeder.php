<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserPermissaoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $iplocal = config('sistema.ipHost');

        $insert = [
            ['id' => 1, 'user_id'=> 1, 'permissao_id' => 1, 'data_inicio' => now(), 'created_user_id' => 1, 'created_ip' => $iplocal, 'created_at' => now()],
            // ['id' => 2, 'user_id'=> 2, 'permissao_id' => 69, 'data_inicio' => now(), 'created_user_id' => 1, 'created_ip' => $iplocal, 'created_at' => now()],
        ];
        
        DB::table('user_permissao')->insert($insert);
    }
}
