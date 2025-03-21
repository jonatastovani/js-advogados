import { CommonFunctions } from "../../commons/CommonFunctions";
import { ModalDefault } from "../../commons/modal/ModalDefault";
import { ModalServicoPagamento } from "./ModalServicoPagamento";

export class ModalSelecionarPagamentoTipo extends ModalDefault {

    /**
     * Configuração local do modal
     */
    #objConfigs = {
        url: {
            base: window.apiRoutes.basePagamentoTipoTenants,
            baseServico: undefined
        },
        sufixo: 'ModalSelecionarPagamentoTipo',
        domainCustom: {
            applyBln: true,
            inheritedBln: true,
        },
    };

    constructor(urlBaseServico) {
        super({
            idModal: "#ModalSelecionarPagamentoTipo",
        });

        this._objConfigs = CommonFunctions.deepMergeObject(this._objConfigs, this.#objConfigs);
        this._objConfigs.url.baseServico = urlBaseServico;
        this.#addEventosPadrao();
    }

    async modalOpen() {
        const self = this;
        await CommonFunctions.loadingModalDisplay(true, { message: 'Carregando tipos de pagamento...', title: 'Aguarde...', elementFocus: null });

        if (!self._checkDomainCustomInherited()) {
            await CommonFunctions.loadingModalDisplay(false);
            return await self._returnPromisseResolve()
        };

        await self.#buscarPagamentoTipos($(self.getIdModal).find('select[name="pagamento_tipo_tenant_id"]'));
        await CommonFunctions.loadingModalDisplay(false);
        await self._modalHideShow();
        return await self._modalOpen();
    }

    _modalReset() {
        const self = this;
        const modal = $(self.getIdModal);
        const formRegistration = modal.find('.formRegistration');
        formRegistration.find('select').val(0);
        formRegistration[0].reset();
        formRegistration.find('input, select, textarea').removeClass('is-valid').removeClass('is-invalid');
    }

    #addEventosPadrao() {
        this.#eventosBotoes();
    }

    #eventosBotoes() {
        const self = this;
        const modal = $(self._idModal);

        modal.find('.openModalPagamentoTipoTenant').on('click', async function () {
            const btn = $(this);
            CommonFunctions.simulateLoading(btn);
            try {
                CommonFunctions.generateNotification('Funcionalidade para criar e alterar formas de pagamento, em desenvolvimento.', 'warning');
                // const objModal = new ModalContaTenant();
                // objModal.setDataEnvModal = {
                //     attributes: {
                //         select: {
                //             quantity: 1,
                //             autoReturn: true,
                //         }
                //     }
                // }
                // await self._modalHideShow(false);
                // const response = await objModal.modalOpen();
                // if (response.refresh) {
                //     if (response.selecteds.length > 0) {
                //         const item = response.selecteds[0];
                //         self.#buscarContas(item.id);
                //     } else {
                //         self.#buscarContas();
                //     }
                // }
            } catch (error) {
                CommonFunctions.generateNotificationErrorCatch(error);
            } finally {
                CommonFunctions.simulateLoading(btn, false);
                // await self._modalHideShow();
            }
        });
    }

    async #buscarPagamentoTipos(selected_id = null) {
        try {
            const self = this;
            let options = { outInstanceParentBln: true };
            selected_id ? options.selectedIdOption = selected_id : null;
            const select = $(`#pagamento_tipo_tenant_id${self.getSufixo}`);
            await CommonFunctions.fillSelect(select, self._objConfigs.url.base, options);
            return true;
        } catch (error) {
            return false;
        }
    }

    async saveButtonAction() {
        const self = this;
        const formRegistration = $(self.getIdModal).find('.formRegistration');
        let data = CommonFunctions.getInputsValues(formRegistration[0]);

        if (self.#saveVerifications(data, formRegistration)) {
            try {
                await self._modalHideShow(false);
                const objModal = new ModalServicoPagamento({ urlApi: `${self._objConfigs.url.baseServico}/pagamentos` });
                objModal.setDataEnvModal = self._checkDomainCustomInheritDataEnvModal({
                    pagamento_tipo_tenant_id: data.pagamento_tipo_tenant_id,
                });
                self._promisseReturnValue = await objModal.modalOpen();
            } catch (error) {
                CommonFunctions.generateNotificationErrorCatch(error);
            } finally {
                self._setEndTimer = true;
            }
        }
    }

    #saveVerifications(data, formRegistration) {
        let blnSave = CommonFunctions.verificationData(data.pagamento_tipo_tenant_id, { field: formRegistration.find('select[name="pagamento_tipo_tenant_id"]'), messageInvalid: 'Selecione um tipo de pagamento.', setFocus: true });
        return blnSave;
    }

}
