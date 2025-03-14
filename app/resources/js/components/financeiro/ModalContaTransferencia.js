import { commonFunctions } from "../../commons/commonFunctions";
import { enumAction } from "../../commons/enumAction";
import { modalRegistrationAndEditing } from "../../commons/modal/modalRegistrationAndEditing";
import { modalContaTenant } from "../tenant/modalContaTenant";

export class modalContaTransferencia extends modalRegistrationAndEditing {

    /**
     * Configuração local do modal
     */
    #objConfigs = {
        url: {
            base: window.apiRoutes.baseTransferenciaConta,
            baseContas: window.apiRoutes.baseContas,
        },
        sufixo: 'modalContaTransferencia',
        data: {},
    };

    constructor() {
        super({
            idModal: "#modalContaTransferencia",
        });
        this._objConfigs = Object.assign(this._objConfigs, this.#objConfigs);
        this._action = enumAction.POST;
    }

    async modalOpen() {
        const self = this;
        try {
            await commonFunctions.loadingModalDisplay(true, { message: 'Carregando contas...' });
            self.#addEventosPadrao();

            if (!await self.#buscarContas()) {
                return self._returnPromisseResolve();
            }
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
        } finally {
            await commonFunctions.loadingModalDisplay(false);
        }
        await self._modalHideShow();
        return await self._modalOpen();
    }

    #addEventosPadrao() {
        const self = this;
        const modal = $(self._idModal);

        commonFunctions.applyCustomNumberMask(modal.find('.campo-monetario'), { format: '#.##0,00', reverse: true });

        const openModalContaTenant = async function (btn, selector) {
            try {
                const objModal = new modalContaTenant();
                objModal.setDataEnvModal = {
                    attributes: {
                        select: {
                            quantity: 1,
                            autoReturn: true,
                        }
                    }
                }
                await self._modalHideShow(false);
                const response = await objModal.modalOpen();
                if (response.refresh) {
                    if (response.selected) {
                        self.#buscarContas(response.selected.id, selector);
                    } else {
                        self.#buscarContas(null, selector);
                    }
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading(btn, false);
                await self._modalHideShow();
            }
        }

        modal.find('.openModalContaTenantOrigem').on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            await openModalContaTenant(btn ,modal.find('select[name="conta_origem_id"]'));
        });

        modal.find('.openModalContaTenantDestino').on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            await openModalContaTenant(btn ,modal.find('select[name="conta_destino_id"]'));
        });
    }

    async #buscarContas(selected_id = null, selector = null) {
        try {
            const self = this;
            let options = selected_id ? { selectedIdOption: selected_id } : {};
            const select = (selector ? $(selector) : $(self.getIdModal).find('.selectConta'));
            await commonFunctions.fillSelect(select, self._objConfigs.url.baseContas, options);
            return true;
        } catch (error) {
            return false;
        }
    }

    saveButtonAction() {
        const self = this;
        const formRegistration = $(self.getIdModal).find('.formRegistration');
        let data = commonFunctions.getInputsValues(formRegistration[0]);
        data.valor = commonFunctions.removeCommasFromCurrencyOrFraction(data.valor);
        
        if (self.#saveVerifications(data, formRegistration)) {
            self._save(data, self._objConfigs.url.base);
        }
    }

    #saveVerifications(data, formRegistration) {
        let blnSave = commonFunctions.verificationData(data.conta_origem_id, { field: formRegistration.find('select[name="conta_origem_id"]'), messageInvalid: 'A conta de origem deve ser informada.', setFocus: true });
        blnSave = commonFunctions.verificationData(data.conta_destino_id, { field: formRegistration.find('select[name="conta_destino_id"]'), messageInvalid: 'A conta de destino deve ser informada.', setFocus: blnSave == true, returnForcedFalse: blnSave == false });
        blnSave = commonFunctions.verificationData(data.data_movimentacao, { field: formRegistration.find('input[name="data_movimentacao"]'), messageInvalid: 'A data de movimentação deve ser informada.', setFocus: blnSave == true, returnForcedFalse: blnSave == false });
        blnSave = commonFunctions.verificationData(data.valor, { field: formRegistration.find('input[name="valor"]'), messageInvalid: 'O valor deve ser informado.', setFocus: blnSave == true, returnForcedFalse: blnSave == false });
        blnSave = commonFunctions.verificationData(data.observacao, { field: formRegistration.find('input[name="observacao"]'), messageInvalid: 'Uma observação deve ser informada.', setFocus: blnSave == true, returnForcedFalse: blnSave == false });
        return blnSave;
    }
}
