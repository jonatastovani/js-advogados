@extends('layouts.pdf.layout-cabecalho-rodape')

@section('title', 'Movimentações Contas por Período')

@section('content')
    <h3 class="text-center">Movimentações Contas por Período</h3>
    <div class="row">
        <div class="col text-right">
            <p class="mb-0">Período de: {{ $dataEnv['data_inicio'] }} - {{ $dataEnv['data_fim'] }}</p>
            <p>Documento gerado em: {{ date('d/m/Y H:i:s') }}</p>
        </div>
    </div>
    <div class="table-responsive mt-2">
        <table class="table-striped table" style="border-collapse: collapse;">
            <thead>
                <tr>
                    <th class="text-nowrap">Status</th>
                    <th class="text-nowrap" title="Tipo de movimentação">Tipo Mov.</th>
                    <th class="text-nowrap" title="Valor Movimentado">Valor Mov.</th>
                    <th class="text-nowrap" title="Data Movimentação">Data Mov.</th>
                    <th class="text-nowrap">Conta</th>
                    <th class="text-nowrap">Descrição</th>
                    <th class="text-nowrap">Dados Específicos</th>
                    <th class="text-nowrap">Cadastro</th>
                </tr>
            </thead>
            <tbody>
                @for ($i = 0; $i < 10; $i++)
                    @foreach ($dataEnv['dados'] as $dado)
                        <tr>
                            <td>{{ $dado['status'] ?? '' }}</td>
                            <td>{{ $dado['movimentacao_tipo'] ?? '' }}</td>
                            <td>{{ $dado['valor_movimentado'] ?? '' }}</td>
                            <td>{{ $dado['data_movimentacao'] ?? '' }}</td>
                            <td>{{ $dado['conta'] ?? '' }}</td>
                            <td>{{ $dado['descricao_automatica'] ?? '' }}</td>
                            <td>{{ $dado['dados_especificos'] ?? '' }}</td>
                            <td>{{ $dado['created_at'] ?? '' }}</td>
                        </tr>
                    @endforeach
                @endfor
            </tbody>
        </table>
    </div>

@endsection
