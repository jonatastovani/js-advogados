import { commonFunctions } from "../../commons/commonFunctions";
import { enumAction } from "../../commons/enumAction";
import { modalRegistrationAndEditing } from "../../commons/modal/modalRegistrationAndEditing";

export class modalAnotacaoLembreteTenant extends modalRegistrationAndEditing {

    constructor(urlApi) {
        super({
            idModal: "#modalAnotacaoLembreteTenant",
        });

        this._action = enumAction.POST;
        this._objConfigs.url.base = urlApi;
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
        return await self._modalOpen();
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
