import { commonFunctions } from "../../../commons/commonFunctions";
import { connectAjax } from "../../../commons/connectAjax";
import { enumAction } from "../../../commons/enumAction";
import { MasksAndValidateHelpers } from "../../../helpers/MasksAndValidateHelpers";
import { RedirectHelper } from "../../../helpers/RedirectHelper";
import { URLHelper } from "../../../helpers/URLHelper";
import { UUIDHelper } from "../../../helpers/UUIDHelper";
import { PessoaDocumentoModule } from "../../../modules/PessoaDocumentoModule";
import { PessoaPerfilModule } from "../../../modules/PessoaPerfilModule";

export class TemplateFormPessoaJuridica {

    _objConfigs = {
        url: {
            basePessoaPerfil: window.apiRoutes.basePessoaPerfil,
            baseEstadoCivilTenant: window.apiRoutes.baseEstadoCivilTenant,
            baseEscolaridadeTenant: window.apiRoutes.baseEscolaridadeTenant,
            baseSexoTenant: window.apiRoutes.baseSexoTenant,
        },
        data: {
            pessoa_dados_id: undefined,
            pessoa_perfil_tipo_id: undefined,
            pessoa_tipo_aplicavel: [],
            documentosNaTela: [],
            perfisNaTela: [],
        },
    };
    _action;
    _idRegister;
    _pessoaDocumentoModule;
    _pessoaPerfilModule;

    async initEvents() {
        const self = this;
        await self._carregamentoModulos();

        await self._verificacaoURL();

        self._addEventosBotoes();
    }

    async _verificacaoURL() {
        const self = this;

        const uuid = URLHelper.getURLSegment();
        if (UUIDHelper.isValidUUID(uuid)) {
            self._idRegister = uuid;
            self._action = enumAction.PUT;
            await self._buscarDados();
        } else {
            self._action = enumAction.POST;
            self._pessoaPerfilModule._inserirPerfilObrigatorio();
        }

    }

    async _carregamentoModulos() {
        const self = this;
        const objData = {
            objConfigs: self._objConfigs,
        }
        self._pessoaDocumentoModule = new PessoaDocumentoModule(self, objData);
        self._pessoaPerfilModule = new PessoaPerfilModule(self, objData);
    }

    _addEventosBotoes() {
        const self = this;

        commonFunctions.applyCustomNumberMask($('.campo-monetario'), { format: '#.##0,00', reverse: true });
        MasksAndValidateHelpers.cpfMask($('.campo-cpf'));
        MasksAndValidateHelpers.addEventCheckCPF({ selector: $('.campo-cpf'), event: 'focusout' });

        $(`#btnSave${self._objConfigs.sufixo}`).on('click', async function (e) {
            e.preventDefault();
            self.#saveButtonAction();
        });
    }

    async _buscarDados() {
        const self = this;

        try {
            await commonFunctions.loadingModalDisplay();

            const response = await self.#getRecurse({ urlApi: self._objConfigs.url.basePessoaPerfil });

            if (response?.data) {
                const responseData = response.data;
                const pessoaDados = responseData.pessoa.pessoa_dados;
                const form = $(`#formDados${self._objConfigs.sufixo}`);

                self._objConfigs.data.pessoa_dados_id = pessoaDados.id;
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
                        self._pessoaDocumentoModule._inserirDocumento(documento);
                    });
                }
                if (responseData.pessoa?.pessoa_perfil.length) {
                    responseData.pessoa.pessoa_perfil.map(perfil => {
                        // Não verifica se o limite de documentos foi atingido porque está vindo direto do banco
                        self._pessoaPerfilModule._inserirPerfil(perfil);
                    });
                }

                if (typeof self.preenchimentoEspecificoBuscaPerfilTipo === 'function') {
                    await self.preenchimentoEspecificoBuscaPerfilTipo(responseData);
                }

            } else {
                $('#myTab, #myTabContent').find('input, textarea, select, button').prop('disabled', true);
                $('.btn-save').prop('disabled', true);
            }

        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
        } finally {
            await commonFunctions.loadingModalDisplay(false);
        }
    }

    async #getRecurse(options = {}) {
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

    #saveButtonAction() {
        const self = this;
        const formRegistration = $(`#formDados${self._objConfigs.sufixo}`);
        let data = commonFunctions.getInputsValues(formRegistration[0]);
        data.pessoa_perfil_tipo_id = self._objConfigs.data.pessoa_perfil_tipo_id;
        data.documentos = self._pessoaDocumentoModule._retornaDocumentosNaTelaSaveButonAction();
        data.perfis = self._pessoaPerfilModule._retornaPerfilsNaTelaSaveButonAction();
        data.capital_social = data.capital_social ? commonFunctions.removeCommasFromCurrencyOrFraction(data.capital_social) : null;

        if (typeof self.saveButtonActionEspecificoPerfilTipo === 'function') {
            data = self.saveButtonActionEspecificoPerfilTipo(data);
        }

        data = self._tratarValoresNulos(data);

        if (self._saveVerifications(data, formRegistration)) {
            self.#save(data, self._objConfigs.url.base);
        }
        return false;
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

    _saveVerifications(data, formRegistration) {
        const self = this;
        let blnSave = commonFunctions.verificationData(data.razao_social, { field: formRegistration.find('input[name="razao_social"]'), messageInvalid: 'O campo <b>Razão Social</b> deve ser informado.', setFocus: true });

        if (typeof self.saveVerificationsEspecificoPerfilTipo === 'function') {
            blnSave = self.saveVerificationsEspecificoPerfilTipo(data, blnSave == false, blnSave == false);
        }

        return blnSave;
    }

    async #save(data, urlApi, options = {}) {
        const self = this;
        const {
            btnSave = $(`#btnSave${self._objConfigs.sufixo}`),
        } = options;

        try {
            commonFunctions.simulateLoading(btnSave);
            const obj = new connectAjax(urlApi);
            obj.setAction(self._action);
            obj.setData(data);
            if (self._action === enumAction.PUT) {
                obj.setParam(self._objConfigs.data.pessoa_dados_id);
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
    new TemplateFormPessoaJuridica();
});