import { connectAjax } from "../../../../commons/connectAjax";
import { enumAction } from "../../../../commons/enumAction";
import { TemplateFormPessoaJuridica } from "../../../pessoa/pessoa-juridica/TemplateFormPessoaJuridica";

class PagePessoaJuridicaFormEmpresa extends TemplateFormPessoaJuridica {

    #objConfigs = {
        url: {
            base: window.apiRoutes.basePessoaJuridica,
        },
        sufixo: 'PagePessoaJuridicaFormEmpresa',
        data: {
            pessoa_perfil_tipo_id: window.Enums.PessoaPerfilTipoEnum.EMPRESA,
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

    async initEvents() {
        const self = this;
        await self._carregamentoModulos();

        await self.buscaEmpresa();

        self._addEventosBotoes();
    }

    async buscaEmpresa() {
        const self = this;
        const objConn = new connectAjax(self._objConfigs.url.basePessoaPerfil);
        objConn.setParam('empresa');
        const response = await objConn.getRequest();

        if (response?.data?.id) {
            self._idRegister = response.data.id;
            self._action = enumAction.PUT;
            await self._buscarDados();
        } else {
            self._action = enumAction.POST;
            self._pessoaPerfilModule._inserirPerfilObrigatorio();
        }
    }
}

$(function () {
    new PagePessoaJuridicaFormEmpresa();
});