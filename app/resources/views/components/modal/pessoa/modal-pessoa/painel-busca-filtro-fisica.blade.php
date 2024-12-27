@php
    use Illuminate\Support\Fluent;
    $mergeDados = new Fluent([
        'camposFiltrados' => [
            'nome' => ['nome' => 'Nome'],
            'nome_social' => ['nome' => 'Nome social'],
            'pai' => ['nome' => 'Pai'],
            'mae' => ['nome' => 'Mãe'],
        ],
        'arrayCamposOrdenacao' => [
            'nome' => ['nome' => 'Nome'],
            'created_at' => ['nome' => 'Data cadastro'],
        ],
        'arrayCamposChecked' => ['nome', 'nome_social'],
    ]);
    $dados = new Fluent(array_merge($mergeDados->toArray(), $dados->toArray()));
@endphp

<x-consulta.formulario-padrao-filtro.componente :sufixo="$sufixo" :dados="$dados" />

@include('components.modal.pessoa.modal-pessoa.tabela-dados-fisica', ['sufixo' => $sufixo])
