@php
    use Illuminate\Support\Fluent;
    $mergeDados = new Fluent([
        'camposFiltrados' => [
            'matricula' => ['nome' => 'Matrícula'],
            'rg' => ['nome' => 'RG'],
            'cpf' => ['nome' => 'CPF'],
            'nome' => ['nome' => 'Nome'],
            'nome_social' => ['nome' => 'Nome social'],
            'pai' => ['nome' => 'Pai'],
            'mae' => ['nome' => 'Mãe'],
            'vulgo_alias' => ['nome' => 'Vulgo / Alias'],
            'rs' => ['nome' => 'RS'],
            'oab' => ['nome' => 'OAB'],
            'telefone' => ['nome' => 'Telefone'],
        ],
        'arrayCamposChecked' => ['matricula', 'cpf'],
    ]);
    $dados = new Fluent(array_merge($mergeDados->toArray(), $dados->toArray()));
@endphp

<x-consulta.formulario-padrao-criterio.componente :sufixo="$sufixo" :dados="$dados" />

@include('components.modal.pessoa.modal-pessoa.tabela-dados-fisica', ['sufixo' => $sufixo])
