import { TemplateFormPessoaJuridica } from "../TemplateFormPessoaJuridica";

class PagePessoaJuridicaFormCliente extends TemplateFormPessoaJuridica {

    constructor() {
        const objConfigs = {
            url: {
                base: window.apiRoutes.basePessoaJuridica,
            },
            sufixo: 'PagePessoaJuridicaFormCliente',
            data: {
                pessoa_perfil_tipo_id: window.Enums.PessoaPerfilTipoEnum.CLIENTE,
                pessoa_tipo_aplicavel: [
                    window.Enums.PessoaTipoEnum.PESSOA_JURIDICA,
                ],
            },
        };

        super({
            objConfigs: objConfigs
        });
        this.initEvents();
    }

}

$(function () {
    new PagePessoaJuridicaFormCliente();
});