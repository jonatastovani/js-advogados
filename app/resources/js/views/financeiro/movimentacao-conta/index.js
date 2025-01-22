import { commonFunctions } from "../../../commons/commonFunctions";
import { templateSearch } from "../../../commons/templates/templateSearch";
import { modalSelecionarDocumento } from "../../../components/documento/modalSelecionarDocumento";
import { modalContaTenant } from "../../../components/tenant/modalContaTenant";
import { BootstrapFunctionsHelper } from "../../../helpers/BootstrapFunctionsHelper";
import { DateTimeHelper } from "../../../helpers/DateTimeHelper";
import { ParticipacaoHelpers } from "../../../helpers/ParticipacaoHelpers";
import { RedirectHelper } from "../../../helpers/RedirectHelper";
import { URLHelper } from "../../../helpers/URLHelper";
import { UUIDHelper } from "../../../helpers/UUIDHelper";

class PageMovimentacaoContaIndex extends templateSearch {

    #objConfigs = {
        querys: {
            consultaFiltros: {
                name: 'consulta-filtros',
                url: `${window.apiRoutes.baseMovimentacaoConta}`,
                urlSearch: `${window.apiRoutes.baseMovimentacaoConta}/consulta-filtros`,
            }
        },
        url: {
            baseMovimentacaoConta: window.apiRoutes.baseMovimentacaoConta,
            baseFrontImpressao: window.frontRoutes.baseFrontImpressao,
            baseContas: window.apiRoutes.baseContas,
        },
        data: {
            // Pré carregamento de dados vindo da URL
            preload: {},
        }
    };

    constructor() {
        super({ sufixo: 'PageMovimentacaoContaIndex' });
        this._objConfigs = Object.assign(this._objConfigs, this.#objConfigs);
        this.initEvents();
    }

    async initEvents() {
        const self = this;

        await self.#preLoadUrlParams();
        self.#addEventosBotoes();
        await self.#buscarContas(self._objConfigs.data?.preload?.conta_id || null);
        await self.#buscarMovimentacoesTipo();
        await self.#buscarMovimentacoesStatusTipo();
        await self.#executarBusca();
    }

    #preLoadUrlParams() {
        const self = this;
        const conta_id = URLHelper.getParameterURL('conta_id');
        if (conta_id && UUIDHelper.isValidUUID(conta_id)) {
            self._objConfigs.data.preload.conta_id = conta_id;
            URLHelper.removeURLParameter('conta_id');
        }
    }

    #addEventosBotoes() {
        const self = this;

        $(`#formDataSearch${self.getSufixo}`).find('.btnBuscar').on('click', async function (e) {
            e.preventDefault();
            self.#executarBusca();
        });

        $(`#openModalConta${self.getSufixo}`).on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const objModal = new modalContaTenant();
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

        // $(`#btnImprimirConsulta${self.getSufixo}`).on('click', async function () {
        //     if (self._objConfigs.querys.consultaFiltros.dataPost) {
        //         let var1 = URLHelper.flattenObject(self._objConfigs.querys.consultaFiltros.dataPost);
        //         let var2 = '';
        //         Object.keys(var1).forEach(function (key) {
        //             var2 += key + '=' + var1[key] + '&';
        //         });

        //         // commonFunctions.simulateLoading(this, true);
        //         // const objConn = new connectAjax(self._objConfigs.url.baseFrontImpressao);
        //         // objConn.setAction(enumAction.POST);
        //         // objConn.setData(self._objConfigs.querys.consultaFiltros.dataPost);

        //         // try {
        //         //     // Passa `true` para abrir em uma nova janela
        //         //     await objConn.downloadPdf('relatorio.pdf', true);
        //         // } catch (error) {
        //         //     console.error('Erro ao gerar o PDF:', error);
        //         //     commonFunctions.generateNotificationErrorCatch(error);
        //         // } finally {
        //         //     commonFunctions.simulateLoading(this, false);
        //         // }
        //     }
        // });

        $(`#btnImprimirConsulta${self.getSufixo}`).on('click', async function () {
            if (self._objConfigs.querys.consultaFiltros.dataPost) {
                RedirectHelper.openURLWithParams(self._objConfigs.url.baseFrontImpressao, self._objConfigs.querys.consultaFiltros.dataPost);
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

            if (data.movimentacao_status_tipo_id && Number(data.movimentacao_status_tipo_id) > 0) {
                appendData.movimentacao_status_tipo_id = data.movimentacao_status_tipo_id;
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
        // let strBtns = '';

        const status = item.status.nome;
        const valorMovimentado = commonFunctions.formatNumberToCurrency(item.valor_movimentado);
        const dataMovimentacao = DateTimeHelper.retornaDadosDataHora(item.data_movimentacao, 2);
        const conta = item.conta.nome;
        const descricaoAutomatica = item.descricao_automatica;
        const movimentacaoTipo = item.movimentacao_tipo.nome;
        const observacaoLancamento = item.observacao ?? '***';

        let dadosEspecificos = ``;

        switch (item.referencia_type) {
            case window.Enums.MovimentacaoContaReferenciaEnum.SERVICO_LANCAMENTO:
                dadosEspecificos = `Serviço ${item.referencia.pagamento.servico.numero_servico}`;
                dadosEspecificos += ` - Pagamento - ${item.referencia.pagamento.numero_pagamento}`;
                dadosEspecificos += ` - ${item.referencia.pagamento.servico.area_juridica.nome}`;
                dadosEspecificos += ` - ${item.referencia.pagamento.servico.titulo}`;
                break;

            default:
                break;
        }

        let classCor = '';
        // for (const StatusLancamento of Object.values(self.#objConfigs.data.configAcoes)) {
        //     if (StatusLancamento.id == item.status_id) {
        //         classCor = StatusLancamento.cor ?? '';
        //         break;
        //     }
        // }     
        const created_at = DateTimeHelper.retornaDadosDataHora(item.created_at, 12);

        let htmlThParticipantesIntegrantes = `
            <td class="text-center ${classCor}">***</td>
        `;

        switch (item.referencia_type) {
            case window.Enums.MovimentacaoContaReferenciaEnum.SERVICO_LANCAMENTO:
                if (item.movimentacao_conta_participante && item.movimentacao_conta_participante.length &&
                    (window.Statics.StatusServicoLancamentoComParticipantes.findIndex(status => status == item.status_id) != -1)
                ) {
                    const arrays = ParticipacaoHelpers.htmlRenderParticipantesMovimentacaoContaParticipante(item.movimentacao_conta_participante);
                    htmlThParticipantesIntegrantes = `
                        <td class="text-center ${classCor}"><button type="button" class="btn btn-sm btn-outline-info border-0" data-bs-toggle="popover" data-bs-title="Participantes da Movimentação ${descricaoAutomatica}" data-bs-html="true" data-bs-content="${arrays.arrayParticipantes.join("<hr class='my-1'>")}">Ver mais</button></td>
                    `;
                }
                break;

            default:
                break;
        }

        $(tbody).append(`
            <tr id=${item.idTr} data-id="${item.id}">
                <td class="text-center">
                    <div class="btn-group btnsAcao" role="group">
                        ${strBtns}
                    </div>
                </td>
                <td class="text-nowrap text-truncate ${classCor}" title="${status}">${status}</td>
                <td class="text-nowrap text-truncate ${classCor}" title="${movimentacaoTipo}">${movimentacaoTipo}</td>
                <td class="text-nowrap text-center ${classCor}" title="${valorMovimentado}">${valorMovimentado}</td>
                <td class="text-nowrap text-center ${classCor}" title="${dataMovimentacao}">${dataMovimentacao}</td>
                <td class="text-nowrap text-center ${classCor}" title="${conta}">${conta}</td>
                <td class="text-nowrap text-truncate ${classCor}" title="${descricaoAutomatica}">${descricaoAutomatica}</td>
                <td class="text-nowrap text-truncate ${classCor}" title="${observacaoLancamento}">${observacaoLancamento}</td>
                <td class="text-nowrap text-truncate ${classCor}" title="${dadosEspecificos}">${dadosEspecificos}</td>
                ${htmlThParticipantesIntegrantes}
                <td class="text-nowrap ${classCor}" title="${created_at ?? ''}">${created_at ?? ''}</td>
            </tr>
        `);

        self.#addEventosRegistrosConsulta(item);
        BootstrapFunctionsHelper.addEventPopover();
        return true;
    }

    #htmlBtns(item) {
        const metadata = item.metadata;

        let strBtns = '';

        if (metadata?.documento_gerado.length) {
            strBtns += `
                <li>
                    <button type="button" class="dropdown-item fs-6 btn-documento-gerado">
                        Documentos gerados (${metadata.documento_gerado.length})
                    </button>
                </li>`;
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

    #addEventosRegistrosConsulta(item) {
        const self = this;

        $(`#${item.idTr}`).find(`.btn-documento-gerado`).on('click', async function () {

            if (item.metadata?.documento_gerado.length) {
                try {
                    commonFunctions.simulateLoading(this);
                    const objModal = new modalSelecionarDocumento();
                    objModal.setDataEnvModal = {
                        idRegister: item.id,
                    };
                    const response = await objModal.modalOpen();
                } catch (error) {
                    commonFunctions.generateNotificationErrorCatch(error);
                } finally {
                    commonFunctions.simulateLoading(this, false);
                }
            }
        });
    }

    async #buscarContas(selected_id = null) {
        try {
            const self = this;
            let options = {
                insertFirstOption: true,
                firstOptionName: 'Todas as contas',
            };
            if (selected_id) Object.assign(options, { selectedIdOption: selected_id });
            const select = $(`#conta_id${self.getSufixo}`);
            await commonFunctions.fillSelect(select, self._objConfigs.url.baseContas, options);
            return true;
        } catch (error) {
            return false;
        }
    }

    async #buscarMovimentacoesTipo(selected_id = null) {
        try {
            const self = this;
            const arrayOpcoes = window.Details.MovimentacaoContaTipoEnum;
            let options = {
                insertFirstOption: true,
                firstOptionName: 'Todas as movimentações',
            };
            if (selected_id) Object.assign(options, { selectedIdOption: selected_id });
            const select = $(`#movimentacao_tipo_id${self.getSufixo}`);
            await commonFunctions.fillSelectArray(select, arrayOpcoes, options);
            return true;
        } catch (error) {
            return false;
        }
    }

    async #buscarMovimentacoesStatusTipo(selected_id = null) {
        try {
            const self = this;
            const arrayOpcoes = window.Statics.MovimentacaoContaStatusTipoStatusParaFiltrosFrontEnd;
            let options = {
                insertFirstOption: true,
                firstOptionName: 'Todos os status',
            };
            if (selected_id) Object.assign(options, { selectedIdOption: selected_id });
            const select = $(`#movimentacao_status_tipo_id${self.getSufixo}`);
            await commonFunctions.fillSelectArray(select, arrayOpcoes, options);
            return true;
        } catch (error) {
            return false;
        }
    }

}

$(function () {
    new PageMovimentacaoContaIndex();
});