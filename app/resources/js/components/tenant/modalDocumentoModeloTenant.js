import { commonFunctions } from "../../commons/commonFunctions";
import { enumAction } from "../../commons/enumAction";
import { modalRegistrationAndEditing } from "../../commons/modal/modalRegistrationAndEditing";
import { DocumentoModeloTenantRenderModule } from "../../modules/DocumentoModeloTenantRenderModule";

export class modalDocumentoModeloTenant extends modalRegistrationAndEditing {

    /**
     * Configuração local do modal
     */
    #objConfigs = {
        url: {
            base: undefined,
            baseDocumentoModeloTenant: window.apiRoutes.baseDocumentoModeloTenant
        },
        sufixo: 'ModalDocumentoModeloTenant',
    };

    /**
     * Configuração local do modal
     */
    #dataEnvModal = {
        documento_modelo_tenant_id: undefined,
        objetos: [],
    };

    constructor(urlApi) {
        super({
            idModal: "#modalDocumentoModeloTenant",
        });

        this._objConfigs.url.base = urlApi;
        this._objConfigs = commonFunctions.deepMergeObject(this._objConfigs, this.#objConfigs);
        this._dataEnvModal = commonFunctions.deepMergeObject(this._dataEnvModal, this.#dataEnvModal);
        this._action = enumAction.POST;

        const objData = {
            objConfigs: this._objConfigs,
            extraConfigs: {
                modeParent: 'searchAndUse',
            }
        }
        this._functionsDocumentoModeloTenantRenderModule = new DocumentoModeloTenantRenderModule(this, objData);

        this.#addEventosBotoes();
    }

    async modalOpen() {
        const self = this;
        if (self._dataEnvModal.idRegister) {
            if (await self.#buscarDados()) {
                await self._modalHideShow();
            } else {
                return await self._returnPromisseResolve();
            }
        } else {
            await self._modalHideShow();
            self._executeFocusElementOnModal($(self.getIdModal).find('.focusRegister'));
        }
        self._functionsDocumentoModeloTenantRenderModule._inserirTodosObjetos(self._dataEnvModal.objetos);
        return await self._modalOpen();
    }

    #addEventosBotoes() {
        const self = this;

    }

    _modalClose() {
        const self = this;
        const modal = $(self.getIdModal);
        console.log(modal.find(`#painelConteudo${self.getSufixo}-tab`));
        modal.find(`#painelConteudo${self.getSufixo}-tab`).trigger('click');
        super._modalClose();
    }

    saveButtonAction() {
        const self = this;
        const formRegistration = $(self.getIdModal).find('.formRegistration');
        let data = commonFunctions.getInputsValues(formRegistration[0]);

        if (self.#saveVerifications(data, formRegistration)) {
            self._save(data, self._objConfigs.url.base);
        }
    }

    #saveVerifications(data, formRegistration) {
        let blnSave = commonFunctions.verificationData(data.titulo, { field: formRegistration.find('input[name="titulo"]'), messageInvalid: 'O título deve ser informado.', setFocus: true });
        blnSave = commonFunctions.verificationData(data.descricao, { field: formRegistration.find('textarea[name="descricao"]'), messageInvalid: 'Uma descrição deve ser adicionada.', setFocus: blnSave == true, returnForcedFalse: blnSave == false });
        return blnSave;
    }

    async #buscarDados() {
        const self = this;

        await commonFunctions.loadingModalDisplay();
        try {
            self._clearForm();
            self._action = enumAction.PUT;
            const response = await self._getRecurse();
            if (response?.data) {
                const responseData = response.data;
                self._updateModalTitle(`Alterar: <b>${responseData.titulo}</b>`);
                const form = $(self.getIdModal).find('.formRegistration');
                form.find('input[name="titulo"]').val(responseData.titulo);
                form.find('textarea[name="descricao"]').val(responseData.descricao).focus();
                return true;
            }
            return false;
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
            return false;
        } finally {
            await commonFunctions.loadingModalDisplay(false);
        }
    }
}