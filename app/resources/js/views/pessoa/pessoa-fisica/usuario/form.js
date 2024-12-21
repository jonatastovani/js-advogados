import { commonFunctions } from "../../../../commons/commonFunctions";
import { UsuarioDomainsModule } from "../../../../modules/UsuarioDomainsModule";
import { TemplateFormPessoaFisica } from "../TemplateFormPessoaFisica";

class PagePessoaFisicaFormUsuario extends TemplateFormPessoaFisica {

    #objConfigs = {
        url: {
            base: window.apiRoutes.basePessoaFisica,
        },
        sufixo: 'PagePessoaFisicaFormUsuario',
        data: {
            pessoa_perfil_tipo_id: window.Enums.PessoaPerfilTipoEnum.USUARIO,
            pessoa_tipo_aplicavel: [
                window.Enums.PessoaTipoEnum.PESSOA_FISICA,
            ],
            user: null,
        },
    };
    _usuarioDomainsModule;

    constructor() {
        super();
        this._objConfigs.url.base = this.#objConfigs.url.base;
        this._objConfigs.sufixo = this.#objConfigs.sufixo;
        this._objConfigs.data.pessoa_perfil_tipo_id = this.#objConfigs.data.pessoa_perfil_tipo_id;
        this._objConfigs.data.pessoa_tipo_aplicavel = this.#objConfigs.data.pessoa_tipo_aplicavel;
        this._objConfigs.data.dominiosNaTela = [];
        this._objConfigs.data.user = this.#objConfigs.data.user;

        const objData = {
            objConfigs: this._objConfigs,
        }
        this._usuarioDomainsModule = new UsuarioDomainsModule(this, objData);

        this.initEvents();
    }

    initEvents() {
        const self = this;
        super.initEvents();
        self.#addEventosBotoes();
    }

    #addEventosBotoes() {
        const self = this;
        commonFunctions.eventRBCkBHidden($(`#alterar_senha_bln${self._objConfigs.sufixo}`), [{
            'div_group': `#divSenha${self._objConfigs.sufixo}`,
            'button': `#alterar_senha_bln${self._objConfigs.sufixo}`,
            'input': [
                `#password${self._objConfigs.sufixo}`,
                `#password_confirmation${self._objConfigs.sufixo}`,
            ]
        }]);
    }

    async preenchimentoEspecificoBuscaPerfilTipo(responseData) {
        const self = this;
        const form = $(`#formDados${self._objConfigs.sufixo}`);

        self._objConfigs.data.user = responseData.user;
        if (responseData?.user?.username) {
            form.find('input[name="username"]').val(responseData.user.username);
        } else {
            $(`#alterar_senha_bln${self._objConfigs.sufixo}`).prop('checked', true).trigger('change');
            $(`#rowAlterarSenhaBln${self._objConfigs.sufixo}`).remove();
        }

        if (responseData?.user?.user_tenant_domains.length) {
            responseData.user.user_tenant_domains.map(dominio => {
                // Não verifica se o limite de documentos foi atingido porque está vindo direto do banco
                self._usuarioDomainsModule._inserirDominio(dominio);
            });
        }
    }

    saveButtonActionEspecificoPerfilTipo(data) {
        const self = this;
        data.user_domains = self._usuarioDomainsModule._retornaDominiosNaTelaSaveButonAction();
        return data;
    }

    saveVerificationsEspecificoPerfilTipo(data, setFocus, returnForcedFalse) {

        const self = this;
        const formRegistration = $(`#formDados${self._objConfigs.sufixo}`);
        let blnSave = commonFunctions.verificationData(data.username, { field: formRegistration.find('input[name="username"]'), messageInvalid: 'O campo <b>usuário</b> deve ser informado.', setFocus: setFocus, returnForcedFalse: returnForcedFalse });

        if (self._objConfigs.data.user) {
            data.user = {
                id: self._objConfigs.data.user.id,
                username: self._objConfigs.data.user.username,
                domain_id: self._objConfigs.data.user.domain_id
            };
        } else {
            data.user = {};
        }
        data.user.username = data.username;

        if (
            [undefined, null].includes(self._idRegister)
            || data.password
            || !self._objConfigs.data.user?.id
            || !formRegistration.find('input[name="password"]').prop('disabled')
        ) {
            if (data.password) {
                if (data.password != data.password_confirmation) {
                    blnSave = false;
                    commonFunctions.generateNotification('Os campos <b>senha</b> e <b>confirmação</b> devem ser iguais.', 'warning');
                } else {
                    data.user.password = data.password;
                }
            } else {
                blnSave = false;
                commonFunctions.generateNotification('O campo <b>senha</b> e <b>confirmação</b> devem ser informados.', 'warning');
            }
        }

        return blnSave;
    }
}

$(function () {
    new PagePessoaFisicaFormUsuario();
});