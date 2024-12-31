import { modalMessage } from "../../components/comum/modalMessage";
import { RedirectHelper } from "../../helpers/RedirectHelper";
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
        runningSearchBln: false,
        typeCurrentSearch: undefined,
    };

    _action;

    _idRegister;

    constructor(objSuper) {
        this._objConfigs = Object.assign(this._objConfigs, objSuper.objConfigs ?? {});
        this._sufixo = objSuper.sufixo ?? this._objConfigs.sufixo;

        if (this._sufixo) this._initEvents();
    }

    /**
     * Retorna o sufixo da página.
     */
    get getSufixo() {
        return this._sufixo;
    }

    _initEvents() {
        const self = this;

        self._addEventosBotoes();
    }

    async _addEventosBotoesPadrao() {
        const self = this;

        $(`#btnSave${self._objConfigs.sufixo}`).on('click', async function (e) {
            e.preventDefault();
            self.saveButtonAction();
        });

    }

    async _getRecurse(options = {}) {
        const self = this;
        const { idRegister = self._idRegister,
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

    async _buscarDados(options) {
        const self = this;
        const {
            message = null,
            title = null,
            elementFocus = null,
            functionPreenchimento = 'preenchimentoDados',
            form = $(`#form${self.getSufixo}`),
        } = options;

        try {
            await commonFunctions.loadingModalDisplay(true, {
                message: message ?? null,
                title: title ?? null,
                elementFocus: elementFocus ?? null
            });

            const response = await self._getRecurse();

            if (response?.data) {
                const retorno = await self[functionPreenchimento](response, { options: options });
            } else {
                form.find('input, textarea, select, button').prop('disabled', true);
            }

        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
        } finally {
            await commonFunctions.loadingModalDisplay(false);
        }
    }

    async _save(data, urlApi, options = {}) {
        const self = this;
        const {
            btnSave = $(`#btnSave${self._objConfigs.sufixo}`),
            success = 'Dados enviados com sucesso!',
            redirectBln = true,
        } = options;

        try {
            commonFunctions.simulateLoading(btnSave);
            const obj = new connectAjax(urlApi);
            obj.setAction(self._action);
            obj.setData(data);
            if (self._action === enumAction.PUT) {
                obj.setParam(self._idRegister);
            }
            const response = await obj.envRequest();

            if (response) {
                if (redirectBln) {
                    RedirectHelper.redirectWithUUIDMessage(`${window.frontRoutes.frontRedirectForm}/${response.data.id}`, success, 'success');
                } else {
                    commonFunctions.generateNotification(success, 'success');
                }
                return true;
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
                } else {
                    return false;
                };
            }
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

}