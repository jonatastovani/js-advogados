import { CommonFunctions } from "../../commons/CommonFunctions";
import { ConnectAjax } from "../../commons/ConnectAjax";
import { ModalDefault } from "../../commons/modal/ModalDefault";

export class ModalSelecionarUsuarioDomains extends ModalDefault {

    /**
     * Configuração local do modal
     */
    #objConfigs = {
        url: {
            base: window.apiRoutes.baseTenantDomains,
        },
        sufixo: 'ModalSelecionarUsuarioDomains',
    };

    #dataEnvModal = {
        pessoa_tipo_aplicavel: [],
    }

    constructor() {
        super({
            idModal: "#ModalSelecionarUsuarioDomains",
        });

        this._objConfigs = CommonFunctions.deepMergeObject(this._objConfigs, this.#objConfigs);
        this._dataEnvModal = CommonFunctions.deepMergeObject(this._dataEnvModal, this.#dataEnvModal);
    }

    async modalOpen() {
        const self = this;

        if (!await self.#buscarUsuarioDomains()) {
            return await self._returnPromisseResolve();
        }
        await self._modalHideShow();
        return await self._modalOpen();
    }

    async preenchimentoDados(response, options) {

    }

    _modalReset() {
        const self = this;
        const modal = $(self.getIdModal);
        const formRegistration = modal.find('.formRegistration');
        formRegistration.find('select').val(0);
        formRegistration[0].reset();
        formRegistration.find('input, select, textarea').removeClass('is-valid').removeClass('is-invalid');
    }

    async #buscarUsuarioDomains(selected_id = null) {
        try {
            const self = this;
            let options = {
                outInstanceParentBln: true,
                displayColumnName: 'name'
            };
            selected_id ? options.selectedIdOption = selected_id : null;
            const select = $(`#domain_id${self._objConfigs.sufixo}`);
            await CommonFunctions.fillSelect(select, self._objConfigs.url.base, options);
            return true
        } catch (error) {
            return false;
        }
    }

    async saveButtonAction() {
        const self = this;
        const formRegistration = $(self.getIdModal).find('.formRegistration');
        let data = CommonFunctions.getInputsValues(formRegistration[0]);

        if (self.#saveVerifications(data, formRegistration)) {
            try {
                const objConn = new ConnectAjax(self._objConfigs.url.base);
                objConn.setParam(data.domain_id);
                const response = await objConn.getRequest();
                if (response.data) {
                    self._promisseReturnValue.register = {
                        domain_id: data.domain_id,
                        domain: response.data,
                    };
                    self._promisseReturnValue.refresh = true;
                    self._endTimer = true;
                } else {
                    CommonFunctions.generateNotification('Erro ao buscar domínio.', 'error');
                }
            } catch (error) {
                CommonFunctions.generateNotificationErrorCatch(error);
            }
        }
    }

    #saveVerifications(data, formRegistration) {
        return CommonFunctions.verificationData(data.domain_id, { field: formRegistration.find('select[name="domain_id"]'), messageInvalid: 'Selecione um domínio.', setFocus: true });
    }

}
