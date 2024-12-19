<script type="module">
    window.Enums = {
        PessoaTipoEnum: @json(\App\Enums\PessoaTipoEnum::toArray()),
        PessoaPerfilTipoEnum: @json(\App\Enums\PessoaPerfilTipoEnum::toArray()),
        ParticipacaoRegistroTipoEnum: @json(\App\Enums\ParticipacaoRegistroTipoEnum::toArray()),
        ServicoParticipacaoReferenciaTipoEnum: @json(\App\Enums\ServicoParticipacaoReferenciaTipoEnum::toArray()),
        LancamentoStatusTipoEnum: @json(\App\Enums\LancamentoStatusTipoEnum::toArray()),
        PagamentoStatusTipoEnum: @json(\App\Enums\PagamentoStatusTipoEnum::toArray()),
        MovimentacaoContaReferenciaEnum: @json(\App\Enums\MovimentacaoContaReferenciaEnum::toArray()),
        MovimentacaoContaStatusTipoEnum: @json(\App\Enums\MovimentacaoContaStatusTipoEnum::toArray()),
        MovimentacaoContaTipoEnum: @json(\App\Enums\MovimentacaoContaTipoEnum::toArray()),
        PagamentoTipoEnum: @json(\App\Enums\PagamentoTipoEnum::toArray()),
    };

    window.Statics = {
        PerfisPermitidoParticipacaoServico: @json(\App\Enums\PessoaPerfilTipoEnum::perfisPermitidoParticipacaoServico()),
        StatusImpossibilitaEdicaoParticipantes: @json(\App\Enums\LancamentoStatusTipoEnum::statusImpossibilitaEdicaoParticipantes()),
        StatusLancamentoTachado: @json(\App\Enums\LancamentoStatusTipoEnum::statusLancamentoTachado()),
        StatusPagamentoTachado: @json(\App\Enums\PagamentoStatusTipoEnum::statusPagamentoTachado()),
        StatusServicoLancamentoComParticipantes: @json(\App\Enums\MovimentacaoContaStatusTipoEnum::statusServicoLancamentoComParticipantes()),
        TiposMovimentacaoParaLancamentos: @json(\App\Enums\MovimentacaoContaTipoEnum::tiposMovimentacaoParaLancamentos()),
        MovimentacaoContaStatusTipoStatusParaFiltrosFrontEnd: @json(\App\Enums\MovimentacaoContaStatusTipoEnum::statusParaFiltrosFrontEnd()),
        LancamentoStatusTipoStatusParaFiltrosFrontEndLancamentoGeral: @json(\App\Enums\LancamentoStatusTipoEnum::statusParaFiltrosFrontEndLancamentoGeral()),
        MovimentacaoContaStatusTipoStatusMostrarBalancoRepasseParceiroFrontEnd: @json(\App\Enums\MovimentacaoContaStatusTipoEnum::statusMostrarBalancoRepasseParceiroFrontEnd()),
        PessoaPerfilTipoRotasPessoaPerfilFormFront: @json(\App\Enums\PessoaPerfilTipoEnum::rotasPessoaPerfilFormFront()),
    }

    window.Details = {
        PessoaPerfilTipoEnum: @json(\App\Enums\PessoaPerfilTipoEnum::staticDetailsToArray()),
        MovimentacaoContaTipoEnum: @json(\App\Enums\MovimentacaoContaTipoEnum::staticDetailsToArray()),
        LancamentoStatusTipoEnum: @json(\App\Enums\LancamentoStatusTipoEnum::staticDetailsToArray()),
    };
</script>
