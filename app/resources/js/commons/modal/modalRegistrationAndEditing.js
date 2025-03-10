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

        let objConfigs = {
            url: {
                base: undefined,
            },
        };

        objSuper.objConfigs = Object.assign(
            objConfigs,
            objSuper.objConfigs ?? {}
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
        self._resetDefaultTitle();
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

    /**
     * Recupera um registro da API.
     * 
     * @param {Object} options - Op es adicionais.
     * @param {number} options.idRegister - ID do registro a ser recuperado.
     * @param {string} options.urlApi - URL da API.
     * 
     * @returns {Promise<Object|boolean>} - Retorna uma Promise que resolve com o objeto de resposta da API caso a solicita o seja bem-sucedida ou false caso contr rio.
     */
    async _getRecurse(options = {}) {
        const self = this;
        const { idRegister = self._dataEnvModal.idRegister,
            urlApi = self._objConfigs.url.base
        } = options;
        try {
            const obj = new connectAjax(urlApi);
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
            fieldRegisterName = 'register',
        } = options;

        try {
            commonFunctions.simulateLoading(btnSave);
            const obj = new connectAjax(urlApi);
            obj.setAction(self._action)
            obj.setData(data);
            if (self._action === enumAction.PUT) {
                obj.setParam(self._dataEnvModal.idRegister);
            }

            const response = await obj.envRequest();
            if (response) {
                commonFunctions.generateNotification(`Dados enviados com sucesso!`, 'success');
                self._promisseReturnValue.refresh = true;
                self._promisseReturnValue[fieldRegisterName] = response.data;
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