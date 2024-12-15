import { commonFunctions } from "../../../../commons/commonFunctions";
import { connectAjax } from "../../../../commons/connectAjax";
import { enumAction } from "../../../../commons/enumAction";
import { modalAreaJuridicaTenant } from "../../../../components/tenant/modalAreaJuridicaTenant";
import { RedirectHelper } from "../../../../helpers/RedirectHelper";
import { URLHelper } from "../../../../helpers/URLHelper";
import { UUIDHelper } from "../../../../helpers/UUIDHelper";

class PageClientePFForm {

    #objConfigs = {
        url: {
            base: window.apiRoutes.basePessoaPerfil,
            basePessoaFisica: window.apiRoutes.basePessoaFisica,
            baseEscolaridadeTenant: window.apiRoutes.baseEscolaridadeTenant,
            baseEstadoCivilTenant: window.apiRoutes.baseEstadoCivilTenant,
            baseGeneroTenant: window.apiRoutes.baseGeneroTenant,
        },
        sufixo: 'PageClientePFForm',
        data: {
        },
    };
    #action;
    #idRegister;

    constructor() {
        const objData = {
            objConfigs: this.#objConfigs,
            extraConfigs: {
                modeParent: 'searchAndUse',
            }
        }
        this.initEvents();
    }

    async initEvents() {
        const self = this;
        await this.#buscarEscolaridade();

        const uuid = URLHelper.getURLSegment();
        if (UUIDHelper.isValidUUID(uuid)) {
            self.#idRegister = uuid;
            const url = `${self.#objConfigs.url.base}/${self.#idRegister}`;
            this.#action = enumAction.PUT;
            await self.#buscarDados();
        } else {
            this.#action = enumAction.POST;
        }

        self.#addEventosBotoes();
    }

    #addEventosBotoes() {
        const self = this;

        $(`#btnOpenAreaJuridicaTenant${self.#objConfigs.sufixo}`).on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const objModal = new modalAreaJuridicaTenant();
                objModal.setDataEnvModal = {
                    attributes: {
                        select: {
                            quantity: 1,
                            autoReturn: true,
                        }
                    }
                }
                const response = await objModal.modalOpen();
                if (response.refresh) {
                    if (response.selected) {
                        self.#buscarEscolaridade(response.selected.id);
                    } else {
                        self.#buscarEscolaridade();
                    }
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading(btn, false);
            }
        });

        $(`#btnSave${self.#objConfigs.sufixo}`).on('click', async function (e) {
            e.preventDefault();
            self.#saveButtonAction();
        });

    }

    async #buscarDados() {
        const self = this;

        try {
            await commonFunctions.loadingModalDisplay();

            const response = await self.#getRecurse();
            
            if (response?.data) {
                const responseData = response.data;
                const pessoaDados = responseData.pessoa.pessoa_dados;
                const form = $(`#formDados${self.#objConfigs.sufixo}`);

                form.find('input[name="nome"]').val(pessoaDados.nome);
                form.find('input[name="mae"]').val(pessoaDados.mae);
                form.find('input[name="pai"]').val(pessoaDados.pai);
                form.find('input[name="nacionalidade"]').val(pessoaDados.nacionalidade);
                form.find('input[name="nascimento_cidade"]').val(pessoaDados.nascimento_cidade);
                form.find('input[name="nascimento_data"]').val(pessoaDados.nascimento_data);
                form.find('input[name="estado_civil_id"]').val(pessoaDados.estado_civil_id);
                form.find('textarea[name="observacao"]').val(responseData.observacao);


            } else {
                $('input, textarea, select, button').prop('disabled', true);
            }

        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
        } finally {
            await commonFunctions.loadingModalDisplay(false);
        }
    }

    async #buscarEscolaridade(selected_id = null) {
        try {
            const self = this;
            let options = selected_id ? { selectedIdOption: selected_id } : {};
            const selArea = $(`#area_juridica_id${self.#objConfigs.sufixo}`);
            await commonFunctions.fillSelect(selArea, self.#objConfigs.url.baseEscolaridadeTenant, options); 0
            return true
        } catch (error) {
            return false;
        }
    }

    async #getRecurse(options = {}) {
        const self = this;
        const { idRegister = self.#idRegister,
            urlApi = self.#objConfigs.url.base,
        } = options;
        console.log("URL", self.#objConfigs.url.base);
        try {
            const obj = new connectAjax(urlApi);
            obj.setParam(idRegister);
            return await obj.getRequest();
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
            return false;
        }
    }

    #saveButtonAction() {
        const self = this;
        const formRegistration = $(`#formServico${self.#objConfigs.sufixo}`);
        let data = commonFunctions.getInputsValues(formRegistration[0]);

        if (self.#saveVerifications(data, formRegistration)) {
            self.#save(data, self.#objConfigs.url.base);
        }
        return false;
    }

    #saveVerifications(data, formRegistration) {
        const self = this;
        if (self.#action == enumAction.POST) {
            let blnSave = commonFunctions.verificationData(data.titulo, { field: formRegistration.find('input[name="titulo"]'), messageInvalid: 'O título deve ser informado.', setFocus: true });
            blnSave = commonFunctions.verificationData(data.area_juridica_id, { field: formRegistration.find('select[name="area_juridica_id"]'), messageInvalid: 'A Área Jurídica deve ser selecionada.', setFocus: blnSave == true, returnForcedFalse: blnSave == false });
            blnSave = commonFunctions.verificationData(data.descricao, { field: formRegistration.find('textarea[name="descricao"]'), messageInvalid: 'A descrição deve ser preenchida.', setFocus: blnSave == true, returnForcedFalse: blnSave == false });
            return blnSave;
        }
        return true;
    }

    async #save(data, urlApi, options = {}) {
        const self = this;
        const {
            btnSave = $(`#btnSave${self.#objConfigs.sufixo}`),
        } = options;

        try {
            commonFunctions.simulateLoading(btnSave);
            const obj = new connectAjax(urlApi);
            obj.setAction(self.#action);
            obj.setData(data);
            if (self.#action === enumAction.PUT) {
                obj.setParam(self.#idRegister);
            }
            const response = await obj.envRequest();

            if (response) {
                if (self.#action === enumAction.PUT) {
                    commonFunctions.generateNotification('Dados do serviço alterados com sucesso!', 'success');
                } else {
                    RedirectHelper.redirectWithUUIDMessage(`${window.frontRoutes.frontRedirectForm}/${response.data.id}`, 'Serviço iniciado com sucesso!', 'success');
                }
            }
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
        }
        finally {
            commonFunctions.simulateLoading(btnSave, false);
        };
    }

}

$(function () {
    new PageClientePFForm();
});