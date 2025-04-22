import { ModalMessage } from "../../components/comum/ModalMessage";
import { HotkeyManagerHelper } from "../../helpers/HotkeyManagerHelper";
import { RedirectHelper } from "../../helpers/RedirectHelper";
import TenantTypeDomainCustomHelper from "../../helpers/TenantTypeDomainCustomHelper";
import { QueueManager } from "../../utils/QueueManager";
import { CommonFunctions } from "../CommonFunctions";
import { ConnectAjax } from "../ConnectAjax";
import { EnumAction } from "../EnumAction";
import InstanceManager from "../InstanceManager";

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

    /** @type {HotkeyManagerHelper} */
    _hotkeyManager;

    constructor(objSuper = {}) {
        CommonFunctions.deepMergeObject(this._objConfigs, objSuper.objConfigs ?? {});
        let sufixo = objSuper.sufixo ?? this._objConfigs.sufixo ?? undefined;

        if (sufixo) {
            this._sufixo = sufixo;
        }

        // InstanceManager.setVerboseTrueAutoFalse = true;
        this._hotkeyManager = InstanceManager.getOrCreateInstance('HotkeyManagerHelper', () => new HotkeyManagerHelper());

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

    get getIdSufixo() {
        return `#${this.getSufixo}`;
    }

    /**
     * Aplica foco a um elemento da página após um tempo determinado.
     *
     * Esta função é útil para garantir que o elemento esteja renderizado antes de receber o foco,
     * especialmente em casos de componentes dinâmicos, abas, modais ou carregamento assíncrono.
     *
     * @param {string|HTMLElement|jQuery} element - Seletor, elemento HTML ou objeto jQuery que receberá o foco.
     * @param {number} [timeout=0] - Tempo de espera em milissegundos antes de aplicar o foco (padrão: 0).
     */
    setFocusElement(element, timeout = 0) {
        setTimeout(() => {
            if ($(element).length) {
                const el = $(element)[0];
                if (el && typeof el.focus === 'function') {
                    el.focus(); // uso do método nativo para maior confiabilidade
                } else {
                    $(element).trigger('focus'); // fallback para compatibilidade
                }
            }
        }, timeout);
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

    //#region Campos para verificação de TenantTypeDomainCustom

    /**
     * Verifica e herda o ID de domínio customizado para envio ao modal.
     * 
     * Esta função verifica se o tenant tem um domínio customizado configurado.
     * Se sim, define o `inherit_domain_id` dentro do objeto `dataEnvModal`,
     * obtendo o ID do objeto enviado. 
     *
     * @param {Object} objData - Objeto contendo os dados contendo o ID de domínio.
     * @param {Object} dataEnvModal - Objeto contendo os dados a serem enviados ao modal.
     *                                Caso `inherit_domain_id` ainda não exista, será inserido.
     * @throws {Error} Se o domínio customizado exigir um ID, mas ele não for encontrado.
     * @private
     */
    _checkDomainCustomInheritDataEnvModalForObjData(objData, dataEnvModal = {}) {
        const self = this;

        // Verifica se há uma instância de domínio customizado ativa
        const instance = TenantTypeDomainCustomHelper.getInstanceTenantTypeDomainCustom;
        if (instance) {

            // Obtém o ID do domínio customizado que deve ser herdado
            const inherit_domain_id = objData?.domain_id;

            // Se o ID não existir, lança um erro
            if (!inherit_domain_id) {
                console.error(objData);
                throw new Error('O ID de Unidade de Domínio a ser herdado não foi informado. Contate o suporte.');
            }

            // Define o inherit_domain_id no objeto
            dataEnvModal.inherit_domain_id ??= undefined;
            dataEnvModal.inherit_domain_id = inherit_domain_id;
        }
        return dataEnvModal;
    }

    //#endregion

    async #addEventsDefault() {
        const self = this;

        self.#addEventBtnSave();
    }

    #addEventBtnSave() {
        const self = this;
        const btnSave = `#btnSave${self.getSufixo}`;

        const acaoSalvar = async () => {
            $(btnSave)?.trigger('click');
        };

        $(btnSave).on("click", async function (e) {
            e.preventDefault();
            await self.saveButtonAction();
        });

        self._hotkeyManager.registrar(self.getIdSufixo, ['ctrl+s', 'ctrl+shift+s'], acaoSalvar);
        self._hotkeyManager.ativarEscopo(self.getIdSufixo);
    }

    async _buscaDadosTenant() {
        const self = this;

        try {
            const urlApi = self._objConfigs?.url?.baseTenant ?? window.apiRoutes.baseTenant;
            const objConn = new ConnectAjax(`${urlApi}/current`);
            const response = await objConn.getRequest();
            self._objConfigs.dados_tenant = response.data;
        } catch (error) {
            CommonFunctions.generateNotificationErrorCatch(error);
        }
    }

    /**
     * Recupera um registro da API conforme a url informada.
     * 
     * @param {Object} options - Opções adicionais.
     * @param {string} options.urlApi - URL da API.
     * @param {boolean} options.checkForcedBefore - Indica se o domínio forçado deve ser verificado antes da consulta.
     * 
     * @returns {Promise<Object|boolean>} - Retorna uma Promise que resolve com o objeto de resposta da API caso a solicita o seja bem-sucedida ou false caso contr rio.
     */
    async _get(options = {}) {
        const self = this;
        const {
            urlApi = self._objConfigs.url.base,
            checkForcedBefore = false,
        } = options;

        try {
            // Se for realizar a checagem de domínio antes, então não se faz a atribuição do domínio forçado no próximo passo, pois já foi setado em outro momento.
            const forcedDomainId = !checkForcedBefore ? null : TenantTypeDomainCustomHelper.checkDomainCustomForcedDomainId(self);
            const obj = new ConnectAjax(urlApi);
            if (forcedDomainId) {
                obj.setForcedDomainCustomId = forcedDomainId;
            }

            const response = await obj.getRequest();
            if (!checkForcedBefore) TenantTypeDomainCustomHelper.checkDomainCustomBlockedChangesDomainId(self, response.data);
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
            idRegister = self._idRegister,
            urlApi = self._objConfigs.url.base,
            checkForcedBefore = false,
        } = options;

        try {
            // Se for realizar a checagem de domínio antes, então não se faz a atribuição do domínio forçado no próximo passo, pois já foi setado em outro momento.
            const forcedDomainId = !checkForcedBefore ? null : TenantTypeDomainCustomHelper.checkDomainCustomForcedDomainId(self);
            const obj = new ConnectAjax(urlApi);
            if (forcedDomainId) {
                obj.setForcedDomainCustomId = forcedDomainId;
            }

            obj.setParam(idRegister);
            const response = await obj.getRequest();
            if (!checkForcedBefore) TenantTypeDomainCustomHelper.checkDomainCustomBlockedChangesDomainId(self, response.data);
            return response;
        } catch (error) {
            CommonFunctions.generateNotificationErrorCatch(error);
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
            await CommonFunctions.loadingModalDisplay(true, {
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
            CommonFunctions.generateNotificationErrorCatch(error);
            return false;
        } finally {
            await CommonFunctions.loadingModalDisplay(false);
        }
    }

    #disableAndRemoveEvents() {
        const self = this;
        const container = $(`#${self.getSufixo}`);

        if (!container.length) return;

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
            CommonFunctions.simulateLoading(btnSave);

            const forcedDomainId = TenantTypeDomainCustomHelper.checkDomainCustomForcedDomainId(self);
            const obj = new ConnectAjax(urlApi);
            if (forcedDomainId) {
                obj.setForcedDomainCustomId = forcedDomainId;
            }
            obj.setAction(action);
            obj.setData(data);
            if (action === EnumAction.PUT) {
                obj.setParam(idRegister);
            }

            if (forcedDomainId && redirectBln) {

                const instance = TenantTypeDomainCustomHelper.getInstanceTenantTypeDomainCustom;

                if (instance && instance.getSelectedValue && forcedDomainId != instance.getSelectedValue) {
                    const nameSelected = TenantTypeDomainCustomHelper.getDomainNameById(instance.getDataCurrentDomain.id);
                    const nameCurrent = TenantTypeDomainCustomHelper.getDomainNameById(forcedDomainId);

                    const objMessage = new ModalMessage();
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
                    CommonFunctions.generateNotification(success, 'success');
                }
                return returnObjectSuccess ? response : true;
            }
            return false
        } catch (error) {
            CommonFunctions.generateNotificationErrorCatch(error);
        }
        finally {
            CommonFunctions.simulateLoading(btnSave, false);
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
            const obj = new ModalMessage();
            obj.setDataEnvModal = {
                title: title,
                message: message,
            };
            obj.setFocusElementWhenClosingModal = button;

            const result = await obj.modalOpen();
            if (result.confirmResult) {

                blnModalLoading = await CommonFunctions.loadingModalDisplay(true, { message: 'Excluindo registro...', title: 'Aguarde...' });

                if (await self._delRecurse(idDel, options)) {
                    if (functionExecuteAfterDelete) {
                        return await self[functionExecuteAfterDelete]
                    } else {
                        CommonFunctions.generateNotification(success, 'success');
                    };
                    return true;
                }
            }
            return false;
        } catch (error) {
            CommonFunctions.generateNotificationErrorCatch(error);
        } finally {
            if (blnModalLoading) await CommonFunctions.loadingModalDisplay(false);
        }

    }

    async _delRecurse(idDel, options = {}) {
        const self = this;
        const {
            url = self._objConfigs.url.base
        } = options;

        try {
            const obj = new ConnectAjax(url);
            obj.setParam(idDel);
            const response = await obj.deleteRequest();
            return true;
        } catch (error) {
            CommonFunctions.generateNotificationErrorCatch(error);
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