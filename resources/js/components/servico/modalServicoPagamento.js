import { commonFunctions } from "../../commons/commonFunctions";
import { connectAjax } from "../../commons/connectAjax";
import { enumAction } from "../../commons/enumAction";
import { modalRegistrationAndEditing } from "../../commons/modal/modalRegistrationAndEditing";
import { DateTimeHelper } from "../../helpers/DateTimeHelper";
import { URLHelper } from "../../helpers/URLHelper";
import { UUIDHelper } from "../../helpers/UUIDHelper";
import { modalConta } from "../financeiro/modalConta";

export class modalServicoPagamento extends modalRegistrationAndEditing {

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
            basePagamentoTipoTenants: window.apiRoutes.basePagamentoTipoTenants,
            baseContas: window.apiRoutes.baseContas,
        },
        sufixo: 'ModalServicoPagamento',
        data: {
            pagamento_tipo_tenant: undefined,
            lancamentos_na_tela: [],
        },
    };

    /** 
     * Conteúdo a ser retornado na promisse como resolve()
    */
    #promisseReturnValue = {
        refresh: false,
    };

    constructor(urlApi) {
        super({
            idModal: "#modalServicoPagamento",
        });

        this._objConfigs = Object.assign(this._objConfigs, this.#objConfigs);
        this._promisseReturnValue = Object.assign(this._promisseReturnValue, this.#promisseReturnValue);
        this._dataEnvModal = Object.assign(this._dataEnvModal, this.#dataEnvModal);
        this._objConfigs.url.base = urlApi;
        this._action = enumAction.POST;

        this.#addEventosPadrao();
    }

    async modalOpen() {
        const self = this;
        await commonFunctions.loadingModalDisplay(true, { message: 'Carregando informações do pagamento...' });
        await this.#buscarContas();

        if (self._dataEnvModal.idRegister) {
            await self.#buscarDados()
        } else {
            if (!self._dataEnvModal.pagamento_tipo_tenant_id) {
                commonFunctions.generateNotification('Tipo de pagamento não informado', 'error');
                return await self._returnPromisseResolve();
            } else {
                await self.#buscarDadosPagamentoTipo();
            }
        }

        await commonFunctions.loadingModalDisplay(false);
        await self._modalHideShow();
        return await self._modalOpen();
    }

    #addEventosPadrao() {
        this.#eventosBotoes();
    }

    #eventosBotoes() {
        const self = this;
        const modal = $(self._idModal);

        modal.find('.openModalConta').on('click', async function () {
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
                await self._modalHideShow(false);
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
                await self._modalHideShow();
            }
        });

        modal.find('.btn-simular').on('click', async function () {
            commonFunctions.simulateLoading($(this));
            try {
                await self.#simularPagamento();
            } finally {
                commonFunctions.simulateLoading($(this), false);
            }
        });
    }

    _modalReset() {
        super._modalReset();
        const self = this;
        $(self.getIdModal).find(`#dados-pagamento${self._objConfigs.sufixo}-tab`).trigger('click');
        $(self.getIdModal).find('.btn-simular').show();
    }

    async #simularPagamento() {
        const self = this;
        const rowLancamentos = $(self.getIdModal).find('.row-lancamentos');

        rowLancamentos.html('');

        const data = self.#obterDados();

        if (!self.#saveVerifications(data, 'simulacao')) {
            return;
        }

        // let data = { "conta_id": "9d3b5146-a013-4544-ad6c-9a46587bac8d", "valor_total": 3000, "entrada_valor": 500, "entrada_data": "2024-10-18", "parcela_data_inicio": "2024-11-20", "parcela_quantidade": 7, "parcela_vencimento_dia": 10 };

        const response = await self.#buscarSimulacao(data);

        if (response?.data) {
            for (const lancamento of response.data.lancamentos) {
                const inserido = await self.#inserirLancamentos(lancamento);
            }
            commonFunctions.generateNotification('Simulação de pagamento concluída.', 'success');
            $(self.getIdModal).find(`#lancamentos${self._objConfigs.sufixo}-tab`).trigger('click');
        }
    }

    async #buscarSimulacao(data) {
        const self = this;
        const configuracao = self._objConfigs.data.pagamento_tipo_tenant.pagamento_tipo.configuracao;

        try {
            const objConn = new connectAjax(URLHelper.formatEndpointUrl(`${configuracao.helper.endpoint_api}/render`));
            objConn.setAction(enumAction.POST);
            objConn.setData(data);
            return await objConn.envRequest();
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
            return false;
        }
    }

    async #inserirLancamentos(lancamento) {
        const self = this;
        const rowLancamentos = $(self.getIdModal).find('.row-lancamentos');
        const data_vencimento = DateTimeHelper.retornaDadosDataHora(lancamento.data_vencimento, 2);
        const valor_esperado = commonFunctions.formatWithCurrencyCommasOrFraction(lancamento.valor_esperado);
        const title_conta = lancamento.conta?.nome ?? 'Conta Padrão do Pagamento';
        const nome_conta = lancamento.conta?.nome ?? `<i>${title_conta}</i>`;

        let htmlAppend = '';
        let btnEditar = '';
        lancamento.idCard = `${UUIDHelper.generateUUID()}${self._objConfigs.sufixo}`;

        if (lancamento.pagamento_id) {
            btnEditar = self.#htmlBtnEdit({ title: 'Editar este lançamento' });
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

        rowLancamentos.append(`
            <div id="${lancamento.idCard}" class="card p-0">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span>${lancamento.descricao_automatica}</span>
                    ${btnEditar}
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
                            <div class="form-text mt-0">Conta</div>
                            <p class="mb-0 text-truncate" title="${title_conta}">
                                ${nome_conta}
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
            commonFunctions.simulateLoading(btn);
            try {
                commonFunctions.generateNotification('Editando lancamento...', 'info');
                // const objModal = new modalServicoPagamento(self.#objConfigs.url.basePagamentos);
                // objModal.setDataEnvModal = {
                //     idRegister: item.id,
                // }
                // const response = await objModal.modalOpen();
                // console.log(response);
                // if (response.refresh && response.register) {
                //     // AtualizarRegistro
                // }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading(btn, false);
            }
        });
    }

    #htmlBtnEdit(options = {}) {
        const {
            title = 'Editar registro',
        } = options;
        return `<button type="button" class="btn btn-outline-primary btn-sm btn-edit border-0" title="${title}"><i class="bi bi-pencil"></i></button>`;
    }

    async #buscarDadosPagamentoTipo(modo_editar_bln = false) {
        const self = this;
        try {
            const objConn = new connectAjax(self._objConfigs.url.basePagamentoTipoTenants);
            objConn.setParam(self._dataEnvModal.pagamento_tipo_tenant_id);
            objConn.setData({ modo_editar_bln: modo_editar_bln })
            objConn.setAction(enumAction.POST);
            const response = await objConn.envRequest();

            self._objConfigs.data.pagamento_tipo_tenant = response.data;
            self._updateModalTitle(`${response.data.nome}`);
            $(self.getIdModal).find('.campos-personalizados').html(response.data.campos_html);
            self.#addEventosCamposPersonalizados();
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
        }
    }

    #addEventosCamposPersonalizados() {
        const self = this;
        const modal = $(self.getIdModal);

        commonFunctions.applyCustomNumberMask(modal.find('.campos-personalizados .campo-monetario'), { format: '#.##0,00', reverse: true });

        modal.find('.campos-personalizados .campo-dia-mes').mask('00', {
            onKeyPress: function (value, event, currentField) {
                const regex = /^(0?[1-9]|[12][0-9]|3[01])$/;
                if (!regex.test(value)) {
                    currentField.val('');
                }
            }
        });

        commonFunctions.applyCustomNumberMask(modal.find('.campos-personalizados .campo-numero'), { format: '#.##0', reverse: true });

    }

    async #buscarContas(selected_id = null) {
        const self = this;
        let options = selected_id ? { selectedIdOption: selected_id } : {};
        const selModulo = $(self.getIdModal).find('select[name="conta_id"]');
        await commonFunctions.fillSelect(selModulo, self._objConfigs.url.baseContas, options);
    }

    async #buscarDados() {
        const self = this;

        try {
            self._clearForm();
            $(self.getIdModal).find('.btn-simular').hide();
            self._action = enumAction.PUT;
            const response = await self._getRecurse();
            if (response?.data) {
                const responseData = response.data;
                const pagamentoTipoTenant = responseData.pagamento_tipo_tenant;
                const configuracao = pagamentoTipoTenant.pagamento_tipo.configuracao;

                self._updateModalTitle(`Alterar: <b>${pagamentoTipoTenant.nome}</b>`);
                self._dataEnvModal.pagamento_tipo_tenant_id = pagamentoTipoTenant.id;
                await self.#buscarDadosPagamentoTipo(true);

                const form = $(self.getIdModal).find('.formRegistration');
                form.find('select[name="conta_id"]').val(responseData.conta_id);
                for (const campo of configuracao.campos_obrigatorios) {
                    const rules = campo.formRequestRule.split('|');
                    let valor = responseData[campo.nome];
                    if (rules.find(rule => rule === 'numeric')) {
                        valor = commonFunctions.formatWithCurrencyCommasOrFraction(valor);
                    }
                    form.find(`#${campo.nome}${self._objConfigs.sufixo}`).val(valor).trigger('input');
                }

                for (const lancamento of responseData.lancamentos) {
                    self.#inserirLancamentos(lancamento);
                }
                form.find('input[name="observacao"]').val(responseData.observacao);
            }
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
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
        let data = commonFunctions.getInputsValues(formRegistration[0]);
        return data;
    }

    #saveVerifications(data, tipo = 'save') {
        const self = this;
        const formRegistration = $(self.getIdModal).find('.formRegistration');
        const configuracao = self._objConfigs.data.pagamento_tipo_tenant.pagamento_tipo.configuracao;
        let blnSave = false;

        if (self._action == enumAction.POST || self._action == enumAction.PUT && tipo == 'save') {

            blnSave = commonFunctions.verificationData(data.conta_id, { field: formRegistration.find('select[name="conta_id"]'), messageInvalid: 'A <b>Conta padrão</b> deve ser informada.', setFocus: true });

            if (self._action == enumAction.POST) {
                for (const campo of configuracao.campos_obrigatorios) {
                    const rules = campo.formRequestRule.split('|');
                    if (rules.find(rule => rule === 'numeric' || rule === 'integer')) {
                        data[campo.nome] = commonFunctions.removeCommasFromCurrencyOrFraction(data[campo.nome]);
                    }

                    blnSave = commonFunctions.verificationData(data[campo.nome], {
                        field: formRegistration.find(`#${campo.nome}${self._objConfigs.sufixo}`),
                        messageInvalid: `O campo <b>${campo.nome_exibir}</b> deve ser informado.`,
                        setFocus: blnSave === true,
                        returnForcedFalse: blnSave === false
                    });
                }
            }
        }

        return blnSave;
    }

}