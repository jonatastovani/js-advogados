import { commonFunctions } from "../../../../commons/commonFunctions";
import { connectAjax } from "../../../../commons/connectAjax";
import { enumAction } from "../../../../commons/enumAction";
import { RedirectHelper } from "../../../../helpers/RedirectHelper";
import { URLHelper } from "../../../../helpers/URLHelper";
import { UUIDHelper } from "../../../../helpers/UUIDHelper";
import { PessoaDocumentoModule } from "../../../../modules/PessoaDocumentoModule";

class PageClientePJForm {

    #objConfigs = {
        url: {
            base: window.apiRoutes.basePessoaJuridica,
            basePessoaPerfil: window.apiRoutes.basePessoaPerfil,
            baseEstadoCivilTenant: window.apiRoutes.baseEstadoCivilTenant,
            baseEscolaridadeTenant: window.apiRoutes.baseEscolaridadeTenant,
            baseSexoTenant: window.apiRoutes.baseSexoTenant,
        },
        sufixo: 'PageClientePJForm',
        data: {
            pessoa_dados_id: undefined,
            pessoa_perfil_tipo_id: window.Enums.PessoaPerfilTipoEnum.CLIENTE,
            pessoa_tipo_aplicavel: [
                window.Enums.PessoaTipoEnum.PESSOA_JURIDICA,
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
            this.#action = enumAction.POST;
        }

        self.#addEventosBotoes();
    }

    #addEventosBotoes() {
        const self = this;

        commonFunctions.applyCustomNumberMask($('.campo-monetario'), { format: '#.##0,00', reverse: true });

        commonFunctions.cpfMask($('.campo-cpf'));
        $('.campo-cpf').on('focusout', function () {
            if (commonFunctions.validateCPF(this.value)) {
                $(this).removeClass('is-invalid').addClass('is-valid');
            } else {
                $(this).removeClass('is-valid').addClass('is-invalid');
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
                form.find('input[name="razao_social"]').val(pessoaDados.razao_social);
                form.find('input[name="nome_fantasia"]').val(pessoaDados.nome_fantasia);
                form.find('input[name="natureza_juridica"]').val(pessoaDados.natureza_juridica);
                form.find('input[name="regime_tributario"]').val(pessoaDados.regime_tributario);
                form.find('input[name="responsavel_legal"]').val(pessoaDados.responsavel_legal);
                form.find('input[name="cpf_responsavel"]').val(pessoaDados.cpf_responsavel).trigger('input');
                form.find('input[name="inscricao_estadual"]').val(pessoaDados.inscricao_estadual);
                form.find('input[name="inscricao_municipal"]').val(pessoaDados.inscricao_municipal);
                form.find('input[name="capital_social"]').val(pessoaDados.capital_social);
                form.find('input[name="cnae"]').val(pessoaDados.cnae);
                form.find('input[name="data_fundacao"]').val(pessoaDados.data_fundacao);
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
        data.capital_social = data.capital_social ? commonFunctions.removeCommasFromCurrencyOrFraction(data.capital_social) : null;

        if (self.#saveVerifications(data, formRegistration)) {
            self.#save(data, self.#objConfigs.url.base);
        }
        return false;
    }

    #saveVerifications(data, formRegistration) {
        const self = this;
        let blnSave = commonFunctions.verificationData(data.razao_social, { field: formRegistration.find('input[name="razao_social"]'), messageInvalid: 'O campo <b>Razão Social</b> deve ser informado.', setFocus: true });
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
    new PageClientePJForm();
});