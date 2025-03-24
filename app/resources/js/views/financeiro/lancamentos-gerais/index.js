import { CommonFunctions } from "../../../commons/CommonFunctions";
import { ConnectAjax } from "../../../commons/ConnectAjax";
import { EnumAction } from "../../../commons/EnumAction";
import { TemplateSearch } from "../../../commons/templates/TemplateSearch";
import { ModalMessage } from "../../../components/comum/ModalMessage";
import { ModalLancamentoGeral } from "../../../components/financeiro/ModalLancamentoGeral";
import { ModalLancamentoGeralMovimentar } from "../../../components/financeiro/ModalLancamentoGeralMovimentar";
import { ModalLancamentoReagendar } from "../../../components/servico/ModalLancamentoReagendar";
import { ModalContaTenant } from "../../../components/tenant/ModalContaTenant";
import { ModalLancamentoCategoriaTipoTenant } from "../../../components/tenant/ModalLancamentoCategoriaTipoTenant";
import { BootstrapFunctionsHelper } from "../../../helpers/BootstrapFunctionsHelper";
import { DateTimeHelper } from "../../../helpers/DateTimeHelper";
import TenantTypeDomainCustomHelper from "../../../helpers/TenantTypeDomainCustomHelper";
import { UUIDHelper } from "../../../helpers/UUIDHelper";

class PageLancamentoGeralIndex extends TemplateSearch {

    #objConfigs = {
        querys: {
            consultaFiltros: {
                name: 'consulta-filtros',
                url: window.apiRoutes.baseLancamentoGeral,
                urlSearch: `${window.apiRoutes.baseLancamentoGeral}/consulta-filtros`,
            }
        },
        url: {
            baseLancamentoGeral: window.apiRoutes.baseLancamentoGeral,
            baseMovimentacaoContaLancamentoGeral: window.apiRoutes.baseMovimentacaoContaLancamentoGeral,
            baseContas: window.apiRoutes.baseContas,
            baseLancamentoCategoriaTipoTenant: window.apiRoutes.baseLancamentoCategoriaTipoTenant,
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
                        window.Enums.LancamentoStatusTipoEnum.REAGENDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_MIGRACAO_SISTEMA,
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
                        window.Enums.LancamentoStatusTipoEnum.REAGENDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_MIGRACAO_SISTEMA,
                    ]
                },
                LIQUIDADO_EM_ANALISE: {
                    id: window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_EM_ANALISE,
                    cor: 'text-success bg-warning',
                    opcao_nos_status: [
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE,
                        window.Enums.LancamentoStatusTipoEnum.REAGENDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_MIGRACAO_SISTEMA,
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
                REAGENDADO_EM_ANALISE: {
                    id: window.Enums.LancamentoStatusTipoEnum.REAGENDADO_EM_ANALISE,
                    cor: 'fst-italic text-bg-warning',
                    opcao_nos_status: [
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_MIGRACAO_SISTEMA,
                    ]
                },
                REAGENDADO: {
                    id: window.Enums.LancamentoStatusTipoEnum.REAGENDADO,
                    cor: 'fst-italic text-secondary-emphasis text-decoration-line-through',
                    opcao_nos_status: [
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE,
                        window.Enums.LancamentoStatusTipoEnum.REAGENDADO_EM_ANALISE,
                    ]
                },
                CANCELADO_EM_ANALISE: {
                    id: window.Enums.LancamentoStatusTipoEnum.CANCELADO_EM_ANALISE,
                    cor: 'fst-italic text-danger text-decoration-line-through bg-warning',
                    opcao_nos_status: [
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE,
                        window.Enums.LancamentoStatusTipoEnum.REAGENDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.CANCELADO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_MIGRACAO_SISTEMA,
                    ]
                },
                CANCELADO: {
                    id: window.Enums.LancamentoStatusTipoEnum.CANCELADO,
                    cor: 'fst-italic text-danger-emphasis text-decoration-line-through',
                    opcao_nos_status: [
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.AGUARDANDO_PAGAMENTO,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.INADIMPLENTE,
                        window.Enums.LancamentoStatusTipoEnum.REAGENDADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.CANCELADO_EM_ANALISE,
                        window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_MIGRACAO_SISTEMA,
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
        super({ sufixo: 'PageLancamentoGeralIndex' });
        this._objConfigs = Object.assign(this._objConfigs, this.#objConfigs);
        this.initEvents();
    }

    initEvents() {
        const self = this;
        self.#addEventosBotoes();
        self._executarBusca();
        self.#buscarContas();
        self.#buscarMovimentacoesTipo();
        self.#buscarLancamentoStatusTipo();
        self.#buscarLancamentoCategoriaTipoTenant();
    }

    #addEventosBotoes() {
        const self = this;

        $(`#formDataSearch${self.getSufixo}`).find('.btnBuscar').on('click', async function (e) {
            e.preventDefault();
            BootstrapFunctionsHelper.removeEventPopover();
            self._executarBusca();
        });

        CommonFunctions.handleModal(self, $(`#openModalConta${self.getSufixo}`), ModalContaTenant, self.#buscarContas.bind(self));

        CommonFunctions.handleModal(self, $(`#openModalLancamentoCategoriaTipoTenant${self.getSufixo}`), ModalLancamentoCategoriaTipoTenant, self.#buscarLancamentoCategoriaTipoTenant.bind(self));

        $(`#btnImprimirConsulta${self.getSufixo}`).on('click', async function () {
            CommonFunctions.generateNotification('Em desenvolvimento', 'warning');
            return;
            if (self._objConfigs.querys.consultaFiltros.dataPost) {
                // Flatten o objeto para gerar os parâmetros
                let flattenedParams = URLHelper.flattenObject(self._objConfigs.querys.consultaFiltros.dataPost);
                let queryString = '';

                // Constrói a query string
                Object.keys(flattenedParams).forEach(function (key) {
                    queryString += encodeURIComponent(key) + '=' + encodeURIComponent(flattenedParams[key]) + '&';
                });

                // Remove o último '&'
                queryString = queryString.slice(0, -1);


                // Crie a URL base (substitua pela URL desejada)
                const baseURL = self._objConfigs.url.baseFrontImpressao;

                // Abre em uma nova guia
                window.open(`${baseURL}?${queryString}`, '_blank');
            }
        });

        $(`#btnInserirLancamento${self.getSufixo}`).on('click', async function () {
            const btn = $(this);
            CommonFunctions.simulateLoading(btn);
            try {
                const objModal = new ModalLancamentoGeral({
                    modoOperacao: window.Enums.LancamentoTipoEnum.LANCAMENTO_GERAL
                });
                const response = await objModal.modalOpen();
                if (response.refresh) {
                    await self._executarBusca();
                }
            } catch (error) {
                CommonFunctions.generateNotificationErrorCatch(error);
            } finally {
                CommonFunctions.simulateLoading(btn, false);
            }
        });
    }

    async _executarBusca() {
        const self = this;

        await self._buscaDadosTenant();

        const getAppendDataQuery = () => {
            const formData = $(`#formDataSearch${self.getSufixo}`);
            let appendData = {};
            let data = CommonFunctions.getInputsValues(formData[0]);

            if (data.conta_id && UUIDHelper.isValidUUID(data.conta_id)) {
                appendData.conta_id = data.conta_id;
            }

            if (data.movimentacao_tipo_id && Number(data.movimentacao_tipo_id) > 0) {
                appendData.movimentacao_tipo_id = data.movimentacao_tipo_id;
            }

            if (data.lancamento_status_tipo_id && Number(data.lancamento_status_tipo_id) > 0) {
                appendData.lancamento_status_tipo_id = data.lancamento_status_tipo_id;
            }

            if (data.categoria_id && UUIDHelper.isValidUUID(data.categoria_id)) {
                appendData.categoria_id = data.categoria_id;
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

        const numero_lancamento = item.numero_lancamento;
        const status = item.status.nome;
        const tipoMovimentacao = item.movimentacao_tipo.nome;
        const valorEsperado = CommonFunctions.formatNumberToCurrency(item.valor_esperado);
        const dataVencimento = DateTimeHelper.retornaDadosDataHora(item.data_vencimento, 2);
        const valorQuitado = item.data_quitado ? CommonFunctions.formatNumberToCurrency(item.valor_quitado) : '***';
        const dataQuitado = item.data_quitado ? DateTimeHelper.retornaDadosDataHora(item.data_quitado, 2) : '***';
        const descricao = item.descricao;
        const categoriaTipo = item.categoria.nome
        const observacao = item.observacao ?? '***';
        const conta = item.conta.nome
        const created_at = DateTimeHelper.retornaDadosDataHora(item.created_at, 12);

        let classCor = '';
        for (const StatusLancamento of Object.values(self.#objConfigs.data.configAcoes)) {
            if (StatusLancamento.id == item.status_id) {
                classCor = StatusLancamento.cor ?? '';
                break;
            }
        }

        $(tbody).append(`
            <tr id=${item.idTr} data-id="${item.id}">
                <td class="text-center">
                    <div class="btn-group btnsAcao" role="group">
                        ${strBtns}
                    </div>
                </td>
                <td class="text-nowrap text-truncate ${classCor}" title="${tipoMovimentacao}">${tipoMovimentacao}</td>
                <td class="text-nowrap text-truncate ${classCor}" title="${status}">${status}</td>
                <td class="text-nowrap ${classCor}" title="${numero_lancamento}">${numero_lancamento}</td>
                <td class="text-nowrap text-truncate ${classCor}" title="${descricao}">${descricao}</td>
                <td class="text-nowrap text-truncate ${classCor}" title="${categoriaTipo}">${categoriaTipo}</td>
                <td class="text-nowrap text-center ${classCor}" title="${valorEsperado}">${valorEsperado}</td>
                <td class="text-nowrap text-center ${classCor}" title="${dataVencimento}">${dataVencimento}</td>
                <td class="text-nowrap text-center ${classCor}" title="${valorQuitado}">${valorQuitado}</td>
                <td class="text-nowrap text-center ${classCor}" title="${dataQuitado}">${dataQuitado}</td>
                <td class="text-nowrap text-center ${classCor}" title="${conta}">${conta}</td>
                <td class="text-nowrap text-truncate ${classCor}" title="${observacao}">${observacao}</td>
                <td class="text-nowrap ${classCor}" title="${created_at ?? ''}">${created_at ?? ''}</td>
            </tr>
        `);

        self.#addEventosRegistrosConsulta(item);
        BootstrapFunctionsHelper.addEventPopover();
        return true;
    }

    #addEventosRegistrosConsulta(item) {
        const self = this;
        const enumLanc = window.Enums.LancamentoStatusTipoEnum;
        const configAcoes = self.#objConfigs.data.configAcoes;

        const openMovimentar = async function (status_id) {
            try {
                const objModal = new ModalLancamentoGeralMovimentar();
                objModal.setDataEnvModal = {
                    idRegister: item.id,
                    pagamento_id: item.pagamento_id,
                    status_id: status_id
                }
                const response = await objModal.modalOpen();
                if (response.refresh) {
                    await self._executarBusca();
                }
            } catch (error) {
                CommonFunctions.generateNotificationErrorCatch(error);
            }
        }

        /**
         * Abre um modal para confirmar a alteração de status de um lançamento.
         * @param {object} [dados={}] - Dados para o modal.
         * @param {string} [dados.descricao] - Descrição do lançamento.
         * @param {string} [dados.status_html] - Status HTML do lançamento.
         * @param {number} [dados.status_id] - ID do status do lançamento.
         */
        const openAlterarStatus = async function (dados = {}) {
            const descricao = dados.descricao ?? item.descricao;
            const status_html = dados.status_html;
            const status_id = dados.status_id;

            try {
                const obj = new ModalMessage();
                obj.setDataEnvModal = {
                    title: 'Alterar Status',
                    message: `Confirma a alteração de status do lancamento <b>${descricao}</b> para <b class="fst-italic">${status_html}</b>?`,
                };
                obj.setFocusElementWhenClosingModal = this;
                const result = await obj.modalOpen();
                if (result.confirmResult) {
                    const objConn = new ConnectAjax(`${self._objConfigs.url.baseMovimentacaoContaLancamentoGeral}/status-alterar`);

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
                        await self._executarBusca();
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
                        urlApi: `${self._objConfigs.url.baseLancamentoGeral}/reagendar`
                    });
                    objModal.setDataEnvModal = self._checkDomainCustomInheritDataEnvModalForObjData(item, {
                        idRegister: item.id,
                        status_id: window.Enums.LancamentoStatusTipoEnum.REAGENDADO,
                        data_atual: item.data_vencimento
                    });
                    const response = await objModal.modalOpen();
                    if (response.refresh) {
                        await self._executarBusca();
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

        // Se houver as configurações do tenant então se verifica se aplica o evento ou não o botão
        if (self._objConfigs.dados_tenant && self._objConfigs.dados_tenant?.lancamento_liquidado_migracao_sistema_bln) {
            btnAcao = $(`#${item.idTr}`).find(`.btn-liquidado-migracao`);
            if (btnAcao.length && configAcoes.LIQUIDADO_MIGRACAO_SISTEMA.opcao_nos_status.findIndex(status => status == item.status_id) != -1) {
                btnAcao.on('click', async function () {
                    await openAlterarStatus({ status_html: 'Liquidado (Migração Sistema)', status_id: enumLanc.LIQUIDADO_MIGRACAO_SISTEMA });
                });
            }
        }

        $(`#${item.idTr} .btn-edit`).on('click', async function () {
            const btn = $(this);
            CommonFunctions.simulateLoading(btn);
            try {
                const objModal = new ModalLancamentoGeral({
                    modoOperacao: window.Enums.LancamentoTipoEnum.LANCAMENTO_GERAL
                });
                objModal.setDataEnvModal = {
                    idRegister: item.id,
                }
                const response = await objModal.modalOpen();
                if (response.refresh) {
                    await self._executarBusca();
                }
            } catch (error) {
                CommonFunctions.generateNotificationErrorCatch(error);
            } finally {
                CommonFunctions.simulateLoading(btn, false);
            }
        });

        $(`#${item.idTr}`).find(`.btn-delete`).click(async function () {
            self._delButtonAction(item.id, item.name, {
                title: `Exclusão de Lançamento`,
                message: `Confirma a exclusão do Lançamento <b>${item.descricao}</b>?`,
                success: `Lançamento excluído com sucesso!`,
                button: this
            });
        });
    }

    #htmlBtns(item) {
        const self = this;
        const configAcoes = self.#objConfigs.data.configAcoes;
        const descricao = item.descricao;
        let strBtns = `
            <li>
                <button type="button" class="dropdown-item fs-6 btn-edit" title="Editar agendamento ${descricao}.">
                    Editar
                </button>
            </li>
            <li>
                <button type="button" class="dropdown-item fs-6 btn-delete text-danger" title="Excluir agendamento ${descricao}.">
                    Excluir
                </button>
            </li>`;

        if (configAcoes.AGUARDANDO_PAGAMENTO_EM_ANALISE.opcao_nos_status.findIndex(status => status == item.status_id) != -1) {
            strBtns += `
                <li>
                    <button type="button" class="dropdown-item fs-6 text-primary btn-aguardando-pagamento-analise" title="Alterar status para Aguardando Pagamento em Análise para o lancamento ${descricao}.">
                        <i class="bi bi-hourglass-top"></i> Aguardando Pagamento (em Análise)
                    </button>
                </li>`;
        }
        if (configAcoes.AGUARDANDO_PAGAMENTO.opcao_nos_status.findIndex(status => status == item.status_id) != -1) {
            strBtns += `
                <li>
                    <button type="button" class="dropdown-item fs-6 text-primary btn-aguardando-pagamento" title="Alterar status para Aguardando Pagamento para o lancamento ${descricao}.">
                        <i class="bi bi-check2-all"></i> Aguardando Pagamento
                    </button>
                </li>`;
        }

        if (configAcoes.LIQUIDADO_EM_ANALISE.opcao_nos_status.findIndex(status => status == item.status_id) != -1) {
            strBtns += `
                <li>
                    <button type="button" class="dropdown-item fs-6 text-success btn-liquidado-analise" title="Receber lancamento ${descricao} com status Liquidado em Análise.">
                        <i class="bi bi-check2"></i> Liquidado (em Análise)
                    </button>
                </li>`;
        }
        if (configAcoes.LIQUIDADO.opcao_nos_status.findIndex(status => status == item.status_id) != -1) {
            strBtns += `
                <li>
                    <button type="button" class="dropdown-item fs-6 text-success btn-liquidado" title="Receber lancamento ${descricao} com status Liquidado.">
                        <i class="bi bi-check2-all"></i> Liquidado
                    </button>
                </li>`;
        }

        if (configAcoes.REAGENDADO_EM_ANALISE.opcao_nos_status.findIndex(status => status == item.status_id) != -1) {
            strBtns += `
                <li>
                    <button type="button" class="dropdown-item fs-6 text-warning btn-reagendado-analise" title="Reagendar lancamento ${descricao} em Análise.">
                        <i class="bi bi-calendar-event"></i> Reagendado (em Análise)
                    </button>
                </li>`;
        }
        if (configAcoes.REAGENDADO.opcao_nos_status.findIndex(status => status == item.status_id) != -1) {
            strBtns += `
                <li>
                    <button type="button" class="dropdown-item fs-6 text-warning btn-reagendado" title="Reagendar lançamento ${descricao}.">
                        <i class="bi bi-check2-all"></i> Reagendado
                    </button>
                </li>`;
        }

        if (configAcoes.CANCELADO_EM_ANALISE.opcao_nos_status.findIndex(status => status == item.status_id) != -1) {
            strBtns += `
                <li>
                    <button type="button" class="dropdown-item fs-6 btn-cancelado-analise text-danger" title="Registrar lançamento ${descricao} com status Cancelado em Análise.">
                        <i class="bi bi-dash-circle"></i> Cancelado (em Análise)
                    </button>
                </li>`;
        }
        if (configAcoes.CANCELADO.opcao_nos_status.findIndex(status => status == item.status_id) != -1) {
            strBtns += `
                <li>
                    <button type="button" class="dropdown-item fs-6 btn-cancelado text-danger" title="Registrar lançamento ${descricao} com status Cancelado.">
                        <i class="bi bi-check2-all"></i> Cancelado
                    </button>
                </li>`;
        }

        // Se houver as configurações do tenant então se verifica se apresenta ou não o botão
        if (self._objConfigs.dados_tenant && self._objConfigs.dados_tenant?.lancamento_liquidado_migracao_sistema_bln) {
            if (configAcoes.LIQUIDADO_MIGRACAO_SISTEMA.opcao_nos_status.findIndex(status => status == item.status_id) != -1) {
                strBtns += `
                <li>
                    <button type="button" class="dropdown-item fs-6 btn-liquidado-migracao" title="Registrar lançamento ${item.descricao_automatica} com status Liquidado Migração Sistema.">
                        <i class="bi bi-journal-check"></i> Liquidado (Migração Sistema)
                    </button>
                </li>`;
            }
        }

        strBtns = `
        <button class="btn dropdown-toggle btn-sm ${!strBtns ? 'disabled border-0' : ''}" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-three-dots-vertical"></i>
        </button>
        <ul class="dropdown-menu">
            ${strBtns}
        </ul>`;

        return strBtns;

    }

    async #buscarContas(selected_id = null) {
        try {
            const self = this;
            let options = {
                outInstanceParentBln: true,
                firstOptionName: 'Todas as contas',
            };
            selected_id ? options.selectedIdOption = selected_id : null;
            const select = $(`#conta_id${self.getSufixo}`);
            await CommonFunctions.fillSelect(select, self._objConfigs.url.baseContas, options);
            return true;
        } catch (error) {
            return false;
        }
    }

    async #buscarMovimentacoesTipo(selected_id = null) {
        try {
            const self = this;
            const arrayOpcoes = window.Statics.TiposMovimentacaoParaLancamentos;
            let options = { firstOptionName: 'Todas as movimentações' };
            selected_id ? options.selectedIdOption = selected_id : null;
            const select = $(`#movimentacao_tipo_id${self.getSufixo}`);
            await CommonFunctions.fillSelectArray(select, arrayOpcoes, options);
            return true;
        } catch (error) {
            return false;
        }
    }

    async #buscarLancamentoStatusTipo(selected_id = null) {
        try {
            const self = this;
            const arrayOpcoes = window.Statics.LancamentoStatusTipoStatusParaFiltrosFrontEndLancamentoGeral;
            let options = { firstOptionName: 'Todos os status' };
            selected_id ? options.selectedIdOption = selected_id : null;
            const select = $(`#lancamento_status_tipo_id${self.getSufixo}`);
            await CommonFunctions.fillSelectArray(select, arrayOpcoes, options);
            return true;
        } catch (error) {
            return false;
        }
    }

    async #buscarLancamentoCategoriaTipoTenant(selected_id = null) {
        try {
            const self = this;
            let options = {
                outInstanceParentBln: true,
                firstOptionName: 'Todas as categorias',
            };
            selected_id ? options.selectedIdOption = selected_id : null;
            const select = $(`#categoria_id${self.getSufixo}`);
            await CommonFunctions.fillSelect(select, self._objConfigs.url.baseLancamentoCategoriaTipoTenant, options);
            return true;
        } catch (error) {
            return false;
        }
    }

}

$(function () {
    new PageLancamentoGeralIndex();
});