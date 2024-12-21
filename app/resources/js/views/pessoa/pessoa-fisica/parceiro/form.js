import { TemplateFormPessoaFisica } from "../TemplateFormPessoaFisica";

class PagePessoaFisicaFormParceiro extends TemplateFormPessoaFisica {

    #objConfigs = {
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

    constructor() {
        super();
        this._objConfigs.url.base = this.#objConfigs.url.base;
        this._objConfigs.sufixo = this.#objConfigs.sufixo;
        this._objConfigs.data.pessoa_perfil_tipo_id = this.#objConfigs.data.pessoa_perfil_tipo_id;
        this._objConfigs.data.pessoa_tipo_aplicavel = this.#objConfigs.data.pessoa_tipo_aplicavel;
        this.initEvents();
    }

}

$(function () {
    new PagePessoaFisicaFormParceiro();
});