import { CommonFunctions } from "../../../commons/CommonFunctions";
import { ConnectAjax } from "../../../commons/ConnectAjax";
import { EnumAction } from "../../../commons/EnumAction";
import { TemplateSearch } from "../../../commons/templates/TemplateSearch";
import { ModalMessage } from "../../../components/comum/ModalMessage";
import { ModalLancamentoServicoMovimentar } from "../../../components/financeiro/ModalLancamentoServicoMovimentar";
import { ModalLancamentoReagendar } from "../../../components/servico/ModalLancamentoReagendar";
import { ModalAreaJuridicaTenant } from "../../../components/tenant/ModalAreaJuridicaTenant";
import { ModalFormaPagamentoTenant } from "../../../components/tenant/ModalFormaPagamentoTenant";
import { BootstrapFunctionsHelper } from "../../../helpers/BootstrapFunctionsHelper";
import { DateTimeHelper } from "../../../helpers/DateTimeHelper";
import { ParticipacaoHelpers } from "../../../helpers/ParticipacaoHelpers";
import TenantTypeDomainCustomHelper from "../../../helpers/TenantTypeDomainCustomHelper";
import { UUIDHelper } from "../../../helpers/UUIDHelper";

class PageLancamentoServicoIndex extends TemplateSearch {

    #objConfigs = {
        querys: {
            consultaFiltros: {
                name: 'consulta-filtros',
                url: `${window.apiRoutes.baseLancamento}/servicos`,
                urlSearch: `${window.apiRoutes.baseLancamento}/servicos/consulta-filtros`,
            }
        },
        url: {
            baseLancamento: window.apiRoutes.baseLancamento,
            baseMovimentacaoContaLancamentoServico: window.apiRoutes.baseMovimentacaoContaLancamentoServico,
            baseFormaPagamento: window.apiRoutes.baseFormaPagamento,
            baseAreaJuridicaTenant: window.apiRoutes.baseAreaJuridicaTenant,
            baseFrontServicoForm: window.frontRoutes.baseFrontServicoForm,
        },
        data: {
            configAcoes: {
                AGUARDANDO_PAGAMENTO_EM_ANALISE: {
                    id: window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO_EM_ANALISE,
                    cor: 'text-bg-warning',
                    opcao_nos_status: [
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO,
                        window.Enums.LancamentoStatusTipoEnum.CANCELADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.CANCELADO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_PARCIALMENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.REAGENDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_MIGRACAO_SISTEMA,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO,
                    ]
                },
                AGUARDANDO_PAGAMENTO: {
                    id: window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO,
                    cor: null,
                    opcao_nos_status: [
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO,
                        window.Enums.LancamentoStatusTipoEnum.CANCELADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.CANCELADO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_PARCIALMENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.REAGENDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_MIGRACAO_SISTEMA,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO,
                    ]
                },
                LIQUIDADO_EM_ANALISE: {
                    id: window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_EM_ANALISE,
                    cor: 'text-success bg-warning',
                    opcao_nos_status: [
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_PARCIALMENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE,
                        window.Enums.LancamentoStatusTipoEnum.REAGENDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_MIGRACAO_SISTEMA,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO,
                    ]
                },
                LIQUIDADO: {
                    id: window.Enums.LancamentoStatusTipoEnum.LIQUIDADO,
                    cor: 'text-success',
                    opcao_nos_status: [
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE,
                        window.Enums.LancamentoStatusTipoEnum.REAGENDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO,
                    ]
                },
                LIQUIDADO_PARCIALMENTE_EM_ANALISE: {
                    id: window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_PARCIALMENTE_EM_ANALISE,
                    cor: 'text-success-emphasis bg-warning',
                    opcao_nos_status: [
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE,
                        window.Enums.LancamentoStatusTipoEnum.REAGENDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_MIGRACAO_SISTEMA,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO,
                    ]
                },
                LIQUIDADO_PARCIALMENTE: {
                    id: window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_PARCIALMENTE,
                    cor: 'text-success-emphasis',
                    opcao_nos_status: [
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_PARCIALMENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO,
                    ]
                },
                REAGENDADO_EM_ANALISE: {
                    id: window.Enums.LancamentoStatusTipoEnum.REAGENDADO_EM_ANALISE,
                    cor: 'fst-italic text-bg-warning',
                    opcao_nos_status: [
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_PARCIALMENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_MIGRACAO_SISTEMA,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO,
                    ]
                },
                REAGENDADO: {
                    id: window.Enums.LancamentoStatusTipoEnum.REAGENDADO,
                    cor: 'fst-italic text-info-emphasis text-decoration-line-through',
                    opcao_nos_status: [
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_PARCIALMENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE,
                        window.Enums.LancamentoStatusTipoEnum.REAGENDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO,
                    ]
                },
                CANCELADO_EM_ANALISE: {
                    id: window.Enums.LancamentoStatusTipoEnum.CANCELADO_EM_ANALISE,
                    cor: 'fst-italic text-secondary text-decoration-line-through bg-warning',
                    opcao_nos_status: [
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_PARCIALMENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_PARCIALMENTE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE,
                        window.Enums.LancamentoStatusTipoEnum.REAGENDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.CANCELADO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_MIGRACAO_SISTEMA,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO,
                    ]
                },
                CANCELADO: {
                    id: window.Enums.LancamentoStatusTipoEnum.CANCELADO,
                    cor: 'fst-italic text-secondary-emphasis text-decoration-line-through',
                    opcao_nos_status: [
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_PARCIALMENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_PARCIALMENTE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE,
                        window.Enums.LancamentoStatusTipoEnum.REAGENDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.CANCELADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_MIGRACAO_SISTEMA,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO,
                    ]
                },
                INADIMPLENTE_EM_ANALISE: {
                    id: window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE_EM_ANALISE,
                    cor: 'text-danger bg-warning',
                    opcao_nos_status: [
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_PARCIALMENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_PARCIALMENTE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE,
                        window.Enums.LancamentoStatusTipoEnum.REAGENDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.CANCELADO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_MIGRACAO_SISTEMA,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO,
                    ]
                },
                INADIMPLENTE: {
                    id: window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE,
                    cor: 'text-danger-emphasis',
                    opcao_nos_status: [
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_PARCIALMENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_PARCIALMENTE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.REAGENDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.CANCELADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_MIGRACAO_SISTEMA,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO,
                    ]
                },
                LIQUIDADO_MIGRACAO_SISTEMA: {
                    id: window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_MIGRACAO_SISTEMA,
                    cor: null,
                    opcao_nos_status: [
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_PARCIALMENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_PARCIALMENTE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE,
                        window.Enums.LancamentoStatusTipoEnum.REAGENDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.CANCELADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO,
                    ]
                },
                EM_ATRASO_EM_ANALISE: {
                    id: window.Enums.LancamentoStatusTipoEnum.EM_ATRASO_EM_ANALISE,
                    cor: 'text-danger bg-warning',
                    opcao_nos_status: [
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_PARCIALMENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_PARCIALMENTE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.REAGENDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.CANCELADO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_MIGRACAO_SISTEMA,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO,
                    ]
                },
                EM_ATRASO: {
                    id: window.Enums.LancamentoStatusTipoEnum.EM_ATRASO,
                    cor: 'text-danger-emphasis',
                    opcao_nos_status: [
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_PARCIALMENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_PARCIALMENTE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE,
                        window.Enums.LancamentoStatusTipoEnum.REAGENDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.CANCELADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_MIGRACAO_SISTEMA,
                        window.Enums.LancamentoStatusTipoEnum.EM_ATRASO_EM_ANALISE,
                    ]
                },
                PAGAMENTO_CANCELADO_EM_ANALISE: {
                    id: window.Enums.LancamentoStatusTipoEnum.PAGAMENTO_CANCELADO_EM_ANALISE,
                    cor: 'fst-italic text-danger-emphasis text-decoration-line-through',
                },
                PAGAMENTO_CANCELADO: {
                    id: window.Enums.LancamentoStatusTipoEnum.PAGAMENTO_CANCELADO,
                    cor: 'fst-italic text-danger-emphasis text-decoration-line-through',
                },
            },
            dados_tenant: undefined,
        },
        dados_tenant: undefined, // Configurações para os status de Liquidado (Migração Sistema)
    };

    constructor() {
        super({ sufixo: 'PageLancamentoServicoIndex' });
        this._objConfigs = CommonFunctions.deepMergeObject(this._objConfigs, this.#objConfigs);
        this.initEvents();
    }

    initEvents() {
        const self = this;
        self.#addEventosBotoes();
        self._executarBusca();
        self.#buscarFormaPagamento();
        self.#buscarLancamentoStatusTipo();
        self.#buscarAreasJuridicas();
    }

    #addEventosBotoes() {
        const self = this;

        $(`#formDataSearch${self.getSufixo}`).find('.btnBuscar').on('click', async function (e) {
            e.preventDefault();
            BootstrapFunctionsHelper.removeEventPopover();
            self._executarBusca();
        });

        CommonFunctions.handleModal(self, $(`#openModalFormaPagamento${self.getSufixo}`), ModalFormaPagamentoTenant, self.#buscarFormaPagamento.bind(self));

        CommonFunctions.handleModal(self, $(`#openModalAreaJuridica${self.getSufixo}`), ModalAreaJuridicaTenant, self.#buscarAreasJuridicas.bind(self));

    }

    async _executarBusca() {
        const self = this;

        await self._buscaDadosTenant();

        const getAppendDataQuery = () => {
            const formData = $(`#formDataSearch${self.getSufixo}`);
            let appendData = {};
            let data = CommonFunctions.getInputsValues(formData[0]);

            if (data.forma_pagamento_id && UUIDHelper.isValidUUID(data.forma_pagamento_id)) {
                appendData.forma_pagamento_id = data.forma_pagamento_id;
            }

            if (data.lancamento_status_tipo_id && Number(data.lancamento_status_tipo_id) > 0) {
                appendData.lancamento_status_tipo_id = data.lancamento_status_tipo_id;
            }

            if (data.area_juridica_id && UUIDHelper.isValidUUID(data.area_juridica_id)) {
                appendData.area_juridica_id = data.area_juridica_id;
            }

            return { appendData: appendData };
        }

        BootstrapFunctionsHelper.removeEventPopover();
        self._setTypeCurrentSearch = self._objConfigs.querys.consultaFiltros.name;
        await self._generateQueryFilters(getAppendDataQuery());
    }

    async insertTableData(item, options = {}) {
        const self = this;
        const {
            tbody,
        } = options;

        let strBtns = self.#htmlBtns(item);

        const numero_servico = item.pagamento.servico.numero_servico;
        const numero_pagamento = item.pagamento.numero_pagamento;
        const status = item.status.nome;
        const valorEsperado = CommonFunctions.formatNumberToCurrency(item.valor_esperado);
        const dataVencimento = DateTimeHelper.retornaDadosDataHora(item.data_vencimento, 2);
        const valorRecebido = item.data_recebimento ? CommonFunctions.formatNumberToCurrency(item.valor_recebido) : '***';
        const dataRecebimento = item.data_recebimento ? DateTimeHelper.retornaDadosDataHora(item.data_recebimento, 2) : '***';
        const formaPagamento = item.forma_pagamento ? item.forma_pagamento.nome : item.pagamento.forma_pagamento.nome;
        const descricaoAutomatica = item.descricao_automatica;
        const observacaoLancamento = item.observacao ?? '***';
        const valorPagamento = item.pagamento.valor_total ? CommonFunctions.formatNumberToCurrency(item.pagamento.valor_total) : '***';
        const tituloServico = item.pagamento.servico.titulo;
        const areaJuridica = item.pagamento.servico.area_juridica.nome;
        const valorLiquidado = item.pagamento.total_liquidado ? CommonFunctions.formatNumberToCurrency(item.pagamento.total_liquidado) : '***';
        const valorAguardando = item.pagamento.total_aguardando ? CommonFunctions.formatNumberToCurrency(item.pagamento.total_aguardando) : '***';
        const valorInadimplente = item.pagamento.total_inadimplente ? CommonFunctions.formatNumberToCurrency(item.pagamento.total_inadimplente) : '***';
        const pagamentoTipo = item.pagamento.pagamento_tipo_tenant.nome ?? item.pagamento.pagamento_tipo_tenant.pagamento_tipo.nome
        const observacaoPagamento = item.pagamento.observacao ?? '***';
        const statusPagamento = item.pagamento.status.nome;

        const arrays = ParticipacaoHelpers.htmlRenderParticipantesEIntegrantes(
            item.participantes.length ? item.participantes :
                (item.pagamento.participantes.length ? item.pagamento.participantes :
                    (item.pagamento.servico.participantes.length ? item.pagamento.servico.participantes : [])
                )
        );

        let classCor = '';
        for (const StatusLancamento of Object.values(self.#objConfigs.data.configAcoes)) {
            if (StatusLancamento.id == item.status_id) {
                classCor = StatusLancamento.cor ?? '';
                break;
            }
        }
        const htmlCliente = self.#htmlRenderCliente(item);

        const created_at = DateTimeHelper.retornaDadosDataHora(item.created_at, 12);
        $(tbody).append(`
            <tr id=${item.idTr} data-id="${item.id}">
                <td class="text-center">
                    <div class="btn-group btnsAcao" role="group">
                        ${strBtns}
                    </div>
                </td>
                <td class="text-nowrap text-truncate ${classCor}" title="${status}">${status}</td>
                <td class="text-nowrap text-center ${classCor}" title="${dataRecebimento}">${dataRecebimento}</td>
                <td class="text-nowrap text-center ${classCor}" title="${valorRecebido}">${valorRecebido}</td>
                <td class="text-nowrap ${classCor}">${htmlCliente}</td>
                <td class="text-nowrap text-center ${classCor}" title="${dataVencimento}">${dataVencimento}</td>
                <td class="text-nowrap text-center ${classCor}" title="${valorEsperado}">${valorEsperado}</td>
                <td class="text-truncate ${classCor}" title="${tituloServico}">${tituloServico}</td>
                <td class="text-nowrap text-truncate ${classCor}" title="${areaJuridica}">${areaJuridica}</td>
                <td class="text-nowrap ${classCor}" title="${formaPagamento}">${formaPagamento}</td>
                <td class="text-nowrap text-truncate ${classCor}" title="${descricaoAutomatica}">${descricaoAutomatica}</td>
                <td class="text-nowrap ${classCor}" title="${numero_pagamento}">${numero_pagamento}</td>
                <td class="text-nowrap text-truncate ${classCor}" title="${observacaoLancamento}">${observacaoLancamento}</td>
                <td class="text-nowrap ${classCor}" title="${numero_servico}">${numero_servico}</td>
                <td class="text-nowrap text-center ${classCor}" title="${valorPagamento}">${valorPagamento}</td>
                <td class="text-nowrap text-center ${classCor}" title="${valorLiquidado}">${valorLiquidado}</td>
                <td class="text-nowrap text-center ${classCor}" title="${valorAguardando}">${valorAguardando}</td>
                <td class="text-nowrap text-center ${classCor}" title="${valorInadimplente}">${valorInadimplente}</td>
                <td class="text-nowrap text-truncate ${classCor}" title="${pagamentoTipo}">${pagamentoTipo}</td>
                <td class="text-nowrap text-truncate ${classCor}" title="${observacaoPagamento}">${observacaoPagamento}</td>
                <td class="text-nowrap text-truncate ${classCor}" title="${statusPagamento}">${statusPagamento}</td>
                <td class="text-center ${classCor}"><button type="button" class="btn btn-sm btn-outline-info border-0" data-bs-toggle="popover" data-bs-title="Participantes do Lançamento ${descricaoAutomatica}" data-bs-html="true" data-bs-content="${arrays.arrayParticipantes.join("<hr class='my-1'>")}">Ver mais</button></td>
                <td class="text-center ${classCor}"><button type="button" class="btn btn-sm btn-outline-info border-0" data-bs-toggle="popover" data-bs-title="Integrantes de Grupos" data-bs-html="true" data-bs-content="${arrays.arrayIntegrantes.join("<hr class='my-1'>")}">Ver mais</button></td>
                <td class="text-nowrap ${classCor}" title="${created_at ?? ''}">${created_at ?? ''}</td>
            </tr>
        `);

        self.#addEventosRegistrosConsulta(item);
        BootstrapFunctionsHelper.addEventPopover();
        return true;
    }

    async functionExecuteOnError(error) {
        const self = this;
        // $(`.campo_totais${self.getSufixo}`).html(CommonFunctions.formatWithCurrencyCommasOrFraction(0));
        // self._objConfigs.atualizandoValores = false;
    }

    async functionExecuteOnSuccess(response) {
        const self = this;
        self.#atualizaValoresTotais();
    }

    async #atualizaValoresTotais() {
        const self = this;

        // Configurar back para retornar soma da consulta separada da geral (todos pagamentos envolvidos)
        return;
        // Se não estiver atualizando os valores, então se executa
        if (!self._objConfigs?.atualizandoValores) {
            self._objConfigs.atualizandoValores = true;

            try {
                const forcedDomainId = TenantTypeDomainCustomHelper.checkDomainCustomForcedDomainId(self);
                const objConn = new ConnectAjax(`${self._objConfigs.querys.consultaFiltros.urlSearch}/obter-totais`);
                if (forcedDomainId) {
                    objConn.setForcedDomainCustomId = forcedDomainId;
                }
                objConn.setAction(EnumAction.POST);
                objConn.setData(self._objConfigs.querys.consultaFiltros.dataPost);
                const response = await objConn.envRequest();

                const totais = response.data.totais;
                // Ativos
                $(`#valorFinal${self.getSufixo}`).html(CommonFunctions.formatWithCurrencyCommasOrFraction(totais.valor_total));
                $(`#totalCancelado${self.getSufixo}`).html(CommonFunctions.formatWithCurrencyCommasOrFraction(totais.total_cancelado));
                $(`#totalAguardando${self.getSufixo}`).html(CommonFunctions.formatWithCurrencyCommasOrFraction(totais.total_aguardando));
                $(`#totalEmAnalise${self.getSufixo}`).html(CommonFunctions.formatWithCurrencyCommasOrFraction(totais.total_analise));
                $(`#totalLiquidado${self.getSufixo}`).html(CommonFunctions.formatWithCurrencyCommasOrFraction(totais.total_liquidado));
                $(`#totalInadimplente${self.getSufixo}`).html(CommonFunctions.formatWithCurrencyCommasOrFraction(totais.total_inadimplente));

            } catch (error) {
                $(`.campo_totais${self.getSufixo}`).html('0,00');
                CommonFunctions.generateNotificationErrorCatch(error);
            } finally {
                self._objConfigs.atualizandoValores = false;
            }
        }

    }

    #htmlBtns(item) {
        const self = this;
        const configAcoes = self.#objConfigs.data.configAcoes;
        let strBtns = '';
        const enumPag = window.Enums.PagamentoStatusTipoEnum;
        const pagamentoAtivo = item.pagamento.status_id == enumPag.ATIVO ? true : false;

        if (pagamentoAtivo) {
            const botoes = self.#getBotoesStatus(item);

            botoes.forEach(botao => {
                const podeExibir =
                    configAcoes[botao.chave]?.opcao_nos_status?.includes(item.status_id) &&
                    botao.condicao();

                if (podeExibir) {
                    strBtns += `
                        <li>
                            <button type="button" class="dropdown-item fs-6 ${botao.cor} ${botao.classe}" 
                                title="Alterar status para ${botao.texto} para o lançamento ${item.descricao_automatica}.">
                                ${botao.icon} ${botao.texto}
                            </button>
                        </li>`;
                }
            });
        }

        let strVerServico = `
            <li>
                <a href="${self._objConfigs.url.baseFrontServicoForm}/${item.pagamento.servico.id}" target="_blank" class="dropdown-item fs-6" title="Ir para o Serviço ${item.pagamento.servico.numero_servico} - ${item.pagamento.servico.titulo}.">
                    <i class="bi bi-box-arrow-up-right"></i> Ver Serviço
                </a>
            </li>`;

        strBtns = `
            <button class="btn dropdown-toggle btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-three-dots-vertical"></i>
            </button>
            <ul class="dropdown-menu">
                ${strVerServico}
                ${strBtns}
            </ul>`;

        return strBtns;
    }

    #getBotoesStatus(item) {
        const self = this;
        const lancamentoDiluido = item.parent_id ? true : false;

        return [
            {
                chave: 'AGUARDANDO_PAGAMENTO_EM_ANALISE',
                classe: 'btn-aguardando-pagamento-analise',
                texto: 'Aguardando Pagamento (em Análise)',
                icon: '<i class="bi bi-hourglass-top"></i>',
                cor: 'text-primary',
                tipoAcao: 'alterar_status',
                condicao: () => true
            },
            {
                chave: 'AGUARDANDO_PAGAMENTO',
                classe: 'btn-aguardando-pagamento',
                texto: 'Aguardando Pagamento',
                icon: '<i class="bi bi-check2-all"></i>',
                cor: 'text-primary',
                tipoAcao: 'alterar_status',
                condicao: () => true
            },
            {
                chave: 'LIQUIDADO_EM_ANALISE',
                classe: 'btn-liquidado-analise',
                texto: 'Liquidado (em Análise)',
                icon: '<i class="bi bi-check2"></i>',
                cor: 'text-success',
                tipoAcao: 'alterar_status',
                condicao: () => true
            },
            {
                chave: 'LIQUIDADO',
                classe: 'btn-liquidado',
                texto: 'Liquidado',
                icon: '<i class="bi bi-check2-all"></i>',
                cor: 'text-success',
                tipoAcao: 'movimentar',
                condicao: () => true
            },
            {
                chave: 'LIQUIDADO_PARCIALMENTE_EM_ANALISE',
                classe: 'btn-liquidado-parcialmente-analise',
                texto: 'Liquidado Parcialmente (em Análise)',
                icon: '<i class="bi bi-exclamation-lg"></i>',
                cor: 'text-success-emphasis',
                tipoAcao: 'alterar_status',
                condicao: () => !lancamentoDiluido
            },
            {
                chave: 'LIQUIDADO_PARCIALMENTE',
                classe: 'btn-liquidado-parcialmente',
                texto: 'Liquidado Parcialmente',
                icon: '<i class="bi bi-check2-all"></i>',
                cor: 'text-success-emphasis',
                tipoAcao: 'movimentar',
                condicao: () => !lancamentoDiluido
            },
            {
                chave: 'REAGENDADO_EM_ANALISE',
                classe: 'btn-reagendado-analise',
                texto: 'Reagendado (em Análise)',
                icon: '<i class="bi bi-calendar-event"></i>',
                cor: 'text-warning',
                tipoAcao: 'alterar_status',
                condicao: () => true
            },
            {
                chave: 'REAGENDADO',
                classe: 'btn-reagendado',
                texto: 'Reagendado',
                icon: '<i class="bi bi-check2-all"></i>',
                cor: 'text-warning',
                tipoAcao: 'reagendar',
                condicao: () => true
            },
            {
                chave: 'EM_ATRASO_EM_ANALISE',
                classe: 'btn-em-atraso-analise',
                texto: 'Em atraso (em Análise)',
                icon: '<i class="bi bi-stopwatch"></i>',
                cor: 'text-danger',
                tipoAcao: 'alterar_status',
                condicao: () => true
            },
            {
                chave: 'EM_ATRASO',
                classe: 'btn-em-atraso',
                texto: 'Em atraso',
                icon: '<i class="bi bi-check2-all"></i>',
                cor: 'text-danger',
                tipoAcao: 'alterar_status',
                condicao: () => true
            },
            {
                chave: 'CANCELADO_EM_ANALISE',
                classe: 'btn-cancelado-analise',
                texto: 'Cancelado (em Análise)',
                icon: '<i class="bi bi-dash-circle"></i>',
                cor: 'text-danger',
                tipoAcao: 'alterar_status',
                condicao: () => true
            },
            {
                chave: 'CANCELADO',
                classe: 'btn-cancelado',
                texto: 'Cancelado',
                icon: '<i class="bi bi-check2-all"></i>',
                cor: 'text-danger',
                tipoAcao: 'alterar_status',
                condicao: () => true
            },
            {
                chave: 'INADIMPLENTE_EM_ANALISE',
                classe: 'btn-inadimplente-analise',
                texto: 'Inadimplente (em Análise)',
                icon: '<i class="bi bi-dash-circle"></i>',
                cor: 'text-danger',
                tipoAcao: 'alterar_status',
                condicao: () => true
            },
            {
                chave: 'INADIMPLENTE',
                classe: 'btn-inadimplente',
                texto: 'Inadimplente',
                icon: '<i class="bi bi-check2-all"></i>',
                cor: 'text-danger',
                tipoAcao: 'alterar_status',
                condicao: () => true
            },
            {
                chave: 'LIQUIDADO_MIGRACAO_SISTEMA',
                classe: 'btn-liquidado-migracao',
                texto: 'Liquidado (Migração Sistema)',
                icon: '<i class="bi bi-journal-check"></i>',
                cor: '',
                tipoAcao: 'alterar_status',
                condicao: () => self._objConfigs.dados_tenant?.lancamento_liquidado_migracao_sistema_bln
            }
        ];
    }

    #addEventosRegistrosConsulta(item) {
        const self = this;
        const enumLanc = window.Enums.LancamentoStatusTipoEnum;
        const configAcoes = self.#objConfigs.data.configAcoes;
        const botoes = self.#getBotoesStatus(item);

        const openMovimentar = async function (status_id) {
            try {
                const objModal = new ModalLancamentoServicoMovimentar();
                objModal.setDataEnvModal = {
                    idRegister: item.id,
                    pagamento_id: item.pagamento_id,
                    status_id: status_id
                }
                const response = await objModal.modalOpen();
                if (response.refresh) {
                    await self._generateQueryFilters();
                }
            } catch (error) {
                CommonFunctions.generateNotificationErrorCatch(error);
            }
        }

        /**
         * Abre um modal para confirmar a alteração de status de um lançamento.
         * @param {object} [dados={}] - Dados para o modal.
         * @param {string} [dados.descricao_automatica] - Descrição automática do lançamento.
         * @param {string} [dados.status_html] - Status HTML do lançamento.
         * @param {number} [dados.status_id] - ID do status do lançamento.
         */
        const openAlterarStatus = async function (dados = {}) {
            const descricao_automatica = dados.descricao_automatica ?? item.descricao_automatica;
            const status_html = dados.status_html;
            const status_id = dados.status_id;

            try {
                const obj = new ModalMessage();
                obj.setDataEnvModal = {
                    title: 'Alterar Status',
                    message: `Confirma a alteração de status do lancamento <b>${descricao_automatica}</b> para <b class="fst-italic">${status_html}</b>?`,
                };
                obj.setFocusElementWhenClosingModal = this;
                const result = await obj.modalOpen();
                if (result.confirmResult) {
                    const objConn = new ConnectAjax(`${self._objConfigs.url.baseMovimentacaoContaLancamentoServico}/status-alterar`);

                    const instance = TenantTypeDomainCustomHelper.getInstanceTenantTypeDomainCustom;
                    if (instance) {
                        if (!item.domain_id) {
                            console.error(item);
                            throw new Error("Unidade de domínio do registro não encontrada. Contate o suporte.");
                        }
                        objConn.setForcedDomainCustomId = item.domain_id;
                    }

                    objConn.setAction(EnumAction.POST);
                    objConn.setData({
                        lancamento_id: item.id,
                        status_id: status_id,
                    });
                    const response = await objConn.envRequest();
                    if (response.data) {
                        await self._generateQueryFilters();
                    }
                }
            } catch (error) {
                CommonFunctions.generateNotificationErrorCatch(error);
            }
        }

        botoes.forEach(config => {
            const btn = $(`#${item.idTr}`).find(`.${config.classe}`);
            const pode = configAcoes[config.chave]?.opcao_nos_status?.includes(item.status_id);

            if (btn.length && pode && config.condicao()) {
                btn.on('click', async function () {
                    const status_id = enumLanc[config.chave];

                    switch (config.tipoAcao) {
                        case 'alterar_status':
                            await openAlterarStatus({ status_html: config.texto, status_id });
                            break;

                        case 'movimentar':
                            await openMovimentar(status_id);
                            break;

                        case 'reagendar':
                            const modal = new ModalLancamentoReagendar({
                                urlApi: `${self._objConfigs.url.baseLancamento}/servicos/reagendar`
                            });
                            modal.setDataEnvModal = self._checkDomainCustomInheritDataEnvModalForObjData(item, {
                                idRegister: item.id,
                                status_id,
                                data_atual: item.data_vencimento
                            });
                            const response = await modal.modalOpen();
                            if (response.refresh) await self._generateQueryFilters();
                            break;
                    }
                });
            }
        });

    }

    #htmlRenderCliente(item) {
        const self = this;

        const arrayCliente = item?.pagamento?.servico?.cliente;
        if (!arrayCliente.length) {
            return '<span class="fst-italic" title="Nenhum cliente encontrado">Nenhum cliente encontrado</span>';
        }

        let nomes = [];

        arrayCliente.map(cliente => {
            const pessoa = cliente.perfil.pessoa;
            let nome = '';

            switch (pessoa.pessoa_dados_type) {
                case window.Enums.PessoaTipoEnum.PESSOA_FISICA:
                    nome = pessoa.pessoa_dados.nome;
                    break;
                case window.Enums.PessoaTipoEnum.PESSOA_JURIDICA:
                    nome = pessoa.pessoa_dados.nome_fantasia;
                    break;

                default:
                    nome = `Não implementado - ${pessoa.pessoa_dados_type}`;
                    console.error(`O tipo de pessoa <b>${pessoa.pessoa_dados_type}</b> ainda não foi implementado.`, cliente);

            }
            nomes.push(nome);
        });

        if (nomes.length > 1) {
            const total = nomes.length;
            return `<button type="button" class="btn btn-sm btn-outline-info border-0 text-nowrap text-reset" data-bs-toggle="popover" data-bs-title="Cliente(s)" data-bs-html="true" data-bs-content="${nomes.join("<hr class='my-1'>")}">${nomes[0]} + ${total - 1}</button>`
        }
        return `<span class="text-nowrap">${nomes[0]}</span>`;
    };

    async #buscarFormaPagamento(selected_id = null) {
        try {
            const self = this;
            let options = {
                outInstanceParentBln: true,
                firstOptionName: 'Todas as formas de pagamento',
            };
            selected_id ? options.selectedIdOption = selected_id : null;
            const select = $(`#forma_pagamento_id${self.getSufixo}`);
            await CommonFunctions.fillSelect(select, self._objConfigs.url.baseFormaPagamento, options);
            return true;
        } catch (error) {
            return false;
        }
    }

    async #buscarAreasJuridicas(selected_id = null) {
        try {
            const self = this;
            let options = {
                outInstanceParentBln: true,
                firstOptionName: 'Todas as áreas jurídicas',
            };
            selected_id ? options.selectedIdOption = selected_id : null;
            const select = $(`#area_juridica_id${self.getSufixo}`);
            await CommonFunctions.fillSelect(select, self._objConfigs.url.baseAreaJuridicaTenant, options);
            return true;
        } catch (error) {
            return false;
        }
    }

    async #buscarLancamentoStatusTipo(selected_id = null) {
        try {
            const self = this;
            const arrayOpcoes = window.Details.LancamentoStatusTipoEnum;
            let options = { firstOptionName: 'Todos os status' };
            selected_id ? options.selectedIdOption = selected_id : null;
            const select = $(`#lancamento_status_tipo_id${self.getSufixo}`);
            await CommonFunctions.fillSelectArray(select, arrayOpcoes, options);
            return true;
        } catch (error) {
            return false;
        }
    }

}

$(function () {
    new PageLancamentoServicoIndex();
});