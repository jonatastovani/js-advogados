import { commonFunctions } from "../../../commons/commonFunctions";
import { enumAction } from "../../../commons/enumAction";
import { TemplateForm } from "../../../commons/templates/TemplateForm";
import { MasksAndValidateHelpers } from "../../../helpers/MasksAndValidateHelpers";
import { URLHelper } from "../../../helpers/URLHelper";
import { UUIDHelper } from "../../../helpers/UUIDHelper";
import { EnderecoModule } from "../../../modules/EnderecoModule";
import { PessoaDocumentoModule } from "../../../modules/PessoaDocumentoModule";
import { PessoaPerfilModule } from "../../../modules/PessoaPerfilModule";

export class TemplateFormPessoaJuridica extends TemplateForm {

    constructor(objSuper) {
        const objConfigs = {
            url: {
                basePessoaPerfil: window.apiRoutes.basePessoaPerfil,
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

        commonFunctions.applyCustomNumberMask($('.campo-monetario'), { format: '#.##0,00', reverse: true });
        MasksAndValidateHelpers.cpfMask($('.campo-cpf'));
        MasksAndValidateHelpers.addEventCheckCPF({ selector: $('.campo-cpf'), event: 'focusout' });

        if (typeof self.addEventosBotoesEspecificoPerfilTipo === 'function') {
            self.addEventosBotoesEspecificoPerfilTipo();
        }
    }

    async preenchimentoDados(response, options = {}) {
        const self = this;
        const form = $(options.form);

        const responseData = response.data;
        const pessoaDados = responseData.pessoa.pessoa_dados;

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

        if (responseData.pessoa?.enderecos.length) {
            responseData.pessoa.enderecos.map(endereco => {
                self._enderecoModule._inserirEndereco(endereco, false);
            });
        }

        if (typeof self.preenchimentoEspecificoBuscaPerfilTipo === 'function') {
            await self.preenchimentoEspecificoBuscaPerfilTipo(responseData);
        }

    }

    saveButtonAction() {
        const self = this;
        const formRegistration = $(`#form${self._objConfigs.sufixo}`);
        let data = commonFunctions.getInputsValues(formRegistration[0]);
        data.pessoa_perfil_tipo_id = self._objConfigs.data.pessoa_perfil_tipo_id;
        data.documentos = self._pessoaDocumentoModule._retornaDocumentosNaTelaSaveButonAction();
        data.perfis = self._pessoaPerfilModule._retornaPerfilsNaTelaSaveButonAction();
        data.capital_social = data.capital_social ? commonFunctions.removeCommasFromCurrencyOrFraction(data.capital_social) : null;
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
        let blnSave = commonFunctions.verificationData(data.razao_social, { field: formRegistration.find('input[name="razao_social"]'), messageInvalid: 'O campo <b>Razão Social</b> deve ser informado.', setFocus: true });

        blnSave = commonFunctions.verificationData(data.nome_fantasia, { field: formRegistration.find('input[name="nome_fantasia"]'), messageInvalid: 'O campo <b>Nome Fantasia</b> deve ser informado.', setFocus: true });

        if (typeof self.saveVerificationsEspecificoPerfilTipo === 'function') {
            blnSave = self.saveVerificationsEspecificoPerfilTipo(data, blnSave == false, blnSave == false);
        }

        return blnSave;
    }
}