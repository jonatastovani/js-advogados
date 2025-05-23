import { CommonFunctions } from "../../commons/CommonFunctions";
import { ConnectAjax } from "../../commons/ConnectAjax";
import { EnumAction } from "../../commons/EnumAction";
import { ModalRegistrationAndEditing } from "../../commons/modal/ModalRegistrationAndEditing";
import { DateTimeHelper } from "../../helpers/DateTimeHelper";
import { SnapshotWatcherHelper } from "../../helpers/SnapshotWatcherHelper";
import { TenantDataHelper } from "../../helpers/TenantDataHelper";
import { URLHelper } from "../../helpers/URLHelper";
import { UUIDHelper } from "../../helpers/UUIDHelper";
import { ValidatorHelper } from "../../helpers/ValidatorHelper";
import { ModalMessage } from "../comum/ModalMessage";
import { ModalFormaPagamentoTenant } from "../tenant/ModalFormaPagamentoTenant";
import { ModalServicoPagamentoLancamento } from "./ModalServicoPagamentoLancamento";

export class ModalServicoPagamento extends ModalRegistrationAndEditing {

    #dataEnvModal = {
        idRegister: undefined,
        pagamento_tipo_tenant_id: undefined
    }

    /**
     * Configuração local do modal
     */
    #objConfigs = {
        url: {
            base: undefined,
            baseLancamentos: undefined,
            basePagamentoTipoTenants: window.apiRoutes.basePagamentoTipoTenants,
            baseFormaPagamento: window.apiRoutes.baseFormaPagamento,
        },
        sufixo: 'ModalServicoPagamento',
        data: {
            pagamento_tipo_tenant: undefined,
            lancamentos_na_tela: [],
            resetar_pagamento_bln: false,
            liquidado_migracao_bln: false,
            personalizar_lancamentos_bln: false,
            personalizar_lancamentos_updated_bln: false
        },
        domainCustom: {
            applyBln: true,
            inheritedBln: true,
        }
    };

    /** 
     * Conteúdo a ser retornado na promisse como resolve()
    */
    #promisseReturnValue = {
        refresh: false,
    };

    /**
     * @type {SnapshotWatcherHelper}
     */
    #camposVerificarSnapshotWatcherHelper;

    constructor(options = {}) {
        super({
            idModal: "#ModalServicoPagamento",
        });

        this._objConfigs = CommonFunctions.deepMergeObject(this._objConfigs, this.#objConfigs);
        this._promisseReturnValue = CommonFunctions.deepMergeObject(this._promisseReturnValue, this.#promisseReturnValue);
        this._dataEnvModal = CommonFunctions.deepMergeObject(this._dataEnvModal, this.#dataEnvModal);
        this._objConfigs.url.base = options.urlApi;
        this._action = EnumAction.POST;
    }

    async modalOpen() {
        const self = this;

        self._queueCheckDomainCustom.setReady();
        await CommonFunctions.loadingModalDisplay(true, { message: 'Carregando informações do pagamento...' });

        try {
            if (!self._checkDomainCustomInherited()) {
                await CommonFunctions.loadingModalDisplay(false);
                return await self._returnPromisseResolve()
            };

            if (self._dataEnvModal.idRegister) {
                self._objConfigs.url.baseLancamentos = `${self._objConfigs.url.base}/${self._dataEnvModal.idRegister}/lancamentos`;
                await self.#buscarDados()
            } else {
                if (!self._dataEnvModal.pagamento_tipo_tenant_id) {
                    CommonFunctions.generateNotification('Tipo de pagamento não informado', 'error');
                    return await self._returnPromisseResolve();
                }

                await self.#buscarDadosPagamentoTipo();
                self.#buscarFormaPagamento();
                self.#buscarStatusPagamento();

                self.#limparLancamentosNaTela();
                self.#verificaPersonalizarLancamentos();
                self.#verificaLiquidadoMigracao();
            }

            await CommonFunctions.loadingModalDisplay(false);
            self._executeFocusElementOnModal($(self.getIdModal).find('.focusRegister'));

            this.#addEventosPadrao();

        } catch (error) {
            await CommonFunctions.loadingModalDisplay(false);
            CommonFunctions.generateNotificationErrorCatch(error);
            return await self._returnPromisseResolve();
        }

        await self._modalHideShow();
        return await self._modalOpen();
    }

    #addEventosPadrao() {
        const self = this;
        const modal = $(self._idModal);

        CommonFunctions.handleModal(self, modal.find('.openModalFormaPagamento'), ModalFormaPagamentoTenant, self.#buscarFormaPagamento.bind(self));

        modal.find('.btn-simular').on('click', async function () {
            CommonFunctions.simulateLoading($(this));
            try {
                // Se o usuário clicar em simular e houver lancamentos personalizados, alerta sobre a perda
                const lancamentosRemover = self._objConfigs.data.lancamentos_na_tela.filter(l => l.status?.nome === 'Simulado');

                if (self._objConfigs.data.personalizar_lancamentos_updated_bln && lancamentosRemover.length) {

                    const objMessage = new ModalMessage();
                    objMessage.setDataEnvModal = {
                        title: 'Atenção',
                        message: `
                            <p>Existem lançamentos personalizados.</p>
                            <p>Este processo irá descartá-los e gerar uma nova simulação com valores e datas gerados pelo sistema.</p>
                            <p>Deseja continuar?</p>
                        `,
                    };
                    self._modalHideShow(false);
                    const result = await objMessage.modalOpen();
                    self._modalHideShow();

                    // Se o usuário cancelar, sai da execução
                    if (!result.confirmResult) return;
                }

                await self.#simularPagamento();
            } finally {
                CommonFunctions.simulateLoading($(this), false);
            }
        });

        modal.find('.resetar_pagamento_bln').on('change', async function () {
            CommonFunctions.simulateLoading($(this));
            try {
                await self.#simularPagamento();
            } finally {
                CommonFunctions.simulateLoading($(this), false);
            }
        });

        // Captura todos os campos relevantes dentro do form do modal
        const selectors = modal.find('form')
            .find('input, select, textarea')
            .map(function () {
                return `#${this.id}`;
            }).get()
            // remove campos sem ID para evitar erros e os campos que não quero que sejam verificados
            .filter(id => id && ![`#personalizar_lancamentos_bln${self.getSufixo}`].includes(id));

        self.#camposVerificarSnapshotWatcherHelper = new SnapshotWatcherHelper(selectors);
    }

    _modalReset() {
        super._modalReset();
        const self = this;
        $(self.getIdModal).find(`#dados-pagamento${self._objConfigs.sufixo}-tab`).trigger('click');
        $(self.getIdModal).find('.elements-pane-lancamentos').css('display', '');
        $(self.getIdModal).find('.div-resetar-lancamentos, .div-resetar-lancamentos, .div-liquidado-migracao, .div-personalizar-lancamentos').hide();
        $(self.getIdModal).find('.btn-simular').show();
        $(`#resetar_pagamento_bln${self.getSufixo}, #liquidado_migracao_bln${self.getSufixo}, #personalizar_lancamentos_bln${self.getSufixo}`)
            .prop('checked', false).attr('disabled', true);
        self.#conteudoDivAlertMessage();
    }

    async #simularPagamento() {
        const self = this;

        if (!self._objConfigs.data.resetar_pagamento_bln) {
            self.#limparLancamentosSimuladosNaTela();
        } else {
            self.#limparLancamentosNaTela();
        }

        const data = self.#obterDados();

        if (!self.#saveVerifications(data, 'simulacao')) {
            return;
        }

        const response = await self.#buscarSimulacao(data);

        if (response?.data) {
            const responseData = response.data;
            responseData.lancamentos.map(lancamento => {
                self.#inserirLancamento(lancamento, { status_id: 1 });
            });
            self.#ativarBtnEditarLancamentos(self._objConfigs.data.personalizar_lancamentos_bln);
            CommonFunctions.generateNotification('Simulação de pagamento efetuada.', 'success');
            $(self.getIdModal).find(`#lancamentos${self._objConfigs.sufixo}-tab`).trigger('click');
        }

        self.#camposVerificarSnapshotWatcherHelper.updateSnapshot();
    }

    async #buscarSimulacao(data) {
        const self = this;
        const pagamentoTipo = self._objConfigs.data.pagamento_tipo_tenant.pagamento_tipo;

        try {
            const objConn = new ConnectAjax(URLHelper.formatEndpointUrl(`${pagamentoTipo.helper.endpoint_api}/render`));
            objConn.setAction(EnumAction.POST);
            objConn.setData(data);
            return await objConn.envRequest();
        } catch (error) {
            CommonFunctions.generateNotificationErrorCatch(error);
            return false;
        }
    }

    async #inserirLancamento(lancamento, pagamento) {
        const self = this;
        const rowLancamentos = $(self.getIdModal).find('.row-lancamentos');

        lancamento.idCol = `${UUIDHelper.generateUUID()}${self._objConfigs.sufixo}`;
        const tachado = self.#verificaLancamentoTachado(lancamento);
        const btns = self.#htmlBtnsLancamento(lancamento, pagamento);

        let btnsDropDown = `
            <div>
                <div class="dropdown">
                    <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu">
                        ${btns}
                    </ul>
                </div>
            </div>`;

        if (!btns) btnsDropDown = '';
        const htmlColsLancamento = self.#htmlColsLancamento(lancamento);
        const htmlAppend = self.#htmlAppendLancamento(lancamento);

        rowLancamentos.append(`
            <div id="${lancamento.idCol}" class="col">
                <div class="card p-0 ${tachado ? 'fst-italic text-secondary-emphasis text-decoration-line-through' : ''}">
                    <div class="card-header d-flex align-items-center justify-content-between py-1">
                        <span class="lancamento-descricao-automatica">${lancamento.descricao_automatica}</span>
                        ${btnsDropDown}
                    </div>
                    <div class="card-body">
                        <div class="row html-cols-lancamento row-cols-1 row-cols-md-2 row-cols-lg-4 align-items-end">
                            ${htmlColsLancamento}
                        </div>
                        <div class="html-append-lancamento">${htmlAppend}</div>
                    </div>
                </div>
            </div>`);

        self.#pushLancamentoNaTela(lancamento);
        self.#addEventosLancamentos(lancamento);
        return lancamento;
    }

    #pushLancamentoNaTela(lancamento) {
        const self = this;
        const lancamentosNaTela = self._objConfigs.data.lancamentos_na_tela;
        lancamentosNaTela.push(lancamento);
    }

    #limparLancamentosNaTela() {
        const self = this;
        $(self.getIdModal).find('.row-lancamentos').html('');
        self._objConfigs.data.lancamentos_na_tela = [];
        self._objConfigs.data.personalizar_lancamentos_updated_bln = false;
        self.#conteudoDivAlertMessage();
    }

    #limparLancamentosSimuladosNaTela() {
        const self = this;

        // Filtra os lançamentos simulados
        const lancamentosRemover = self._objConfigs.data.lancamentos_na_tela.filter(l => l.status?.nome === 'Simulado');

        // Remove da tela os HTMLs correspondentes
        lancamentosRemover.forEach(lancamento => {
            if (lancamento.idCol) {
                $(`#${lancamento.idCol}`).remove();
            }
        });

        // Remove do array principal os simulados
        self._objConfigs.data.lancamentos_na_tela = self._objConfigs.data.lancamentos_na_tela.filter(l => l.status?.nome !== 'Simulado');

        // Atualiza flag e mensagem
        self.#conteudoDivAlertMessage();
    }

    #conteudoDivAlertMessage(html = null) {
        const self = this;
        const divAlertMessage = $(self.getIdModal).find('.divAlertMessage');

        // Remove espaços em branco e verifica se o conteúdo é "vazio"
        const isConteudoValido = html && html.trim() !== '';

        if (isConteudoValido) {
            divAlertMessage.html(html).show('fast');
        } else {
            divAlertMessage.hide('fast').html('');
        }
    }

    #verificaLancamentoTachado(lancamento) {
        return window.Statics.StatusLancamentoTachado.findIndex(x => x == lancamento.status_id) != -1;
    }

    #verificaPagamentoAtivo(pagamento) {
        return pagamento.status_id == window.Enums.PagamentoStatusTipoEnum.ATIVO ? true : false;;
    }

    #htmlBtnsLancamento(lancamento, pagamento) {
        const self = this;

        const tachado = self.#verificaLancamentoTachado(lancamento);
        const pagamentoAtivo = self.#verificaPagamentoAtivo(pagamento);
        const personalizavel = self.#verificaPagamentoTipoComLancamentosPersonalizaveis();
        const nameBtnEdit = pagamento?.id ? 'btn-edit' : 'btn-edit-object';
        const disabledTachado = personalizavel && pagamentoAtivo && !tachado ? '' : 'disabled';
        const semprePersonalizavel = self.#verificaPagamentoTipoSemprePersonalizaveis();

        let htmlBtns = `<li>
            <button type="button" class="dropdown-item fs-6 ${nameBtnEdit} ${disabledTachado}" title="Editar Lançamento ${lancamento.descricao_automatica}">Editar</button>
        </li>`;

        if (semprePersonalizavel & !disabledTachado && pagamentoAtivo && lancamento?.id) {
            htmlBtns += `<li>
                <button type="button" class="dropdown-item fs-6 btn-danger btn-delete-lancamento ${disabledTachado}" title="Excluir Lançamento ${lancamento.descricao_automatica}">Excluir</button>
            </li>`;
        }

        return htmlBtns;
    }

    #htmlColsLancamento(lancamento) {

        let htmlColsLancamento = `
            <div class="col">
                <div class="form-text mt-0">Status</div>
                <p>${lancamento.status.nome}</p>
            </div>`;

        const title_forma_pagamento = lancamento.forma_pagamento?.nome ?? 'Forma de Pagamento Padrão do Pagamento';
        const nome_forma_pagamento = lancamento.forma_pagamento?.nome ?? `<i>${title_forma_pagamento}</i>`;
        htmlColsLancamento += `
            <div class="col">
                <div class="form-text mt-0">Forma de Pagamento</div>
                <p class="text-truncate" title="${title_forma_pagamento}">
                    ${nome_forma_pagamento}
                </p>
            </div>`;

        const valor_esperado = CommonFunctions.formatNumberToCurrency(lancamento.valor_esperado); htmlColsLancamento += `
            <div class="col">
                <div class="form-text mt-0">Valor Esperado</div>
                <p>${valor_esperado}</p>
            </div>`;

        const data_vencimento = DateTimeHelper.retornaDadosDataHora(lancamento.data_vencimento, 2);
        htmlColsLancamento += `
            <div class="col">
                <div class="form-text mt-0">Data de vencimento</div>
                <p>${data_vencimento}</p>
            </div>`;

        if (lancamento.valor_recebido) {
            const valor_recebido = CommonFunctions.formatNumberToCurrency(lancamento.valor_recebido);
            htmlColsLancamento += `
                <div class="col">
                    <div class="form-text mt-0">Valor Recebido</div>
                    <p>${valor_recebido}</p>
                </div> `;
        }

        if (lancamento.data_recebimento) {
            const data_recebimento = DateTimeHelper.retornaDadosDataHora(lancamento.data_recebimento, 2);
            htmlColsLancamento += `
                <div class="col">
                    <div class="form-text mt-0">Data recebimento</div>
                    <p>${data_recebimento}</p>
                </div> `;
        }

        return htmlColsLancamento;
    }

    #htmlAppendLancamento(lancamento) {

        let htmlAppend = '';

        if (lancamento.observacao) {
            htmlAppend += `
                <div class="row">
                    <div class="col">
                        <label class="form-text">Observação</label>
                        <p class="mb-0 text-truncate lancamento-observacao" title="${lancamento.observacao}">
                            ${lancamento.observacao}
                        </p>
                    </div>
                </div>`;
        }

        return htmlAppend;
    }

    #getIndexLancamento(lancamento, options = {}) {
        const self = this;
        const lancamentoIndex = self._objConfigs.data.lancamentos_na_tela.findIndex(lanc => lanc.idCol == lancamento.idCol);
        const exibirNotificacao = options.exibirNotificacao ?? true;
        if (lancamentoIndex == -1) {
            const message = "Lançamento não encontrado na tela.";
            if (exibirNotificacao) {
                CommonFunctions.generateNotification(message, 'error');
            }
            console.error(message, lancamento, self._objConfigs.data.lancamentos_na_tela);
            return false;
        }
        return lancamentoIndex;
    }

    async #addEventosLancamentos(lancamento) {
        const self = this;
        const personalizavel = self.#verificaPagamentoTipoComLancamentosPersonalizaveis();

        if (personalizavel) {
            $(`#${lancamento.idCol}`).find('.btn-edit').on('click', async function () {
                const btn = $(this);
                CommonFunctions.simulateLoading(btn);
                try {
                    const objModal = new ModalServicoPagamentoLancamento({ urlApi: self._objConfigs.url.baseLancamentos });
                    objModal.setDataEnvModal = { idRegister: lancamento.id, };
                    await self._modalHideShow(false);
                    const response = await objModal.modalOpen();
                    if (response.refresh && response.register) {
                        self.#buscarLancamentos()
                    }
                } catch (error) {
                    CommonFunctions.generateNotificationErrorCatch(error);
                } finally {
                    await self._modalHideShow();
                    CommonFunctions.simulateLoading(btn, false);
                }
            });

            $(`#${lancamento.idCol}`).find('.btn-edit-object').on('click', async function () {
                if (!self._objConfigs.data.personalizar_lancamentos_bln) return;

                const btn = $(this);
                CommonFunctions.simulateLoading(btn);
                try {
                    const lancamentoIndex = self.#getIndexLancamento(lancamento);
                    if (lancamentoIndex === false) {
                        return false;
                    }
                    const register = self._objConfigs.data.lancamentos_na_tela[lancamentoIndex];

                    const objModal = new ModalServicoPagamentoLancamento();
                    objModal.setDataEnvModal = { register };
                    await self._modalHideShow(false);
                    const response = await objModal.modalOpen();
                    if (response.refresh && response.register) {
                        await self.#updateLancamento(response.register)
                    }
                } catch (error) {
                    CommonFunctions.generateNotificationErrorCatch(error);
                } finally {
                    await self._modalHideShow();
                    CommonFunctions.simulateLoading(btn, false);
                }
            });

            $(`#${lancamento.idCol}`).find('.btn-delete-lancamento').on('click', async function () {

                if (!lancamento?.id) return;

                const btn = $(this);
                CommonFunctions.simulateLoading(btn);
                let blnModalLoading = false;
                try {
                    const lancamentoIndex = self.#getIndexLancamento(lancamento);
                    if (lancamentoIndex === false) {
                        CommonFunctions.generateNotification('Lançamento não encontrado na tela. Se o problema persistir, contate o desenvolvedor.', 'error');
                        console.error({ lancamento }, { 'lancamentos_na_tela': self._objConfigs.data.lancamentos_na_tela });
                        return false;
                    }

                    const objMessage = new ModalMessage();
                    objMessage.setDataEnvModal = {
                        title: 'Exlclusão de Lançamento',
                        message: `<p>Confirma a exclusão do lançamento <b>${lancamento.descricao_automatica}</b>?</p>`,
                    };
                    self._modalHideShow(false);
                    const result = await objMessage.modalOpen();

                    if (!result.confirmResult) return;

                    blnModalLoading = await CommonFunctions.loadingModalDisplay(true, { message: 'Excluindo registro...', title: 'Aguarde...' });
                    if (await self._delRecurse(lancamento.id, {
                        url: self._objConfigs.url.baseLancamentos,
                    })) {
                        CommonFunctions.generateNotification('Lançamento excluído com sucesso!', 'success');

                        self._objConfigs.data.lancamentos_na_tela = self._objConfigs.data.lancamentos_na_tela.filter(l => l.id !== lancamento.id);

                        // Remove da tela o HTML correspondente
                        $(`#${lancamento.idCol}`).remove();

                        self._promisseReturnValue.refresh = true;
                    }

                } catch (error) {
                    CommonFunctions.generateNotificationErrorCatch(error);
                } finally {
                    if (blnModalLoading) await CommonFunctions.loadingModalDisplay(false);
                    await self._modalHideShow();
                    CommonFunctions.simulateLoading(btn, false);
                }
            });
        }
    }

    async #updateLancamento(lancamento) {
        const self = this;

        const lancamentoIndex = self.#getIndexLancamento(lancamento);
        if (lancamentoIndex === false) {
            return false;
        }

        // Marca que houve alteração personalizada
        self._objConfigs.data.personalizar_lancamentos_updated_bln = true;

        const lancamentoNaTela = self._objConfigs.data.lancamentos_na_tela[lancamentoIndex];

        // Clona forma_pagamento original antes de sobrescrever com deepMerge
        const formaPagamentoOriginal = lancamentoNaTela.forma_pagamento;

        CommonFunctions.deepMergeObject(lancamentoNaTela, lancamento);

        // Se for nulo, vazio ou 0, limpa a forma de pagamento
        if (!UUIDHelper.isValidUUID(lancamentoNaTela.forma_pagamento_id)) {
            delete lancamentoNaTela.forma_pagamento;
            lancamentoNaTela.forma_pagamento_id = null;
        } else {

            // Se o ID de forma de pagamento for diferente do original (ou não havia original), busca
            if (!formaPagamentoOriginal || formaPagamentoOriginal.id !== lancamentoNaTela.forma_pagamento_id) {
                lancamentoNaTela.forma_pagamento = await self.#buscarFormaPagamentoPorId(lancamentoNaTela.forma_pagamento_id);
            }
        }

        // Atualiza visual
        const colLancamento = $(`#${lancamentoNaTela.idCol}`);
        colLancamento.find('.lancamento-descricao-automatica').html(lancamentoNaTela.descricao_automatica);
        colLancamento.find('.html-cols-lancamento').html(self.#htmlColsLancamento(lancamentoNaTela));
        colLancamento.find('.html-append-lancamento').html(self.#htmlAppendLancamento(lancamentoNaTela));

        await self.#validarSomaLancamentosComValorTotal();
    }

    #mensagemValidacaoSomaLancamentos() {
        const self = this;

        if (!window.Statics.PagamentoTipoComConferenciaDeValorTotal.includes(self._objConfigs.data.pagamento_tipo_tenant.pagamento_tipo.id)) return '';
        const lancamentos = self._objConfigs.data.lancamentos_na_tela || [];

        const somaLancamentos = lancamentos.reduce((acc, l) => acc + parseFloat(l.valor_esperado || 0), 0);

        const valorTotal = CommonFunctions.removeCommasFromCurrencyOrFraction(
            $(`${self.getIdModal} input[name="valor_total"]`).val() ?? 0
        );

        const diferenca = somaLancamentos - valorTotal;

        const formatado = (v) => CommonFunctions.formatWithCurrencyCommasOrFraction(v, { decimalPlaces: 2 });

        if (diferenca > 0) {
            return `A soma dos lançamentos (<strong>R$ ${formatado(somaLancamentos)}</strong>) ultrapassa o valor total (<strong>R$ ${formatado(valorTotal)}</strong>) em <strong>R$ ${formatado(diferenca)}</strong>.`;
        } else if (diferenca < 0) {
            return `A soma dos lançamentos (<strong>R$ ${formatado(somaLancamentos)}</strong>) está abaixo do valor total (<strong>R$ ${formatado(valorTotal)}</strong>) em <strong>R$ ${formatado(Math.abs(diferenca))}</strong>.`;
        }

        return ''; // Tudo certo
    }

    async #validarSomaLancamentosComValorTotal() {
        const self = this;
        const mensagem = self.#mensagemValidacaoSomaLancamentos();
        self.#conteudoDivAlertMessage(mensagem);
    }

    async #buscarFormaPagamentoPorId(id) {
        const self = this;
        const objConn = new ConnectAjax(self._objConfigs.url.baseFormaPagamento);
        objConn.setParam(id);
        const response = await objConn.getRequest();
        return response.data;
    }

    async #buscarDadosPagamentoTipo(modo_editar_bln = false) {
        const self = this;
        try {
            const objConn = new ConnectAjax(self._objConfigs.url.basePagamentoTipoTenants);
            objConn.setParam(self._dataEnvModal.pagamento_tipo_tenant_id);
            objConn.setData({ modo_editar_bln: modo_editar_bln })
            objConn.setAction(EnumAction.POST);
            const response = await objConn.envRequest();

            self._objConfigs.data.pagamento_tipo_tenant = response.data;
            if (!self._dataEnvModal?.idRegister) self._updateModalTitle(`${response.data.nome}`);
            $(self.getIdModal).find('.campos-personalizados').html(response.data.campos_html);
            self.#addEventosCamposPersonalizados();
            const pagamentoTipo = self._objConfigs.data.pagamento_tipo_tenant.pagamento_tipo;

            if (pagamentoTipo.id == window.Enums.PagamentoTipoEnum.CONDICIONADO) {
                $(self.getIdModal).find('.elements-pane-lancamentos').css('display', 'none');
            }
        } catch (error) {
            throw error;
        }
    }

    #addEventosCamposPersonalizados() {
        const self = this;
        const modal = $(self.getIdModal);

        CommonFunctions.applyCustomNumberMask(modal.find('.campos-personalizados .campo-monetario'), { format: '#.##0,00', reverse: true });

        modal.find('.campos-personalizados .campo-dia-mes').mask('00', {
            onKeyPress: function (value, event, currentField) {
                const regex = /^(0?[1-9]|[12][0-9]|3[01])$/;
                if (!regex.test(value)) {
                    currentField.val('');
                }
            }
        });

        CommonFunctions.applyCustomNumberMask(modal.find('.campos-personalizados .campo-numero'), { format: '#.##0', reverse: true });

        const pagamentoTipo = self._objConfigs.data.pagamento_tipo_tenant.pagamento_tipo;
        if (pagamentoTipo.id == window.Enums.PagamentoTipoEnum.RECORRENTE) {
            self.#gerarCronExpressao();

            // aqui vai a ação dos elementos inputs e selects do cron
            modal.find('.dadosCron .inputCron').on('change', function () {
                self.#gerarCronExpressao();
            });
        }
    }

    async #buscarFormaPagamento(selected_id = null) {
        try {
            const self = this;
            let options = { outInstanceParentBln: true };
            selected_id ? options.selectedIdOption = selected_id : null;
            const select = $(`#forma_pagamento_id${self.getSufixo}`);
            await CommonFunctions.fillSelect(select, self._objConfigs.url.baseFormaPagamento, options);
            return true;
        } catch (error) {
            return false;
        }
    }

    async #buscarStatusPagamento(selected_id = null) {
        try {
            const self = this;
            const arrayOpcoes = self._dataEnvModal.idRegister ?
                window.Statics.StatusParaPagamentosServicosExistentes :
                window.Statics.StatusParaNovosPagamentosServicos;
            let options = { insertFirstOption: false };
            selected_id ? options.selectedIdOption = selected_id : null;
            const select = $(`#status_id${self.getSufixo}`);
            await CommonFunctions.fillSelectArray(select, arrayOpcoes, options);
            return true;
        } catch (error) {
            return false;
        }
    }

    async #buscarDados() {
        const self = this;
        self.#limparLancamentosNaTela();

        try {
            self._clearForm();
            self._action = EnumAction.PUT;
            const response = await self._getRecurse();

            if (response?.data) {
                const responseData = response.data;

                const pagamentoTipoTenant = responseData.pagamento_tipo_tenant;
                const pagamentoTipo = pagamentoTipoTenant.pagamento_tipo;

                self._updateModalTitle(`Alterar: <b>${pagamentoTipoTenant.nome}</b>`);
                self._dataEnvModal.pagamento_tipo_tenant_id = pagamentoTipoTenant.id;

                await self.#buscarDadosPagamentoTipo(true);
                await self.#verificaLancamentos(responseData);
                await self.#verificaPersonalizarLancamentos();
                await self.#verificaLiquidadoMigracaoPagamentoSemprePersonalizaveis();

                const form = $(self.getIdModal).find('.formRegistration');
                await self.#buscarFormaPagamento(responseData.forma_pagamento_id);
                await self.#buscarStatusPagamento(responseData.status_id);

                const tipoCampos = [pagamentoTipo.campos_obrigatorios, pagamentoTipo.campos_opcionais ?? []];
                for (const tipoCampo of tipoCampos) {
                    for (const campo of tipoCampo) {

                        if (pagamentoTipo.id == window.Enums.PagamentoTipoEnum.RECORRENTE && campo.nome == 'cron_expressao') {
                            const arrCron = responseData.cron_expressao.split(' ');
                            if (arrCron.length !== 5 || (arrCron[2] === '*' && arrCron[3] === '*' && arrCron[4] === '*')) {
                                console.error('A expressão cron é inválida.', responseData.cron_expressao);
                            } else {
                                form.find('select[name="cronDay"]').val(arrCron[2]);
                                form.find('select[name="cronMonth"]').val(arrCron[3]);
                                form.find('select[name="cronWeekday"]').val(arrCron[4]);
                            }
                            self.#gerarCronExpressao();
                            form.find('input[name="cron_data_inicio"]').val(responseData.cron_data_inicio);
                            // form.find('input[name="cron_data_fim"]').val(responseData.cron_data_fim);

                        } else {
                            let rules = (campo.form_request_rule_helper ?? campo.form_request_rule).split('|').filter(Boolean);

                            let valor = responseData[campo.nome];
                            if (rules.find(rule => rule === 'numeric')) {
                                valor = CommonFunctions.formatWithCurrencyCommasOrFraction(valor);
                            }
                            form.find(`#${campo.nome}${self._objConfigs.sufixo}`).val(valor).trigger('input');
                        }
                    }
                }

                for (const lancamento of responseData.lancamentos) {
                    self.#inserirLancamento(lancamento, responseData);
                }
                form.find('input[name="observacao"]').val(responseData.observacao);
            }
        } catch (error) {
            CommonFunctions.generateNotificationErrorCatch(error);
        }
    }

    async #verificaLancamentos(responseData) {
        const self = this;
        let blnStatus = false;
        const pagamentoTipoNaoRecriaveis = self.#verificaPagamentoTipoNaoRecriaveis();

        // Se não for recriável, então nem ofere a opção de recriar
        if (!pagamentoTipoNaoRecriaveis) {

            // Verifica se há alguma movimentação vinculada a lançamentos
            const possuiMovimentacao = responseData.lancamentos.some(l => l?.movimentacao_conta?.length);

            // Se não houver, verifica se há movimentação com liquidado migração. Se houver, verifica se o domínio cancelar_liquidado_migracao_sistema_automatico_bln está ativo 
            if (possuiMovimentacao) {
                blnStatus = true;
            } else {
                const tenantData = await TenantDataHelper.getTenantData();

                // Verifica se há alguma movimentação com status LIQUIDADO_MIGRACAO_SISTEMA
                const possuiMovimentacaoLiquidadoMigracao = responseData.lancamentos.some(l =>
                    Number(l.status_id) === window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_MIGRACAO_SISTEMA
                );

                if (possuiMovimentacaoLiquidadoMigracao && !tenantData?.cancelar_liquidado_migracao_sistema_automatico_bln) {
                    blnStatus = true;
                };
            }
            self.#visibilidadeResetarLancamentos(!blnStatus);
        }
    }

    #visibilidadeResetarLancamentos(statusVisibilidade = false) {
        const self = this;
        const ckbResetar = $(`#resetar_pagamento_bln${self.getSufixo}`);

        if (statusVisibilidade) {
            $(self.getIdModal).find('.div-resetar-lancamentos').show();
            $(self.getIdModal).find('.btn-simular').hide();
            ckbResetar.prop('checked', false).attr('disabled', false);

            ckbResetar.off('change').on('change', () => {
                const statusChecked = ckbResetar.is(':checked');
                self._objConfigs.data.resetar_pagamento_bln = statusChecked;

                $(self.getIdModal).find('.btn-simular')[statusChecked ? 'show' : 'hide']();

                self.#aplicarStatusEdicaoCamposCamposReadonly(statusChecked);

                // Se for falso o reset, então já se aplica falso na visibilidade do liquidado migração, pois não editará os registros antigos, mesmo que o tenant tenha a opção de lancamento_liquidado_migracao_sistema_bln ativa.
                statusChecked ? self.#verificaLiquidadoMigracao() : self.#visibilidadeLiquidadoMigracao();

                // Se for falso o reset, então já se aplica falso na visibilidade dos lançamentos personalizados, pois não editará os registros antigos.
                statusChecked ? self.#verificaPersonalizarLancamentos() : self.#visibilidadePersonalizarLancamentos();

                // Se for true, então busca uma simulação com os dados que estão na tela, para liberar a personalização. Caso contrário, busca os lançamentos já cadastrados.
                statusChecked ? self.#simularPagamento() : self.#buscarLancamentos()
            });

        } else {
            $(self.getIdModal).find('.btn-simular, .div-resetar-lancamentos').hide();
            ckbResetar.prop('checked', false).attr('disabled', true).off('change');
            self._objConfigs.data.resetar_pagamento_bln = false;
        }
    }

    #aplicarStatusEdicaoCamposCamposReadonly(statusEditable = true) {
        const self = this;
        $(`${self.getIdModal} .campo-readonly`).prop('readonly', !statusEditable);
        $(`${self.getIdModal} .campo-readonly-disabled`).prop('disabled', !statusEditable);
    }

    async #verificaLiquidadoMigracaoPagamentoSemprePersonalizaveis() {
        const self = this;
        const pagamentoTipoSemprePersonalizaveis = self.#verificaPagamentoTipoSemprePersonalizaveis();
        if (pagamentoTipoSemprePersonalizaveis) await self.#verificaLiquidadoMigracao();
    }

    /**
     * Verifica se o tenant tem a opção de lancamento_liquidado_migracao_sistema_bln ativa e define a visibilidade da opção de liquidado migração. Esta execução deve ser realizada depois do buscarDadosPagamentoTipo, pois verifica também o tipo do pagamento.
     */
    async #verificaLiquidadoMigracao() {
        const self = this;
        const tenantData = await TenantDataHelper.getTenantData();
        const permiteLiquidadoMigracao = window.Statics.PagamentoTipoQuePermiteLiquidadoMigracao.includes(self._objConfigs.data.pagamento_tipo_tenant.pagamento_tipo.id);

        self.#visibilidadeLiquidadoMigracao(tenantData?.lancamento_liquidado_migracao_sistema_bln && permiteLiquidadoMigracao);
    }

    #visibilidadeLiquidadoMigracao(statusVisibilidade = false) {
        const self = this;

        const ckbLiquidadoMigracao = $(`#liquidado_migracao_bln${self.getSufixo}`);

        if (statusVisibilidade) {
            $(self.getIdModal).find('.div-liquidado-migracao').show();

            ckbLiquidadoMigracao.prop('checked', false).attr('disabled', false);

            ckbLiquidadoMigracao.off('change').on('change', () => {
                const statusChecked = ckbLiquidadoMigracao.is(':checked');
                self._objConfigs.data.liquidado_migracao_bln = statusChecked;
            });
        } else {
            $(self.getIdModal).find('.div-liquidado-migracao').hide();
            ckbLiquidadoMigracao.prop('checked', false).attr('disabled', true).off('change');
            self._objConfigs.data.liquidado_migracao_bln = false;
        }
    }

    #verificaPagamentoTipoComLancamentosPersonalizaveis() {
        const self = this;
        return window.Statics.PagamentoTipoComLancamentosPersonalizaveis.includes(self._objConfigs.data.pagamento_tipo_tenant.pagamento_tipo.id);
    }

    #verificaPagamentoTipoSemprePersonalizaveis() {
        const self = this;
        return window.Statics.PagamentoTipoSemprePersonalizaveis.includes(self._objConfigs.data.pagamento_tipo_tenant.pagamento_tipo.id);
    }

    #verificaPagamentoTipoNaoRecriaveis() {
        const self = this;
        return window.Statics.PagamentoTipoNaoRecriaveis.includes(self._objConfigs.data.pagamento_tipo_tenant.pagamento_tipo.id);
    }

    #verificaPersonalizarLancamentos() {
        const self = this;
        const permitePersonalizar = self.#verificaPagamentoTipoComLancamentosPersonalizaveis();
        const pagamentoTipoSemprePersonalizaveis = self.#verificaPagamentoTipoSemprePersonalizaveis();

        if (pagamentoTipoSemprePersonalizaveis) {

            self.#aplicarStatusEdicaoCamposCamposReadonly();
            self._objConfigs.data.personalizar_lancamentos_bln = permitePersonalizar;
            self._objConfigs.data.personalizar_lancamentos_updated_bln = permitePersonalizar;
        } else {

            self.#visibilidadePersonalizarLancamentos(permitePersonalizar && (
                !self._dataEnvModal?.idRegister ||
                self._objConfigs.data.resetar_pagamento_bln
            ));
        }
    }

    #visibilidadePersonalizarLancamentos(statusVisibilidade = false) {
        const self = this;
        const ckbPersonalizarLancamentos = $(`#personalizar_lancamentos_bln${self.getSufixo}`);

        if (statusVisibilidade) {
            // Mostra a opção de personalizar lançamentos
            $(self.getIdModal).find('.div-personalizar-lancamentos').show();

            // Ativa o checkbox
            ckbPersonalizarLancamentos.prop('checked', false).attr('disabled', false);

            // Define evento de alteração
            ckbPersonalizarLancamentos.off('change').on('change', async () => {
                const statusChecked = ckbPersonalizarLancamentos.is(':checked');

                // Se o usuário tentar desmarcar após ter personalizado, alerta sobre a perda
                if (!statusChecked && self._objConfigs.data.personalizar_lancamentos_updated_bln) {

                    const objMessage = new ModalMessage();
                    objMessage.setDataEnvModal = {
                        title: 'Atenção',
                        message: `
                            <p>Existem lançamentos personalizados.</p>
                            <p>Desmarcar esta opção irá descartá-los e gerar uma nova simulação com valores e datas gerados pelo sistema.</p>
                            <p>Deseja continuar?</p>
                        `,
                    };
                    self._modalHideShow(false);
                    const result = await objMessage.modalOpen();
                    self._modalHideShow();

                    // Se o usuário cancelar, volta o checkbox para "true" e sai da função
                    if (!result.confirmResult) {
                        ckbPersonalizarLancamentos.prop('checked', true);
                        return;
                    } else {
                        await self.#simularPagamento();
                    }
                }

                self.#ativarBtnEditarLancamentos(statusChecked);

                // Atualiza os dados com o novo estado do checkbox
                self._objConfigs.data.personalizar_lancamentos_bln = statusChecked;
                self._objConfigs.data.personalizar_lancamentos_updated_bln = false;
            });

        } else {

            // Esconde a opção e reseta os dados
            $(self.getIdModal).find('.div-personalizar-lancamentos').hide();
            ckbPersonalizarLancamentos.prop('checked', false).attr('disabled', true).off('change');
            self._objConfigs.data.personalizar_lancamentos_bln = false;
            self._objConfigs.data.personalizar_lancamentos_updated_bln = false;
        }
    }

    #ativarBtnEditarLancamentos(status = true) {
        const self = this;
        $(self.getIdModal).find(`.row-lancamentos .btn-edit-object`).attr('disabled', !status);
    }

    async #buscarLancamentos() {
        const self = this;
        self.#limparLancamentosNaTela();

        try {
            const response = await self._getRecurse();
            if (response?.data) {
                const responseData = response.data;
                responseData.lancamentos.map(lancamento => {
                    self.#inserirLancamento(lancamento, responseData);
                })
            }
        } catch (error) {
            CommonFunctions.generateNotificationErrorCatch(error);
        }
    }

    /**
     * Gera a expressão cron com base nos valores selecionados nos inputs e atualiza a visualização.
     */
    #gerarCronExpressao() {
        const self = this;

        const dadosCron = $(self.getIdModal).find('.dadosCron');
        const day = dadosCron.find('select[name="cronDay"]').val() || '*';
        const month = dadosCron.find('select[name="cronMonth"]').val() || '*';
        const weekday = dadosCron.find('select[name="cronWeekday"]').val() || '*';
        // const minute = dadosCron.find('select[name="cronMinute"]').val() || '*';
        // const hour = dadosCron.find('select[name="cronHour"]').val() || '*';

        // Monta a expressão cron
        const cronExpressao = `* * ${day} ${month} ${weekday}`;

        if (cronExpressao === '* * * * *') {
            $(self.getIdModal).find(`#cronExpression${self.getSufixo}`).val('Selecione a recorrência');
        } else {
            if (!self.#validarExpressaoCron(cronExpressao)) {
                // Atualiza a expressão e tradução no DOM
                $(self.getIdModal).find(`#cronExpression${self.getSufixo}`).val('Valores inválidos');
                self._objConfigs.data.cronExpressao = undefined;
                return false;
            }
        }

        self._objConfigs.data.cronExpressao = cronExpressao;

        // Exemplo de uso
        const cronParts = {
            day: day,
            month: month,
            weekday: weekday,
            hour: '*',
            minute: '*'
        };

        const traducao = self.#gerarTraducaoCron(cronParts);
        $(self.getIdModal).find(`#cronExpression${self.getSufixo}`).val(traducao);
    }

    #gerarTraducaoCron(cronParts) {
        const self = this;

        const dadosCron = $(self.getIdModal).find('.dadosCron');
        let traducao = [];

        if (cronParts.day !== '*') {
            traducao.push(`Todo dia ${cronParts.day}`);
        }
        if (cronParts.month !== '*') {
            if (!traducao.length) {
                traducao.push('Todo');
            } else {
                traducao.push('do');
            }
            traducao.push(`mês de ${dadosCron.find(`select[name="cronMonth"] option[value="${cronParts.month}"]`).text().trim()}`);
        }

        if (cronParts.weekday !== '*') {
            if (!traducao.length) {
                traducao.push('Todas(os) as(os)');
            } else {
                traducao.push('nas(os)');
            }
            traducao.push(`${dadosCron.find(`select[name="cronWeekday"] option[value="${cronParts.weekday}"]`).text().trim()}`);
        }

        if (traducao.length === 0) {
            traducao.push('Selecione a recorrência');
        }

        // Retorna a tradução como uma string formatada
        return traducao.join(' ').trim();
    }

    /**
     * Valida uma expressão cron com base no formato padrão (* * * * *).
     * @param {string} expressao - A expressão cron a ser validada.
     * @returns {boolean} Retorna `true` se a expressão for válida, caso contrário `false`.
     */
    #validarExpressaoCron(expressao) {
        const regex = /^(\*|([0-5]?\d)) (\*|([01]?\d|2[0-3])) (\*|([1-9]|[12]\d|3[01])) (\*|([1-9]|1[0-2])) (\*|[0-7])$/;

        if (regex.test(expressao)) {
            return true;
        } else {
            return false;
        }
    }

    saveButtonAction() {
        const self = this;
        const data = self.#obterDados();
        data.pagamento_tipo_tenant_id = self._objConfigs.data.pagamento_tipo_tenant.id;
        data.resetar_pagamento_bln = self._objConfigs.data.resetar_pagamento_bln;
        data.liquidado_migracao_bln = self._objConfigs.data.liquidado_migracao_bln;

        const verificacao = self.#saveVerifications(data);
        if (verificacao) {
            self._save(data, self._objConfigs.url.base);
        }
    }

    #obterDados() {
        const self = this;
        const formRegistration = $(self.getIdModal).find('.formRegistration');
        let data = CommonFunctions.getInputsValues(formRegistration[0]);
        const pagamentoTipo = self._objConfigs.data.pagamento_tipo_tenant.pagamento_tipo;

        if (pagamentoTipo.id == window.Enums.PagamentoTipoEnum.RECORRENTE) {
            data.cron_expressao = self._objConfigs.data.cronExpressao;
        }

        return data;
    }

    #getLancamentosNaTelaParaEnvio() {
        const self = this;
        return self._objConfigs.data.lancamentos_na_tela.map(lancamento => {
            return ValidatorHelper.validarItem(lancamento, window.Statics.PagamentoTipoCamposLancamentosPersonalizados);
        });
    }

    #saveVerifications(data, tipo = 'save') {
        const self = this;
        const formRegistration = $(self.getIdModal).find('.formRegistration');
        const pagamentoTipo = self._objConfigs.data.pagamento_tipo_tenant.pagamento_tipo;
        const PagamentoTipoSemprePersonalizaveis = window.Statics.PagamentoTipoSemprePersonalizaveis;
        let blnSave = false;

        if (self._action == EnumAction.POST || (
            self._action == EnumAction.PUT && (
                tipo == 'save' ||
                (tipo == 'simulacao' &&
                    (self._objConfigs.data?.resetar_pagamento_bln || PagamentoTipoSemprePersonalizaveis))
            )
        )) {

            blnSave = CommonFunctions.verificationData(data.forma_pagamento_id, {
                field: formRegistration.find('select[name="forma_pagamento_id"]'),
                messageInvalid: 'A <b>Forma de Pagamento padrão</b> deve ser informada.',
                setFocus: true
            });

            // if (self._action == EnumAction.POST) {
            for (const campo of pagamentoTipo.campos_obrigatorios) {
                let rules = '';

                if (tipo == 'simulacao') {
                    rules = campo.form_request_rule_helper ?? campo.form_request_rule;
                } else {
                    rules = campo?.form_request_rule ?? '';
                }
                rules = rules.split('|').filter(Boolean);

                if (!rules.length && tipo == 'simulacao') {
                    CommonFunctions.generateNotification(`O campo <b>${campo.nome_exibir}</b> deve possuir regras de validação. Se o problema persistir, contate o desenvolvedor.`, 'warning');
                    continue;
                } else if (!rules.length) {
                    continue;
                }

                const nullable = rules.find(rule => rule === 'nullable');

                if (rules.find(rule => rule === 'numeric' || rule === 'integer')) {
                    data[campo.nome] = CommonFunctions.removeCommasFromCurrencyOrFraction(data[campo.nome]);
                }

                if (pagamentoTipo.id == window.Enums.PagamentoTipoEnum.RECORRENTE && campo.nome == 'cron_expressao') {
                    if (data[campo.nome] == '* * * * *') {
                        CommonFunctions.generateNotification('A <b>Recorrência</b> deve ser informada.', 'warning');
                        blnSave = false;
                    }
                } else if (!nullable) {
                    blnSave = CommonFunctions.verificationData(data[campo.nome], {
                        field: formRegistration.find(`#${campo.nome}${self._objConfigs.sufixo}`),
                        messageInvalid: `O campo <b>${campo.nome_exibir}</b> deve ser informado.`,
                        setFocus: blnSave === true,
                        returnForcedFalse: blnSave === false
                    });
                }
            }

            if (tipo == 'save' &&
                self._objConfigs.data.personalizar_lancamentos_bln
                // &&
                // self._objConfigs.data.personalizar_lancamentos_updated_bln
            ) {

                if (!self._objConfigs.data.personalizar_lancamentos_updated_bln) {

                    CommonFunctions.generateNotification('A personalização dos lançamentos foi ativada, porém nenhuma alteração foi realizada. Revise a personalização dos lançamentos ou desative a opção.', 'warning');
                    blnSave = false;

                } else if (self.#camposVerificarSnapshotWatcherHelper.hasChanged()) {

                    CommonFunctions.generateNotification('Foi detectado alterações nos campos do pagamento. É necessário simular novamente para continuar ou desfazer as alterações antes de salvar.', 'warning');
                    blnSave = false;

                } else {

                    const lancamentosFiltrados = self.#getLancamentosNaTelaParaEnvio();
                    for (const lancamento of lancamentosFiltrados) {
                        if (lancamento.errors.length) {
                            blnSave = false;

                            CommonFunctions.generateNotification(`O lançamento <b>${lancamento.filtrados.descricao_automatica}</b> possui pendências para serem corrigidas.`, 'warning', {
                                itemsArray: lancamento.errors,
                            });
                            console.error(lancamento);
                        }
                    }

                    const mensagemValidaSoma = this.#mensagemValidacaoSomaLancamentos();
                    if (mensagemValidaSoma) {
                        blnSave = false;
                        this.#conteudoDivAlertMessage(mensagemValidaSoma);
                        CommonFunctions.generateNotification(mensagemValidaSoma, 'warning');
                    }

                    data.lancamentos = lancamentosFiltrados.map(lancamento => lancamento.filtrados);
                    data.personalizar_lancamentos_bln = true;
                }

            }
        }

        return blnSave;
    }

}
