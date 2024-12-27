import { TemplateFormPessoaJuridica } from "../TemplateFormPessoaJuridica";

class PagePessoaJuridicaFormCliente extends TemplateFormPessoaJuridica {

    #objConfigs = {
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
    new PagePessoaJuridicaFormCliente();
});