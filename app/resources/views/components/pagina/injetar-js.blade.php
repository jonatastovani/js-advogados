<script type="module">
    window.Enums = {
        PessoaTipoEnum: @json(\App\Enums\PessoaTipoEnum::toArray()),
        PessoaPerfilTipoEnum: @json(\App\Enums\PessoaPerfilTipoEnum::toArray()),
        ParticipacaoRegistroTipoEnum: @json(\App\Enums\ParticipacaoRegistroTipoEnum::toArray()),
        ServicoParticipacaoReferenciaTipoEnum: @json(\App\Enums\ServicoParticipacaoReferenciaTipoEnum::toArray()),
        LancamentoStatusTipoEnum: @json(\App\Enums\LancamentoStatusTipoEnum::toArray()),
        PagamentoStatusTipoEnum: @json(\App\Enums\PagamentoStatusTipoEnum::toArray()),
        MovimentacaoContaReferenciaEnum: @json(\App\Enums\MovimentacaoContaReferenciaEnum::toArray()),
    };

    window.Statics = {
        PerfisPermitidoParticipacaoServico: @json(\App\Enums\PessoaPerfilTipoEnum::perfisPermitidoParticipacaoServico()),
        StatusImpossibilitaEdicaoParticipantes: @json(\App\Enums\LancamentoStatusTipoEnum::statusImpossibilitaEdicaoParticipantes()),
        StatusLancamentoTachado: @json(\App\Enums\LancamentoStatusTipoEnum::statusLancamentoTachado()),
        StatusPagamentoTachado: @json(\App\Enums\PagamentoStatusTipoEnum::statusPagamentoTachado()),
    }

    window.Details = {
        PessoaPerfilTipoEnum: @json(\App\Enums\PessoaPerfilTipoEnum::staticDetailsToArray()),
    };
</script>
