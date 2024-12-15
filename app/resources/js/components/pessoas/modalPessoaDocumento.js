import { commonFunctions } from "../../commons/commonFunctions";
import { connectAjax } from "../../commons/connectAjax";
import { enumAction } from "../../commons/enumAction";
import { modalRegistrationAndEditing } from "../../commons/modal/modalRegistrationAndEditing";

export class modalPessoaDocumento extends modalRegistrationAndEditing {

    #dataEnvModal = {
        idRegister: undefined,
        documento_tipo_tenant_id: undefined
    }

    /**
     * Configuração local do modal
     */
    #objConfigs = {
        url: {
            baseDocumentoTipoTenants: window.apiRoutes.baseDocumentoTipoTenants,
        },
        sufixo: 'ModalPessoaDocumento',
        data: {
            documento_tipo_tenant: undefined,
        },
    };

    /** 
     * Conteúdo a ser retornado na promisse como resolve()
    */
    #promisseReturnValue = {
        refresh: false,
    };

    constructor(options = {}) {
        super({
            idModal: "#modalPessoaDocumento",
        });

        this._objConfigs = Object.assign(this._objConfigs, this.#objConfigs);
        this._promisseReturnValue = Object.assign(this._promisseReturnValue, this.#promisseReturnValue);
        this._dataEnvModal = Object.assign(this._dataEnvModal, this.#dataEnvModal);
        this._objConfigs.url.base = options.urlApi;
        this._action = enumAction.POST;

        this.#addEventosPadrao();
    }

    async modalOpen() {
        const self = this;
        await commonFunctions.loadingModalDisplay(true, { message: 'Carregando informações do documento...' });
        let blnOpen = false;

        if (self._dataEnvModal.idRegister) {
            self._objConfigs.url.baseLancamentos = `${self._objConfigs.url.base}/${self._dataEnvModal.idRegister}/lancamentos`;
            blnOpen = await self.#buscarDados()
        } else {
            if (!self._dataEnvModal.documento_tipo_tenant_id) {
                commonFunctions.generateNotification('Tipo de documento não informado', 'error');
                return await self._returnPromisseResolve();
            } else {
                blnOpen = await self.#buscarDadosDocumentoTipo();
            }
        }

        await commonFunctions.loadingModalDisplay(false);

        if (!blnOpen) {
            return await self._returnPromisseResolve();
        }
        await self._modalHideShow();
        return await self._modalOpen();
    }

    #addEventosPadrao() {
        const self = this;
        const modal = $(self._idModal);

        modal.find('.btn-simular').on('click', async function () {
            commonFunctions.simulateLoading($(this));
            try {
                await self.#simularDocumento();
            } finally {
                commonFunctions.simulateLoading($(this), false);
            }
        });
    }

    _modalReset() {
        super._modalReset();
        const self = this;
        $(self.getIdModal).find(`#dados-documento${self._objConfigs.sufixo}-tab`).trigger('click');
        $(self.getIdModal).find('.elements-pane-lancamentos').show();
    }

    async #simularDocumento() {
        const self = this;
        const rowLancamentos = $(self.getIdModal).find('.row-lancamentos');
        rowLancamentos.html('');

        const data = self.#obterDados();

        if (!self.#saveVerifications(data, 'simulacao')) {
            return;
        }
    }

    async #buscarDadosDocumentoTipo() {
        const self = this;
        try {
            const objConn = new connectAjax(self._objConfigs.url.baseDocumentoTipoTenants);
            objConn.setParam(self._dataEnvModal.documento_tipo_tenant_id);
            const response = await objConn.getRequest();

            self._objConfigs.data.documento_tipo_tenant = response.data;
            self._updateModalTitle(`${response.data.nome}`);
            $(`#divCamposDocumento${self.getSufixo}`).html(response.data.campos_html);
            self.#addEventosCamposPersonalizados();
            return true;
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
            return false;
        }
    }

    #addEventosCamposPersonalizados() {
        const self = this;
        const modal = $(self.getIdModal);

        commonFunctions.cpfMask($(`#divCamposDocumento${self.getSufixo}`).find('.campo-cpf'));
    }

    async #buscarDados() {
        const self = this;
        $(self.getIdModal).find('.row-lancamentos').html('');

        try {
            self._clearForm();
            $(self.getIdModal).find('.btn-simular').hide();
            self._action = enumAction.PUT;
            const response = await self._getRecurse();
            if (response?.data) {
                return;
                const responseData = response.data;
                const documentoTipoTenant = responseData.documento_tipo_tenant;
                const documentoTipo = documentoTipoTenant.documento_tipo;
                const configuracao = documentoTipo.configuracao;

                self._updateModalTitle(`Alterar: <b>${documentoTipoTenant.nome}</b>`);
                self._dataEnvModal.documento_tipo_tenant_id = documentoTipoTenant.id;
                await self.#buscarDadosDocumentoTipo(true);

                const form = $(self.getIdModal).find('.formRegistration');
                form.find('select[name="conta_id"]').val(responseData.conta_id);
                form.find('select[name="status_id"]').val(responseData.status_id);

                const tipoCampos = [configuracao.campos_obrigatorios, configuracao.campos_opcionais ?? []];
                for (const tipoCampo of tipoCampos) {
                    for (const campo of tipoCampo) {

                        const rules = campo.form_request_rule.split('|');
                        let valor = responseData[campo.nome];
                        if (rules.find(rule => rule === 'numeric')) {
                            valor = commonFunctions.formatWithCurrencyCommasOrFraction(valor);
                        }
                        form.find(`#${campo.nome}${self._objConfigs.sufixo}`).val(valor).trigger('input');
                    }
                }
                return true;
            }
            return false;
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
            return false;
        }
    }

    saveButtonAction() {
        const self = this;
        const data = self.#obterDados();
        data.documento_tipo_tenant_id = self._objConfigs.data.documento_tipo_tenant.id;

        if (self.#saveVerifications(data)) {
            return;
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
        const documentoTipo = self._objConfigs.data.documento_tipo_tenant.documento_tipo;
        const configuracao = documentoTipo.configuracao;
        let blnSave = false;

        if (self._action == enumAction.POST || self._action == enumAction.PUT && tipo == 'save') {

            blnSave = commonFunctions.verificationData(data.conta_id, { field: formRegistration.find('select[name="conta_id"]'), messageInvalid: 'A <b>Conta padrão</b> deve ser informada.', setFocus: true });

            if (self._action == enumAction.POST) {
                for (const campo of configuracao.campos_obrigatorios) {
                    const rules = campo.form_request_rule.split('|');
                    const nullable = rules.find(rule => rule === 'nullable');

                    if (rules.find(rule => rule === 'numeric' || rule === 'integer')) {
                        data[campo.nome] = commonFunctions.removeCommasFromCurrencyOrFraction(data[campo.nome]);
                    }

                    if (documentoTipo.id == window.Enums.DocumentoTipoEnum.RECORRENTE && campo.nome == 'cron_expressao') {
                        if (data[campo.nome] == '* * * * *') {
                            commonFunctions.generateNotification('A <b>Recorrência</b> deve ser informada.', 'warning');
                            blnSave = false;
                        }
                    } else {
                        blnSave = commonFunctions.verificationData(data[campo.nome], {
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