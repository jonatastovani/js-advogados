<script type="module">
    window.Enums = {
        PessoaTipoEnum: @json(\App\Enums\PessoaTipoEnum::toArray()),
        PessoaPerfilTipoEnum: @json(\App\Enums\PessoaPerfilTipoEnum::toArray()),
        ParticipacaoRegistroTipoEnum: @json(\App\Enums\ParticipacaoRegistroTipoEnum::toArray()),
        ParticipacaoReferenciaTipoEnum: @json(\App\Enums\ParticipacaoReferenciaTipoEnum::toArray()),
        LancamentoStatusTipoEnum: @json(\App\Enums\LancamentoStatusTipoEnum::toArray()),
        PagamentoStatusTipoEnum: @json(\App\Enums\PagamentoStatusTipoEnum::toArray()),
        MovimentacaoContaReferenciaEnum: @json(\App\Enums\MovimentacaoContaReferenciaEnum::toArray()),
        MovimentacaoContaStatusTipoEnum: @json(\App\Enums\MovimentacaoContaStatusTipoEnum::toArray()),
        MovimentacaoContaTipoEnum: @json(\App\Enums\MovimentacaoContaTipoEnum::toArray()),
        PagamentoTipoEnum: @json(\App\Enums\PagamentoTipoEnum::toArray()),
        DocumentoGeradoTipoEnum: @json(\App\Enums\DocumentoGeradoTipoEnum::toArray()),
        ParticipacaoTipoTenantConfiguracaoTipoEnum: @json(\App\Enums\ParticipacaoTipoTenantConfiguracaoTipoEnum::toArray()),
        BalancoRepasseTipoParentEnum: @json(\App\Enums\BalancoRepasseTipoParentEnum::toArray()),
        MovimentacaoContaParticipanteStatusTipoEnum: @json(\App\Enums\MovimentacaoContaParticipanteStatusTipoEnum::toArray()),
        TagTipoTenantEnum: @json(\App\Enums\TagTipoTenantEnum::toArray()),
        LancamentoTipoEnum: @json(\App\Enums\LancamentoTipoEnum::toArray()),
        DocumentoModeloTipoEnum: @json(\App\Enums\DocumentoModeloTipoEnum::toArray()),
        DocumentoTipoEnum: @json(\App\Enums\DocumentoTipoEnum::toArray()),
    };

    window.Statics = {
        PerfisPermitidoParticipacaoServico: @json(\App\Enums\PessoaPerfilTipoEnum::perfisPermitidoParticipacaoServico()),
        PerfisPermitidoParticipacaoRessarcimento: @json(\App\Enums\PessoaPerfilTipoEnum::perfisPermitidoParticipacaoRessarcimento()),
        StatusImpossibilitaEdicaoLancamentoServico: @json(\App\Enums\LancamentoStatusTipoEnum::statusImpossibilitaEdicaoLancamentoServico()),
        StatusLancamentoTachado: @json(\App\Enums\LancamentoStatusTipoEnum::statusLancamentoTachado()),
        StatusPagamentoTachado: @json(\App\Enums\PagamentoStatusTipoEnum::statusPagamentoTachado()),
        StatusServicoLancamentoComParticipantes: @json(\App\Enums\MovimentacaoContaStatusTipoEnum::statusServicoLancamentoComParticipantes()),
        StatusMovimentacaoParticipanteStatusMostrarBalancoRepasseFrontEnd: @json(\App\Enums\MovimentacaoContaParticipanteStatusTipoEnum::statusMostrarBalancoRepasseFrontEnd()),
        TiposMovimentacaoParaLancamentos: @json(\App\Enums\MovimentacaoContaTipoEnum::tiposMovimentacaoParaLancamentos()),
        MovimentacaoContaStatusTipoStatusParaFiltrosFrontEnd: @json(\App\Enums\MovimentacaoContaStatusTipoEnum::statusParaFiltrosFrontEnd()),
        LancamentoStatusTipoStatusParaFiltrosFrontEndLancamentoGeral: @json(\App\Enums\LancamentoStatusTipoEnum::statusParaFiltrosFrontEndLancamentoGeral()),
        LancamentoStatusTipoStatusParaFiltrosFrontEndLancamentoRessarcimento: @json(\App\Enums\LancamentoStatusTipoEnum::statusParaFiltrosFrontEndLancamentoRessarcimento()),
        MovimentacaoContaStatusTipoStatusMostrarBalancoRepasseFrontEnd: @json(\App\Enums\MovimentacaoContaStatusTipoEnum::statusMostrarBalancoRepasseFrontEnd()),
        PessoaPerfilTipoRotasPessoaPerfilFormFront: @json(\App\Enums\PessoaPerfilTipoEnum::rotasPessoaPerfilFormFront()),
        PerfisPermitidoClienteServico: @json(\App\Enums\PessoaPerfilTipoEnum::perfisPermitidoClienteServico()),
        StatusParaNovosPagamentosServicos: @json(\App\Enums\PagamentoStatusTipoEnum::statusParaNovosPagamentosServicos()),
        StatusParaPagamentosServicosExistentes: @json(\App\Enums\PagamentoStatusTipoEnum::statusParaPagamentosServicosExistentes()),
        OrdemPadraoStatusLancamentoServico: @json(\App\Enums\LancamentoStatusTipoEnum::ordemPadraoStatusLancamentoServico()),
        PagamentoTipoQuePermiteLiquidadoMigracao: @json(\App\Enums\PagamentoTipoEnum::pagamentoTipoQuePermiteLiquidadoMigracao()),
        PagamentoTipoComLancamentosPersonalizaveis: @json(\App\Enums\PagamentoTipoEnum::pagamentoTipoComLancamentosPersonalizaveis()),
        PagamentoTipoComConferenciaDeValorTotal: @json(\App\Enums\PagamentoTipoEnum::pagamentoTipoComConferenciaDeValorTotal()),
        PagamentoTipoCamposLancamentosPersonalizados: @json(\App\Enums\PagamentoTipoEnum::pagamentoTipoCamposLancamentosPersonalizados()),
        PagamentoTipoNaoRecriaveis: @json(\App\Enums\PagamentoTipoEnum::pagamentoTipoNaoRecriaveis()),
        PagamentoTipoSemprePersonalizaveis: @json(\App\Enums\PagamentoTipoEnum::pagamentoTipoSemprePersonalizaveis()),
        LancamentoTipoQuePermiteLiquidadoMigracao: @json(\App\Enums\LancamentoTipoEnum::lancamentoTipoQuePermiteLiquidadoMigracao()),
    }

    window.Details = {
        PessoaPerfilTipoEnum: @json(\App\Enums\PessoaPerfilTipoEnum::staticDetailsToArray()),
        MovimentacaoContaTipoEnum: @json(\App\Enums\MovimentacaoContaTipoEnum::staticDetailsToArray()),
        LancamentoStatusTipoEnum: @json(\App\Enums\LancamentoStatusTipoEnum::staticDetailsToArray()),
        PessoaTipoEnum: @json(\App\Enums\PessoaTipoEnum::staticDetailsToArray()),
        PagamentoStatusTipoEnum: @json(\App\Enums\PagamentoStatusTipoEnum::staticDetailsToArray()),
        LancamentosCategoriaEnum: @json(\App\Enums\LancamentosCategoriaEnum::staticDetailsToArray()),
    };
</script>

@component('components.api.api-routes', [
    'routes' => [
        'baseTenant' => route('api.tenant'),
    ],
])
@endcomponent
