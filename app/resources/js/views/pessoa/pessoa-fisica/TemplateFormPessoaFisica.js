import { commonFunctions } from "../../../commons/commonFunctions";
import { enumAction } from "../../../commons/enumAction";
import { TemplateForm } from "../../../commons/templates/TemplateForm";
import { modalEscolaridadeTenant } from "../../../components/tenant/modalEscolaridadeTenant";
import { modalEstadoCivilTenant } from "../../../components/tenant/modalEstadoCivilTenant";
import { modalSexoTenant } from "../../../components/tenant/modalSexoTenant";
import { URLHelper } from "../../../helpers/URLHelper";
import { UUIDHelper } from "../../../helpers/UUIDHelper";
import { EnderecoModule } from "../../../modules/EnderecoModule";
import { PessoaDocumentoModule } from "../../../modules/PessoaDocumentoModule";
import { PessoaPerfilModule } from "../../../modules/PessoaPerfilModule";

export class TemplateFormPessoaFisica extends TemplateForm {

    constructor(objSuper) {
        const objConfigs = {
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
                enderecosNaTela: [],
            },
        };

        objSuper.objConfigs = commonFunctions.deepMergeObject(objConfigs, objSuper.objConfigs ?? {});
        super(objSuper);
    }

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
            await self._buscarDados({
                urlApi: self._objConfigs.url.basePessoaPerfil,
            });
        } else {
            self.#buscarEscolaridade();
            self.#buscarEstadoCivil();
            self.#buscarSexo();

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
        self._enderecoModule = new EnderecoModule(self, objData);
    }

    _addEventosBotoes() {
        const self = this;

        commonFunctions.handleModal(self, $(`#btnOpenEstadoCivilTenant${self._objConfigs.sufixo}`), modalEstadoCivilTenant, self.#buscarEstadoCivil.bind(self));

        commonFunctions.handleModal(self, $(`#btnOpenEscolaridadeTenant${self._objConfigs.sufixo}`), modalEscolaridadeTenant, self.#buscarEscolaridade.bind(self));

        commonFunctions.handleModal(self, $(`#btnOpenSexoTenant${self._objConfigs.sufixo}`), modalSexoTenant, self.#buscarSexo.bind(self));
    }

    async preenchimentoDados(response, options = {}) {
        const self = this;
        const form = $(options.form);

        const responseData = response.data;
        const pessoaDados = responseData.pessoa.pessoa_dados;

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

        if (responseData.pessoa?.enderecos.length) {
            responseData.pessoa.enderecos.map(endereco => {
                self._enderecoModule._inserirEndereco(endereco, false);
            });
        }

        if (typeof self.preenchimentoEspecificoBuscaPerfilTipo === 'function') {
            await self.preenchimentoEspecificoBuscaPerfilTipo(responseData);
        }

    }

    async #buscarEscolaridade(selected_id = null) {
        try {
            const self = this;
            let options = { firstOptionValue: null };
            selected_id ? Object.assign(options, { selectedIdOption: selected_id }) : null;
            const select = $(`#escolaridade_id${self._objConfigs.sufixo}`);
            await commonFunctions.fillSelect(select, self._objConfigs.url.baseEscolaridadeTenant, options); 0
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
            const select = $(`#estado_civil_id${self._objConfigs.sufixo}`);
            await commonFunctions.fillSelect(select, self._objConfigs.url.baseEstadoCivilTenant, options); 0
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
            const select = $(`#sexo_id${self._objConfigs.sufixo}`);
            await commonFunctions.fillSelect(select, self._objConfigs.url.baseSexoTenant, options); 0
            return true
        } catch (error) {
            return false;
        }
    }

    saveButtonAction() {
        const self = this;
        const formRegistration = $(`#form${self._objConfigs.sufixo}`);
        let data = commonFunctions.getInputsValues(formRegistration[0]);
        data.pessoa_perfil_tipo_id = self._objConfigs.data.pessoa_perfil_tipo_id;
        data.documentos = self._pessoaDocumentoModule._retornaDocumentosNaTelaSaveButonAction();
        data.perfis = self._pessoaPerfilModule._retornaPerfilsNaTelaSaveButonAction();
        data.enderecos = self._enderecoModule._retornaEnderecosNaTelaSaveButonAction();

        if (typeof self.saveButtonActionEspecificoPerfilTipo === 'function') {
            data = self.saveButtonActionEspecificoPerfilTipo(data);
        }

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
        let blnSave = commonFunctions.verificationData(data.nome, { field: formRegistration.find('input[name="nome"]'), messageInvalid: 'O campo <b>nome</b> deve ser informado.', setFocus: true });

        if (typeof self.saveVerificationsEspecificoPerfilTipo === 'function') {
            blnSave = self.saveVerificationsEspecificoPerfilTipo(data, blnSave == false, blnSave == false);
        }

        return blnSave;
    }
}
