import { CommonFunctions } from "../../commons/CommonFunctions";
import { EnumAction } from "../../commons/EnumAction";
import { ModalRegistrationAndEditing } from "../../commons/modal/ModalRegistrationAndEditing";
import { ModalContaTenant } from "../tenant/ModalContaTenant";

export class ModalContaTransferencia extends ModalRegistrationAndEditing {

    /**
     * Configuração local do modal
     */
    #objConfigs = {
        url: {
            base: window.apiRoutes.baseTransferenciaConta,
            baseContas: window.apiRoutes.baseContas,
        },
        sufixo: 'ModalContaTransferencia',
        data: {},
    };

    constructor() {
        super({
            idModal: "#ModalContaTransferencia",
        });
        this._objConfigs = Object.assign(this._objConfigs, this.#objConfigs);
        this._action = EnumAction.POST;
    }

    async modalOpen() {
        const self = this;
        try {
            await CommonFunctions.loadingModalDisplay(true, { message: 'Carregando contas...' });
            self.#addEventosPadrao();

            if (!await self.#buscarContas()) {
                return self._returnPromisseResolve();
            }
        } catch (error) {
            CommonFunctions.generateNotificationErrorCatch(error);
        } finally {
            await CommonFunctions.loadingModalDisplay(false);
        }
        await self._modalHideShow();
        return await self._modalOpen();
    }

    #addEventosPadrao() {
        const self = this;
        const modal = $(self._idModal);

        CommonFunctions.applyCustomNumberMask(modal.find('.campo-monetario'), { format: '#.##0,00', reverse: true });

        const openModalContaTenant = async function (btn, selector) {
            try {
                const objModal = new ModalContaTenant();
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
                CommonFunctions.generateNotificationErrorCatch(error);
            } finally {
                CommonFunctions.simulateLoading(btn, false);
                await self._modalHideShow();
            }
        }

        modal.find('.openModalContaTenantOrigem').on('click', async function () {
            const btn = $(this);
            CommonFunctions.simulateLoading(btn);
            await openModalContaTenant(btn ,modal.find('select[name="conta_origem_id"]'));
        });

        modal.find('.openModalContaTenantDestino').on('click', async function () {
            const btn = $(this);
            CommonFunctions.simulateLoading(btn);
            await openModalContaTenant(btn ,modal.find('select[name="conta_destino_id"]'));
        });
    }

    async #buscarContas(selected_id = null, selector = null) {
        try {
            const self = this;
            let options = selected_id ? { selectedIdOption: selected_id } : {};
            const select = (selector ? $(selector) : $(self.getIdModal).find('.selectConta'));
            await CommonFunctions.fillSelect(select, self._objConfigs.url.baseContas, options);
            return true;
        } catch (error) {
            return false;
        }
    }

    saveButtonAction() {
        const self = this;
        const formRegistration = $(self.getIdModal).find('.formRegistration');
        let data = CommonFunctions.getInputsValues(formRegistration[0]);
        data.valor = CommonFunctions.removeCommasFromCurrencyOrFraction(data.valor);
        
        if (self.#saveVerifications(data, formRegistration)) {
            self._save(data, self._objConfigs.url.base);
        }
    }

    #saveVerifications(data, formRegistration) {
        let blnSave = CommonFunctions.verificationData(data.conta_origem_id, { field: formRegistration.find('select[name="conta_origem_id"]'), messageInvalid: 'A conta de origem deve ser informada.', setFocus: true });
        blnSave = CommonFunctions.verificationData(data.conta_destino_id, { field: formRegistration.find('select[name="conta_destino_id"]'), messageInvalid: 'A conta de destino deve ser informada.', setFocus: blnSave == true, returnForcedFalse: blnSave == false });
        blnSave = CommonFunctions.verificationData(data.data_movimentacao, { field: formRegistration.find('input[name="data_movimentacao"]'), messageInvalid: 'A data de movimentação deve ser informada.', setFocus: blnSave == true, returnForcedFalse: blnSave == false });
        blnSave = CommonFunctions.verificationData(data.valor, { field: formRegistration.find('input[name="valor"]'), messageInvalid: 'O valor deve ser informado.', setFocus: blnSave == true, returnForcedFalse: blnSave == false });
        blnSave = CommonFunctions.verificationData(data.observacao, { field: formRegistration.find('input[name="observacao"]'), messageInvalid: 'Uma observação deve ser informada.', setFocus: blnSave == true, returnForcedFalse: blnSave == false });
        return blnSave;
    }
}
