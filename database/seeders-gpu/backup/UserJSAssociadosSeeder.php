<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\Auth\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserJSAssociadosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $insert = [
            [
                'name' => 'Jorge Silva',
                'email' => 'jorgesilva@jsadvogados',
                'password' => bcrypt('password'),
                'tenant_id' => 'jsadvogados'
            ],
            [
                'name' => 'Jéter Tovani',
                'email' => 'jetertovani@jsadvogados',
                'password' => bcrypt('password'),
                'tenant_id' => 'jsadvogados'
            ],
            [
                'name' => 'Eloíza',
                'email' => 'eloiza@jsadvogados',
                'password' => bcrypt('password'),
                'tenant_id' => 'jsadvogados'
            ],
            [
                'name' => 'Simone',
                'email' => 'simone@jsadvogados',
                'password' => bcrypt('password'),
                'tenant_id' => 'jsadvogados'
            ],
            [
                'name' => 'Eduardo',
                'email' => 'eduardo@jsadvogados',
                'password' => bcrypt('password'),
                'tenant_id' => 'jsadvogados'
            ],
        ];

        foreach ($insert as $data) {
            User::create($data);
        }
    }
}
