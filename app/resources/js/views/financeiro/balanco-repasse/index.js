import { CommonFunctions } from "../../../commons/CommonFunctions";
import { ConnectAjax } from "../../../commons/ConnectAjax";
import { EnumAction } from "../../../commons/EnumAction";
import { TemplateSearch } from "../../../commons/templates/TemplateSearch";
import { ModalSelecionarConta } from "../../../components/financeiro/ModalSelecionarConta";
import { ModalPessoa } from "../../../components/pessoas/ModalPessoa";
import { ModalContaTenant } from "../../../components/tenant/ModalContaTenant";
import { BootstrapFunctionsHelper } from "../../../helpers/BootstrapFunctionsHelper";
import { DateTimeHelper } from "../../../helpers/DateTimeHelper";
import TenantTypeDomainCustomHelper from "../../../helpers/TenantTypeDomainCustomHelper";
import { URLHelper } from "../../../helpers/URLHelper";
import { UUIDHelper } from "../../../helpers/UUIDHelper";

class PageBalancoRepasseIndex extends TemplateSearch {

    #objConfigs = {
        querys: {
            consultaFiltros: {
                name: 'consulta-filtros',
                url: `${window.apiRoutes.baseRepasse}`,
                urlSearch: `${window.apiRoutes.baseRepasse}/consulta-filtros`,
                // functionExecuteOnError: 'functionExecuteOnError',
            }
        },
        url: {
            baseContas: window.apiRoutes.baseContas,
            baseLancarRepasse: window.apiRoutes.baseLancarRepasse,
            baseRepasse: window.apiRoutes.baseRepasse,
            baseFrontImpressao: window.frontRoutes.baseFrontImpressao,
        },
        data: {
            perfil_id: undefined,
            totais: {
                debito: 0,
                credito: 0,
                debito_liquidado: 0,
                credito_liquidado: 0,
            },
            selecionados: [],
        },
        domainCustom: {
            applyBln: true,
        },
    };

    constructor() {
        super({ sufixo: 'PageBalancoRepasseIndex', withOutVerifyDomainCustom: true });
        this._objConfigs = CommonFunctions.deepMergeObject(this._objConfigs, this.#objConfigs);
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
            self._executarBusca();
        });

        const preencherInfoPessoa = (perfil) => {
            const card = $(`#dados-pessoa${self.getSufixo}`);

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

            card.find(`.nome-pessoa`).html(nome);
            card.find(`.card-perfil-referencia`).html(`Perfil Referência: <span class="fw-bolder">${perfilNome}</span>`);
            self._objConfigs.data.perfil_id = perfil.id;
            self._objConfigs.data.perfil = perfil;
            self.#statusCampos(true);
        }

        $(`#btnSelecionarPessoa${self.getSufixo}`).on('click', async function () {
            const btn = $(this);
            CommonFunctions.simulateLoading(btn);
            try {
                const dataEnvModal = {
                    perfis_busca: window.Statics.PerfisPermitidoBalancoRepasse,
                };
                const objModal = new ModalPessoa({ dataEnvModal });
                const response = await objModal.modalOpen();
                if (response.refresh && response.selected) {
                    preencherInfoPessoa(response.selected);
                }
            } catch (error) {
                CommonFunctions.generateNotificationErrorCatch(error);
            } finally {
                CommonFunctions.simulateLoading(btn, false);
            }
        });

        $(`#btnImprimirConsulta${self.getSufixo}`).on('click', async function () {
            if (self._objConfigs.querys.consultaFiltros.dataPost) {
                // Flatten o objeto para gerar os parâmetros
                let flattenedParams = URLHelper.flattenObject(self._objConfigs.querys.consultaFiltros.dataPost);
                let queryString = '';

                // Constrói a query string
                Object.keys(flattenedParams).forEach(function (key) {
                    if (queryString != '') queryString += '&';
                    queryString += `${encodeURIComponent(key)}=${encodeURIComponent(flattenedParams[key])}`;
                });

                const instance = TenantTypeDomainCustomHelper.getInstanceTenantTypeDomainCustom;
                if (instance) {
                    queryString += `&${instance.getNameAttributeKey}=${self._objConfigs.domainCustom.domain_id}`;
                }

                // Crie a URL base (substitua pela URL desejada)
                const baseURL = self._objConfigs.url.baseFrontImpressao;

                // Abre em uma nova guia
                window.open(`${baseURL}?${queryString}`, '_blank');
            }
        });

        CommonFunctions.handleModal(self, $(`#openModalConta${self.getSufixo}`), ModalContaTenant, self.#buscarContas.bind(self));

        $(`#btnLancarRepasse${self.getSufixo}`).on('click', async function () {
            if (self._objConfigs.querys.consultaFiltros.dataPost) {

                const btn = $(this);
                CommonFunctions.simulateLoading(btn);

                try {

                    const objModal = new ModalSelecionarConta();
                    objModal.setDataEnvModal = {
                        perfil: self._objConfigs.data.perfil,
                    };
                    const responseConta = await objModal.modalOpen();

                    if (responseConta.refresh) {

                        const forcedDomainId = TenantTypeDomainCustomHelper.checkDomainCustomForcedDomainId(self);
                        const objConn = new ConnectAjax(self._objConfigs.url.baseLancarRepasse);
                        if (forcedDomainId) {
                            objConn.setForcedDomainCustomId = forcedDomainId;
                        }

                        objConn.setAction(EnumAction.POST);
                        objConn.setData(
                            CommonFunctions.deepMergeObject(
                                responseConta.register,
                                self._objConfigs.querys.consultaFiltros.dataPost
                            ));
                        const response = await objConn.envRequest();
                        if (response.data) {
                            CommonFunctions.generateNotification('Repasse efetuado com sucesso!', 'success');
                            await self._executarBusca();
                        }
                    }
                } catch (error) {
                    CommonFunctions.generateNotificationErrorCatch(error);
                } finally {
                    CommonFunctions.simulateLoading(btn, false);
                }
            }
        });

        $(`#ckbCheckAll${self.getSufixo}`).on('change', async function () {
            const tableData = $(`#tableData${self.getSufixo} tbody`);
            const ckbNaTela = tableData.find('.ckbSelecionado');
            ckbNaTela.prop('checked', $(this).is(':checked')).trigger('change');
        });

        const instance = TenantTypeDomainCustomHelper.getInstanceTenantTypeDomainCustom;
        if (instance) {
            self.#addEventosDomainCustom();
        }
        self.#statusCampos(false);
    }

    #addEventosDomainCustom() {
        const self = this;

        const selectDomain = $(`#domain_id${self.getSufixo}`);
        if (!selectDomain.length) {
            CommonFunctions.generateNotification('Elemento de seleção de domínio não encontrado! Contate o suporte.', 'error');
        }

        selectDomain.on('change', async function () {
            try {
                const domainId = $(this).val();
                const domain = TenantTypeDomainCustomHelper.getDomainNameById(domainId);

                if (!domain) {
                    console.warn(`Domínio não encontrado: ${domainId}`, TenantTypeDomainCustomHelper.getDomainsOptions);
                    throw new Error(`A unidade selecionada não foi encontrada. Contate o suporte.`, 'success');
                }

                self.setForcedDomainIdBlockedChanges = domainId;

                if (self._objConfigs.data.perfil_id) {
                    await self._executarBusca();
                }
            } catch (error) {
                CommonFunctions.generateNotificationErrorCatch(error);
            }
        });

        const instance = TenantTypeDomainCustomHelper.getInstanceTenantTypeDomainCustom;

        // Seleciona por padrão o domínio já selecionado. Caso contrário, seleciona o primeiro.
        const selectedGlobal = instance.getSelectedValue;
        if (selectedGlobal) {
            selectDomain.val(selectedGlobal).trigger('change');
        } else {
            selectDomain.trigger('change');
        }

    }

    async _executarBusca() {
        const self = this;

        // self._objConfigs.data.totais = {
        //     debito: 0,
        //     credito: 0,
        //     credito_liquidado: 0,
        //     debito_liquidado: 0,
        // };
        // self._objConfigs.data.selecionados = []
        // $(`#ckbCheckAll${self.getSufixo}`).prop('checked', false);
        $(`.campo_totais${self.getSufixo}`).html('<span class="spinner-border spinner-border-sm" role="loading" aria-hidden="true"></span>');

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

            appendData.perfil_id = self._objConfigs.data.perfil_id;

            return { appendData: appendData };
        }

        if (self._objConfigs.data.perfil_id) {
            BootstrapFunctionsHelper.removeEventPopover();
            self._setTypeCurrentSearch = self._objConfigs.querys.consultaFiltros.name;
            self._objConfigs.atualizandoValores = false;
            await self._generateQueryFilters(getAppendDataQuery());
        } else {
            CommonFunctions.generateNotification('Selecione uma pessoa', 'warning');
        }
    }

    #statusCampos(status = true) {
        const self = this;
        const camposConsulta = $(`#camposConsulta${self.getSufixo}`).find(`input, select, button, submit`);

        if (status) {
            camposConsulta.removeAttr('disabled');
            self._executarBusca();
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

        const parent = item.parent;

        const status = item.status.nome;
        let movimentacaoTipo = '';
        const valorParticipante = `R$ ${CommonFunctions.formatWithCurrencyCommasOrFraction(item.valor_participante)}`;

        let dataMovimentacao = '';
        const descricaoAutomatica = item.descricao_automatica;
        let conta = 'Erro Conta';

        let dadosEspecificos = '';
        let dadosEspecificosTitle = '';

        switch (item.parent_type) {
            case window.Enums.BalancoRepasseTipoParentEnum.MOVIMENTACAO_CONTA:

                dataMovimentacao = DateTimeHelper.retornaDadosDataHora(parent.data_movimentacao, 2);
                movimentacaoTipo = parent.movimentacao_tipo.nome;

                dadosEspecificos = parent.descricao_automatica;
                dadosEspecificosTitle = `Descrição ${parent.descricao_automatica}`;
                conta = parent.conta_domain.conta.nome;

                switch (parent.referencia_type) {

                    case window.Enums.MovimentacaoContaReferenciaEnum.SERVICO_LANCAMENTO:
                        // dadosEspecificos = `NS#${parent.referencia.pagamento.servico.numero_servico}`;
                        dadosEspecificosTitle = `Número de Serviço ${parent.referencia.pagamento.servico.numero_servico}`;

                        dadosEspecificos += ` - NP#${parent.referencia.pagamento.numero_pagamento}`;
                        dadosEspecificosTitle += ` - Número do Pagamento ${parent.referencia.pagamento.numero_pagamento}`;

                        dadosEspecificos += ` - (${parent.referencia.pagamento.servico.area_juridica.nome})`;
                        dadosEspecificosTitle += ` - (Área Jurídica ${parent.referencia.pagamento.servico.area_juridica.nome})`;

                        dadosEspecificos += ` - ${parent.referencia.pagamento.servico.titulo}`;
                        dadosEspecificosTitle += ` - Título ${parent.referencia.pagamento.servico.titulo}`;
                        break;

                    // case window.Enums.MovimentacaoContaReferenciaEnum.LANCAMENTO_GERAL:
                    //     dadosEspecificos += ` - NL#${parent.referencia.numero_lancamento}`;
                    //     dadosEspecificos += ` - (${parent.referencia.categoria.nome})`;
                    //     break;

                    default:
                        const message = `Tipo de referência de movimentação de conta não configurado.`;
                        console.error(parent.referencia_type, parent);
                        throw new Error(message);
                }
                break;

            case window.Enums.BalancoRepasseTipoParentEnum.LANCAMENTO_RESSARCIMENTO:

                dataMovimentacao = DateTimeHelper.retornaDadosDataHora(parent.data_vencimento, 2);
                movimentacaoTipo = parent.parceiro_movimentacao_tipo.nome;

                dadosEspecificos = item.descricao_automatica;
                dadosEspecificosTitle = `Descrição Automática: ${item.descricao_automatica}`;

                dadosEspecificos += ` - NR#${parent.numero_lancamento}`;
                dadosEspecificosTitle += ` - Número do Ressarcimento ${parent.numero_lancamento}`;

                dadosEspecificos += ` - (${parent.categoria.nome})`;
                dadosEspecificosTitle += ` - (Categoria ${parent.categoria.nome})`;

                dadosEspecificos += ` - ${parent.descricao}`;
                dadosEspecificosTitle += ` - Descrição: ${parent.descricao}`;

                conta = parent.conta.nome;

                break;

            default:
                const message = `Tipo parent de registro de balanço de parceiro não configurado.`;
                console.error(item.parent_type, item);
                throw new Error(message);
        }

        // self.#executarSomatoriaTotais(item);

        const created_at = DateTimeHelper.retornaDadosDataHora(parent.created_at, 12);

        $(tbody).append(`
            <tr id=${item.idTr} data-id="${item.id}">
                <td class="text-center">
                    <div class="btn-group btnsAcao" role="group">
                        ${strBtns}
                    </div>
                </td>
                <td class="text-nowrap text-truncate campo-tabela-truncate-35" title="${status}">${status}</td>
                <td class="text-nowrap text-truncate" title="${movimentacaoTipo}">${movimentacaoTipo}</td>
                <td class="text-nowrap text-center" title="${valorParticipante}">${valorParticipante}</td>
                <td class="text-nowrap text-center" title="${dataMovimentacao}">${dataMovimentacao}</td>
                <td class="text-nowrap text-truncate campo-tabela-truncate-30" title="${descricaoAutomatica}">${descricaoAutomatica}</td>
                <td class="text-nowrap text-truncate campo-tabela-truncate-35" title="${dadosEspecificosTitle}">${dadosEspecificos}</td>
                <td class="text-nowrap text-truncate" title="${conta}">${conta}</td>
                <td class="text-nowrap" title="${created_at ?? ''}">${created_at ?? ''}</td>
            </tr>
        `);

        self.#addEventosRegistrosConsulta(item);
        BootstrapFunctionsHelper.addEventPopover();
        return true;
    }

    async functionExecuteOnError(error) {
        const self = this;
        $(`.campo_totais${self.getSufixo}`).html(CommonFunctions.formatWithCurrencyCommasOrFraction(0));
        self._objConfigs.atualizandoValores = false;
    }

    async functionExecuteOnSuccess(response) {
        const self = this;
        self.#atualizaValoresTotais();
    }

    async #atualizaValoresTotais() {
        const self = this;

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
                $(`#total_credito${self.getSufixo}`).html(CommonFunctions.formatWithCurrencyCommasOrFraction(totais.credito));
                $(`#total_debito${self.getSufixo}`).html(CommonFunctions.formatWithCurrencyCommasOrFraction(totais.debito));
                $(`#total_saldo${self.getSufixo}`).html(CommonFunctions.formatWithCurrencyCommasOrFraction(totais.total_saldo));

                // Liquidados
                $(`#total_credito_liquidado${self.getSufixo}`).html(CommonFunctions.formatWithCurrencyCommasOrFraction(totais.credito_liquidado));
                $(`#total_debito_liquidado${self.getSufixo}`).html(CommonFunctions.formatWithCurrencyCommasOrFraction(totais.debito_liquidado));
                $(`#total_saldo_liquidado${self.getSufixo}`).html(CommonFunctions.formatWithCurrencyCommasOrFraction(totais.total_saldo_liquidado));

            } catch (error) {
                $(`.campo_totais${self.getSufixo}`).html('0,00');
                CommonFunctions.generateNotificationErrorCatch(error);
            } finally {
                self._objConfigs.atualizandoValores = false;
            }
        }

    }

    #htmlBtns() {

        return '';
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
                CommonFunctions.generateNotificationErrorCatch(error);
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

    async #buscarMovimentacoesStatusTipo(selected_id = null) {
        try {
            const self = this;
            const arrayOpcoes = window.Statics.StatusMovimentacaoParticipanteStatusMostrarBalancoRepasseFrontEnd;
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
    new PageBalancoRepasseIndex();
});