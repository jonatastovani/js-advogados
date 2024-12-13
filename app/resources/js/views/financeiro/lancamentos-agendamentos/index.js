import { commonFunctions } from "../../../commons/commonFunctions";
import { templateSearch } from "../../../commons/templates/templateSearch";
import { modalConta } from "../../../components/financeiro/modalConta";
import { modalLancamentoGeral } from "../../../components/financeiro/modalLancamentoGeral";
import { modalLancamentoCategoriaTipoTenant } from "../../../components/tenant/modalLancamentoCategoriaTipoTenant";
import { BootstrapFunctionsHelper } from "../../../helpers/BootstrapFunctionsHelper";
import { DateTimeHelper } from "../../../helpers/DateTimeHelper";
import { UUIDHelper } from "../../../helpers/UUIDHelper";

class LancamentoAgendamentoIndex extends templateSearch {

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
        data: {
        }
    };

    constructor() {
        super({ sufixo: 'LancamentoAgendamentoIndex' });
        this._objConfigs = Object.assign(this._objConfigs, this.#objConfigs);
        this.initEvents();
    }

    initEvents() {
        const self = this;
        self.#addEventosBotoes();
        self.#executarBusca();
        self.#buscarContas();
        self.#buscarMovimentacoesTipo();
        self.#buscarLancamentoCategoriaTipoTenant();
    }

    #addEventosBotoes() {
        const self = this;

        $(`#formDataSearch${self.getSufixo}`).find('.btnBuscar').on('click', async function (e) {
            e.preventDefault();
            BootstrapFunctionsHelper.removeEventPopover();
            self.#executarBusca();
        });

        $(`#openModalConta${self.getSufixo}`).on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const objModal = new modalConta();
                objModal.setDataEnvModal = {
                    attributes: {
                        select: {
                            quantity: 1,
                            autoReturn: true,
                        }
                    }
                }

                const response = await objModal.modalOpen();
                if (response.refresh) {
                    if (response.selected) {
                        self.#buscarContas(response.selected.id);
                    } else {
                        self.#buscarContas();
                    }
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading(btn, false);
            }
        });

        $(`#openModalLancamentoCategoriaTipoTenant${self.getSufixo}`).on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);

            try {
                const objModal = new modalLancamentoCategoriaTipoTenant();
                objModal.setDataEnvModal = {
                    attributes: {
                        select: {
                            quantity: 1,
                            autoReturn: true,
                        }
                    }
                }

                const response = await objModal.modalOpen();
                if (response.refresh) {
                    if (response.selected) {
                        self.#buscarLancamentoCategoriaTipoTenant(response.selected.id);
                    } else {
                        self.#buscarLancamentoCategoriaTipoTenant();
                    }
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading(btn, false);
            }
        });

        $(`#btnInserirAgendamento${self.getSufixo}`).on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const objModal = new modalLancamentoGeral({ modoAgendamento: true });
                // objModal.setDataEnvModal = {
                //     idRegister: "9d7f9116-eb25-4090-993d-cdf0ae143c03",
                //     pagamento_id: "9d7f9116-d30a-4559-9231-3083ad482553",
                //     status_id: window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_EM_ANALISE
                // }
                const response = await objModal.modalOpen();
                if (response.refresh) {
                    await self.#executarBusca();
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading(btn, false);
            }
        });
    }

    async #executarBusca() {
        const self = this;

        const getAppendDataQuery = () => {
            const formData = $(`#formDataSearch${self.getSufixo}`);
            let appendData = {};
            let data = commonFunctions.getInputsValues(formData[0]);

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

        let strBtns = self.#HtmlBtns(item);

        const tipoMovimentacao = item.movimentacao_tipo.nome;
        const valorEsperado = commonFunctions.formatNumberToCurrency(item.valor_esperado);
        const dataVencimento = DateTimeHelper.retornaDadosDataHora(item.data_vencimento, 2);
        const recorrente_bln = item.recorrente_bln ? 'Sim' : 'Não';
        const ativo_bln = item.ativo_bln ? 'Sim' : 'Não';
        const descricao = item.descricao;
        const categoriaTipo = item.categoria.nome
        const observacao = item.observacao ?? '***';
        const conta = item.conta.nome
        const created_at = DateTimeHelper.retornaDadosDataHora(item.created_at, 12);

        let classCor = '';

        $(tbody).append(`
            <tr id=${item.idTr} data-id="${item.id}">
                <td class="text-center">
                    <div class="btn-group btnsAcao" role="group">
                        ${strBtns}
                    </div>
                </td>
                <td class="text-nowrap text-truncate ${classCor}" title="${tipoMovimentacao}">${tipoMovimentacao}</td>
                <td class="text-nowrap text-truncate ${classCor}" title="${descricao}">${descricao}</td>
                <td class="text-nowrap text-truncate ${classCor}" title="${categoriaTipo}">${categoriaTipo}</td>
                <td class="text-nowrap text-center ${classCor}" title="${valorEsperado}">${valorEsperado}</td>
                <td class="text-nowrap text-center ${classCor}" title="${dataVencimento}">${dataVencimento}</td>
                <td class="text-nowrap text-center ${classCor}" title="${recorrente_bln}">${recorrente_bln}</td>
                <td class="text-nowrap text-center ${classCor}" title="${ativo_bln}">${ativo_bln}</td>
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

        $(`#${item.idTr} .btn-edit`).on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const objModal = new modalLancamentoGeral({ modoAgendamento: true });
                objModal.setDataEnvModal = {
                    idRegister: item.id,
                }
                const response = await objModal.modalOpen();
                if (response.refresh) {
                    await self.#executarBusca();
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading(btn, false);
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

    #HtmlBtns(item) {
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
                insertFirstOption: true,
                firstOptionName: 'Todas as contas',
            };
            if (selected_id) Object.assign(options, { selectedIdOption: selected_id });
            const selModulo = $(`#conta_id${self.getSufixo}`);
            await commonFunctions.fillSelect(selModulo, self._objConfigs.url.baseContas, options);
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
                insertFirstOption: true,
                firstOptionName: 'Todas as movimentações',
            };
            if (selected_id) Object.assign(options, { selectedIdOption: selected_id });
            const selModulo = $(`#movimentacao_tipo_id${self.getSufixo}`);
            await commonFunctions.fillSelectArray(selModulo, arrayOpcoes, options);
            return true;
        } catch (error) {
            return false;
        }
    }

    async #buscarLancamentoCategoriaTipoTenant(selected_id = null) {
        try {
            const self = this;
            let options = {
                insertFirstOption: true,
                firstOptionName: 'Todas as categorias',
            };
            if (selected_id) Object.assign(options, { selectedIdOption: selected_id });
            const selModulo = $(`#categoria_id${self.getSufixo}`);
            await commonFunctions.fillSelect(selModulo, self._objConfigs.url.baseLancamentoCategoriaTipoTenant, options);
            return true;
        } catch (error) {
            return false;
        }
    }

}

$(function () {
    new LancamentoAgendamentoIndex();
});