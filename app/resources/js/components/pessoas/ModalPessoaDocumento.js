import { CommonFunctions } from "../../commons/CommonFunctions";
import { ConnectAjax } from "../../commons/ConnectAjax";
import { EnumAction } from "../../commons/EnumAction";
import { ModalRegistrationAndEditing } from "../../commons/modal/ModalRegistrationAndEditing";
import { MasksAndValidateHelpers } from "../../helpers/MasksAndValidateHelpers";
import { URLHelper } from "../../helpers/URLHelper";

export class ModalPessoaDocumento extends ModalRegistrationAndEditing {

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
            baseChavePix: window.apiRoutes.baseChavePix,
        },
        sufixo: 'ModalPessoaDocumento',
        data: {
            documento_tipo_tenant: undefined,
            modal_default_size: 'modal-sm',
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
            idModal: "#ModalPessoaDocumento",
        });

        this._objConfigs = CommonFunctions.deepMergeObject(this._objConfigs, this.#objConfigs);
        this._promisseReturnValue = CommonFunctions.deepMergeObject(this._promisseReturnValue, this.#promisseReturnValue);
        this._dataEnvModal = CommonFunctions.deepMergeObject(this._dataEnvModal, this.#dataEnvModal);
        this._objConfigs.url.base = options.urlApi;
    }

    async modalOpen() {
        const self = this;
        await CommonFunctions.loadingModalDisplay(true, { message: 'Carregando informações do documento...' });
        let blnOpen = false;

        if (self._dataEnvModal.register) {
            blnOpen = await self.#preencherDados()
        } else {
            if (!self._dataEnvModal.documento_tipo_tenant_id) {
                CommonFunctions.generateNotification('Tipo de documento não informado', 'error');
                return await self._returnPromisseResolve();
            } else {
                blnOpen = await self.#buscarDadosDocumentoTipo();
            }
        }

        await CommonFunctions.loadingModalDisplay(false);

        if (!blnOpen) {
            return await self._returnPromisseResolve();
        }
        await self._modalHideShow();
        return await self._modalOpen();
    }

    _modalReset() {
        super._modalReset();
        const self = this;
        $(self.getIdModal).find(`#dados-documento${self.getSufixo}-tab`).trigger('click');
    }

    async #buscarDadosDocumentoTipo() {
        const self = this;
        try {
            const objConn = new ConnectAjax(self._objConfigs.url.baseDocumentoTipoTenants);
            objConn.setParam(self._dataEnvModal.documento_tipo_tenant_id);
            const response = await objConn.getRequest();

            const responseData = response.data;
            if (responseData.campos_html) {
                self._objConfigs.data.documento_tipo_tenant = responseData;
                self._objConfigs.data.documento_tipo_tenant_id = responseData.id;
                self._updateModalTitle(`${responseData.nome}`);
                $(`#divCamposDocumento${self.getSufixo}`).html(responseData.campos_html);
                self.#addEventosCamposPersonalizados();

                await self.#configsModal(responseData.documento_tipo.id);

                return true;
            } else {
                CommonFunctions.generateNotification('HTML não encontrado.', 'error');
                return false;
            }
        } catch (error) {
            CommonFunctions.generateNotificationErrorCatch(error);
            return false;
        }
    }

    async #configsModal(documento_tipo_id) {
        const self = this;
        const modal = $(self.getIdModal);
        const arraySize = ['modal-sm', 'modal-lg', 'modal-xl'];
        const tipos = window.Enums.DocumentoTipoEnum;
        let size = 'modal-sm';

        switch (documento_tipo_id) {
            case tipos.CHAVE_PIX:
                await self.#buscarTiposChavePix();
                size = '';
                break;
        }

        modal.find('.modal-dialog').removeClass(arraySize.join(' ')).addClass(size);
    }

    #addEventosCamposPersonalizados() {
        const self = this;
        const modal = $(self.getIdModal);

        // MasksAndValidateHelpers.cpfMask(modal.find('.campo-cpf'));
        // MasksAndValidateHelpers.addEventCheckCPF({ selector: modal.find('.campo-cpf'), event: 'focusout' });

        MasksAndValidateHelpers.cnpjMask(modal.find('.campo-cnpj'));
        MasksAndValidateHelpers.addEventCheckCNPJ({ selector: modal.find('.campo-cnpj'), event: 'focusout' });

        MasksAndValidateHelpers.chavePixMask(modal.find('select[name="tipo_chave"]'), modal.find('input[name="numero"]'));
    }

    async #preencherDados() {
        const self = this;

        try {
            self._clearForm();
            const registerData = self._dataEnvModal.register;

            if (!registerData) return false;

            const documentoTipoTenant = registerData.documento_tipo_tenant;
            const documentoTipo = documentoTipoTenant.documento_tipo;

            self._updateModalTitle(`Alterar: <b>${documentoTipoTenant.nome}</b>`);
            self._dataEnvModal.documento_tipo_tenant_id = documentoTipoTenant.id;

            if (await self.#buscarDadosDocumentoTipo()) {
                const form = $(self.getIdModal).find('.formRegistration');

                console.warn('registerData', CommonFunctions.clonePure(registerData));

                for (const campo of documentoTipo.campos_obrigatorios) {

                    let rules = campo?.form_request_rule ?? '';
                    if (Array.isArray(rules)) {
                        rules = rules.join('|');
                    }
                    rules = rules.split('|').filter(Boolean);

                    let valor = registerData[campo.nome];
                    if (rules.find(rule => rule === 'numeric')) {
                        valor = CommonFunctions.formatWithCurrencyCommasOrFraction(valor);
                    }
                    const elementFound = form.find(`#${campo.nome}${self.getSufixo}`);
                 
                    if (elementFound.length) {
                        console.log('elementFound', elementFound);
                        console.log("elementFound.is('select')", elementFound.is('select'));
                        const eventTrigger = elementFound.is('select') ? 'change' : 'input';
                        elementFound.val(valor).trigger(eventTrigger);
                        console.log('valor', elementFound.val());
                    }
                }

            } else {
                return false;
            }
            return true;
        } catch (error) {
            CommonFunctions.generateNotificationErrorCatch(error);
            return false;
        }
    }

    async #buscarTiposChavePix(selected_id = null) {
        const self = this;
        const select = $(self.getIdModal).find('select[name="tipo_chave"]');
        return await CommonFunctions.fillSelect(select, self._objConfigs.url.baseChavePix, {
            outInstanceParentBln: true,
            firstOptionValue: 0,
            'trigger': 'change',
            ...(selected_id && { selectedIdOption: selected_id })
        });
    }

    async saveButtonAction() {
        const self = this;
        const data = self.#obterDadosSave();

        if (await self.#saveVerifications(data)) {
            self._promisseReturnValue.refresh = true;
            self._promisseReturnValue.register = data;
            self._endTimer = true;
        }
    }

    #obterDadosSave() {
        const self = this;
        const formRegistration = $(self.getIdModal).find('.formRegistration');
        let data = CommonFunctions.getInputsValues(formRegistration[0]);

        const documentoTipoTenant = self._objConfigs.data.documento_tipo_tenant;
        const documentoTipo = documentoTipoTenant.documento_tipo;

        data.documento_tipo_tenant = documentoTipoTenant;
        data.documento_tipo_tenant_id = documentoTipoTenant.id;
        data.documento_tipo_id = documentoTipo.id;

        if (self._dataEnvModal.register) {
            data = CommonFunctions.deepMergeObject(self._dataEnvModal.register, data);
        }
        return data;
    }

    async #saveVerifications(data) {
        const self = this;
        const formRegistration = $(self.getIdModal).find('.formRegistration');
        const documentoTipo = self._objConfigs.data.documento_tipo_tenant.documento_tipo;
        let blnSave = true;

        for (const campo of documentoTipo.campos_obrigatorios) {
            let rules = campo?.form_request_rule ?? '';
            if (Array.isArray(rules)) {
                rules = rules.join('|');
            }
            rules = rules.split('|').filter(Boolean);

            if (!rules.length) continue;

            const nullable = rules.find(rule => rule === 'nullable');

            if (rules.find(rule => rule === 'numeric' || rule === 'integer')) {
                data[campo.nome] = CommonFunctions.removeCommasFromCurrencyOrFraction(data[campo.nome]);
            }

            if (!nullable) {
                blnSave = CommonFunctions.verificationData(data[campo.nome], {
                    field: formRegistration.find(`#${campo.nome}${self.getSufixo}`),
                    messageInvalid: `O campo <b>${campo.nome_exibir}</b> deve ser informado.`,
                    setFocus: blnSave === true,
                    returnForcedFalse: blnSave === false
                });
            }
        }

        if (blnSave && documentoTipo?.helper?.endpoint_api) {
            const urlEndPoint = URLHelper.formatEndpointUrl(documentoTipo.helper.endpoint_api);
            try {
                const objConn = new ConnectAjax(urlEndPoint);
                objConn.setAction(EnumAction.POST);
                // enviar somente o necessário
                const dataEnviar = CommonFunctions.clonePure(data)
                delete dataEnviar.documento_tipo_tenant;
                objConn.setData(dataEnviar);

                const response = await objConn.envRequest();

                if (!response?.data?.valido) {
                    blnSave = false;
                    CommonFunctions.generateNotification(response.data.mensagem ?? "Documento inválido.", 'warning');
                }
            } catch (error) {
                CommonFunctions.generateNotificationErrorCatch(error);
                return false
            }
        }
        return blnSave;
    }


    // async #saveVerifications(data) {
    //     const self = this;
    //     const formRegistration = $(self.getIdModal).find('.formRegistration');
    //     const documentoTipo = self._objConfigs.data.documento_tipo_tenant.documento_tipo;

    //     const inputNumero = formRegistration.find('input[name="numero"]');
    //     let blnSave = CommonFunctions.verificationData(data.numero, { field: inputNumero, messageInvalid: 'O campo <b>número</b> deve ser preenchido.', setFocus: true });

    //     if (data.numero && documentoTipo.helper?.endpoint_api) {
    //         const urlEndPoint = URLHelper.formatEndpointUrl(documentoTipo.helper.endpoint_api);
    //         try {
    //             const objConn = new ConnectAjax(urlEndPoint);
    //             objConn.setAction(EnumAction.POST);
    //             objConn.setData({
    //                 texto: data.numero,
    //             });
    //             const response = await objConn.envRequest();

    //             if (response.data) {
    //                 if (response.data.valido) {
    //                     // Se estiver vindo falso, continua falso
    //                     blnSave = !blnSave ? false : true;
    //                     inputNumero.removeClass('is-invalid').addClass('is-valid');
    //                 } else {
    //                     blnSave === true ? inputNumero.trigger('focus') : null;
    //                     inputNumero.removeClass('is-valid').addClass('is-invalid');
    //                     blnSave = false;
    //                     CommonFunctions.generateNotification(response.data.mensagem ?? "Documento inválido", 'warning');
    //                 }
    //             }
    //         } catch (error) {
    //             CommonFunctions.generateNotificationErrorCatch(error);
    //             return false
    //         }
    //     }

    //     // if (configuracao?.exp_reg) {
    //     //     data.numero = data.numero.replace('.', '');
    //     //     const regex = new RegExp(configuracao.exp_reg.slice(1, -1)); // Remove os delimitadores '/'

    //     //     if (!regex.test(data.numero)) {
    //     //         blnSave = false;
    //     //         CommonFunctions.generateNotification('O número informado não está no formato válido.', 'warning');
    //     //         inputNumero.focus();
    //     //     } else {
    //     //         // Se estiver vindo falso, continua falso
    //     //         blnSave = !blnSave ? false : true;
    //     //     }
    //     // }

    //     // if (configuracao?.campos_adicionais) {
    //     //     for (const campo of configuracao.campos_adicionais) {
    //     //         const rules = campo.form_request_rule.split('|');
    //     //         const nullable = rules.find(rule => rule === 'nullable');

    //     //         if (rules.find(rule => rule === 'numeric' || rule === 'integer')) {
    //     //             data[campo.nome] = CommonFunctions.removeCommasFromCurrencyOrFraction(data[campo.nome]);
    //     //         }

    //     //         blnSave = CommonFunctions.verificationData(data[campo.nome], {
    //     //             field: formRegistration.find(`#${campo.nome}${self.getSufixo}`),
    //     //             messageInvalid: `O campo <b>${campo.nome_exibir}</b> deve ser informado.`,
    //     //             setFocus: blnSave === true,
    //     //             returnForcedFalse: blnSave === false
    //     //         });
    //     //     }
    //     // }

    //     return blnSave;
    // }

}
