import TenantTypeDomainCustomHelper from "../../helpers/TenantTypeDomainCustomHelper";
import { QueueManager } from "../../utils/QueueManager";
import { CommonFunctions } from "../CommonFunctions";
import { ConnectAjax } from "../ConnectAjax";
import { EnumAction } from "../EnumAction";
import { ModalDefault } from "./ModalDefault";

export class ModalRegistrationAndEditing extends ModalDefault {

    /**
     * Variável para reservar a ação a ser executada
     */
    _action;

    constructor(objSuper) {
        let promisseReturnValueAppend = {
            register: undefined,
        };

        objSuper.promisseReturnValue = CommonFunctions.deepMergeObject(
            promisseReturnValueAppend,
            objSuper.promisseReturnValue ?? {}
        );

        let dataEnvModalAppend = {
            idRegister: undefined
        };

        objSuper.dataEnvModal = CommonFunctions.deepMergeObject(
            dataEnvModalAppend,
            objSuper.dataEnvModal ?? {}
        );

        let objConfigs = {
            url: {
                base: undefined,
            },
            modeNewOrEditingRegister: true, // Inicia com ela verdadeira, pois o formulário estará pronto para edição
        };

        objSuper.objConfigs = CommonFunctions.deepMergeObject(
            objConfigs,
            objSuper.objConfigs ?? {}
        );

        super(objSuper);

        this.#addEventsDefault();

        this._queueCheckDomainCustom = new QueueManager();
        this._queueCheckDomainCustom.enqueue(() => TenantTypeDomainCustomHelper.checkElementsDomainCustom(this, { stop_variable: true }));
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
        self._action = EnumAction.POST;
        formRegistration.find('select').val(0);
        formRegistration[0].reset();
        formRegistration.find('input, select, textarea').removeClass('is-valid').removeClass('is-invalid');
    }

    //#region Campos de busca padrão

    /**
     * Recupera registros da API conforme a url informada.
     * 
     * @param {Object} options - Opções adicionais.
     * @param {string} options.urlApi - URL da API.
     * 
     * @returns {Promise<Object|boolean>} - Retorna uma Promise que resolve com o objeto de resposta da API caso a solicita o seja bem-sucedida ou false caso contrário.
     */
    async _get(options = {}) {
        const self = this;
        const { urlApi = self._objConfigs.url.base } = options;
        try {
            const forcedDomainId = TenantTypeDomainCustomHelper.checkDomainCustomForcedDomainId(self);
            const obj = new ConnectAjax(urlApi);
            if (forcedDomainId) {
                obj.setForcedDomainCustomId = forcedDomainId;
            }
            const response = await obj.getRequest();
            if (!forcedDomainId) {
                TenantTypeDomainCustomHelper.checkDomainCustomBlockedChangesDomainId(self, response.data);
            }
            return response;
        } catch (error) {
            CommonFunctions.generateNotificationErrorCatch(error);
            return false;
        }
    }

    /**
     * Recupera um registro da API.
     * 
     * @param {Object} options - Opções adicionais.
     * @param {number} options.idRegister - ID do registro a ser recuperado.
     * @param {string} options.urlApi - URL da API.
     * @param {boolean} options.checkForcedBefore - Indica se o domínio forçado deve ser verificado antes da consulta.
     * 
     * @returns {Promise<Object|boolean>} - Retorna uma Promise que resolve com o objeto de resposta da API caso a solicita o seja bem-sucedida ou false caso contr rio.
     */
    async _getRecurse(options = {}) {
        const self = this;
        const {
            idRegister = self._dataEnvModal.idRegister,
            urlApi = self._objConfigs.url.base,
            checkForcedBefore = false,
        } = options;
        try {
            // Se for realizar a checagem de domínio antes, então não se faz a atribuição do domínio forçado no próximo passo, pois já foi setado em outro momento.
            const forcedDomainId = !checkForcedBefore ? null : TenantTypeDomainCustomHelper.checkDomainCustomForcedDomainId(self);
            const objConn = new ConnectAjax(urlApi);
            if (forcedDomainId) {
                objConn.setForcedDomainCustomId = forcedDomainId;
            }
            objConn.setParam(idRegister);
            const response = await objConn.getRequest();
            if (!checkForcedBefore) TenantTypeDomainCustomHelper.checkDomainCustomBlockedChangesDomainId(self, response.data);
            return response;
        } catch (error) {
            CommonFunctions.generateNotificationErrorCatch(error);
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
            CommonFunctions.simulateLoading(btnSave);

            const forcedDomainId = TenantTypeDomainCustomHelper.checkDomainCustomForcedDomainId(self);
            const obj = new ConnectAjax(urlApi);
            if (forcedDomainId) {
                obj.setForcedDomainCustomId = forcedDomainId;
            }
            obj.setAction(self._action)
            obj.setData(data);
            if (self._action === EnumAction.PUT) {
                obj.setParam(self._dataEnvModal.idRegister);
            }

            // Remover, aqui não tem redirecionamento
            // if (forcedDomainId) {

            //     const instance = TenantTypeDomainCustomHelper.getInstanceTenantTypeDomainCustom;

            //     if (instance && instance.getSelectedValue && forcedDomainId != instance.getSelectedValue) {
            //         const nameSelected = TenantTypeDomainCustomHelper.getDomainNameById(instance.getDataCurrentDomain.id);
            //         const nameCurrent = TenantTypeDomainCustomHelper.getDomainNameById(forcedDomainId);

            //         const objMessage = new ModalMessage();
            //         objMessage.setDataEnvModal = {
            //             title: 'Atenção',
            //             message: `<p>A unidade de visualização é <b>${nameSelected}</b> e este registro pertence a <b>${nameCurrent}</b>. Os dados serão salvos corretamente, mas o redirecionamento pode não encontrá-lo.</p><p>Deseja continuar?</p>`,
            //         };
            //         const result = await objMessage.modalOpen();
            //         if (!result.confirmResult) {
            //             return false;
            //         }
            //     }
            // }

            const response = await obj.envRequest();
            if (response) {
                CommonFunctions.generateNotification(`Dados enviados com sucesso!`, 'success');
                self._promisseReturnValue.refresh = true;
                self._promisseReturnValue[fieldRegisterName] = response.data;
                self._endTimer = true;
            }
        } catch (error) {
            CommonFunctions.generateNotificationErrorCatch(error);
        }
        finally {
            CommonFunctions.simulateLoading(btnSave, false);
        };
    }

    //#endregion

}
