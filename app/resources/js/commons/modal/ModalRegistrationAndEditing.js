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

        this._queueCheckDomainCustom = new QueueManager();
        this._queueCheckDomainCustom.enqueue(() => TenantTypeDomainCustomHelper.checkElementsDomainCustom(this, { stop_variable: true }));
    }

    //#region Getters e Setters

    /**
     * Define o ID forçado de domínio e marca as alterações como bloqueadas.
     * 
     * @param {number|string} id - ID do domínio a ser definido.
     */
    set setForcedDomainIdBlockedChanges(id) {
        this._objConfigs.domainCustom.blocked_changes = true;
        this._objConfigs.domainCustom.domain_id = id;
    }

    /**
     * Retorna o ID do domínio forçado que está bloqueado de alterações.
     * 
     * @returns {number|string|undefined} ID do domínio forçado e bloqueado, se definido. Caso contrário, retorna `undefined`.
     */
    get getForcedDomainIdBlockedChanges() {
        return this._objConfigs.domainCustom.domain_id;
    }

    /**
     * Define o ID forçado de domínio e marca as alterações como bloqueadas.
     * 
     * @param {number|string} id - ID do domínio a ser definido.
     */
    set setForcedDomainIdBlockedChanges(id) {
        this._objConfigs.domainCustom.blocked_changes = true;
        this._objConfigs.domainCustom.domain_id = id;
    }

    //#endregion

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


    //#region Verificações de domínio customizado

    /**
     * Verifica e herda o ID de domínio customizado para envio ao modal.
     * 
     * Esta função verifica se o tenant tem um domínio customizado configurado.
     * Se sim, define o `inherit_domain_id` dentro do objeto `dataEnvModal` 
     *
     * @param {Object} dataEnvModal - Objeto contendo os dados a serem enviados ao modal.
     *                                Caso `inherit_domain_id` ainda não exista, será inserido.
     * @throws {Error} Se o domínio customizado exigir um ID, mas ele não for encontrado.
     * @private
     */
    _checkDomainCustomInheritDataEnvModal(dataEnvModal = {}) {
        const self = this;

        // Verifica se há uma instância de domínio customizado ativa
        const instance = TenantTypeDomainCustomHelper.getInstanceTenantTypeDomainCustom;
        if (instance) {

            // Obtém o ID do domínio customizado que deve ser herdado
            const inherit_domain_id = self.getForcedDomainIdBlockedChanges;

            // Se o ID não existir, lança um erro
            if (!inherit_domain_id) {
                console.error(self._objConfigs.domainCustom);
                throw new Error('O ID de Unidade de Domínio a ser herdado não foi informado. Contate o suporte.');
            }

            // Define o inherit_domain_id no objeto
            dataEnvModal.inherit_domain_id ??= undefined;
            dataEnvModal.inherit_domain_id = inherit_domain_id;
        }
        return dataEnvModal;
    }

    /**
     * Verifica se o domínio customizado deve ser herdado e aplica a configuração.
     * 
     * Esta função verifica se o domínio customizado deve ser herdado (`inheritedBln`).
     * Se essa configuração estiver ativa, ela obtém o `inherit_domain_id` da variável
     * `dataEnvModal` e o define como o domínio forçado (`setForcedDomainIdBlockedChanges`).
     * 
     * Caso o `inherit_domain_id` não esteja presente, gera uma notificação de erro
     * e retorna `false`, impedindo a continuidade da lógica.
     *
     * @returns {boolean} Retorna `true` se o domínio for herdado corretamente, `false` caso contrário.
     * @private
     */
    _checkDomainCustomInherited() {
        const self = this;

        // Obtém a instância do gerenciador de domínio customizado
        const instance = TenantTypeDomainCustomHelper.getInstanceTenantTypeDomainCustom;
        if (!instance) return false; // Se não houver instância, encerra a função

        // Verifica se a configuração de herança de domínio está ativada
        if (self._objConfigs?.domainCustom?.inheritedBln) {

            // Obtém o ID do domínio herdado da variável `dataEnvModal`
            const domainId = self._dataEnvModal.inherit_domain_id;

            // Se não houver ID de domínio herdado, exibe erro e retorna falso
            if (!domainId) {
                CommonFunctions.generateNotification(
                    'O ID da Unidade de domínio herdada não foi enviado. Caso o erro persista, contate o suporte.',
                    'error'
                );
                console.error(self._dataEnvModal);
                return false;
            }

            // Define o domínio herdado como o domínio forçado
            self.setForcedDomainIdBlockedChanges = domainId;
        }

        return true; // Retorna `true` caso a herança de domínio tenha sido aplicada com sucesso
    }

    //#endregion
}
