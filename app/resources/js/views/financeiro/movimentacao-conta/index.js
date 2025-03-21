import { CommonFunctions } from "../../../commons/CommonFunctions";
import { TemplateSearch } from "../../../commons/templates/TemplateSearch";
import { ModalSelecionarDocumento } from "../../../components/documento/ModalSelecionarDocumento";
import { ModalContaTenant } from "../../../components/tenant/ModalContaTenant";
import { BootstrapFunctionsHelper } from "../../../helpers/BootstrapFunctionsHelper";
import { DateTimeHelper } from "../../../helpers/DateTimeHelper";
import { ParticipacaoHelpers } from "../../../helpers/ParticipacaoHelpers";
import { RedirectHelper } from "../../../helpers/RedirectHelper";
import { URLHelper } from "../../../helpers/URLHelper";
import { UUIDHelper } from "../../../helpers/UUIDHelper";

class PageMovimentacaoContaIndex extends TemplateSearch {

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
        await self._executarBusca();
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
            self._executarBusca();
        });

        CommonFunctions.handleModal(self, $(`#openModalConta${self.getSufixo}`), ModalContaTenant, self.#buscarContas.bind(self));

        // $(`#btnImprimirConsulta${self.getSufixo}`).on('click', async function () {
        //     if (self._objConfigs.querys.consultaFiltros.dataPost) {
        //         let var1 = URLHelper.flattenObject(self._objConfigs.querys.consultaFiltros.dataPost);
        //         let var2 = '';
        //         Object.keys(var1).forEach(function (key) {
        //             var2 += key + '=' + var1[key] + '&';
        //         });

        //         // CommonFunctions.simulateLoading(this, true);
        //         // const objConn = new ConnectAjax(self._objConfigs.url.baseFrontImpressao);
        //         // objConn.setAction(EnumAction.POST);
        //         // objConn.setData(self._objConfigs.querys.consultaFiltros.dataPost);

        //         // try {
        //         //     // Passa `true` para abrir em uma nova janela
        //         //     await objConn.downloadPdf('relatorio.pdf', true);
        //         // } catch (error) {
        //         //     console.error('Erro ao gerar o PDF:', error);
        //         //     CommonFunctions.generateNotificationErrorCatch(error);
        //         // } finally {
        //         //     CommonFunctions.simulateLoading(this, false);
        //         // }
        //     }
        // });

        $(`#btnImprimirConsulta${self.getSufixo}`).on('click', async function () {
            if (self._objConfigs.querys.consultaFiltros.dataPost) {
                RedirectHelper.openURLWithParams(self._objConfigs.url.baseFrontImpressao, self._objConfigs.querys.consultaFiltros.dataPost);
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
        const valorMovimentado = CommonFunctions.formatNumberToCurrency(item.valor_movimentado);
        const dataMovimentacao = DateTimeHelper.retornaDadosDataHora(item.data_movimentacao, 2);
        const conta = item.conta_domain.conta.nome;
        const descricaoAutomatica = item.descricao_automatica;
        const movimentacaoTipo = item.movimentacao_tipo.nome;
        const observacaoLancamento = item.observacao ?? '***';

        let dadosEspecificos = ``;
        let dadosEspecificosTitle = ``;

        switch (item.referencia_type) {
            case window.Enums.MovimentacaoContaReferenciaEnum.SERVICO_LANCAMENTO:
                // dadosEspecificos = `NS#${item.referencia.pagamento.servico.numero_servico}`;
                dadosEspecificosTitle = `Número de Serviço ${item.referencia.pagamento.servico.numero_servico}`;

                dadosEspecificos += ` - NP#${item.referencia.pagamento.numero_pagamento}`;
                dadosEspecificosTitle += ` - Número de Pagamento ${item.referencia.pagamento.numero_pagamento}`;

                dadosEspecificos += ` - (${item.referencia.pagamento.servico.area_juridica.nome})`;
                dadosEspecificosTitle += ` - (Área Jurídica ${item.referencia.pagamento.servico.area_juridica.nome})`;
                dadosEspecificos += ` - ${item.referencia.pagamento.servico.titulo}`;
                dadosEspecificosTitle += ` - Título ${item.referencia.pagamento.servico.titulo}`;
                break;

            case window.Enums.MovimentacaoContaReferenciaEnum.LANCAMENTO_GERAL:
                dadosEspecificos = `NL#${item.referencia.numero_lancamento}`;
                dadosEspecificosTitle = `Número de Lançamento ${item.referencia.numero_lancamento}`;

                dadosEspecificos += ` - (${item.referencia.categoria.nome})`;
                dadosEspecificosTitle += ` - (Categoria ${item.referencia.categoria.nome})`;

                dadosEspecificos += ` - ${item.descricao_automatica}`;
                dadosEspecificosTitle += ` - Descrição ${item.descricao_automatica}`;
                break;

            case window.Enums.MovimentacaoContaReferenciaEnum.DOCUMENTO_GERADO:
                dadosEspecificos = `ND#${item.referencia.numero_documento}`;
                dadosEspecificosTitle = `Número do Documento ${item.referencia.numero_documento}`;
                dadosEspecificos += ` - ${item.descricao_automatica}`;
                dadosEspecificosTitle += ` - Descrição ${item.descricao_automatica}`;
                break;

            default:
                break;
        }

        const created_at = DateTimeHelper.retornaDadosDataHora(item.created_at, 12);

        let htmlThParticipantesIntegrantes = `
            <td class="text-center">***</td>
        `;

        switch (item.referencia_type) {
            case window.Enums.MovimentacaoContaReferenciaEnum.SERVICO_LANCAMENTO:
            case window.Enums.MovimentacaoContaReferenciaEnum.LANCAMENTO_GERAL:
                if (item.movimentacao_conta_participante && item.movimentacao_conta_participante.length &&
                    (window.Statics.StatusServicoLancamentoComParticipantes.findIndex(status => status == item.status_id) != -1)
                ) {
                    const arrays = ParticipacaoHelpers.htmlRenderParticipantesMovimentacaoContaParticipante(item.movimentacao_conta_participante);
                    htmlThParticipantesIntegrantes = `
                        <td class="text-center"><button type="button" class="btn btn-sm btn-outline-info border-0" data-bs-toggle="popover" data-bs-title="Participantes da Movimentação ${descricaoAutomatica}" data-bs-html="true" data-bs-content="${arrays.arrayParticipantes.join("<hr class='my-1'>")}">Ver mais</button></td>
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
                <td class="text-nowrap text-truncate campo-tabela-truncate-35" title="${status}">${status}</td>
                <td class="text-nowrap text-truncate campo-tabela-truncate-35" title="${movimentacaoTipo}">${movimentacaoTipo}</td>
                <td class="text-nowrap text-center" title="${valorMovimentado}">${valorMovimentado}</td>
                <td class="text-nowrap text-center" title="${dataMovimentacao}">${dataMovimentacao}</td>
                <td class="text-nowrap text-center" title="${conta}">${conta}</td>
                <td class="text-nowrap text-truncate campo-tabela-truncate-35" title="${descricaoAutomatica}">${descricaoAutomatica}</td>
                <td class="text-nowrap text-truncate campo-tabela-truncate-30" title="${observacaoLancamento}">${observacaoLancamento}</td>
                <td class="text-nowrap text-truncate campo-tabela-truncate-30" title="${dadosEspecificosTitle}">${dadosEspecificos}</td>
                ${htmlThParticipantesIntegrantes}
                <td class="text-nowrap" title="${created_at ?? ''}">${created_at ?? ''}</td>
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

        if (strBtns) {
            strBtns = `
            <button class="btn dropdown-toggle btn-sm ${!strBtns ? 'disabled border-0' : ''}" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-three-dots-vertical"></i>
            </button>
            <ul class="dropdown-menu">
                ${strBtns}
            </ul>`;
        }

        return strBtns;
    }

    #addEventosRegistrosConsulta(item) {
        const self = this;

        $(`#${item.idTr}`).find(`.btn-documento-gerado`).on('click', async function () {

            if (item.metadata?.documento_gerado.length) {
                try {
                    CommonFunctions.simulateLoading(this);
                    const objModal = new ModalSelecionarDocumento();
                    objModal.setDataEnvModal = {
                        idRegister: item.id,
                    };
                    const response = await objModal.modalOpen();
                } catch (error) {
                    CommonFunctions.generateNotificationErrorCatch(error);
                } finally {
                    CommonFunctions.simulateLoading(this, false);
                }
            }
        });
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
            const arrayOpcoes = window.Details.MovimentacaoContaTipoEnum;
            let options = { firstOptionName: 'Todas as movimentações' };
            selected_id ? options.selectedIdOption = selected_id : null;
            const select = $(`#movimentacao_tipo_id${self.getSufixo}`);
            await CommonFunctions.fillSelectArray(select, arrayOpcoes, options);
            return true;
        } catch (error) {
            return false;
        }
    }

    async #buscarMovimentacoesStatusTipo(selected_id = null) {
        try {
            const self = this;
            const arrayOpcoes = window.Statics.MovimentacaoContaStatusTipoStatusParaFiltrosFrontEnd;
            let options = { firstOptionName: 'Todos os status' };
            selected_id ? options.selectedIdOption = selected_id : null;
            const select = $(`#movimentacao_status_tipo_id${self.getSufixo}`);
            await CommonFunctions.fillSelectArray(select, arrayOpcoes, options);
            return true;
        } catch (error) {
            return false;
        }
    }
}

$(function () {
    new PageMovimentacaoContaIndex();
});