import { commonFunctions } from "../../commons/commonFunctions";
import { connectAjax } from "../../commons/connectAjax";
import { enumAction } from "../../commons/enumAction";
import { modalRegistrationAndEditing } from "../../commons/modal/modalRegistrationAndEditing";
import { MasksAndValidateHelpers } from "../../helpers/MasksAndValidateHelpers";
import { URLHelper } from "../../helpers/URLHelper";

export class modalPessoaDocumento extends modalRegistrationAndEditing {

    #dataEnvModal = {
        register: undefined,
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

        this.#addEventosPadrao();
    }

    async modalOpen() {
        const self = this;
        await commonFunctions.loadingModalDisplay(true, { message: 'Carregando informações do documento...' });
        let blnOpen = false;

        if (self._dataEnvModal.register) {
            blnOpen = await self.#preencherDados()
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

        // modal.find('.btn-simular').on('click', async function () {
        //     commonFunctions.simulateLoading($(this));
        //     try {
        //         await self.#simularDocumento();
        //     } finally {
        //         commonFunctions.simulateLoading($(this), false);
        //     }
        // });
    }

    _modalReset() {
        super._modalReset();
        const self = this;
        $(self.getIdModal).find(`#dados-documento${self._objConfigs.sufixo}-tab`).trigger('click');
        $(self.getIdModal).find('.elements-pane-lancamentos').show();
    }

    async #buscarDadosDocumentoTipo() {
        const self = this;
        try {
            const objConn = new connectAjax(self._objConfigs.url.baseDocumentoTipoTenants);
            objConn.setParam(self._dataEnvModal.documento_tipo_tenant_id);
            const response = await objConn.getRequest();

            if(response.data.campos_html){
                self._objConfigs.data.documento_tipo_tenant = response.data;
                self._objConfigs.data.documento_tipo_tenant_id = response.data.id;
                self._updateModalTitle(`${response.data.nome}`);
                $(`#divCamposDocumento${self.getSufixo}`).html(response.data.campos_html);
                self.#addEventosCamposPersonalizados();
                return true;
            } else {
                commonFunctions.generateNotification('HTML não encontrado.', 'error');
                return false;
            }
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
            return false;
        }
    }

    #addEventosCamposPersonalizados() {
        const self = this;
        const modal = $(self.getIdModal);

        MasksAndValidateHelpers.cpfMask(modal.find('.campo-cpf'));
        MasksAndValidateHelpers.addEventCheckCPF({ selector: modal.find('.campo-cpf'), event: 'focusout' });

        MasksAndValidateHelpers.cnpjMask(modal.find('.campo-cnpj'));
        MasksAndValidateHelpers.addEventCheckCNPJ({ selector: modal.find('.campo-cnpj'), event: 'focusout' });
    }

    async #preencherDados() {
        const self = this;

        try {
            self._clearForm();
            const registerData = self._dataEnvModal.register;
            if (registerData) {
                const documentoTipoTenant = registerData.documento_tipo_tenant;
                const documentoTipo = documentoTipoTenant.documento_tipo;
                // const configuracao = documentoTipo.configuracao;

                self._updateModalTitle(`Alterar: <b>${documentoTipoTenant.nome}</b>`);
                self._dataEnvModal.documento_tipo_tenant_id = documentoTipoTenant.id;
                if (await self.#buscarDadosDocumentoTipo()) {
                    const form = $(self.getIdModal).find('.formRegistration');
                    form.find('input[name="numero"]').val(registerData.numero);
                    // form.find('select[name="status_id"]').val(registerData.status_id);

                    // const tipoCampos = [configuracao.campos_obrigatorios, configuracao.campos_opcionais ?? []];
                    // for (const tipoCampo of tipoCampos) {
                    //     for (const campo of tipoCampo) {

                    //         const rules = campo.form_request_rule.split('|');
                    //         let valor = registerData[campo.nome];
                    //         if (rules.find(rule => rule === 'numeric')) {
                    //             valor = commonFunctions.formatWithCurrencyCommasOrFraction(valor);
                    //         }
                    //         form.find(`#${campo.nome}${self._objConfigs.sufixo}`).val(valor).trigger('input');
                    //     }
                    // }
                } else {
                    return false;
                }
                return true;
            }
            return false;
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
            return false;
        }
    }

    async saveButtonAction() {
        const self = this;
        const data = self.#obterDados();

        if (await self.#saveVerifications(data)) {
            self._promisseReturnValue.refresh = true;
            self._promisseReturnValue.register = data;
            self._endTimer = true;
        }
    }

    #obterDados() {
        const self = this;
        const formRegistration = $(self.getIdModal).find('.formRegistration');
        let data = commonFunctions.getInputsValues(formRegistration[0]);
        data.documento_tipo_tenant_id = self._objConfigs.data.documento_tipo_tenant.id;
        data.documento_tipo_tenant = self._objConfigs.data.documento_tipo_tenant;

        if (self._dataEnvModal.register) {
            data = commonFunctions.deepMergeObject(self._dataEnvModal.register, data);
        }
        return data;
    }

    async #saveVerifications(data) {
        const self = this;
        const formRegistration = $(self.getIdModal).find('.formRegistration');
        const documentoTipo = self._objConfigs.data.documento_tipo_tenant.documento_tipo;

        const inputNumero = formRegistration.find('input[name="numero"]');
        let blnSave = commonFunctions.verificationData(data.numero, { field: inputNumero, messageInvalid: 'O campo <b>número</b> deve ser preenchido.', setFocus: true });

        if (data.numero && documentoTipo.helper?.endpoint_api) {
            const urlEndPoint = URLHelper.formatEndpointUrl(documentoTipo.helper.endpoint_api);
            try {
                const objConn = new connectAjax(urlEndPoint);
                objConn.setAction(enumAction.POST);
                objConn.setData({
                    texto: data.numero,
                });
                const response = await objConn.envRequest();

                if (response.data) {
                    if (response.data.valido) {
                        // Se estiver vindo falso, continua falso
                        blnSave = !blnSave ? false : true;
                        inputNumero.removeClass('is-invalid').addClass('is-valid');
                    } else {
                        blnSave === true ? inputNumero.trigger('focus') : null;
                        inputNumero.removeClass('is-valid').addClass('is-invalid');
                        blnSave = false;
                        commonFunctions.generateNotification(response.data.mensagem ?? "Documento inválido", 'warning');
                    }
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
                return false
            }
        }

        // if (configuracao?.exp_reg) {
        //     data.numero = data.numero.replace('.', '');
        //     const regex = new RegExp(configuracao.exp_reg.slice(1, -1)); // Remove os delimitadores '/'

        //     if (!regex.test(data.numero)) {
        //         blnSave = false;
        //         commonFunctions.generateNotification('O número informado não está no formato válido.', 'warning');
        //         inputNumero.focus();
        //     } else {
        //         // Se estiver vindo falso, continua falso
        //         blnSave = !blnSave ? false : true;
        //     }
        // }

        // if (configuracao?.campos_adicionais) {
        //     for (const campo of configuracao.campos_adicionais) {
        //         const rules = campo.form_request_rule.split('|');
        //         const nullable = rules.find(rule => rule === 'nullable');

        //         if (rules.find(rule => rule === 'numeric' || rule === 'integer')) {
        //             data[campo.nome] = commonFunctions.removeCommasFromCurrencyOrFraction(data[campo.nome]);
        //         }

        //         blnSave = commonFunctions.verificationData(data[campo.nome], {
        //             field: formRegistration.find(`#${campo.nome}${self._objConfigs.sufixo}`),
        //             messageInvalid: `O campo <b>${campo.nome_exibir}</b> deve ser informado.`,
        //             setFocus: blnSave === true,
        //             returnForcedFalse: blnSave === false
        //         });
        //     }
        // }

        return blnSave;
    }

}