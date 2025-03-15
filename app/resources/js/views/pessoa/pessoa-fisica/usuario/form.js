import { CommonFunctions } from "../../../../commons/CommonFunctions";
import { UsuarioDomainsModule } from "../../../../modules/UsuarioDomainsModule";
import { TemplateFormPessoaFisica } from "../TemplateFormPessoaFisica";

class PagePessoaFisicaFormUsuario extends TemplateFormPessoaFisica {

    _usuarioDomainsModule;

    constructor() {
        const objConfigs = {
            url: {
                base: window.apiRoutes.basePessoaFisica,
            },
            sufixo: 'PagePessoaFisicaFormUsuario',
            data: {
                pessoa_perfil_tipo_id: window.Enums.PessoaPerfilTipoEnum.USUARIO,
                pessoa_tipo_aplicavel: [
                    window.Enums.PessoaTipoEnum.PESSOA_FISICA,
                ],
                dominiosNaTela: [],
                user: null,
            },
        };

        super({
            objConfigs: objConfigs
        });

        const objData = {
            objConfigs: this._objConfigs,
        }
        this._usuarioDomainsModule = new UsuarioDomainsModule(this, objData);

        this.initEvents();
    }

    initEvents() {
        super.initEvents();
    }

    async preenchimentoEspecificoBuscaPerfilTipo(responseData) {
        const self = this;
        const form = $(`#form${self._objConfigs.sufixo}`);

        self._objConfigs.data.user = responseData.user;
        if (responseData?.user?.email) {
            form.find('input[name="email"]').val(responseData.user.email);
            form.find('input[name="name"]').val(responseData.user.name);
        }

        if (responseData?.user?.user_tenant_domains.length) {
            responseData.user.user_tenant_domains.map(dominio => {
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
        const formRegistration = $(`#form${self._objConfigs.sufixo}`);

        let blnSave = CommonFunctions.verificationData(data.email, {
            field: formRegistration.find('input[name="email"]'),
            messageInvalid: 'O campo <b>email</b> deve ser informado.',
            setFocus: setFocus,
            returnForcedFalse: returnForcedFalse
        });

        blnSave = CommonFunctions.verificationData(data.name, {
            field: formRegistration.find('input[name="name"]'),
            messageInvalid: 'O campo <b>Nome de exibição</b> deve ser informado.',
            setFocus: blnSave == false,
            returnForcedFalse: blnSave == false
        });

        if (self._objConfigs.data.user) {
            data.user = {
                id: self._objConfigs.data.user.id,
                name: self._objConfigs.data.user.name,
                email: self._objConfigs.data.user.email,
                domain_id: self._objConfigs.data.user.domain_id
            };
        } else {
            data.user = {};
        }
        data.user.name = data.name;
        data.user.email = data.email;

        return blnSave;
    }
}

$(function () {
    new PagePessoaFisicaFormUsuario();
});