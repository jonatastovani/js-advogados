@extends('layouts.pdf.layout-cabecalho-rodape')

@section('title', $dataEnv['title'])

@section('content')
    <h3 class="text-center">{{ $dataEnv['title'] }}</h3>
    <div class="row">
        <div class="col-sm-5">
            <h5 class="mb-0">{{ $dataEnv['participante_perfil_nome'] }}: {{ $dataEnv['participante_nome'] }}</h4>
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
    <div class="row no-page-break">
        <div class="col-sm-6 pdf-row-group">
            <p class="mb-0">Total crédito: {{ $dataEnv['somatorias']['credito'] }}</p>
            <p class="mb-0">Total débito: {{ $dataEnv['somatorias']['debito'] }}</p>
            <p class="mb-0">Saldo: {{ $dataEnv['somatorias']['total_saldo'] }}</p>
        </div>
        {{-- <div class="col-sm-5 pdf-row-group">
            <p class="mb-0">Total crédito liquidado: {{ $dataEnv['somatorias']['credito_liquidado'] }}</p>
            <p class="mb-0">Total débito liquidado: {{ $dataEnv['somatorias']['debito_liquidado'] }}</p>
            <p class="mb-0">Saldo liquidado: {{ $dataEnv['somatorias']['total_saldo_liquidado'] }}</p>
        </div> --}}
    </div>

@endsection
