import { CommonFunctions } from "../../commons/CommonFunctions";
import { EnumAction } from "../../commons/EnumAction";
import { ModalRegistrationAndEditing } from "../../commons/modal/ModalRegistrationAndEditing";

export class ModalAnotacaoLembreteTenant extends ModalRegistrationAndEditing {

    constructor(urlApi) {
        super({
            idModal: "#ModalAnotacaoLembreteTenant",
        });

        this._action = EnumAction.POST;
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
        let data = CommonFunctions.getInputsValues(formRegistration[0]);

        if (self.#saveVerifications(data, formRegistration)) {
            self._save(data, self._objConfigs.url.base);
        }
    }

    #saveVerifications(data, formRegistration) {
        let blnSave = CommonFunctions.verificationData(data.titulo, { field: formRegistration.find('input[name="titulo"]'), messageInvalid: 'O título deve ser informado.', setFocus: true });
        blnSave = CommonFunctions.verificationData(data.descricao, { field: formRegistration.find('textarea[name="descricao"]'), messageInvalid: 'Uma descrição deve ser adicionada.', setFocus: blnSave == true, returnForcedFalse: blnSave == false });
        return blnSave;
    }

    async #buscarDados() {
        const self = this;

        await CommonFunctions.loadingModalDisplay();
        try {
            self._clearForm();
            self._action = EnumAction.PUT;
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
            CommonFunctions.generateNotificationErrorCatch(error);
            return false;
        } finally {
            await CommonFunctions.loadingModalDisplay(false);
        }
    }
}
