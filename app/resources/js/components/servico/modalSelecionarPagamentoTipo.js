import { commonFunctions } from "../../commons/commonFunctions";
import { modalDefault } from "../../commons/modal/modalDefault";
import { modalServicoPagamento } from "./modalServicoPagamento";

export class modalSelecionarPagamentoTipo extends modalDefault {

    /**
     * Configuração local do modal
     */
    #objConfigs = {
        url: {
            base: window.apiRoutes.basePagamentoTipoTenants,
            baseServico: undefined
        },
        sufixo: 'ModalSelecionarPagamentoTipo',
    };

    constructor(urlBaseServico) {
        super({
            idModal: "#modalSelecionarPagamentoTipo",
        });

        this._objConfigs = Object.assign(this._objConfigs, this.#objConfigs);
        this._objConfigs.url.baseServico = urlBaseServico;
        this.#addEventosPadrao();
    }

    async modalOpen() {
        const self = this;
        await commonFunctions.loadingModalDisplay(true, { message: 'Carregando tipos de pagamento...', title: 'Aguarde...', elementFocus: null });
        await self.#buscarPagamentoTipos($(self.getIdModal).find('select[name="pagamento_tipo_tenant_id"]'));
        await commonFunctions.loadingModalDisplay(false);
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
            commonFunctions.simulateLoading(btn);
            try {
                commonFunctions.generateNotification('Funcionalidade para criar e alterar formas de pagamento, em desenvolvimento.', 'warning');
                // const objModal = new modalConta();
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
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading(btn, false);
                // await self._modalHideShow();
            }
        });
    }

    async #buscarPagamentoTipos(selected_id = null) {
        const self = this;
        let options = selected_id ? { selectedIdOption: selected_id } : {};
        const selModulo = $(self.getIdModal).find('select[name="pagamento_tipo_tenant_id"]');
        await commonFunctions.fillSelect(selModulo, self._objConfigs.url.base, options);
    }

    async saveButtonAction() {
        const self = this;
        const formRegistration = $(self.getIdModal).find('.formRegistration');
        let data = commonFunctions.getInputsValues(formRegistration[0]);

        if (self.#saveVerifications(data, formRegistration)) {
            try {
                await self._modalHideShow(false);
                const objModal = new modalServicoPagamento({ urlApi: `${self._objConfigs.url.baseServico}/pagamentos` });
                objModal._dataEnvModal = {
                    pagamento_tipo_tenant_id: data.pagamento_tipo_tenant_id,
                }
                self._promisseReturnValue = await objModal.modalOpen();
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                self._setEndTimer = true;
            }
        }
    }

    #saveVerifications(data, formRegistration) {
        let blnSave = commonFunctions.verificationData(data.pagamento_tipo_tenant_id, { field: formRegistration.find('select[name="pagamento_tipo_tenant_id"]'), messageInvalid: 'Selecione um tipo de pagamento.', setFocus: true });
        return blnSave;
    }

}