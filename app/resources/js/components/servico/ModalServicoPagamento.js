import { CommonFunctions } from "../../commons/CommonFunctions";
import { ConnectAjax } from "../../commons/ConnectAjax";
import { EnumAction } from "../../commons/EnumAction";
import { ModalRegistrationAndEditing } from "../../commons/modal/ModalRegistrationAndEditing";
import { DateTimeHelper } from "../../helpers/DateTimeHelper";
import { URLHelper } from "../../helpers/URLHelper";
import { UUIDHelper } from "../../helpers/UUIDHelper";
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

    constructor(options = {}) {
        super({
            idModal: "#ModalServicoPagamento",
        });

        this._objConfigs = Object.assign(this._objConfigs, this.#objConfigs);
        this._promisseReturnValue = Object.assign(this._promisseReturnValue, this.#promisseReturnValue);
        this._dataEnvModal = Object.assign(this._dataEnvModal, this.#dataEnvModal);
        this._objConfigs.url.base = options.urlApi;
        this._action = EnumAction.POST;

        this.#addEventosPadrao();
    }

    async modalOpen() {
        const self = this;

        self._queueCheckDomainCustom.setReady();
        await CommonFunctions.loadingModalDisplay(true, { message: 'Carregando informações do pagamento...' });

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
            } else {
                await self.#buscarDadosPagamentoTipo();
            }

            self.#buscarFormaPagamento();
            self.#buscarStatusPagamento();
        }

        await CommonFunctions.loadingModalDisplay(false);
        await self._modalHideShow();
        return await self._modalOpen();
    }

    #addEventosPadrao() {
        const self = this;
        const modal = $(self._idModal);

        modal.find('.openModalFormaPagamento').on('click', async function () {
            const btn = $(this);
            CommonFunctions.simulateLoading(btn);
            try {
                const objModal = new ModalFormaPagamentoTenant();
                objModal.setDataEnvModal = {
                    attributes: {
                        select: {
                            quantity: 1,
                            autoReturn: true,
                        }
                    }
                }
                await self._modalHideShow(false);
                const response = await objModal.modalOpen();
                if (response.refresh) {
                    if (response.selected) {
                        self.#buscarFormaPagamento(response.selected.id);
                    } else {
                        self.#buscarFormaPagamento();
                    }
                }
            } catch (error) {
                CommonFunctions.generateNotificationErrorCatch(error);
            } finally {
                CommonFunctions.simulateLoading(btn, false);
                await self._modalHideShow();
            }
        });

        modal.find('.btn-simular').on('click', async function () {
            CommonFunctions.simulateLoading($(this));
            try {
                await self.#simularPagamento();
            } finally {
                CommonFunctions.simulateLoading($(this), false);
            }
        });
    }

    _modalReset() {
        super._modalReset();
        const self = this;
        $(self.getIdModal).find(`#dados-pagamento${self._objConfigs.sufixo}-tab`).trigger('click');
        $(self.getIdModal).find('.elements-pane-lancamentos').show();
    }

    async #simularPagamento() {
        const self = this;
        const rowLancamentos = $(self.getIdModal).find('.row-lancamentos');
        rowLancamentos.html('');

        const data = self.#obterDados();

        if (!self.#saveVerifications(data, 'simulacao')) {
            return;
        }

        const response = await self.#buscarSimulacao(data);

        if (response?.data) {
            for (const lancamento of response.data.lancamentos) {
                const inserido = await self.#inserirLancamentos(lancamento, { status_id: 1 });
            }
            CommonFunctions.generateNotification('Simulação de pagamento concluída.', 'success');
            $(self.getIdModal).find(`#lancamentos${self._objConfigs.sufixo}-tab`).trigger('click');
        }
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

    async #inserirLancamentos(lancamento, pagamento) {
        const self = this;
        const rowLancamentos = $(self.getIdModal).find('.row-lancamentos');
        const data_vencimento = DateTimeHelper.retornaDadosDataHora(lancamento.data_vencimento, 2);
        const valor_esperado = CommonFunctions.formatWithCurrencyCommasOrFraction(lancamento.valor_esperado);
        const title_forma_pagamento = lancamento.forma_pagamento?.nome ?? 'Forma de Pagamento Padrão do Pagamento';
        const nome_forma_pagamento = lancamento.forma_pagamento?.nome ?? `<i>${title_forma_pagamento}</i>`;

        let htmlAppend = '';
        let btns = '';
        lancamento.idCard = `${UUIDHelper.generateUUID()}${self._objConfigs.sufixo}`;
        const pagamentoAtivo = pagamento.status_id == window.Enums.PagamentoStatusTipoEnum.ATIVO ? true : false;
        const tachado = (window.Statics.StatusLancamentoTachado.findIndex(x => x == lancamento.status_id) != -1);

        if (lancamento.pagamento_id) {
            btns = `
            <li><button type="button" class="dropdown-item fs-6 btn-participacao-lancamento btn-edit ${pagamentoAtivo && !tachado ? '' : 'disabled'}" title="Editar Lançamento ${lancamento.descricao_automatica}">Editar</button></li>`;

            if (lancamento.observacao) {
                const observacao = lancamento.observacao ?? '';
                htmlAppend = `
                <div class="row">
                    <div class="col">
                        <label class="form-text">Observação (opcional)</label>
                        <p class="mb-0 text-truncate observacao-parcela" title="${observacao}">
                            ${observacao}
                        </p>
                    </div>
                </div>`;
            }
        }

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

        rowLancamentos.append(`
            <div id="${lancamento.idCard}" class="card p-0 ${tachado ? 'fst-italic text-secondary-emphasis text-decoration-line-through' : ''}">
                <div class="card-header d-flex align-items-center justify-content-between py-1">
                    <span>${lancamento.descricao_automatica}</span>
                    ${btnsDropDown}
                </div>
                <div class="card-body">
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 align-items-end">
                        <div class="col">
                            <div class="form-text mt-0">Data de vencimento</div>
                            <p class="mb-0">${data_vencimento}</p>
                        </div>
                        <div class="col">
                            <div class="form-text mt-0">Valor</div>
                            <p class="mb-0">${valor_esperado}</p>
                        </div>
                        <div class="col">
                            <div class="form-text mt-0">Status</div>
                            <p class="mb-0">${lancamento.status.nome}</p>
                        </div>
                        <div class="col">
                            <div class="form-text mt-0">Forma de pagamento</div>
                            <p class="mb-0 text-truncate" title="${title_forma_pagamento}">
                                ${nome_forma_pagamento}
                            </p>
                        </div>
                    </div>
                    ${htmlAppend}
                </div>
            </div>`);

        self.#addEventosLancamentos(lancamento);
        return lancamento;
    }

    async #addEventosLancamentos(lancamento) {
        const self = this;

        $(`#${lancamento.idCard}`).find('.btn-edit').on('click', async function () {
            const btn = $(this);
            CommonFunctions.simulateLoading(btn);
            try {
                const objModal = new ModalServicoPagamentoLancamento({ urlApi: self._objConfigs.url.baseLancamentos });
                objModal.setDataEnvModal = {
                    idRegister: lancamento.id,
                }
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
            self._updateModalTitle(`${response.data.nome}`);
            $(self.getIdModal).find('.campos-personalizados').html(response.data.campos_html);
            self.#addEventosCamposPersonalizados();
            const pagamentoTipo = self._objConfigs.data.pagamento_tipo_tenant.pagamento_tipo;

            if (pagamentoTipo.id == window.Enums.PagamentoTipoEnum.CONDICIONADO) {
                $(self.getIdModal).find('.elements-pane-lancamentos').hide('fast');
            }

        } catch (error) {
            CommonFunctions.generateNotificationErrorCatch(error);
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
            let options = {
                outInstanceParentBln: true,
            };
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
            const arrayOpcoes = window.Details.PagamentoStatusTipoEnum;
            let options = {
                insertFirstOption: false,
            };
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
        $(self.getIdModal).find('.row-lancamentos').html('');

        try {
            self._clearForm();
            $(self.getIdModal).find('.btn-simular').hide();
            self._action = EnumAction.PUT;
            const response = await self._getRecurse();
            if (response?.data) {
                const responseData = response.data;
                const pagamentoTipoTenant = responseData.pagamento_tipo_tenant;
                const pagamentoTipo = pagamentoTipoTenant.pagamento_tipo;

                self._updateModalTitle(`Alterar: <b>${pagamentoTipoTenant.nome}</b>`);
                self._dataEnvModal.pagamento_tipo_tenant_id = pagamentoTipoTenant.id;
                await self.#buscarDadosPagamentoTipo(true);

                const form = $(self.getIdModal).find('.formRegistration');
                self.#buscarFormaPagamento(responseData.forma_pagamento_id);
                self.#buscarStatusPagamento(responseData.status_id);

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

                            const rules = campo.form_request_rule.split('|');
                            let valor = responseData[campo.nome];
                            if (rules.find(rule => rule === 'numeric')) {
                                valor = CommonFunctions.formatWithCurrencyCommasOrFraction(valor);
                            }
                            form.find(`#${campo.nome}${self._objConfigs.sufixo}`).val(valor).trigger('input');
                        }
                    }
                }

                for (const lancamento of responseData.lancamentos) {
                    self.#inserirLancamentos(lancamento, responseData);
                }
                form.find('input[name="observacao"]').val(responseData.observacao);
            }
        } catch (error) {
            CommonFunctions.generateNotificationErrorCatch(error);
        }
    }

    async #buscarLancamentos() {
        const self = this;
        $(self.getIdModal).find('.row-lancamentos').html('');

        try {
            const response = await self._getRecurse();
            if (response?.data) {
                const responseData = response.data;
                responseData.lancamentos.map(lancamento => {
                    self.#inserirLancamentos(lancamento, lancamento.pagamento);
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

        if (self.#saveVerifications(data)) {
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

    #saveVerifications(data, tipo = 'save') {
        const self = this;
        const formRegistration = $(self.getIdModal).find('.formRegistration');
        const pagamentoTipo = self._objConfigs.data.pagamento_tipo_tenant.pagamento_tipo;
        let blnSave = false;

        if (self._action == EnumAction.POST || self._action == EnumAction.PUT && tipo == 'save') {

            blnSave = CommonFunctions.verificationData(data.forma_pagamento_id, { field: formRegistration.find('select[name="forma_pagamento_id"]'), messageInvalid: 'A <b>Forma de Pagamento padrão</b> deve ser informada.', setFocus: true });

            if (self._action == EnumAction.POST) {
                for (const campo of pagamentoTipo.campos_obrigatorios) {
                    const rules = campo.form_request_rule.split('|');
                    const nullable = rules.find(rule => rule === 'nullable');

                    if (rules.find(rule => rule === 'numeric' || rule === 'integer')) {
                        data[campo.nome] = CommonFunctions.removeCommasFromCurrencyOrFraction(data[campo.nome]);
                    }

                    if (pagamentoTipo.id == window.Enums.PagamentoTipoEnum.RECORRENTE && campo.nome == 'cron_expressao') {
                        if (data[campo.nome] == '* * * * *') {
                            CommonFunctions.generateNotification('A <b>Recorrência</b> deve ser informada.', 'warning');
                            blnSave = false;
                        }
                    } else {
                        blnSave = CommonFunctions.verificationData(data[campo.nome], {
                            field: formRegistration.find(`#${campo.nome}${self._objConfigs.sufixo}`),
                            messageInvalid: `O campo <b>${campo.nome_exibir}</b> deve ser informado.`,
                            setFocus: blnSave === true,
                            returnForcedFalse: blnSave === false
                        });
                    }
                }
            }
        }

        return blnSave;
    }

}
