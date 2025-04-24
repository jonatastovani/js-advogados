import { TemplateFormPessoaJuridica } from "../TemplateFormPessoaJuridica";

class PagePessoaJuridicaFormTerceiro extends TemplateFormPessoaJuridica {

    constructor() {
        const objConfigs = {
            url: {
                base: window.apiRoutes.basePessoaJuridica,
            },
            sufixo: 'PagePessoaJuridicaFormTerceiro',
            data: {
                pessoa_perfil_tipo_id: window.Enums.PessoaPerfilTipoEnum.TERCEIRO,
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
    new PagePessoaJuridicaFormTerceiro();
});