import { modalMessage } from "../../components/comum/modalMessage";
import { RedirectHelper } from "../../helpers/RedirectHelper";
import TenantTypeDomainCustomHelper from "../../helpers/TenantTypeDomainCustomHelper";
import { QueueManager } from "../../utils/QueueManager";
import { commonFunctions } from "../commonFunctions";
import { connectAjax } from "../connectAjax";
import { enumAction } from "../enumAction";


export class TemplateForm {

    /**
     * Sufixo da template
     */
    _sufixo;

    /**
     * Objeto para reservar configurações do template
     */
    _objConfigs = {
        domainCustom: {
            applyBln: false,
            domain_id: undefined,
            blocked_changes: false,
        },
    };

    /**
     * Variável para reservar a ação a ser executada
     */
    _action;

    /**
     * Variável para reservar o id do que está sendo editado
     */
    _idRegister;

    /** @type {QueueManager} */
    _queueSelectDomainCustom;

    constructor(objSuper = {}) {
        commonFunctions.deepMergeObject(this._objConfigs, objSuper.objConfigs ?? {});
        let sufixo = objSuper.sufixo ?? this._objConfigs.sufixo ?? undefined;

        if (sufixo) {
            this._sufixo = sufixo;
        }

        this.#addEventsDefault();

        this._queueCheckDomainCustom = new QueueManager();
        this._queueCheckDomainCustom.enqueue(() => TenantTypeDomainCustomHelper.checkElementsDomainCustom(this, { stop_variable: true }));
    }

    /**
     * Retorna o sufixo da página.
     */
    get getSufixo() {
        return this._sufixo ?? this._objConfigs.sufixo ?? undefined;
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

    /**
     * Retorna o ID do domínio forçado que está bloqueado de alterações.
     * 
     * @returns {number|string|undefined} ID do domínio forçado e bloqueado, se definido. Caso contrário, retorna `undefined`.
     */
    get getForcedDomainIdBlockedChanges() {
        return this._objConfigs.domainCustom.domain_id;
    }

    async #addEventsDefault() {
        const self = this;

        self.#addEventBtnSave();
    }

    #addEventBtnSave() {
        const btnSave = `#btnSave${this.getSufixo}`;
        const self = this;
        $(btnSave).on("click", async function (e) {
            e.preventDefault();
            await self.saveButtonAction();
        });
    }

    async _getRecurse(options = {}) {
        const self = this;
        const {
            idRegister = self._idRegister,
            urlApi = self._objConfigs.url.base,
            outCheckForced = false,
        } = options;

        try {
            // Se for realizar a checagem de domínio forçado, então não se faz a atribuição do domínio forçado no próximo passo
            const forcedDomainId = !outCheckForced ? null : TenantTypeDomainCustomHelper.checkDomainCustomForcedDomainId(self);
            const obj = new connectAjax(urlApi);
            if (forcedDomainId) {
                obj.setForcedDomainCustomId = forcedDomainId;
            }

            obj.setParam(idRegister);
            const response = await obj.getRequest();
            if (!outCheckForced) TenantTypeDomainCustomHelper.checkDomainCustomBlockedChangesDomainId(self, response.data);
            return response;
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
            return false;
        }
    }

    async _buscarDados(options = {}) {
        const self = this;
        const {
            message = null,
            title = null,
            elementFocus = null,
            functionPreenchimento = 'preenchimentoDados',
            form = $(`#form${self.getSufixo}`),
        } = options;

        options.form = form;
        try {
            await commonFunctions.loadingModalDisplay(true, {
                message: message ?? null,
                title: title ?? null,
                elementFocus: elementFocus ?? null
            });

            const response = await self._getRecurse(options);

            if (response?.data) {
                await self[functionPreenchimento](response, options);
            } else {
                self.#disableAndRemoveEvents();
            }
            return response;

        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
            return false;
        } finally {
            await commonFunctions.loadingModalDisplay(false);
        }
    }

    #disableAndRemoveEvents() {
        const self = this;
        const container = $(`#${self.getSufixo}`);

        if (!container.length) return;

        console.log(container.find('input, textarea, select, button, a, label, fieldset'));
        // Remove eventos e desativa elementos interativos
        container.find('input, textarea, select, button, a, label, fieldset')
            .off("click keypress change focus blur")
            .off()
            .prop('disabled', true)
            .addClass('disabled');

        // Remove links e eventos `click`
        container.find('a').off('click').on('click', (e) => e.preventDefault());
    }

    _tratarValoresNulos(data) {
        return Object.fromEntries(
            Object.entries(data).map(([key, value]) => {
                if (value === "null") {
                    value = null;
                }
                return [key, value];
            })
        );
    }

    /**
     * Envia os dados para uma API, salvando-os no banco de dados.
     * 
     * @param {Object} data - Dados a serem enviados.
     * @param {string} urlApi - URL da API.
     * @param {Object} options - Opções adicionais.
     * @param {string} options.idRegister - ID do registro a ser editado.
     * @param {string} options.action - Ação a ser executada (POST, PUT).
     * @param {jQuery} options.btnSave - Elemento do botão de salvar.
     * @param {string} options.success - Mensagem de sucesso.
     * @param {boolean} options.redirectBln - Redirecionar após o salvamento.
     * @param {string} options.frontRedirectForm - Rota da página de redirecionamento.
     * @param {boolean} options.redirectWithIdBln - Redirecionar com o ID do registro.
     * @param {string} options.redirectWithId - Nome do campo que contém o ID do registro.
     * @param {boolean} options.returnObjectSuccess - Retornar o objeto de resposta da API.
     * 
     * @returns {Promise<boolean|Object>} - Retorna uma Promise que resolve com o objeto de resposta da API caso a solicitação seja bem-sucedida ou false caso contrario.
     */
    async _save(data, urlApi, options = {}) {
        const self = this;
        const {
            idRegister = self._idRegister,
            action = self._action,
            btnSave = $(`#btnSave${self._objConfigs.sufixo}`),
            success = 'Dados enviados com sucesso!',
            redirectBln = true,
            frontRedirectForm = window.frontRoutes.frontRedirectForm,
            redirectWithIdBln = false,
            redirectWithId = 'id',
            returnObjectSuccess = false,
        } = options;

        try {
            commonFunctions.simulateLoading(btnSave);

            const forcedDomainId = TenantTypeDomainCustomHelper.checkDomainCustomForcedDomainId(self);
            const obj = new connectAjax(urlApi);
            if (forcedDomainId) {
                obj.setForcedDomainCustomId = forcedDomainId;
            }
            obj.setAction(action);
            obj.setData(data);
            if (action === enumAction.PUT) {
                obj.setParam(idRegister);
            }

            if (forcedDomainId) {

                const instance = TenantTypeDomainCustomHelper.getInstanceTenantTypeDomainCustom;

                if (instance && instance.getSelectedValue && forcedDomainId != instance.getSelectedValue) {
                    const nameSelected = TenantTypeDomainCustomHelper.getDomainNameById(instance.getDataCurrentDomain.id);
                    const nameCurrent = TenantTypeDomainCustomHelper.getDomainNameById(forcedDomainId);

                    const objMessage = new modalMessage();
                    objMessage.setDataEnvModal = {
                        title: 'Atenção',
                        message: `<p>A unidade de visualização é <b>${nameSelected}</b> e este registro pertence a <b>${nameCurrent}</b>. Os dados serão salvos corretamente, mas o redirecionamento pode não encontrá-lo.</p><p>Deseja continuar?</p>`,
                    };
                    const result = await objMessage.modalOpen();
                    if (!result.confirmResult) {
                        return false;
                    }
                }
            }

            const response = await obj.envRequest();

            if (response) {
                if (redirectBln) {
                    RedirectHelper.redirectWithUUIDMessage(
                        `${frontRedirectForm}${redirectWithIdBln ? `/${response.data[redirectWithId]}` : ''}`,
                        success, 'success');
                } else {
                    commonFunctions.generateNotification(success, 'success');
                }
                return returnObjectSuccess ? response : true;
            }
            return false
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
        }
        finally {
            commonFunctions.simulateLoading(btnSave, false);
        };
    }

    async _delButtonAction(idDel, nameDel, options = {}) {

        const self = this;
        const { button = null,
            title = 'Exclusão de Registro',
            message = `Confirma a exclusão do registro <b>${nameDel}</b>?`,
            success = `Registro excluído com sucesso!`,
            functionExecuteAfterDelete = null
        } = options;

        let blnModalLoading = false;
        try {
            const obj = new modalMessage();
            obj.setDataEnvModal = {
                title: title,
                message: message,
            };
            obj.setFocusElementWhenClosingModal = button;

            const result = await obj.modalOpen();
            if (result.confirmResult) {

                blnModalLoading = await commonFunctions.loadingModalDisplay(true, { message: 'Excluindo registro...', title: 'Aguarde...' });

                if (await self._delRecurse(idDel, options)) {
                    if (functionExecuteAfterDelete) {
                        return await self[functionExecuteAfterDelete]
                    } else {
                        commonFunctions.generateNotification(success, 'success');
                    };
                    return true;
                }
            }
            return false;
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
        } finally {
            if (blnModalLoading) await commonFunctions.loadingModalDisplay(false);
        }

    }

    async _delRecurse(idDel, options = {}) {
        const self = this;
        const {
            url = self._objConfigs.url.base
        } = options;

        try {
            const obj = new connectAjax(url);
            obj.setParam(idDel);
            const response = await obj.deleteRequest();
            return true;
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
            return false;
        }
    }

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

}