import { TemplateFormPessoaFisica } from "../TemplateFormPessoaFisica";

class PagePessoaFisicaFormParceiro extends TemplateFormPessoaFisica {

    constructor() {
        const objConfigs = {
            url: {
                base: window.apiRoutes.basePessoaFisica,
            },
            sufixo: 'PagePessoaFisicaFormParceiro',
            data: {
                pessoa_perfil_tipo_id: window.Enums.PessoaPerfilTipoEnum.PARCEIRO,
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
    new PagePessoaFisicaFormParceiro();
});