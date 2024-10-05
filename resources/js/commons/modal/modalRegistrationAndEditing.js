import { commonFunctions } from "../commonFunctions";
import { connectAjax } from "../connectAjax";
import { enumAction } from "../enumAction";
import { modalDefault } from "./modalDefault";

export class modalRegistrationAndEditing extends modalDefault {

    /**
     * Variável para reservar a ação a ser executada
     */
    _action;

    constructor(objSuper) {
        let promisseReturnValueAppend = {
            register: undefined,
        };

        objSuper.promisseReturnValue = Object.assign(
            promisseReturnValueAppend,
            objSuper.promisseReturnValue ?? {}
        );

        let dataEnvModalAppend = {
            idRegister: undefined
        };

        objSuper.dataEnvModal = Object.assign(
            dataEnvModalAppend,
            objSuper.dataEnvModal ?? {}
        );

        super(objSuper);

        this.#addEventsDefault();
    }

    #addEventsDefault() {
        const self = this;
    }

    _modalReset() {
        const self = this;
        self._clearForm();
        const titleDefault = $(self.getIdModal).find('.modal-title').data('title-default');
        if (titleDefault) {
            self._updateModalTitle(titleDefault);
        }
    }

    _clearForm() {
        const self = this;
        const modal = $(self.getIdModal);
        const formRegistration = modal.find('.formRegistration');
        self._action = enumAction.POST;
        formRegistration.find('select').val(0);
        formRegistration[0].reset();
        formRegistration.find('input, select, textarea').removeClass('is-valid').removeClass('is-invalid');
    }

    //#region Campos de busca padrão

    async _getRecurse(options = {}) {
        const self = this;
        const { idRegister = self._dataEnvModal.idRegister } = options;
        try {
            const obj = new connectAjax(self._objConfigs.url);
            obj.setParam(idRegister);
            return await obj.getRequest();
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
            return false;
        }
    }

    //#endregion

    //#region Campos de salvamento padrão

    async _save(data, urlApi, options = {}) {
        const self = this;
        const modal = $(self.getIdModal);
        const {
            btnSave = modal.find('.btn-save'),
        } = options;

        try {
            commonFunctions.simulateLoading(btnSave);
            const obj = new connectAjax(urlApi);
            obj.setAction(self._action)
            obj.setData(data);
            if (self._dataEnvModal.idRegister) {
                obj.setParam(self._dataEnvModal.idRegister);
                obj.setAction(enumAction.PUT);
            } else {
                obj.setAction(enumAction.POST);
            }

            const response = await obj.envRequest();
            if (response) {
                commonFunctions.generateNotification(`Dados enviados com sucesso!`, 'success');
                self._promisseReturnValue.refresh = true;
                self._promisseReturnValue.register = response.data;
                self._endTimer = true;
            }
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
        }
        finally {
            commonFunctions.simulateLoading(btnSave, false);
        };
    }

    //#endregion

}