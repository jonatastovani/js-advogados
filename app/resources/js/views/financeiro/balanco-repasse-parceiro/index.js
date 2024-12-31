import { commonFunctions } from "../../../commons/commonFunctions";
import { connectAjax } from "../../../commons/connectAjax";
import { enumAction } from "../../../commons/enumAction";
import { templateSearch } from "../../../commons/templates/templateSearch";
import { modalMessage } from "../../../components/comum/modalMessage";
import { modalPessoa } from "../../../components/pessoas/modalPessoa";
import { modalContaTenant } from "../../../components/tenant/modalContaTenant";
import { BootstrapFunctionsHelper } from "../../../helpers/BootstrapFunctionsHelper";
import { DateTimeHelper } from "../../../helpers/DateTimeHelper";
import { URLHelper } from "../../../helpers/URLHelper";
import { UUIDHelper } from "../../../helpers/UUIDHelper";

class PageBalancoRepasseParceiroIndex extends templateSearch {

    #objConfigs = {
        querys: {
            consultaFiltros: {
                name: 'consulta-filtros',
                url: `${window.apiRoutes.baseRepasseParceiro}`,
                urlSearch: `${window.apiRoutes.baseRepasseParceiro}/consulta-filtros`,
            }
        },
        url: {
            baseContas: window.apiRoutes.baseContas,
            baseLancarRepasseParceiro: window.apiRoutes.baseLancarRepasseParceiro,
            baseRepasseParceiro: window.apiRoutes.baseRepasseParceiro,
            baseFrontImpressao: window.frontRoutes.baseFrontImpressao,
        },
        data: {
            parceiro_id: undefined,
            totais: {
                debito: 0,
                credito: 0,
            },
            selecionados: [],
        }
    };

    constructor() {
        super({ sufixo: 'PageBalancoRepasseParceiroIndex' });
        this._objConfigs = Object.assign(this._objConfigs, this.#objConfigs);
        this.initEvents();
    }

    initEvents() {
        const self = this;
        self.#addEventosBotoes();
        self.#buscarContas();
        self.#buscarMovimentacoesTipo();
        self.#buscarMovimentacoesStatusTipo();
    }

    #addEventosBotoes() {
        const self = this;

        $(`#formDataSearch${self.getSufixo}`).find('.btnBuscar').on('click', async function (e) {
            e.preventDefault();
            self.#executarBusca();
        });

        const preencherInfoParceiro = (perfil) => {
            const card = $(`#dados-parceiro${self.getSufixo}`);

            let nome = '';
            const perfilNome = perfil.perfil_tipo.nome;
            switch (perfil.pessoa.pessoa_dados_type) {

                case window.Enums.PessoaTipoEnum.PESSOA_FISICA:
                    nome = perfil.pessoa.pessoa_dados.nome;
                    break;

                case window.Enums.PessoaTipoEnum.PESSOA_JURIDICA:
                    nome = perfil.pessoa.pessoa_dados.nome_fantasia;
                    break;

                default:
                    throw new Error('Tipo de pessoa inválido');
            }

            card.find(`.nome-parceiro`).html(nome);
            card.find(`.card-perfil-referencia`).html(perfilNome);
            self._objConfigs.data.parceiro_id = perfil.id;
            self.#statusCampos(true);
        }

        $(`#btnSelecionarParceiro${self.getSufixo}`).on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const dataEnvModalAppend = {
                    perfis_busca: window.Statics.PerfisPermitidoParticipacaoServico,
                };
                const objModal = new modalPessoa({ dataEnvModal: dataEnvModalAppend });
                const response = await objModal.modalOpen();
                if (response.refresh && response.selected) {
                    preencherInfoParceiro(response.selected);
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading(btn, false);
            }
        });

        $(`#btnImprimirConsulta${self.getSufixo}`).on('click', async function () {
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
                    if (response.selecteds.length > 0) {
                        const item = response.selecteds[0];
                        self.#buscarContas(item.id);
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

        $(`#btnLancarRepasse${self.getSufixo}`).on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const selecionados = self._objConfigs.data.selecionados;
                if (selecionados.length == 0) {
                    commonFunctions.generateNotification('Selecione pelo menos uma movimentação para efetuar o repasse!', 'warning');
                    return;
                }
                let participacoesIds = selecionados.map(movimentacao => movimentacao.id);

                const message = participacoesIds.length > 1 ? `Confirma o repasse das movimentações selecionadas?` : `Confirma o repasse da movimentação selecionada?`;
                const objMessage = new modalMessage();
                objMessage.setDataEnvModal = {
                    title: 'Efetuar Repasse',
                    message: message,
                }
                const responseMessage = await objMessage.modalOpen();
                if (!responseMessage.confirmResult) return;

                const objConn = new connectAjax(self._objConfigs.url.baseLancarRepasseParceiro);
                objConn.setAction(enumAction.POST);
                objConn.setData({
                    participacoes: participacoesIds
                });
                const response = await objConn.envRequest();
                if (response.data) {
                    commonFunctions.generateNotification('Repasse efetuado com sucesso!', 'success');
                    await self.#executarBusca();
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading(btn, false);
            }
        });

        $(`#ckbCheckAll${self.getSufixo}`).on('change', async function () {
            const tableData = $(`#tableData${self.getSufixo} tbody`);
            const ckbNaTela = tableData.find('.ckbSelecionado');
            ckbNaTela.prop('checked', $(this).is(':checked')).trigger('change');
        });

        self.#statusCampos(false);
    }

    async #executarBusca() {
        const self = this;

        self._objConfigs.data.totais = {
            debito: 0,
            credito: 0,
        };
        self._objConfigs.data.selecionados = []
        $(`#ckbCheckAll${self.getSufixo}`).prop('checked', false);

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

            appendData.parceiro_id = self._objConfigs.data.parceiro_id;

            return { appendData: appendData };
        }

        if (self._objConfigs.data.parceiro_id) {
            BootstrapFunctionsHelper.removeEventPopover();
            self._setTypeCurrentSearch = self._objConfigs.querys.consultaFiltros.name;
            await self._generateQueryFilters(getAppendDataQuery());
            self.#atualizaValoresTotais();
        } else {
            commonFunctions.generateNotification('Selecione um parceiro', 'warning');
        }
    }

    #statusCampos(status = true) {
        const self = this;
        const camposConsulta = $(`#camposConsulta${self.getSufixo}`).find(`input, select, button, submit`);

        if (status) {
            camposConsulta.removeAttr('disabled');
            self.#executarBusca();
        } else {
            camposConsulta.attr('disabled', 'disabled');
        }
    }

    async insertTableData(item, options = {}) {
        const self = this;
        const {
            tbody,
        } = options;

        let strBtns = self.#htmlBtns(item);

        const movimentacao = item.parent;

        const status = item.status.nome;
        const movimentacaoTipo = movimentacao.movimentacao_tipo.nome;
        const valorParticipante = `R$ ${commonFunctions.formatWithCurrencyCommasOrFraction(item.valor_participante)}`;

        switch (movimentacao.movimentacao_tipo_id) {
            case window.Enums.MovimentacaoContaTipoEnum.CREDITO:
                self._objConfigs.data.totais.credito += item.valor_participante;
                break;
            case window.Enums.MovimentacaoContaTipoEnum.DEBITO:
                self._objConfigs.data.totais.debito += item.valor_participante;
                break;
        }

        const dataMovimentacao = DateTimeHelper.retornaDadosDataHora(movimentacao.data_movimentacao, 2);
        const descricaoAutomatica = item.descricao_automatica;
        const conta = movimentacao.conta.nome;

        let dadosEspecificos = movimentacao.descricao_automatica;

        switch (movimentacao.referencia_type) {
            case window.Enums.MovimentacaoContaReferenciaEnum.SERVICO_LANCAMENTO:
                dadosEspecificos += ` - Serviço ${movimentacao.referencia.pagamento.servico.numero_servico}`;
                dadosEspecificos += ` - Pagamento - ${movimentacao.referencia.pagamento.numero_pagamento}`;
                dadosEspecificos += ` - ${movimentacao.referencia.pagamento.servico.area_juridica.nome}`;
                dadosEspecificos += ` - ${movimentacao.referencia.pagamento.servico.titulo}`;
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
        const created_at = DateTimeHelper.retornaDadosDataHora(movimentacao.created_at, 12);

        $(tbody).append(`
            <tr id=${item.idTr} data-id="${item.id}">
                <td class="text-center">
                    <div class="btn-group btnsAcao" role="group">
                        ${strBtns}
                    </div>
                </td>
                <td class="text-nowrap text-truncate ${classCor}" title="${status}">${status}</td>
                <td class="text-nowrap text-truncate ${classCor}" title="${movimentacaoTipo}">${movimentacaoTipo}</td>
                <td class="text-nowrap text-center ${classCor}" title="${valorParticipante}">${valorParticipante}</td>
                <td class="text-nowrap text-center ${classCor}" title="${dataMovimentacao}">${dataMovimentacao}</td>
                <td class="text-nowrap text-truncate ${classCor}" title="${descricaoAutomatica}">${descricaoAutomatica}</td>
                <td class="text-nowrap text-truncate ${classCor}" title="${dadosEspecificos}">${dadosEspecificos}</td>
                <td class="text-nowrap text-truncate ${classCor}" title="${conta}">${conta}</td>
                <td class="text-nowrap ${classCor}" title="${created_at ?? ''}">${created_at ?? ''}</td>
            </tr>
        `);

        self.#addEventosRegistrosConsulta(item);
        BootstrapFunctionsHelper.addEventPopover();
        return true;
    }

    #atualizaValoresTotais() {
        const self = this;
        $(`#total_credito${self.getSufixo}`).html(`R$ ${commonFunctions.formatWithCurrencyCommasOrFraction(self._objConfigs.data.totais.credito)}`);
        $(`#total_debito${self.getSufixo}`).html(`R$ ${commonFunctions.formatWithCurrencyCommasOrFraction(self._objConfigs.data.totais.debito)}`);
        $(`#total_saldo${self.getSufixo}`).html(`R$ ${commonFunctions.formatWithCurrencyCommasOrFraction(self._objConfigs.data.totais.credito - self._objConfigs.data.totais.debito)}`);
    }

    #htmlBtns() {

        // let strBtns = `
        //     <div class="input-group">
        //         <div class="input-group-text border-0 rounded-end-0 bg-transparent">
        //             <input class="form-check-input mt-0 ckbSelecionado" type="checkbox" value="" aria-label="Checkbox for following text input">
        //         </div>
        //     </div>
        //     <button class="btn dropdown-toggle btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        //         <i class="bi bi-three-dots-vertical"></i>
        //     </button>
        //     <ul class="dropdown-menu">
        //         <li>
        //             <button type="button" class="dropdown-item fs-6 btn-edit" title="Efetuar aos participantes.">
        //                 Efetuar repasse
        //             </button>
        //         </li>
        //     </ul>`;
        
        let strBtns = `
            <div class="input-group">
                <div class="input-group-text border-0 rounded-end-0 bg-transparent">
                    <input class="form-check-input mt-0 ckbSelecionado" type="checkbox" value="" aria-label="Checkbox for following text input">
                </div>
            </div>`;

        return strBtns;
    }

    #addEventosRegistrosConsulta(item) {
        const self = this;

        const buscaIndex = (itemVerificar) => {
            return self._objConfigs.data.selecionados.findIndex(itemBusca => itemBusca.id == itemVerificar.id);
        };

        $(`#${item.idTr}`).find(`.ckbSelecionado`).on('change', async function () {
            const tableData = $(`#tableData${self.getSufixo} tbody`);
            const ckbCheckAll = $(`#ckbCheckAll${self.getSufixo}`);

            try {
                let selecionados = self._objConfigs.data.selecionados;
                const index = buscaIndex(item);
                const prop = $(this).prop('checked');

                if (index === -1 && prop) {
                    // Adiciona o item se ele não estiver na lista e estiver selecionado
                    selecionados.push(item);
                    if (tableData.find(`.ckbSelecionado`).length == tableData.find(`.ckbSelecionado:checked`).length) {
                        ckbCheckAll.prop('checked', true);
                    }
                } else if (index > -1 && !prop) {
                    // Remove o item se ele estiver na lista e não estiver selecionado
                    selecionados.splice(index, 1); // Remove diretamente pelo índice
                    ckbCheckAll.prop('checked', false);
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
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
            const arrayOpcoes = window.Statics.TiposMovimentacaoParaLancamentos;
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
            const arrayOpcoes = window.Statics.MovimentacaoContaStatusTipoStatusMostrarBalancoRepasseParceiroFrontEnd;
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
    new PageBalancoRepasseParceiroIndex();
});