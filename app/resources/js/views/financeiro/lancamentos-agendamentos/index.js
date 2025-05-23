import { CommonFunctions } from "../../../commons/CommonFunctions";
import { TemplateSearch } from "../../../commons/templates/TemplateSearch";
import { ModalLancamentoGeral } from "../../../components/financeiro/ModalLancamentoGeral";
import { ModalContaTenant } from "../../../components/tenant/ModalContaTenant";
import { ModalLancamentoCategoriaTipoTenant } from "../../../components/tenant/ModalLancamentoCategoriaTipoTenant";
import { BootstrapFunctionsHelper } from "../../../helpers/BootstrapFunctionsHelper";
import { DateTimeHelper } from "../../../helpers/DateTimeHelper";
import { ParticipacaoHelpers } from "../../../helpers/ParticipacaoHelpers";
import { UUIDHelper } from "../../../helpers/UUIDHelper";

class PageLancamentoAgendamentoIndex extends TemplateSearch {

    #objConfigs = {
        querys: {
            consultaFiltros: {
                name: 'consulta-filtros',
                url: window.apiRoutes.baseLancamentoAgendamento,
                urlSearch: `${window.apiRoutes.baseLancamentoAgendamento}/consulta-filtros`,
            }
        },
        url: {
            baseLancamentoAgendamento: window.apiRoutes.baseLancamentoAgendamento,
            baseMovimentacaoContaLancamentoGeral: window.apiRoutes.baseMovimentacaoContaLancamentoGeral,
            baseContas: window.apiRoutes.baseContas,
            baseLancamentoCategoriaTipoTenant: window.apiRoutes.baseLancamentoCategoriaTipoTenant,
        },
    };

    constructor() {
        super({ sufixo: 'PageLancamentoAgendamentoIndex' });
        this._objConfigs = Object.assign(this._objConfigs, this.#objConfigs);
        this.initEvents();
    }

    initEvents() {
        const self = this;
        self.#addEventosBotoes();
        self._executarBusca();
        self.#buscarContas();
        self.#buscarMovimentacoesTipo();
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

        $(`#btnInserirAgendamentoGeral${self.getSufixo}`).on('click', async function () {
            const btn = $(this);
            CommonFunctions.simulateLoading(btn);
            try {
                const objModal = new ModalLancamentoGeral({
                    modoOperacao: window.Enums.LancamentoTipoEnum.LANCAMENTO_AGENDAMENTO
                });
                objModal.setDataEnvModal = {
                    agendamento_tipo: window.Enums.LancamentoTipoEnum.LANCAMENTO_GERAL
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

        $(`#btnInserirAgendamentoRessarcimento${self.getSufixo}`).on('click', async function () {
            const btn = $(this);
            CommonFunctions.simulateLoading(btn);
            try {
                const objModal = new ModalLancamentoGeral({
                    modoOperacao: window.Enums.LancamentoTipoEnum.LANCAMENTO_AGENDAMENTO
                });
                objModal.setDataEnvModal = {
                    agendamento_tipo: window.Enums.LancamentoTipoEnum.LANCAMENTO_RESSARCIMENTO
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

            if (data.categoria_id && UUIDHelper.isValidUUID(data.categoria_id)) {
                appendData.categoria_id = data.categoria_id;
            }

            if (data.recorrente_bln && [1, 0].includes(Number(data.recorrente_bln))) {
                appendData.recorrente_bln = data.recorrente_bln;
            }

            if (data.ativo_bln && [1, 0].includes(Number(data.ativo_bln))) {
                appendData.ativo_bln = data.ativo_bln;
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

        const tipoAgendamento = self.#retornaTipoAgendamento(item.agendamento_tipo);
        const tipoMovimentacao = item.movimentacao_tipo.nome;
        const valorEsperado = CommonFunctions.formatNumberToCurrency(item.valor_esperado);
        const dataVencimento = DateTimeHelper.retornaDadosDataHora(item.data_vencimento, 2);
        const recorrente_bln = item.recorrente_bln ? 'Sim' : 'Não';
        const ativo_bln = item.ativo_bln ? 'Sim' : 'Não';
        const descricao = item.descricao;
        const categoriaTipo = item.categoria.nome
        const observacao = item.observacao ?? '***';
        const conta = item.conta.nome
        const created_at = DateTimeHelper.retornaDadosDataHora(item.created_at, 12);

        const btnsVerMais = ParticipacaoHelpers.htmlRenderBtnsVerMaisParticipantesEIntegrantes(item.participantes ?? [], {
            titleParticipantes: `Participante(s) do Agendamento ${descricao}`,
        });

        $(tbody).append(`
            <tr id=${item.idTr} data-id="${item.id}">
                <td class="text-center">
                    <div class="btn-group btnsAcao" role="group">
                        ${strBtns}
                    </div>
                </td>
                <td class="text-nowrap text-truncate" title="${tipoAgendamento}">${tipoAgendamento}</td>
                <td class="text-nowrap text-truncate" title="${tipoMovimentacao}">${tipoMovimentacao}</td>
                <td class="text-nowrap text-truncate" title="${categoriaTipo}">${categoriaTipo}</td>
                <td class="text-nowrap text-truncate" title="${descricao}">${descricao}</td>
                <td class="text-nowrap text-center" title="${valorEsperado}">${valorEsperado}</td>
                <td class="text-nowrap text-center" title="${dataVencimento}">${dataVencimento}</td>
                <td class="text-center">${btnsVerMais.btnParticipantes}</td>
                <td class="text-center">${btnsVerMais.btnIntegrantes}</td>                
                <td class="text-nowrap text-center" title="${recorrente_bln}">${recorrente_bln}</td>
                <td class="text-nowrap text-center" title="${ativo_bln}">${ativo_bln}</td>
                <td class="text-nowrap text-center" title="${conta}">${conta}</td>
                <td class="text-nowrap text-truncate" title="${observacao}">${observacao}</td>
                <td class="text-nowrap" title="${created_at ?? ''}">${created_at ?? ''}</td>
            </tr>
        `);

        self.#addEventosRegistrosConsulta(item);
        BootstrapFunctionsHelper.addEventPopover();
        return true;
    }

    #retornaTipoAgendamento(agendamento_tipo) {
        switch (agendamento_tipo) {
            case window.Enums.LancamentoTipoEnum.LANCAMENTO_GERAL:
                return 'Comum';
            case window.Enums.LancamentoTipoEnum.LANCAMENTO_RESSARCIMENTO:
                return 'Ressarcimento';
            default:
                return 'Não configurado';
        }
    }

    #addEventosRegistrosConsulta(item) {
        const self = this;

        $(`#${item.idTr} .btn-edit`).on('click', async function () {
            const btn = $(this);
            CommonFunctions.simulateLoading(btn);
            try {
                const objModal = new ModalLancamentoGeral({
                    modoOperacao: window.Enums.LancamentoTipoEnum.LANCAMENTO_AGENDAMENTO
                });
                objModal.setDataEnvModal = {
                    idRegister: item.id,
                    agendamento_tipo: item.agendamento_tipo
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
                title: `Exclusão de Agendamento`,
                message: `Confirma a exclusão do Agendamento <b>${item.descricao}</b>?`,
                success: `Agendamento excluído com sucesso!`,
                button: this
            });
        });
    }

    #htmlBtns(item) {
        const descricao = item.descricao;
        let strBtns = '';

        strBtns = `
        <button class="btn dropdown-toggle btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-three-dots-vertical"></i>
        </button>
        <ul class="dropdown-menu">
            <li>
                <button type="button" class="dropdown-item fs-6 btn-edit" title="Editar agendamento ${descricao}.">
                    Editar
                </button>
            </li>
            <li>
                <button type="button" class="dropdown-item fs-6 btn-delete text-danger" title="Excluir agendamento ${descricao}.">
                    Excluir
                </button>
            </li>
        </ul>`;

        return strBtns;

    }

    async #buscarContas(selected_id = null) {
        try {
            const self = this;
            let options = {
                outInstanceParentBln: true,
                insertFirstOption: true,
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
            let options = {
                outInstanceParentBln: true,
                insertFirstOption: true,
                firstOptionName: 'Todas as movimentações',
            };
            selected_id ? options.selectedIdOption = selected_id : null;
            const select = $(`#movimentacao_tipo_id${self.getSufixo}`);
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
                insertFirstOption: true,
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
    new PageLancamentoAgendamentoIndex();
});