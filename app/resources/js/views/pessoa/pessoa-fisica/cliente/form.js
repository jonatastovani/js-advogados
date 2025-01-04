import { TemplateFormPessoaFisica } from "../TemplateFormPessoaFisica";

class PagePessoaFisicaFormCliente extends TemplateFormPessoaFisica {

    constructor() {
        const objConfigs = {
            url: {
                base: window.apiRoutes.basePessoaFisica,
            },
            sufixo: 'PagePessoaFisicaFormCliente',
            data: {
                pessoa_perfil_tipo_id: window.Enums.PessoaPerfilTipoEnum.CLIENTE,
                pessoa_tipo_aplicavel: [
                    window.Enums.PessoaTipoEnum.PESSOA_FISICA,
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
    new PagePessoaFisicaFormCliente();
});