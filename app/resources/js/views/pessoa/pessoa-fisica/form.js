import { CommonFunctions } from "../../../commons/CommonFunctions";
import { EnumAction } from "../../../commons/EnumAction";
import { TemplateForm } from "../../../commons/templates/TemplateForm";
import { ModalPessoaDocumento } from "../../../components/pessoas/ModalPessoaDocumento";
import { ModalEscolaridadeTenant } from "../../../components/tenant/ModalEscolaridadeTenant";
import { ModalEstadoCivilTenant } from "../../../components/tenant/ModalEstadoCivilTenant";
import { ModalSexoTenant } from "../../../components/tenant/ModalSexoTenant";
import { URLHelper } from "../../../helpers/URLHelper";
import { UUIDHelper } from "../../../helpers/UUIDHelper";
import { EnderecoModule } from "../../../modules/EnderecoModule";
import { PessoaDocumentoModule } from "../../../modules/PessoaDocumentoModule";
import { PessoaPerfilModule } from "../../../modules/PessoaPerfilModule";
import { UsuarioDomainsModule } from "../../../modules/UsuarioDomainsModule";

export class PagePessoaFisicaForm extends TemplateForm {

    constructor() {
        const objConfigs = {
            url: {
                base: window.apiRoutes.basePessoaFisica,
                basePessoa: window.apiRoutes.basePessoa,
                baseEstadoCivilTenant: window.apiRoutes.baseEstadoCivilTenant,
                baseEscolaridadeTenant: window.apiRoutes.baseEscolaridadeTenant,
                baseSexoTenant: window.apiRoutes.baseSexoTenant,
            },
            sufixo: 'PagePessoaFisicaForm',
            data: {
                pessoa_tipo_aplicavel: [
                    window.Enums.PessoaTipoEnum.PESSOA_FISICA,
                ],
                pessoa_dados_id: undefined,
                documentosNaTela: [],
                perfisNaTela: [],
                enderecosNaTela: [],
                dominiosNaTela: [],
                user: null,
            },
        };

        super({ objConfigs });

        this.initEvents();
    }

    get getPerfisNaTela() {
        return this._pessoaPerfilModule.getPerfisNaTela;
    }

    get getUsuarioDomainsModule() {
        return this._usuarioDomainsModule;
    }

    async initEvents() {
        const self = this;
        await self.#carregamentoModulos();

        await self.#verificacaoURL();

        self.#addEventosBotoes();
        // self.abrirDocumento();
    }

    async abrirDocumento() {
        const modal = new ModalPessoaDocumento();
        modal.setDataEnvModal = {
            documento_tipo_tenant_id: "9eecfa3d-1b2b-46cb-9503-2d7270e20ce3"
        };
        console.warn(await modal.modalOpen());
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
            self.#buscarEscolaridade();
            self.#buscarEstadoCivil();
            self.#buscarSexo();

            self._action = EnumAction.POST;
            // self._pessoaPerfilModule._inserirPerfilObrigatorio();
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
        self._usuarioDomainsModule = new UsuarioDomainsModule(self, objData);
    }

    #addEventosBotoes() {
        const self = this;

        CommonFunctions.handleModal(self, $(`#btnOpenEstadoCivilTenant${self._objConfigs.sufixo}`), ModalEstadoCivilTenant, self.#buscarEstadoCivil.bind(self));

        CommonFunctions.handleModal(self, $(`#btnOpenEscolaridadeTenant${self._objConfigs.sufixo}`), ModalEscolaridadeTenant, self.#buscarEscolaridade.bind(self));

        CommonFunctions.handleModal(self, $(`#btnOpenSexoTenant${self._objConfigs.sufixo}`), ModalSexoTenant, self.#buscarSexo.bind(self));
    }

    async preenchimentoDados(response, options = {}) {
        const self = this;
        const form = $(options.form);

        const responseData = response.data;
        const pessoaDados = responseData.pessoa_dados;

        self._objConfigs.data.pessoa_dados_id = pessoaDados.id;
        form.find('input[name="nome"]').val(pessoaDados.nome);
        form.find('input[name="mae"]').val(pessoaDados.mae);
        form.find('input[name="pai"]').val(pessoaDados.pai);
        form.find('input[name="nacionalidade"]').val(pessoaDados.nacionalidade);
        form.find('input[name="nascimento_cidade"]').val(pessoaDados.nascimento_cidade);
        form.find('input[name="nascimento_estado"]').val(pessoaDados.nascimento_estado);
        form.find('input[name="nascimento_data"]').val(pessoaDados.nascimento_data);
        form.find('input[name="profissao"]').val(pessoaDados.profissao);
        self.#buscarEscolaridade(pessoaDados.escolaridade_id);
        self.#buscarEstadoCivil(pessoaDados.estado_civil_id);
        self.#buscarSexo(pessoaDados.sexo_id);
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

        self._usuarioDomainsModule._verificaEPreencheDadosUser(responseData);

        self.setFocusElement(form.find('input[name="nome"]'));
    }

    async #buscarEscolaridade(selected_id = null) {
        try {
            const self = this;
            let options = { outInstanceParentBln: true, firstOptionValue: null };
            selected_id ? options.selectedIdOption = selected_id : null;
            const select = $(`#escolaridade_id${self._objConfigs.sufixo}`);
            await CommonFunctions.fillSelect(select, self._objConfigs.url.baseEscolaridadeTenant, options); 0
            return true
        } catch (error) {
            return false;
        }
    }

    async #buscarEstadoCivil(selected_id = null) {
        try {
            const self = this;
            let options = { outInstanceParentBln: true, firstOptionValue: null };
            selected_id ? options.selectedIdOption = selected_id : null;
            const select = $(`#estado_civil_id${self._objConfigs.sufixo}`);
            await CommonFunctions.fillSelect(select, self._objConfigs.url.baseEstadoCivilTenant, options); 0
            return true
        } catch (error) {
            return false;
        }
    }

    async #buscarSexo(selected_id = null) {
        try {
            const self = this;
            let options = { outInstanceParentBln: true, firstOptionValue: null };
            selected_id ? options.selectedIdOption = selected_id : null;
            const select = $(`#sexo_id${self._objConfigs.sufixo}`);
            await CommonFunctions.fillSelect(select, self._objConfigs.url.baseSexoTenant, options); 0
            return true
        } catch (error) {
            return false;
        }
    }

    saveButtonAction() {
        const self = this;
        const formRegistration = $(`#form${self._objConfigs.sufixo}`);
        let data = CommonFunctions.getInputsValues(formRegistration[0]);
        data.documentos = self._pessoaDocumentoModule._retornaDocumentosNaTelaSaveButonAction();
        data.enderecos = self._enderecoModule._retornaEnderecosNaTelaSaveButonAction();

        self._pessoaPerfilModule.saveButtonActionEspecificoPerfil(data);
        self._usuarioDomainsModule.saveButtonActionEspecificoUsuarioDomains(data);

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
        let blnSave = CommonFunctions.verificationData(data.nome, { field: formRegistration.find('input[name="nome"]'), messageInvalid: 'O campo <b>nome</b> deve ser informado.', setFocus: true });

        blnSave = self._pessoaPerfilModule.saveVerificationsEspecificoPerfil(data, blnSave == true, blnSave == false);

        blnSave = self._usuarioDomainsModule.saveVerificationsEspecificoUsuarioDomains(data, blnSave == true, blnSave == false);

        return blnSave;
    }
}

$(function () {
    new PagePessoaFisicaForm();
});