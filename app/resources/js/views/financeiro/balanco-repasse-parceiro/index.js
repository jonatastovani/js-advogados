import { commonFunctions } from "../../../commons/commonFunctions";
import { templateSearch } from "../../../commons/templates/templateSearch";
import { modalLancamentoMovimentar } from "../../../components/financeiro/modalLancamentoMovimentar";
import { modalPessoa } from "../../../components/pessoas/modalPessoa";
import { BootstrapFunctionsHelper } from "../../../helpers/BootstrapFunctionsHelper";
import { DateTimeHelper } from "../../../helpers/DateTimeHelper";
import { ServicoParticipacaoHelpers } from "../../../helpers/ServicoParticipacaoHelpers";
import { URLHelper } from "../../../helpers/URLHelper";

class PageBalancoRepasseParceiroIndex extends templateSearch {

    #objConfigs = {
        querys: {
            consultaFiltros: {
                name: 'consulta-filtros',
                url: `${window.apiRoutes.baseBalancoRepasseParceiro}`,
                urlSearch: `${window.apiRoutes.baseBalancoRepasseParceiro}/consulta-filtros`,
            }
        },
        url: {
            baseBalancoRepasseParceiro: window.apiRoutes.baseBalancoRepasseParceiro,
            baseFrontImpressao: window.frontRoutes.baseFrontImpressao,
        },
        data: {
            parceiro_id: undefined,
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

                default:
                    throw new Error('Tipo de pessoa inválido');
            }

            card.find(`.nome-parceiro`).html(nome);
            card.find(`.card-perfil-referencia`).html(perfilNome);
            card.show('fast');
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
                console.log(flattenedParams);
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

        self.#statusCampos(false);
        const openModal = async () => {
            try {
                const objModal = new modalLancamentoMovimentar({
                    urlApi: `${self._objConfigs.url.baseServico}/`
                });
                objModal.setDataEnvModal = {
                    idRegister: "9d7f9116-eb25-4090-993d-cdf0ae143c03",
                    pagamento_id: "9d7f9116-d30a-4559-9231-3083ad482553",
                    status_id: window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_EM_ANALISE
                }
                const response = await objModal.modalOpen();
                console.log(response);

            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            }
        }

        // openModal();
    }

    #executarBusca() {
        const self = this;
        if (self._objConfigs.data.parceiro_id) {
            BootstrapFunctionsHelper.removeEventPopover();
            self._setTypeCurrentSearch = self._objConfigs.querys.consultaFiltros.name;
            self._generateQueryFilters({
                appendData: {
                    parceiro_id: self._objConfigs.data.parceiro_id
                }
            });
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

        if (!self.#objConfigs.data?.console) {
            self.#objConfigs.data.console = true;
            console.log(item);
        }

        let strBtns = ``;
        // let strBtns = self.#HtmlBtns(item);

        const status = item.status.nome;
        const valorMovimentado = commonFunctions.formatWithCurrencyCommasOrFraction(item.valor_movimentado);
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

        // const valorEsperado = commonFunctions.formatWithCurrencyCommasOrFraction(item.valor_esperado);
        // const dataVencimento = DateTimeHelper.retornaDadosDataHora(item.data_vencimento, 2);
        // const numero_servico = item.pagamento.servico.numero_servico;
        // const valorPagamento = item.pagamento.valor_total ? commonFunctions.formatWithCurrencyCommasOrFraction(item.pagamento.valor_total) : '***';
        // const tituloServico = item.pagamento.servico.titulo;
        // const areaJuridica = item.pagamento.servico.area_juridica.nome;
        // const valorLiquidado = item.pagamento.total_liquidado ? commonFunctions.formatWithCurrencyCommasOrFraction(item.pagamento.total_liquidado) : '***';
        // const valorAguardando = item.pagamento.total_aguardando ? commonFunctions.formatWithCurrencyCommasOrFraction(item.pagamento.total_aguardando) : '***';
        // const valorInadimplente = item.pagamento.total_inadimplente ? commonFunctions.formatWithCurrencyCommasOrFraction(item.pagamento.total_inadimplente) : '***';
        // const pagamentoTipo = item.pagamento.pagamento_tipo_tenant.nome ?? item.pagamento.pagamento_tipo_tenant.pagamento_tipo.nome
        // const observacaoPagamento = item.pagamento.observacao ?? '***';
        // const statusPagamento = item.status.nome;

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
            <td class="text-center ${classCor}">***</td>
        `;

        switch (item.referencia_type) {
            case window.Enums.MovimentacaoContaReferenciaEnum.SERVICO_LANCAMENTO:
                if (item.referencia?.participantes && item.referencia.participantes.length &&
                    (window.Statics.StatusServicoLancamentoComParticipantes.findIndex(status => status == item.status_id) != -1)
                ) {
                    const arrays = ServicoParticipacaoHelpers.htmlRenderParticipantesEIntegrantes(item.referencia.participantes);
                    htmlThParticipantesIntegrantes = `
                        <td class="text-center ${classCor}"><button type="button" class="btn btn-sm btn-outline-info border-0" data-bs-toggle="popover" data-bs-title="Participantes do Lançamento ${descricaoAutomatica}" data-bs-html="true" data-bs-content="${arrays.arrayParticipantes.join("<hr class='my-1'>")}">Ver mais</button></td>
                        <td class="text-center ${classCor}"><button type="button" class="btn btn-sm btn-outline-info border-0" data-bs-toggle="popover" data-bs-title="Integrantes de Grupos" data-bs-html="true" data-bs-content="${arrays.arrayIntegrantes.join("<hr class='my-1'>")}">Ver mais</button></td>
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

        // `
        //         <td class="text-nowrap ${classCor}" title="${numero_servico}">${numero_servico}</td>
        //         <td class="text-nowrap text-center ${classCor}" title="${valorEsperado}">${valorEsperado}</td>
        //         <td class="text-nowrap text-center ${classCor}" title="${dataVencimento}">${dataVencimento}</td>
        //         <td class="text-nowrap text-center ${classCor}" title="${valorPagamento}">${valorPagamento}</td>
        //         <td class="text-truncate ${classCor}" title="${tituloServico}">${tituloServico}</td>
        //         <td class="text-nowrap text-truncate ${classCor}" title="${areaJuridica}">${areaJuridica}</td>
        //         <td class="text-nowrap text-center ${classCor}" title="${valorLiquidado}">${valorLiquidado}</td>
        //         <td class="text-nowrap text-center ${classCor}" title="${valorAguardando}">${valorAguardando}</td>
        //         <td class="text-nowrap text-center ${classCor}" title="${valorInadimplente}">${valorInadimplente}</td>
        //         <td class="text-nowrap text-truncate ${classCor}" title="${pagamentoTipo}">${pagamentoTipo}</td>
        //         <td class="text-nowrap text-truncate ${classCor}" title="${observacaoPagamento}">${observacaoPagamento}</td>
        //         <td class="text-nowrap text-truncate ${classCor}" title="${statusPagamento}">${statusPagamento}</td>
        //         `;

        // self.#addEventosRegistrosConsulta(item);
        BootstrapFunctionsHelper.addEventPopover();
        return true;
    }

    #HtmlBtns(item) {
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

            strBtns = `
            <div class="btn-group">
                <button class="btn dropdown-toggle btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-three-dots-vertical"></i>
                </button>
                <ul class="dropdown-menu">
                    ${strBtns}
                </ul>
            </div>`;
        }
        return strBtns;

    }

    #htmlRenderParticipantesEIntegrantes(participantes) {

    }

    // #addEventosRegistrosConsulta(item) {
    //     const self = this;
    //     const enumLanc = window.Enums.LancamentoStatusTipoEnum;
    //     const configAcoes = self.#objConfigs.data.configAcoes;
    //     const lancamentoDiluido = item.parent_id ? true : false;

    //     const openMovimentar = async function (status_id) {
    //         try {
    //             const objModal = new modalLancamentoMovimentar();
    //             objModal.setDataEnvModal = {
    //                 idRegister: item.id,
    //                 pagamento_id: item.pagamento_id,
    //                 status_id: status_id
    //             }
    //             const response = await objModal.modalOpen();
    //             if (response.refresh) {
    //                 await self._generateQueryFilters();
    //             }
    //         } catch (error) {
    //             commonFunctions.generateNotificationErrorCatch(error);
    //         }
    //     }

    //     let btnAcao = $(`#${item.idTr}`).find(`.btn-aguardando-pagamento-analise`);
    //     if (btnAcao.length && configAcoes.AGUARDANDO_PAGAMENTO_EM_ANALISE.opcao_nos_status.findIndex(status => status == item.status_id) != -1) {
    //         btnAcao.click(async function () {
    //             try {
    //                 const obj = new modalMessage();
    //                 obj.setDataEnvModal = {
    //                     title: 'Alterar Status',
    //                     message: `Confirma a alteração de status do lançamento <b>${item.descricao_automatica}</b> para <b class="fst-italic">Aguardando Pagamento (em Análise)</b>?`,
    //                 };
    //                 obj.setFocusElementWhenClosingModal = this;
    //                 const result = await obj.modalOpen();
    //                 if (result.confirmResult) {
    //                     const objConn = new connectAjax(`${self._objConfigs.url.baseBalancoRepasseParceiro}/servicos/status-alterar`);
    //                     objConn.setAction(enumAction.POST);
    //                     objConn.setData({
    //                         lancamento_id: item.id,
    //                         status_id: enumLanc.AGUARDANDO_PAGAMENTO_EM_ANALISE,
    //                     });
    //                     const response = await objConn.envRequest();
    //                     if (response.data) {
    //                         await self._generateQueryFilters();
    //                     }
    //                 }
    //             } catch (error) {
    //                 commonFunctions.generateNotificationErrorCatch(error);
    //             }
    //         });
    //     }

    //     btnAcao = $(`#${item.idTr}`).find(`.btn-aguardando-pagamento`);
    //     if (btnAcao.length && configAcoes.AGUARDANDO_PAGAMENTO.opcao_nos_status.findIndex(status => status == item.status_id) != -1) {
    //         btnAcao.click(async function () {
    //             try {
    //                 const obj = new modalMessage();
    //                 obj.setDataEnvModal = {
    //                     title: 'Alterar Status',
    //                     message: `Confirma a alteração de status do lançamento <b>${item.descricao_automatica}</b> para <b class="fst-italic">Aguardando Pagamento</b>?`,
    //                 };
    //                 obj.setFocusElementWhenClosingModal = this;
    //                 const result = await obj.modalOpen();
    //                 if (result.confirmResult) {
    //                     const objConn = new connectAjax(`${self._objConfigs.url.baseBalancoRepasseParceiro}/servicos/status-alterar`);
    //                     objConn.setAction(enumAction.POST);
    //                     objConn.setData({
    //                         lancamento_id: item.id,
    //                         status_id: enumLanc.AGUARDANDO_PAGAMENTO,
    //                     });
    //                     const response = await objConn.envRequest();
    //                     if (response.data) {
    //                         await self._generateQueryFilters();
    //                     }
    //                 }
    //             } catch (error) {
    //                 commonFunctions.generateNotificationErrorCatch(error);
    //             }
    //         });
    //     }

    //     btnAcao = $(`#${item.idTr}`).find(`.btn-liquidado-analise`);
    //     if (btnAcao.length && configAcoes.LIQUIDADO_EM_ANALISE.opcao_nos_status.findIndex(status => status == item.status_id) != -1) {
    //         btnAcao.click(async function () {
    //             try {
    //                 const obj = new modalMessage();
    //                 obj.setDataEnvModal = {
    //                     title: 'Alterar Status',
    //                     message: `Confirma a alteração de status do lançamento <b>${item.descricao_automatica}</b> para <b class="fst-italic">Liquidado (em Análise)</b>?`,
    //                 };
    //                 obj.setFocusElementWhenClosingModal = this;
    //                 const result = await obj.modalOpen();
    //                 if (result.confirmResult) {
    //                     const objConn = new connectAjax(`${self._objConfigs.url.baseBalancoRepasseParceiro}/servicos/status-alterar`);
    //                     objConn.setAction(enumAction.POST);
    //                     objConn.setData({
    //                         lancamento_id: item.id,
    //                         status_id: enumLanc.LIQUIDADO_EM_ANALISE,
    //                     });
    //                     const response = await objConn.envRequest();
    //                     if (response.data) {
    //                         await self._generateQueryFilters();
    //                     }
    //                 }
    //             } catch (error) {
    //                 commonFunctions.generateNotificationErrorCatch(error);
    //             }
    //         });
    //     }

    //     btnAcao = $(`#${item.idTr}`).find(`.btn-liquidado`);
    //     if (btnAcao.length && configAcoes.LIQUIDADO.opcao_nos_status.findIndex(status => status == item.status_id) != -1) {
    //         btnAcao.click(async function () {
    //             await openMovimentar(window.Enums.LancamentoStatusTipoEnum.LIQUIDADO);
    //         });
    //     }

    //     btnAcao = $(`#${item.idTr}`).find(`.btn-liquidado-parcialmente-analise`);
    //     if (btnAcao.length && configAcoes.LIQUIDADO_PARCIALMENTE_EM_ANALISE.opcao_nos_status.findIndex(status => status == item.status_id) != -1
    //         && !lancamentoDiluido) {

    //         btnAcao.click(async function () {
    //             try {
    //                 const obj = new modalMessage();
    //                 obj.setDataEnvModal = {
    //                     title: 'Alterar Status',
    //                     message: `Confirma a alteração de status do lançamento <b>${item.descricao_automatica}</b> para <b class="fst-italic"> Liquidado Parcialmente (em Análise)</b>?`,
    //                 };
    //                 obj.setFocusElementWhenClosingModal = this;
    //                 const result = await obj.modalOpen();
    //                 if (result.confirmResult) {
    //                     const objConn = new connectAjax(`${self._objConfigs.url.baseBalancoRepasseParceiro}/servicos/status-alterar`);
    //                     objConn.setAction(enumAction.POST);
    //                     objConn.setData({
    //                         lancamento_id: item.id,
    //                         status_id: enumLanc.LIQUIDADO_PARCIALMENTE_EM_ANALISE,
    //                     });
    //                     const response = await objConn.envRequest();
    //                     if (response.data) {
    //                         await self._generateQueryFilters();
    //                     }
    //                 }
    //             } catch (error) {
    //                 commonFunctions.generateNotificationErrorCatch(error);
    //             }
    //         });
    //     }

    //     btnAcao = $(`#${item.idTr}`).find(`.btn-liquidado-parcialmente`);
    //     if (btnAcao.length && configAcoes.LIQUIDADO_PARCIALMENTE.opcao_nos_status.findIndex(status => status == item.status_id) != -1
    //         && !lancamentoDiluido) {
    //         btnAcao.click(async function () {
    //             await openMovimentar(window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_PARCIALMENTE);
    //         });
    //     }

    //     btnAcao = $(`#${item.idTr}`).find(`.btn-reagendado-analise`);
    //     if (btnAcao.length && configAcoes.REAGENDADO_EM_ANALISE.opcao_nos_status.findIndex(status => status == item.status_id) != -1) {

    //         btnAcao.click(async function () {
    //             try {
    //                 const obj = new modalMessage();
    //                 obj.setDataEnvModal = {
    //                     title: 'Alterar Status',
    //                     message: `Confirma a alteração de status do lançamento <b>${item.descricao_automatica}</b> para <b class="fst-italic"> Reagendado (em Análise)</b>?`,
    //                 };
    //                 obj.setFocusElementWhenClosingModal = this;
    //                 const result = await obj.modalOpen();
    //                 if (result.confirmResult) {
    //                     const objConn = new connectAjax(`${self._objConfigs.url.baseBalancoRepasseParceiro}/servicos/status-alterar`);
    //                     objConn.setAction(enumAction.POST);
    //                     objConn.setData({
    //                         lancamento_id: item.id,
    //                         status_id: enumLanc.REAGENDADO_EM_ANALISE,
    //                     });
    //                     const response = await objConn.envRequest();
    //                     if (response.data) {
    //                         await self._generateQueryFilters();
    //                     }
    //                 }
    //             } catch (error) {
    //                 commonFunctions.generateNotificationErrorCatch(error);
    //             }
    //         });
    //     }

    //     btnAcao = $(`#${item.idTr}`).find(`.btn-reagendado`);
    //     if (btnAcao.length && configAcoes.REAGENDADO.opcao_nos_status.findIndex(status => status == item.status_id) != -1) {
    //         btnAcao.click(async function () {
    //             try {
    //                 const objModal = new modalLancamentoReagendar({
    //                     urlApi: `${self._objConfigs.url.baseLancamento}/servicos/reagendar`
    //                 });
    //                 objModal.setDataEnvModal = {
    //                     idRegister: item.id,
    //                     status_id: window.Enums.LancamentoStatusTipoEnum.REAGENDADO,
    //                     data_atual: item.data_vencimento
    //                 }
    //                 const response = await objModal.modalOpen();
    //                 if (response.refresh) {
    //                     await self._generateQueryFilters();
    //                 }
    //             } catch (error) {
    //                 commonFunctions.generateNotificationErrorCatch(error);
    //             }
    //         });

    //         // if (!self._objConfigs.data?.blnClick) {
    //         //     $(`#${item.idTr}`).find(`.btn-reagendado`).click();
    //         //     self._objConfigs.data.blnClick = true;
    //         // }
    //     }

    //     btnAcao = $(`#${item.idTr}`).find(`.btn-cancelado-analise`);
    //     if (btnAcao.length && configAcoes.CANCELADO_EM_ANALISE.opcao_nos_status.findIndex(status => status == item.status_id) != -1) {

    //         btnAcao.click(async function () {
    //             try {
    //                 const obj = new modalMessage();
    //                 obj.setDataEnvModal = {
    //                     title: 'Alterar Status',
    //                     message: `Confirma a alteração de status do lançamento <b>${item.descricao_automatica}</b> para <b class="fst-italic"> Cancelado (em Análise)</b>?`,
    //                 };
    //                 obj.setFocusElementWhenClosingModal = this;
    //                 const result = await obj.modalOpen();
    //                 if (result.confirmResult) {
    //                     const objConn = new connectAjax(`${self._objConfigs.url.baseBalancoRepasseParceiro}/servicos/status-alterar`);
    //                     objConn.setAction(enumAction.POST);
    //                     objConn.setData({
    //                         lancamento_id: item.id,
    //                         status_id: enumLanc.CANCELADO_EM_ANALISE,
    //                     });
    //                     const response = await objConn.envRequest();
    //                     if (response.data) {
    //                         await self._generateQueryFilters();
    //                     }
    //                 }
    //             } catch (error) {
    //                 commonFunctions.generateNotificationErrorCatch(error);
    //             }
    //         });
    //     }

    //     btnAcao = $(`#${item.idTr}`).find(`.btn-cancelado`);
    //     if (btnAcao.length && configAcoes.CANCELADO.opcao_nos_status.findIndex(status => status == item.status_id) != -1) {

    //         btnAcao.click(async function () {
    //             try {
    //                 const obj = new modalMessage();
    //                 obj.setDataEnvModal = {
    //                     title: 'Alterar Status',
    //                     message: `Confirma a alteração de status do lançamento <b>${item.descricao_automatica}</b> para <b class="fst-italic"> Cancelado </b>?`,
    //                 };
    //                 obj.setFocusElementWhenClosingModal = this;
    //                 const result = await obj.modalOpen();
    //                 if (result.confirmResult) {
    //                     const objConn = new connectAjax(`${self._objConfigs.url.baseBalancoRepasseParceiro}/servicos/status-alterar`);
    //                     objConn.setAction(enumAction.POST);
    //                     objConn.setData({
    //                         lancamento_id: item.id,
    //                         status_id: enumLanc.CANCELADO,
    //                     });
    //                     const response = await objConn.envRequest();
    //                     if (response.data) {
    //                         await self._generateQueryFilters();
    //                     }
    //                 }
    //             } catch (error) {
    //                 commonFunctions.generateNotificationErrorCatch(error);
    //             }
    //         });
    //     }
    // }
}

$(function () {
    new PageBalancoRepasseParceiroIndex();
});