<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RefPermissaoGrupoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $insert = [
            ['id' => 1, 'nome' => 'Administrador do Sistema', 'individuais' => true],
            ['id' => 2, 'nome' => 'Diretoria Geral/Supervisão', 'individuais' => true],
            ['id' => 3, 'nome' => 'Diretoria de Centro', 'individuais' => true],
            ['id' => 4, 'nome' => 'Gerenciar Administradores de Núcleos', 'individuais' => true],
            ['id' => 5, 'nome' => 'Gerenciar Inclusão', 'individuais' => false],

            // ['id' => 6, 'nome' => 'Gerenciar Entradas', 'individuais' => false],
            // ['id' => 7, 'nome' => 'Gerenciar Ítens do Kit', 'individuais' => false],
            // ['id' => 8, 'nome' => 'Gerenciar Ítens Pertences', 'individuais' => false],
            // ['id' => 9, 'nome' => 'Gerenciar Pertences Guardados', 'individuais' => false],
            // ['id' => 10, 'nome' => 'Gerenciar Sedex Retidos', 'individuais' => false],
            // ['id' => 11, 'nome' => 'Inclusão e Dados Cadastrais', 'individuais' => false],
            // ['id' => 12, 'nome' => 'Gerenciar Entrada Inclusão', 'individuais' => false],
            // ['id' => 13, 'nome' => 'Gerenciar Listagem Artigos', 'individuais' => false],
            // ['id' => 14, 'nome' => 'Gerenciar Apresentações', 'individuais' => false],
            // ['id' => 15, 'nome' => 'Gerenciar Transferências', 'individuais' => false],
            // ['id' => 16, 'nome' => 'Gerenciar Unidades Prisionais', 'individuais' => false],
            // ['id' => 17, 'nome' => 'Gerenciar Penal', 'individuais' => true],
            // ['id' => 18, 'nome' => 'Zeladoria de Raios/Celas', 'individuais' => true],
            // ['id' => 19, 'nome' => 'Gerenciar Listagem Origens Inclusões', 'individuais' => false],
            // ['id' => 20, 'nome' => 'Gerenciar Listagem Tipos de Documentos', 'individuais' => false],
            // ['id' => 21, 'nome' => 'Gerenciar Listagem Profissões', 'individuais' => false],
            // ['id' => 22, 'nome' => 'Gerenciar Listagem Estados', 'individuais' => false],
            // ['id' => 23, 'nome' => 'Gerenciar Listagem Nacionalidades', 'individuais' => false],
            // ['id' => 24, 'nome' => 'Gerenciar Listagem Gêneros', 'individuais' => false],
            // ['id' => 25, 'nome' => 'Gerenciar Listagem Escolaridades', 'individuais' => false],
            // ['id' => 26, 'nome' => 'Gerenciar Listagem Cidades', 'individuais' => false],
            // ['id' => 27, 'nome' => 'Gerenciar Listagem Estado Civil', 'individuais' => false],
            // ['id' => 28, 'nome' => 'Gerenciar Listagem Cores de cabelos', 'individuais' => false],
            // ['id' => 29, 'nome' => 'Gerenciar Listagem Tipos de cabelos', 'individuais' => false],
            // ['id' => 30, 'nome' => 'Gerenciar Listagem Cores de peles', 'individuais' => false],
            // ['id' => 31, 'nome' => 'Gerenciar Listagem Cores de Olhos', 'individuais' => false],
            // ['id' => 32, 'nome' => 'Gerenciar Listagem Crenças', 'individuais' => false],
            // ['id' => 33, 'nome' => 'Gerenciar Listagem Tipos de Olhos', 'individuais' => false],
            // ['id' => 34, 'nome' => 'Gerenciar Listagem Orgãos Emissores', 'individuais' => false],
        ];        

        DB::table('ref_permissao_grupo')->insert($insert);

    }
}
