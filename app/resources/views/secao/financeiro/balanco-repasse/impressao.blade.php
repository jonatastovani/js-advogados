@extends('layouts.pdf.layout-cabecalho-rodape')

@section('title', 'Balanço de Repasse de Parceiro')

@section('content')
    <h3 class="text-center">Balanço de Repasse de Parceiro</h3>
    <div class="row">
        <div class="col-sm-5">
            <p class="mb-0">Parceiro: {{ $dataEnv['dados_participante']['referencia']['pessoa']['pessoa_dados']['nome'] }}
            </p>
            <p>Perfil referência: {{ $dataEnv['dados_participante']['referencia']['perfil_tipo']['nome'] }}</p>
        </div>
        <div class="col-sm-6 text-right">
            <p class="mb-0">Mês referência: {{ $dataEnv['mes_ano'] }}</p>
            <p>Documento gerado em: {{ date('d/m/Y H:i:s') }}</p>
        </div>
    </div>
    <div class="table-responsive mt-2">
        <table class="table-striped table" style="border-collapse: collapse;">
            <thead>
                <tr>
                    <th class="text-nowrap">Status</th>
                    <th class="text-nowrap" title="Tipo de movimentação">Tipo Mov.</th>
                    <th class="text-nowrap">Valor</th>
                    <th class="text-nowrap" title="Data Movimentação">Data Mov.</th>
                    <th class="text-nowrap">Participação</th>
                    <th class="text-nowrap">Descrição</th>
                    <th class="text-nowrap" title="Conta de onde o valor será compensado ou debitado">Conta Base</th>
                    {{-- <th class="text-nowrap">Cadastro</th> --}}
                </tr>
            </thead>
            <tbody>
                @foreach ($dataEnv['processedData'] as $dado)
                    <tr>
                        <td>{{ $dado['status'] ?? '' }}</td>
                        <td>{{ $dado['movimentacao_tipo'] ?? '' }}</td>
                        <td class="text-nowrap">{{ $dado['valor_participante'] ?? '' }}</td>
                        <td class="text-nowrap">{{ $dado['data_movimentacao'] ?? '' }}</td>
                        <td>{{ $dado['descricao_automatica'] ?? '' }}</td>
                        <td>{{ $dado['dados_especificos'] ?? '' }}</td>
                        <td>{{ $dado['conta'] ?? '' }}</td>
                        {{-- <td>{{ $dado['created_at'] ?? '' }}</td> --}}
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
        <div class="col-sm-5 pdf-row-group">
            <p class="mb-0">Total crédito liquidado: {{ $dataEnv['somatorias']['credito_liquidado'] }}</p>
            <p class="mb-0">Total débito liquidado: {{ $dataEnv['somatorias']['debito_liquidado'] }}</p>
            <p class="mb-0">Saldo liquidado: {{ $dataEnv['somatorias']['total_saldo_liquidado'] }}</p>
        </div>
    </div>
@endsection
