import { commonFunctions } from "../../../../commons/commonFunctions";
import { connectAjax } from "../../../../commons/connectAjax";
import { enumAction } from "../../../../commons/enumAction";
import { modalEscolaridadeTenant } from "../../../../components/tenant/modalEscolaridadeTenant";
import { modalEstadoCivilTenant } from "../../../../components/tenant/modalEstadoCivilTenant";
import { modalSexoTenant } from "../../../../components/tenant/modalSexoTenant";
import { RedirectHelper } from "../../../../helpers/RedirectHelper";
import { URLHelper } from "../../../../helpers/URLHelper";
import { UUIDHelper } from "../../../../helpers/UUIDHelper";
import { PessoaDocumentoModule } from "../../../../modules/PessoaDocumentoModule";

class PageClientePFForm {

    #objConfigs = {
        url: {
            base: window.apiRoutes.basePessoaFisica,
            basePessoaPerfil: window.apiRoutes.basePessoaPerfil,
            baseEstadoCivilTenant: window.apiRoutes.baseEstadoCivilTenant,
            baseEscolaridadeTenant: window.apiRoutes.baseEscolaridadeTenant,
            baseSexoTenant: window.apiRoutes.baseSexoTenant,
        },
        sufixo: 'PageClientePFForm',
        data: {
            pessoa_dados_id: undefined,
            pessoa_perfil_tipo_id: window.Enums.PessoaPerfilTipoEnum.CLIENTE,
            pessoa_tipo_aplicavel: [
                window.Enums.PessoaTipoEnum.PESSOA_FISICA,
            ],
            documentosNaTela: [],
        },
    };
    #action;
    #idRegister;
    #pessoaDocumentoModule;

    constructor() {
        const objData = {
            objConfigs: this.#objConfigs,
        }
        this.#pessoaDocumentoModule = new PessoaDocumentoModule(this, objData);
        this.initEvents();
    }

    async initEvents() {
        const self = this;
        const uuid = URLHelper.getURLSegment();
        if (UUIDHelper.isValidUUID(uuid)) {
            self.#idRegister = uuid;
            const url = `${self.#objConfigs.url.base}/${self.#idRegister}`;
            this.#action = enumAction.PUT;
            await self.#buscarDados();
        } else {
            this.#buscarEscolaridade();
            this.#buscarEstadoCivil();
            this.#buscarSexo();

            this.#action = enumAction.POST;
        }

        self.#addEventosBotoes();
    }

    #addEventosBotoes() {
        const self = this;

        $(`#btnOpenEstadoCivilTenant${self.#objConfigs.sufixo}`).on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const objModal = new modalEstadoCivilTenant();
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
                        self.#buscarEstadoCivil(response.selected.id);
                    } else {
                        self.#buscarEstadoCivil();
                    }
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading(btn, false);
            }
        });

        $(`#btnOpenEscolaridadeTenant${self.#objConfigs.sufixo}`).on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const objModal = new modalEscolaridadeTenant();
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

        $(`#btnOpenSexoTenant${self.#objConfigs.sufixo}`).on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const objModal = new modalSexoTenant();
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
                        self.#buscarSexo(response.selected.id);
                    } else {
                        self.#buscarSexo();
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

            const response = await self.#getRecurse({ urlApi: self.#objConfigs.url.basePessoaPerfil });

            if (response?.data) {
                const responseData = response.data;
                const pessoaDados = responseData.pessoa.pessoa_dados;
                const form = $(`#formDados${self.#objConfigs.sufixo}`);

                self.#objConfigs.data.pessoa_dados_id = pessoaDados.id;
                form.find('input[name="nome"]').val(pessoaDados.nome);
                form.find('input[name="mae"]').val(pessoaDados.mae);
                form.find('input[name="pai"]').val(pessoaDados.pai);
                form.find('input[name="nacionalidade"]').val(pessoaDados.nacionalidade);
                form.find('input[name="nascimento_cidade"]').val(pessoaDados.nascimento_cidade);
                form.find('input[name="nascimento_estado"]').val(pessoaDados.nascimento_estado);
                form.find('input[name="nascimento_data"]').val(pessoaDados.nascimento_data);
                self.#buscarEscolaridade(pessoaDados.escolaridade_id);
                self.#buscarEstadoCivil(pessoaDados.estado_civil_id);
                self.#buscarSexo(pessoaDados.sexo_id);
                form.find('textarea[name="observacao"]').val(pessoaDados.observacao);
                form.find('input[name="ativo_bln"]').prop('checked', pessoaDados.ativo_bln);

                if (responseData.pessoa?.documentos.length) {
                    responseData.pessoa.documentos.map(documento => {
                        // Não verifica se o limite de documentos foi atingido porque está vindo direto do banco
                        self.#pessoaDocumentoModule._inserirDocumento(documento);
                    });
                }
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
            let options = { firstOptionValue: null };
            selected_id ? Object.assign(options, { selectedIdOption: selected_id }) : null;
            const select = $(`#escolaridade_id${self.#objConfigs.sufixo}`);
            await commonFunctions.fillSelect(select, self.#objConfigs.url.baseEscolaridadeTenant, options); 0
            return true
        } catch (error) {
            return false;
        }
    }

    async #buscarEstadoCivil(selected_id = null) {
        try {
            const self = this;
            let options = { firstOptionValue: null };
            selected_id ? Object.assign(options, { selectedIdOption: selected_id }) : null;
            const select = $(`#estado_civil_id${self.#objConfigs.sufixo}`);
            await commonFunctions.fillSelect(select, self.#objConfigs.url.baseEstadoCivilTenant, options); 0
            return true
        } catch (error) {
            return false;
        }
    }

    async #buscarSexo(selected_id = null) {
        try {
            const self = this;
            let options = { firstOptionValue: null };
            selected_id ? Object.assign(options, { selectedIdOption: selected_id }) : null;
            const select = $(`#sexo_id${self.#objConfigs.sufixo}`);
            await commonFunctions.fillSelect(select, self.#objConfigs.url.baseSexoTenant, options); 0
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
        const formRegistration = $(`#formDados${self.#objConfigs.sufixo}`);
        let data = commonFunctions.getInputsValues(formRegistration[0]);
        data.documentos = self.#pessoaDocumentoModule._retornaDocumentosNaTelaSaveButonAction();
        data.pessoa_perfil_tipo_id = self.#objConfigs.data.pessoa_perfil_tipo_id;
        data = self.#pessoaDocumentoModule._tratarValoresNulos(data);

        if (self.#saveVerifications(data, formRegistration)) {
            self.#save(data, self.#objConfigs.url.base);
        }
        return false;
    }

    #saveVerifications(data, formRegistration) {
        const self = this;
        let blnSave = commonFunctions.verificationData(data.nome, { field: formRegistration.find('input[name="nome"]'), messageInvalid: 'O campo <b>nome</b> deve ser informado.', setFocus: true });
        // blnSave = commonFunctions.verificationData(data.area_juridica_id, { field: formRegistration.find('select[name="area_juridica_id"]'), messageInvalid: 'A Área Jurídica deve ser selecionada.', setFocus: blnSave == true, returnForcedFalse: blnSave == false });
        return blnSave;
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
                obj.setParam(self.#objConfigs.data.pessoa_dados_id);
            }
            const response = await obj.envRequest();

            if (response) {
                RedirectHelper.redirectWithUUIDMessage(window.frontRoutes.frontRedirectForm, 'Dados enviados com sucesso!', 'success');
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