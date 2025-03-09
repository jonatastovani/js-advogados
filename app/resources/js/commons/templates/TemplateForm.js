import { modalMessage } from "../../components/comum/modalMessage";
import { RedirectHelper } from "../../helpers/RedirectHelper";
import TenantTypeDomainCustomHelper from "../../helpers/TenantTypeDomainCustomHelper";
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

    constructor(objSuper = {}) {
        commonFunctions.deepMergeObject(this._objConfigs, objSuper.objConfigs ?? {});
        let sufixo = objSuper.sufixo ?? this._objConfigs.sufixo ?? undefined;

        if (sufixo) {
            this._sufixo = sufixo;
        }

        this.#addEventsDefault();

        // Adiciona um pequeno delay para garantir que a classe derivada finalize o construtor
        setTimeout(() => {
            TenantTypeDomainCustomHelper.checkElementsDomainCustom(this);
        }, 0);
    }

    /**
     * Retorna o sufixo da página.
     */
    get getSufixo() {
        return this._sufixo ?? this._objConfigs.sufixo ?? undefined;
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
                form.find('input, textarea, select, button').prop('disabled', true);
            }

        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
        } finally {
            await commonFunctions.loadingModalDisplay(false);
        }
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

            const forcedDomainId = self.#checkDomainCustomForcedDomainId();
            const obj = new connectAjax(urlApi);
            if (forcedDomainId) {
                obj.setForcedDomainCustomId = forcedDomainId;
            }
            obj.setAction(action);
            obj.setData(data);
            if (action === enumAction.PUT) {
                obj.setParam(idRegister);
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

    #checkDomainCustomForcedDomainId() {
        const self = this;
        const instance = TenantTypeDomainCustomHelper.getInstanceTenantTypeDomainCustom;

        // Se não houver instância ou a seleção atual do usuário for diferente de `0`, retorna `false` para a inclusão forçada do domínio.
        if (!instance || instance.getSelectedValue != 0) return false;
        const domainId = self._objConfigs?.domainCustom?.domain_id;
        if (!domainId) {
            throw new Error(`Informação de unidade de domínio não encontrada. Caso erro persista, contate o suporte.`);
        }
        return domainId;
    }
}