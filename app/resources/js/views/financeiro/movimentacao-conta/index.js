import { CommonFunctions } from "../../../commons/CommonFunctions";
import { TemplateSearch } from "../../../commons/templates/TemplateSearch";
import { ModalSelecionarDocumento } from "../../../components/documento/ModalSelecionarDocumento";
import { ModalContaTenant } from "../../../components/tenant/ModalContaTenant";
import { BootstrapFunctionsHelper } from "../../../helpers/BootstrapFunctionsHelper";
import { DateTimeHelper } from "../../../helpers/DateTimeHelper";
import { ParticipacaoHelpers } from "../../../helpers/ParticipacaoHelpers";
import { PessoaNomeHelper } from "../../../helpers/PessoaNomeHelper";
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

        let dadosEspecificos = [];
        let dadosEspecificosTitle = [];

        const referencia = item.referencia;

        switch (item.referencia_type) {
            case window.Enums.MovimentacaoContaReferenciaEnum.SERVICO_LANCAMENTO:

                const pagamento = referencia.pagamento;
                const servico = pagamento.servico;

                const clientesRender = self.#htmlRenderCliente(referencia);
                dadosEspecificos.push(`<span class="fw-bold">${clientesRender}</span>`);
                dadosEspecificosTitle.push(`Cliente: ${clientesRender}`);

                dadosEspecificos.push(servico.titulo);
                dadosEspecificosTitle.push(`Título: ${servico.titulo}`);

                dadosEspecificos.push(referencia.descricao_automatica);
                dadosEspecificosTitle.push(`Descrição: ${referencia.descricao_automatica}`);

                dadosEspecificos.push(`(${servico.area_juridica.nome})`);
                dadosEspecificosTitle.push(`Área Jurídica: ${servico.area_juridica.nome}`);

                dadosEspecificos.push(`NP#${pagamento.numero_pagamento}`);
                dadosEspecificosTitle.push(`Número de Pagamento ${pagamento.numero_pagamento}`);

                // dadosEspecificos.push(`NS#${servico.numero_servico}`);
                dadosEspecificosTitle.push(`Número de Serviço: ${servico.numero_servico}`);
                break;

            case window.Enums.MovimentacaoContaReferenciaEnum.LANCAMENTO_GERAL:

                dadosEspecificos.push(item.descricao_automatica);
                dadosEspecificosTitle.push(`Descrição: ${item.descricao_automatica}`);

                dadosEspecificos.push(`(${referencia.categoria.nome})`);
                dadosEspecificosTitle.push(`Categoria: ${referencia.categoria.nome}`);

                dadosEspecificos.push(`NL#${referencia.numero_lancamento}`);
                dadosEspecificosTitle.push(`Número de Lançamento: ${referencia.numero_lancamento}`);
                break;

            case window.Enums.MovimentacaoContaReferenciaEnum.DOCUMENTO_GERADO:
                dadosEspecificos.push(item.descricao_automatica);
                dadosEspecificosTitle.push(`Descrição: ${item.descricao_automatica}`);

                dadosEspecificos.push(`ND#${referencia.numero_documento}`);
                dadosEspecificosTitle.push(`Número do Documento: ${referencia.numero_documento}`);
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
                if (item?.movimentacao_conta_participante?.length &&
                    (window.Statics.StatusServicoLancamentoComParticipantes.findIndex(status => status == item.status_id) != -1)
                ) {
                    const btnsVerMais = ParticipacaoHelpers.htmlRenderBtnVerMaisParticipantesMovimentacaoContaParticipante(item.movimentacao_conta_participante, {
                        titleParticipantes: `Participante(s) da Movimentação`,
                    });
                    htmlThParticipantesIntegrantes = `
                        <td class="text-center">
                            ${btnsVerMais.btnParticipantes}
                        </td>
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
                <td class="text-nowrap text-truncate campo-tabela-truncate-45" title="${dadosEspecificosTitle.join(' - ')}">${dadosEspecificos.join(' - ')}</td>
                <td class="text-nowrap text-truncate campo-tabela-truncate-30" title="${observacaoLancamento}">${observacaoLancamento}</td>
                <td class="text-nowrap text-truncate campo-tabela-truncate-35" title="${descricaoAutomatica}">${descricaoAutomatica}</td>
                ${htmlThParticipantesIntegrantes}
                <td class="text-nowrap text-center" title="${conta}">${conta}</td>
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

    #htmlRenderCliente(lancamentoServico) {

        const arrayCliente = lancamentoServico?.pagamento?.servico?.cliente;
        if (!arrayCliente || !arrayCliente.length) {
            return '';
        }

        // Utilizando o helper para obter os nomes completos
        const nomes = PessoaNomeHelper.extrairNomes(arrayCliente.map(cliente => ({ perfil: cliente.perfil })));

        if (nomes.length > 1) {
            const total = nomes.length;
            return `${nomes[0].nome_completo} + ${total - 1}`;
        }
        return nomes[0]?.nome_completo || 'Nome não encontrado';
    };

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