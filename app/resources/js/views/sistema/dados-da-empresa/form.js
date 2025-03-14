import { connectAjax } from "../../../commons/connectAjax";
import { enumAction } from "../../../commons/enumAction";
import { TemplateFormPessoaJuridica } from "../../pessoa/pessoa-juridica/TemplateFormPessoaJuridica";

class PagePessoaJuridicaFormEmpresa extends TemplateFormPessoaJuridica {

    constructor() {
        const objConfigs = {
            url: {
                base: window.apiRoutes.basePessoaJuridica,
                // baseContas: window.apiRoutes.baseContas,
            },
            sufixo: 'PagePessoaJuridicaFormEmpresa',
            data: {
                pessoa_perfil_tipo_id: window.Enums.PessoaPerfilTipoEnum.EMPRESA,
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

    async initEvents() {
        const self = this;
        await self._carregamentoModulos();

        await self.buscaEmpresa();

        self._addEventosBotoes();
        // self.#buscarContas();
    }

    async addEventosBotoesEspecificoPerfilTipo() {
        const self = this;

        // $(`#openModalConta${self._objConfigs.sufixo}`).on('click', async function () {
        //     const btn = $(this);
        //     commonFunctions.simulateLoading(btn);
        //     try {
        //         const objModal = new ModalContaTenant();
        //         objModal.setDataEnvModal = {
        //             attributes: {
        //                 select: {
        //                     quantity: 1,
        //                     autoReturn: true,
        //                 }
        //             }
        //         }
        //         await self._modalHideShow(false);
        //         const response = await objModal.modalOpen();
        //         if (response.refresh) {
        //             if (response.selecteds.length > 0) {
        //                 const item = response.selecteds[0];
        //                 self.#buscarContas(item.id);
        //             } else {
        //                 self.#buscarContas();
        //             }
        //         }
        //     } catch (error) {
        //         commonFunctions.generateNotificationErrorCatch(error);
        //     } finally {
        //         commonFunctions.simulateLoading(btn, false);
        //         await self._modalHideShow();
        //     }
        // });
    }

    // async #buscarContas(selected_id = null) {
    //     try {
    //         const self = this;
    //         let options = selected_id ? { selectedIdOption: selected_id } : {};
    //         const select = $(`#conta_id${self.getSufixo}`);
    //         await commonFunctions.fillSelect(select, self._objConfigs.url.baseContas, options);
    //         return true;
    //     } catch (error) {
    //         return false;
    //     }
    // }

    async buscaEmpresa() {
        const self = this;
        const objConn = new connectAjax(self._objConfigs.url.basePessoaPerfil);
        objConn.setParam('empresa');
        const response = await objConn.getRequest();

        if (response?.data?.id) {
            self._idRegister = response.data.id;
            self._action = enumAction.PUT;
            await self._buscarDados({
                urlApi: self._objConfigs.url.basePessoaPerfil,
            });
        } else {
            self._action = enumAction.POST;
            self._pessoaPerfilModule._inserirPerfilObrigatorio();
        }
    }
}

$(function () {
    new PagePessoaJuridicaFormEmpresa();
});