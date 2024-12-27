@php
    use Illuminate\Support\Fluent;
    $mergeDados = new Fluent([
        'camposFiltrados' => [
            'razao_social' => ['nome' => 'Razão social'],
            'nome_fantasia' => ['nome' => 'Nome fantasia'],
            'responsavel_legal' => ['nome' => 'Responsável legal'],
            'documento' => ['nome' => 'Documento'],
        ],
        'arrayCamposOrdenacao' => [
            'razao_social' => ['nome' => 'Razão social'],
            'nome_fantasia' => ['nome' => 'Nome fantasia'],
            'created_at' => ['nome' => 'Data cadastro'],
        ],
        'arrayCamposChecked' => ['razao_social', 'nome_fantasia', 'documento'],
    ]);
    $dados = new Fluent(array_merge($mergeDados->toArray(), $dados->toArray()));
@endphp

<x-consulta.formulario-padrao-filtro.componente :sufixo="$sufixo" :dados="$dados" />

@include('components.modal.pessoa.modal-pessoa.tabela-dados-juridica', ['sufixo' => $sufixo])
