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
            }
        }
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

        const created_at = DateTimeHelper.retornaDadosDataHora(item.created_at, 12);
        $(tbody).append(`
            <tr id=${item.idTr} data-id="${item.id}">
                <td class="text-center">
                    <div class="btn-group btnsAcao" role="group">
                        ${strBtns}
                    </div>
                </td>
                <td class="text-nowrap ${classCor}" title="${numero_servico}">${numero_servico}</td>
                <td class="text-nowrap ${classCor}" title="${numero_pagamento}">${numero_pagamento}</td>
                <td class="text-nowrap text-truncate ${classCor}" title="${descricaoAutomatica}">${descricaoAutomatica}</td>
                <td class="text-truncate ${classCor}" title="${tituloServico}">${tituloServico}</td>
                <td class="text-nowrap text-truncate ${classCor}" title="${areaJuridica}">${areaJuridica}</td>
                <td class="text-nowrap text-center ${classCor}" title="${valorEsperado}">${valorEsperado}</td>
                <td class="text-nowrap text-center ${classCor}" title="${dataVencimento}">${dataVencimento}</td>
                <td class="text-nowrap ${classCor}" title="${formaPagamento}">${formaPagamento}</td>
                <td class="text-nowrap text-truncate ${classCor}" title="${status}">${status}</td>
                <td class="text-nowrap text-center ${classCor}" title="${valorRecebido}">${valorRecebido}</td>
                <td class="text-nowrap text-center ${classCor}" title="${dataRecebimento}">${dataRecebimento}</td>
                <td class="text-nowrap text-truncate ${classCor}" title="${observacaoLancamento}">${observacaoLancamento}</td>
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

    #htmlBtns(item) {
        const self = this;
        const configAcoes = self.#objConfigs.data.configAcoes;
        const lancamentoDiluido = item.parent_id ? true : false;
        let strBtns = '';
        const enumPag = window.Enums.PagamentoStatusTipoEnum;
        const pagamentoAtivo = item.pagamento.status_id == enumPag.ATIVO ? true : false;

        if (pagamentoAtivo) {

            if (configAcoes.AGUARDANDO_PAGAMENTO_EM_ANALISE.opcao_nos_status.findIndex(status => status == item.status_id) != -1) {
                strBtns += `
                <li>
                    <button type="button" class="dropdown-item fs-6 text-primary btn-aguardando-pagamento-analise" title="Alterar status para Aguardando Pagamento em Análise para o lancamento ${item.descricao_automatica}.">
                        <i class="bi bi-hourglass-top"></i> Aguardando Pagamento (em Análise)
                    </button>
                </li>`;
            }
            if (configAcoes.AGUARDANDO_PAGAMENTO.opcao_nos_status.findIndex(status => status == item.status_id) != -1) {
                strBtns += `
                <li>
                    <button type="button" class="dropdown-item fs-6 text-primary btn-aguardando-pagamento" title="Alterar status para Aguardando Pagamento para o lancamento ${item.descricao_automatica}.">
                        <i class="bi bi-check2-all"></i> Aguardando Pagamento
                    </button>
                </li>`;
            }

            if (configAcoes.LIQUIDADO_EM_ANALISE.opcao_nos_status.findIndex(status => status == item.status_id) != -1) {
                strBtns += `
                <li>
                    <button type="button" class="dropdown-item fs-6 text-success btn-liquidado-analise" title="Receber lancamento ${item.descricao_automatica} com status Liquidado em Análise.">
                        <i class="bi bi-check2"></i> Liquidado (em Análise)
                    </button>
                </li>`;
            }
            if (configAcoes.LIQUIDADO.opcao_nos_status.findIndex(status => status == item.status_id) != -1) {
                strBtns += `
                <li>
                    <button type="button" class="dropdown-item fs-6 text-success btn-liquidado" title="Receber lancamento ${item.descricao_automatica} com status Liquidado.">
                        <i class="bi bi-check2-all"></i> Liquidado
                    </button>
                </li>`;
            }

            if (configAcoes.LIQUIDADO_PARCIALMENTE_EM_ANALISE?.opcao_nos_status.findIndex(status => status == item.status_id) != -1
                && !lancamentoDiluido) {
                strBtns += `
                <li>
                    <button type="button" class="dropdown-item fs-6 text-success-emphasis btn-liquidado-parcialmente-analise" title="Receber lancamento ${item.descricao_automatica} com status Liquidado Parcial em Análise.">
                        <i class="bi bi-exclamation-lg"></i> Liquidado Parcialmente (em Análise)
                    </button>
                </li>`;
            }
            if (configAcoes.LIQUIDADO_PARCIALMENTE.opcao_nos_status.findIndex(status => status == item.status_id) != -1
                && !lancamentoDiluido) {
                strBtns += `
                <li>
                    <button type="button" class="dropdown-item fs-6 text-success-emphasis btn-liquidado-parcialmente" title="Receber lançamento ${item.descricao_automatica} com status Liquidado Parcial.">
                        <i class="bi bi-check2-all"></i> Liquidado Parcialmente
                    </button>
                </li>`;
            }

            if (configAcoes.REAGENDADO_EM_ANALISE.opcao_nos_status.findIndex(status => status == item.status_id) != -1) {
                strBtns += `
                <li>
                    <button type="button" class="dropdown-item fs-6 text-warning btn-reagendado-analise" title="Reagendar lancamento ${item.descricao_automatica} em Análise.">
                        <i class="bi bi-calendar-event"></i> Reagendado (em Análise)
                    </button>
                </li>`;
            }
            if (configAcoes.REAGENDADO.opcao_nos_status.findIndex(status => status == item.status_id) != -1) {
                strBtns += `
                <li>
                    <button type="button" class="dropdown-item fs-6 text-warning btn-reagendado" title="Reagendar lançamento ${item.descricao_automatica}.">
                        <i class="bi bi-check2-all"></i> Reagendado
                    </button>
                </li>`;
            }

            if (configAcoes.CANCELADO_EM_ANALISE.opcao_nos_status.findIndex(status => status == item.status_id) != -1) {
                strBtns += `
                <li>
                    <button type="button" class="dropdown-item fs-6 btn-cancelado-analise text-danger" title="Registrar lançamento ${item.descricao_automatica} com status Cancelado em Análise.">
                        <i class="bi bi-dash-circle"></i> Cancelado (em Análise)
                    </button>
                </li>`;
            }
            if (configAcoes.CANCELADO.opcao_nos_status.findIndex(status => status == item.status_id) != -1) {
                strBtns += `
                <li>
                    <button type="button" class="dropdown-item fs-6 btn-cancelado text-danger" title="Registrar lançamento ${item.descricao_automatica} com status Cancelado.">
                        <i class="bi bi-check2-all"></i> Cancelado
                    </button>
                </li>`;
            }

            if (configAcoes.INADIMPLENTE_EM_ANALISE.opcao_nos_status.findIndex(status => status == item.status_id) != -1) {
                strBtns += `
                <li>
                    <button type="button" class="dropdown-item fs-6 btn-inadimplente-analise text-danger" title="Registrar lançamento ${item.descricao_automatica} com status Inadimplente em Análise.">
                        <i class="bi bi-dash-circle"></i> Inadimplente (em Análise)
                    </button>
                </li>`;
            }
            if (configAcoes.INADIMPLENTE.opcao_nos_status.findIndex(status => status == item.status_id) != -1) {
                strBtns += `
                <li>
                    <button type="button" class="dropdown-item fs-6 btn-inadimplente text-danger" title="Registrar lançamento ${item.descricao_automatica} com status Inadimplente.">
                        <i class="bi bi-check2-all"></i> Inadimplente
                    </button>
                </li>`;
            }

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

    #addEventosRegistrosConsulta(item) {
        const self = this;
        const enumLanc = window.Enums.LancamentoStatusTipoEnum;
        const configAcoes = self.#objConfigs.data.configAcoes;
        const lancamentoDiluido = item.parent_id ? true : false;

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

        let btnAcao = $(`#${item.idTr}`).find(`.btn-aguardando-pagamento-analise`);
        if (btnAcao.length && configAcoes.AGUARDANDO_PAGAMENTO_EM_ANALISE.opcao_nos_status.findIndex(status => status == item.status_id) != -1) {
            btnAcao.on('click', async function () {
                await openAlterarStatus({ status_html: 'Aguardando Pagamento (em Análise)', status_id: enumLanc.AGUARDANDO_PAGAMENTO_EM_ANALISE });
            });
        }

        btnAcao = $(`#${item.idTr}`).find(`.btn-aguardando-pagamento`);
        if (btnAcao.length && configAcoes.AGUARDANDO_PAGAMENTO.opcao_nos_status.findIndex(status => status == item.status_id) != -1) {
            btnAcao.on('click', async function () {
                await openAlterarStatus({ status_html: 'Aguardando Pagamento', status_id: enumLanc.AGUARDANDO_PAGAMENTO });
            });
        }

        btnAcao = $(`#${item.idTr}`).find(`.btn-liquidado-analise`);
        if (btnAcao.length && configAcoes.LIQUIDADO_EM_ANALISE.opcao_nos_status.findIndex(status => status == item.status_id) != -1) {
            btnAcao.on('click', async function () {
                await openAlterarStatus({ status_html: 'Liquidado (em Análise)', status_id: enumLanc.LIQUIDADO_EM_ANALISE });
            });
        }

        btnAcao = $(`#${item.idTr}`).find(`.btn-liquidado`);
        if (btnAcao.length && configAcoes.LIQUIDADO.opcao_nos_status.findIndex(status => status == item.status_id) != -1) {
            btnAcao.on('click', async function () {
                await openMovimentar(window.Enums.LancamentoStatusTipoEnum.LIQUIDADO);
            });
        }

        btnAcao = $(`#${item.idTr}`).find(`.btn-liquidado-parcialmente-analise`);
        if (btnAcao.length && configAcoes.LIQUIDADO_PARCIALMENTE_EM_ANALISE.opcao_nos_status.findIndex(status => status == item.status_id) != -1
            && !lancamentoDiluido) {
            btnAcao.on('click', async function () {
                await openAlterarStatus({ status_html: 'Liquidado Parcialmente (em Análise)', status_id: enumLanc.LIQUIDADO_PARCIALMENTE_EM_ANALISE });
            });
        }

        btnAcao = $(`#${item.idTr}`).find(`.btn-liquidado-parcialmente`);
        if (btnAcao.length && configAcoes.LIQUIDADO_PARCIALMENTE.opcao_nos_status.findIndex(status => status == item.status_id) != -1
            && !lancamentoDiluido) {
            btnAcao.on('click', async function () {
                await openMovimentar(window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_PARCIALMENTE);
            });
        }

        btnAcao = $(`#${item.idTr}`).find(`.btn-reagendado-analise`);
        if (btnAcao.length && configAcoes.REAGENDADO_EM_ANALISE.opcao_nos_status.findIndex(status => status == item.status_id) != -1) {
            btnAcao.on('click', async function () {
                await openAlterarStatus({ status_html: 'Reagendado (em Análise)', status_id: enumLanc.REAGENDADO_EM_ANALISE });
            });
        }

        btnAcao = $(`#${item.idTr}`).find(`.btn-reagendado`);
        if (btnAcao.length && configAcoes.REAGENDADO.opcao_nos_status.findIndex(status => status == item.status_id) != -1) {
            btnAcao.on('click', async function () {
                try {
                    const objModal = new ModalLancamentoReagendar({
                        urlApi: `${self._objConfigs.url.baseLancamento}/servicos/reagendar`
                    });
                    objModal.setDataEnvModal = {
                        idRegister: item.id,
                        status_id: window.Enums.LancamentoStatusTipoEnum.REAGENDADO,
                        data_atual: item.data_vencimento
                    }
                    const response = await objModal.modalOpen();
                    if (response.refresh) {
                        await self._generateQueryFilters();
                    }
                } catch (error) {
                    CommonFunctions.generateNotificationErrorCatch(error);
                }
            });
        }

        btnAcao = $(`#${item.idTr}`).find(`.btn-cancelado-analise`);
        if (btnAcao.length && configAcoes.CANCELADO_EM_ANALISE.opcao_nos_status.findIndex(status => status == item.status_id) != -1) {
            btnAcao.on('click', async function () {
                await openAlterarStatus({ status_html: 'Cancelado (em Análise)', status_id: enumLanc.CANCELADO_EM_ANALISE });
            });
        }

        btnAcao = $(`#${item.idTr}`).find(`.btn-cancelado`);
        if (btnAcao.length && configAcoes.CANCELADO.opcao_nos_status.findIndex(status => status == item.status_id) != -1) {
            btnAcao.on('click', async function () {
                await openAlterarStatus({ status_html: 'Cancelado', status_id: enumLanc.CANCELADO });
            });
        }

        btnAcao = $(`#${item.idTr}`).find(`.btn-inadimplente-analise`);
        if (btnAcao.length && configAcoes.INADIMPLENTE_EM_ANALISE.opcao_nos_status.findIndex(status => status == item.status_id) != -1) {
            btnAcao.on('click', async function () {
                await openAlterarStatus({ status_html: 'Inadimplente (em Análise)', status_id: enumLanc.INADIMPLENTE_EM_ANALISE });
            });
        }

        btnAcao = $(`#${item.idTr}`).find(`.btn-inadimplente`);
        if (btnAcao.length && configAcoes.INADIMPLENTE.opcao_nos_status.findIndex(status => status == item.status_id) != -1) {
            btnAcao.on('click', async function () {
                await openAlterarStatus({ status_html: 'Inadimplente', status_id: enumLanc.INADIMPLENTE });
            });
        }
    }

    async #buscarFormaPagamento(selected_id = null) {
        try {
            const self = this;
            let options = {
                outInstanceParentBln: true,
                insertFirstOption: true,
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
                insertFirstOption: true,
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
            let options = {
                insertFirstOption: true,
                firstOptionName: 'Todos os status',
            };
            if (selected_id) Object.assign(options, { selectedIdOption: selected_id });
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