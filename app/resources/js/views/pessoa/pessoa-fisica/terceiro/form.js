import { TemplateFormPessoaFisica } from "../TemplateFormPessoaFisica";

class PagePessoaFisicaFormTerceiro extends TemplateFormPessoaFisica {

    constructor() {
        const objConfigs = {
            url: {
                base: window.apiRoutes.basePessoaFisica,
            },
            sufixo: 'PagePessoaFisicaFormTerceiro',
            data: {
                pessoa_perfil_tipo_id: window.Enums.PessoaPerfilTipoEnum.TERCEIRO,
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
    new PagePessoaFisicaFormTerceiro();
});