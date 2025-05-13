import { CommonFunctions } from "../../../commons/CommonFunctions";
import { TemplateSearch } from "../../../commons/templates/TemplateSearch";
import { ModalLancamentoGeral } from "../../../components/financeiro/ModalLancamentoGeral";
import { ModalContaTenant } from "../../../components/tenant/ModalContaTenant";
import { ModalLancamentoCategoriaTipoTenant } from "../../../components/tenant/ModalLancamentoCategoriaTipoTenant";
import { BootstrapFunctionsHelper } from "../../../helpers/BootstrapFunctionsHelper";
import { DateTimeHelper } from "../../../helpers/DateTimeHelper";
import { ParticipacaoHelpers } from "../../../helpers/ParticipacaoHelpers";
import { UUIDHelper } from "../../../helpers/UUIDHelper";

class PageLancamentoRessarcimentoIndex extends TemplateSearch {

    #objConfigs = {
        querys: {
            consultaFiltros: {
                name: 'consulta-filtros',
                url: window.apiRoutes.baseLancamentoRessarcimento,
                urlSearch: `${window.apiRoutes.baseLancamentoRessarcimento}/consulta-filtros`,
            }
        },
        url: {
            baseLancamentoRessarcimento: window.apiRoutes.baseLancamentoRessarcimento,
            baseMovimentacaoContaLancamentoRessarcimento: window.apiRoutes.baseMovimentacaoContaLancamentoRessarcimento,
            baseContas: window.apiRoutes.baseContas,
            baseLancamentoCategoriaTipoTenant: window.apiRoutes.baseLancamentoCategoriaTipoTenant,
        },
        data: {},
    };

    constructor() {
        super({ sufixo: 'PageLancamentoRessarcimentoIndex' });
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

        $(`#btnInserirRessarcimento${self.getSufixo}`).on('click', async function () {
            const btn = $(this);
            CommonFunctions.simulateLoading(btn);
            try {
                const objModal = new ModalLancamentoGeral({
                    modoOperacao: window.Enums.LancamentoTipoEnum.LANCAMENTO_RESSARCIMENTO
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

        const btnsVerMais = ParticipacaoHelpers.htmlRenderBtnsVerMaisParticipantesEIntegrantes(item.participantes ?? [], {
            titleParticipantes: `Participante(s) do Ressarcimento ${descricao}`,
        });

        $(tbody).append(`
            <tr id=${item.idTr} data-id="${item.id}">
                <td class="text-center">
                    <div class="btn-group btnsAcao" role="group">
                        ${strBtns}
                    </div>
                </td>
                <td class="text-nowrap text-truncate" title="${tipoMovimentacao}">${tipoMovimentacao}</td>
                <td class="text-nowrap text-truncate" title="${categoriaTipo}">${categoriaTipo}</td>
                <td class="text-nowrap text-center" title="${dataQuitado}">${dataQuitado}</td>
                <td class="text-nowrap text-center" title="${valorQuitado}">${valorQuitado}</td>
                <td class="text-nowrap text-truncate" title="${descricao}">${descricao}</td>
                <td class="text-center">${btnsVerMais.btnParticipantes}</td>
                <td class="text-center">${btnsVerMais.btnIntegrantes}</td>
                <td class="text-nowrap text-truncate" title="${status}">${status}</td>
                <td class="text-nowrap text-center" title="${valorEsperado}">${valorEsperado}</td>
                <td class="text-nowrap text-center" title="${dataVencimento}">${dataVencimento}</td>
                <td class="text-nowrap text-truncate" title="${observacao}">${observacao}</td>
                <td class="text-nowrap text-center" title="${conta}">${conta}</td>
                <td class="text-nowrap" title="${numero_lancamento}">${numero_lancamento}</td>
                <td class="text-nowrap" title="${created_at ?? ''}">${created_at ?? ''}</td>
            </tr>
        `);

        self.#addEventosRegistrosConsulta(item);
        BootstrapFunctionsHelper.addEventPopover();
        return true;
    }

    #addEventosRegistrosConsulta(item) {
        const self = this;

        $(`#${item.idTr} .btn-edit`).on('click', async function () {
            const btn = $(this);
            CommonFunctions.simulateLoading(btn);
            try {
                const objModal = new ModalLancamentoGeral({
                    modoOperacao: window.Enums.LancamentoTipoEnum.LANCAMENTO_RESSARCIMENTO
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
            const arrayOpcoes = window.Statics.LancamentoStatusTipoStatusParaFiltrosFrontEndLancamentoRessarcimento;
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
    new PageLancamentoRessarcimentoIndex();
});