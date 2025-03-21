import { CommonFunctions } from "../../commons/CommonFunctions";
import { EnumAction } from "../../commons/EnumAction";
import { ModalDefault } from "../../commons/modal/ModalDefault";

export class ModalSelecionarDocumentoModeloTenant extends ModalDefault {

    /**
     * Configuração local do modal
     */
    #objConfigs = {
        url: {
            base: `${window.apiRoutes.baseDocumentoModeloTenant}/documento-modelo-tipo`,
        },
        data: {
            itensNaTela: [],
        },
        sufixo: 'ModalSelecionarDocumentoModeloTenant',
    };

    #dataEnvModal = {
        documento_modelo_tipo_id: undefined,
    }

    constructor() {
        super({
            idModal: "#ModalSelecionarDocumentoModeloTenant",
        });

        this._objConfigs = Object.assign(this._objConfigs, this.#objConfigs);
        this._dataEnvModal = Object.assign(this._dataEnvModal, this.#dataEnvModal);
    }

    async modalOpen() {
        const self = this;
        let blnOpen = false;

        await CommonFunctions.loadingModalDisplay(true, { message: 'Carregando tipos de modelo de documento...', title: 'Aguarde...', elementFocus: null });

        if (!self._dataEnvModal.documento_modelo_tipo_id) {
            CommonFunctions.generateNotification('Tipo de modelo de documento não informado.', 'error');
            return await self._returnPromisseResolve();
        }

        try {
            await self.#buscarDocumentoModeloTipos();
            blnOpen = true;
        } catch (error) {
            blnOpen = false;
        } finally {
            await CommonFunctions.loadingModalDisplay(false);
        }

        if (!blnOpen) {
            return await self._returnPromisseResolve();
        }
        await self._modalHideShow();
        return await self._modalOpen();
    }

    _modalReset() {
        const self = this;
        const modal = $(self.getIdModal);
        const formRegistration = modal.find('.formRegistration');
        formRegistration.find('select').val(0);
        formRegistration[0].reset();
        formRegistration.find('input, select, textarea').removeClass('is-valid').removeClass('is-invalid');
    }

    async #buscarDocumentoModeloTipos(selected_id = null) {
        const self = this;
        let options = {
            typeRequest: EnumAction.POST,
            envData: {
                documento_modelo_tipo_id: self._dataEnvModal.documento_modelo_tipo_id,
            },
            outInstanceParentBln: true,
        };
        selected_id ? options.selectedIdOption = selected_id : null;
        const select = $(self.getIdModal).find('select[name="documento_modelo_tenant_id"]');
        const response = await CommonFunctions.fillSelect(select, self._objConfigs.url.base, options);
        self._objConfigs.data.itensNaTela = response.response.data;
        return response;
    }

    async saveButtonAction() {
        const self = this;
        const formRegistration = $(self.getIdModal).find('.formRegistration');
        let data = CommonFunctions.getInputsValues(formRegistration[0]);
        if (self.#saveVerifications(data, formRegistration)) {
            self._promisseReturnValue.refresh = true;
            self._promisseReturnValue.register = self._objConfigs.data.itensNaTela.find(item => item.id == data.documento_modelo_tenant_id);
            self._setEndTimer = true;
            // try {
            //     await self._modalHideShow(false);
            //     const objModal = new ModalDocumentoModeloTenant();
            //     objModal._dataEnvModal = {
            //         documento_modelo_tenant_id: data.documento_modelo_tenant_id,
            //     }
            //     self._promisseReturnValue = await objModal.modalOpen();
            // } catch (error) {
            //     CommonFunctions.generateNotificationErrorCatch(error);
            // } finally {
            //     self._setEndTimer = true;
            // }
        }
    }

    #saveVerifications(data, formRegistration) {
        return CommonFunctions.verificationData(data.documento_modelo_tenant_id, { field: formRegistration.find('select[name="documento_modelo_tenant_id"]'), messageInvalid: 'Selecione um tipo de modelo de documento.', setFocus: true });
    }

}
