@extends('layouts.pdf.layout-cabecalho-rodape')

@section('title', $dataEnv['title'])

@section('content')
    <h3 class="text-center">{{ $dataEnv['title'] }}</h3>
    <div class="row">
        <div class="col-sm-5">
            <h5 class="mb-0">Parceiro: {{ $dataEnv['nome_participante'] }}</h4>
        </div>
        <div class="col-sm-6 text-right">
            <p class="mb-0">Mês referência: {{ $dataEnv['mes_ano'] }}</p>
            <p>Documento gerado em: {{ $dataEnv['data_documento'] }}</p>
        </div>
    </div>

    <div class="table-responsive mt-2">
        <table class="table-striped table" style="border-collapse: collapse;">
            <thead>
                <tr>
                    <th class="text-nowrap" title="Tipo de movimentação">Tipo Mov.</th>
                    <th class="text-nowrap">Valor Lançado</th>
                    <th class="text-nowrap">Valor Participante</th>
                    <th class="text-nowrap" title="Data Movimentação">Data Mov.</th>
                    <th class="text-nowrap">Descrição</th>
                    <th class="text-nowrap">Descrição Específica</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($dataEnv['processedData'] as $dado)
                    <tr>
                        <td>{{ $dado['movimentacao_tipo'] ?? '' }}</td>
                        <td class="text-nowrap">{{ $dado['valor_parcela'] ?? '' }}</td>
                        <td class="text-nowrap">{{ $dado['valor_participante'] ?? '' }}</td>
                        <td class="text-nowrap">{{ $dado['data_movimentacao'] ?? '' }}</td>
                        <td>{{ $dado['descricao_automatica'] ?? '' }}</td>
                        <td>{{ $dado['dados_especificos'] ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="row">
        <div class="col-12">
            <p class="text-right mb-0">Total crédito: R$ {{ $dataEnv['total_credito'] }}</p>
            <p class="text-right mb-0">Total débito: R$ {{ $dataEnv['total_debito'] }}</p>
            <p class="text-right mb-0">Saldo: R$ {{ $dataEnv['total_saldo'] }}</p>
        </div>
    </div>
@endsection
