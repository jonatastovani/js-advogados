import { commonFunctions } from "../../commons/commonFunctions";
import { modalDefault } from "../../commons/modal/modalDefault";

export class modalSelecionarConta extends modalDefault {

    /**
     * Configuração local do modal
     */
    #objConfigs = {
        url: {
            base: window.apiRoutes.baseContas,
        },
        sufixo: 'ModalSelecionarConta',
    };

    constructor() {
        super({
            idModal: "#modalSelecionarConta",
        });

        this._objConfigs = commonFunctions.deepMergeObject(this._objConfigs, this.#objConfigs);
        this.#addEventosPadrao();
    }

    async modalOpen() {
        const self = this;
        let blnOpen = false;
        await commonFunctions.loadingModalDisplay(true, { message: 'Carregando contas...', title: 'Aguarde...', elementFocus: null });

        try {
            blnOpen = await self.#buscarContas();
        } catch (error) {
            blnOpen = false;
        } finally {
            await commonFunctions.loadingModalDisplay(false);
        }
        
        if (!blnOpen) {
            return await self._returnPromisseResolve();
        }
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

        modal.find('.openModalDocumentoTipoTenant').on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                commonFunctions.generateNotification('Funcionalidade para criar e alterar formas de documento, em desenvolvimento.', 'warning');
                // const objModal = new modalContaTenant();
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

    async #buscarContas(selected_id = null) {
        try {
            const self = this;
            let options = selected_id ? { selectedIdOption: selected_id } : {};
            const select = $(`#conta_debito_id${self.getSufixo}`);
            await commonFunctions.fillSelect(select, self._objConfigs.url.base, options);
            return true;
        } catch (error) {
            return false;
        }
    }

    async saveButtonAction() {
        const self = this;
        const formRegistration = $(self.getIdModal).find('.formRegistration');
        let data = commonFunctions.getInputsValues(formRegistration[0]);
        if (self.#saveVerifications(data, formRegistration)) {
            try {
                await self._modalHideShow(false);
                const objModal = new modalPessoaDocumento();
                objModal._dataEnvModal = {
                    documento_tipo_tenant_id: data.documento_tipo_tenant_id,
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
        return commonFunctions.verificationData(data.documento_tipo_tenant_id, { field: formRegistration.find('select[name="documento_tipo_tenant_id"]'), messageInvalid: 'Selecione um tipo de documento.', setFocus: true });
    }

}