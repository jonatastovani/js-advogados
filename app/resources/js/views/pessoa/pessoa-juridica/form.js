import { CommonFunctions } from "../../../commons/CommonFunctions";
import { EnumAction } from "../../../commons/EnumAction";
import { TemplateForm } from "../../../commons/templates/TemplateForm";
import { MasksAndValidateHelpers } from "../../../helpers/MasksAndValidateHelpers";
import { URLHelper } from "../../../helpers/URLHelper";
import { UUIDHelper } from "../../../helpers/UUIDHelper";
import { EnderecoModule } from "../../../modules/EnderecoModule";
import { PessoaDocumentoModule } from "../../../modules/PessoaDocumentoModule";
import { PessoaPerfilModule } from "../../../modules/PessoaPerfilModule";

export class PagePessoaJuridicaForm extends TemplateForm {

    constructor() {
        const objConfigs = {
            url: {
                base: window.apiRoutes.basePessoaJuridica,
                basePessoa: window.apiRoutes.basePessoa,
                baseEstadoCivilTenant: window.apiRoutes.baseEstadoCivilTenant,
                baseEscolaridadeTenant: window.apiRoutes.baseEscolaridadeTenant,
                baseSexoTenant: window.apiRoutes.baseSexoTenant,
            },
            sufixo: 'PagePessoaJuridicaForm',
            data: {
                pessoa_tipo_aplicavel: [
                    window.Enums.PessoaTipoEnum.PESSOA_JURIDICA,
                ],
                pessoa_dados_id: undefined,
                documentosNaTela: [],
                perfisNaTela: [],
                enderecosNaTela: [],
            },
        };

        super({ objConfigs });

        this.initEvents();
    }

    get getPerfisNaTela() {
        return this._pessoaPerfilModule.getPerfisNaTela;
    }

    async initEvents() {
        const self = this;
        await self.#carregamentoModulos();

        await self.#verificacaoURL();

        self.#addEventosBotoes();
    }

    async #verificacaoURL() {
        const self = this;

        const uuid = URLHelper.getURLSegment();
        if (UUIDHelper.isValidUUID(uuid)) {
            self._idRegister = uuid;
            self._action = EnumAction.PUT;
            await self._buscarDados({
                urlApi: self._objConfigs.url.basePessoa,
            });
        } else {
            self._action = EnumAction.POST;
        }

    }

    async #carregamentoModulos() {
        const self = this;
        const objData = {
            objConfigs: self._objConfigs,
        }
        self._pessoaDocumentoModule = new PessoaDocumentoModule(self, objData);
        self._pessoaPerfilModule = new PessoaPerfilModule(self, objData);
        self._enderecoModule = new EnderecoModule(self, objData);
    }

    #addEventosBotoes() {

        CommonFunctions.applyCustomNumberMask($('.campo-monetario'), { format: '#.##0,00', reverse: true });
        MasksAndValidateHelpers.cpfMask($('.campo-cpf'));
        MasksAndValidateHelpers.addEventCheckCPF({ selector: $('.campo-cpf'), event: 'focusout' });

    }

    async preenchimentoDados(response, options = {}) {
        const self = this;
        const form = $(options.form);

        const responseData = response.data;
        const pessoaDados = responseData.pessoa_dados;

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

        if (responseData?.documentos.length) {
            responseData.documentos.map(documento => {
                // Não verifica se o limite de documentos foi atingido porque está vindo direto do banco
                self._pessoaDocumentoModule._inserirDocumento(documento);
            });
        }

        if (responseData?.pessoa_perfil.length) {
            responseData.pessoa_perfil.map(perfil => {
                // Envia a pessoa para ser identificado o tipo de pessoa e perfil e fornecer a rota de redirecionamento para edição do perfil
                perfil.pessoa = CommonFunctions.clonePure(responseData);
                // Não verifica se o perfil existe porque está vindo direto do banco
                self._pessoaPerfilModule._inserirPerfil(perfil);
            });
        }

        if (responseData?.enderecos.length) {
            responseData.enderecos.map(endereco => {
                self._enderecoModule._inserirEndereco(endereco, false);
            });
        }

        self.setFocusElement(form.find('input[name="razao_social"]'));
    }

    saveButtonAction() {
        const self = this;
        const formRegistration = $(`#form${self._objConfigs.sufixo}`);
        let data = CommonFunctions.getInputsValues(formRegistration[0]);
        data.documentos = self._pessoaDocumentoModule._retornaDocumentosNaTelaSaveButonAction();
        data.enderecos = self._enderecoModule._retornaEnderecosNaTelaSaveButonAction();
        data.capital_social = data.capital_social ? CommonFunctions.removeCommasFromCurrencyOrFraction(data.capital_social) : null;

        self._pessoaPerfilModule.saveButtonActionEspecificoPerfil(data);

        data = self._tratarValoresNulos(data);

        if (self._saveVerifications(data, formRegistration)) {
            self._save(data, self._objConfigs.url.base, {
                idRegister: self._objConfigs.data.pessoa_dados_id
            });
        }
        return false;
    }

    _saveVerifications(data, formRegistration) {
        const self = this;
        let blnSave = CommonFunctions.verificationData(data.razao_social, { field: formRegistration.find('input[name="razao_social"]'), messageInvalid: 'O campo <b>Razão Social</b> deve ser informado.', setFocus: true });

        blnSave = CommonFunctions.verificationData(data.nome_fantasia, { field: formRegistration.find('input[name="nome_fantasia"]'), messageInvalid: 'O campo <b>Nome Fantasia</b> deve ser informado.', setFocus: blnSave == true, returnForcedFalse: blnSave == false });

        blnSave = self._pessoaPerfilModule.saveVerificationsEspecificoPerfil(data, blnSave == true, blnSave == false);

        return blnSave;
    }
}

$(function () {
    new PagePessoaJuridicaForm();
});