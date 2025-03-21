import { CommonFunctions } from "../../commons/CommonFunctions";
import { EnumAction } from "../../commons/EnumAction";
import { ModalRegistrationAndEditing } from "../../commons/modal/ModalRegistrationAndEditing";
import { DateTimeHelper } from "../../helpers/DateTimeHelper";
import { ModalFormaPagamentoTenant } from "../tenant/ModalFormaPagamentoTenant";

export class ModalServicoPagamentoLancamento extends ModalRegistrationAndEditing {

    #objConfigs = {
        url: {
            base: undefined,
            baseFormaPagamento: window.apiRoutes.baseFormaPagamento,
        },
        sufixo: 'ModalServicoPagamentoLancamento',
        data: {},
    };

    constructor(options = {}) {
        super({
            idModal: "#ModalServicoPagamentoLancamento",
        });

        this._objConfigs = Object.assign(this._objConfigs, this.#objConfigs);
        this._action = EnumAction.POST;
        this._objConfigs.url.base = options.urlApi;
        this.#addEventosPadrao();
    }

    async modalOpen() {
        const self = this;
        if (self._dataEnvModal.idRegister) {
            await CommonFunctions.loadingModalDisplay();
            await self.#buscarFormaPagamento();
            if (!await self.#buscarDados()) {
                await CommonFunctions.loadingModalDisplay(false);
                return await self._returnPromisseResolve();
            }
        } else {
            CommonFunctions.generateNotification('ID de Lancamento não informado. Caso o erro persista, contate o desenvolvedor.', 'error');
            return await self._returnPromisseResolve();
        }
        await CommonFunctions.loadingModalDisplay(false);
        await self._modalHideShow();
        return await self._modalOpen();
    }

    #addEventosPadrao() {
        const self = this;
        const modal = $(self._idModal);

        modal.find('.openModalFormaPagamento').on('click', async function () {
            const btn = $(this);
            CommonFunctions.simulateLoading(btn);
            try {
                const objModal = new ModalFormaPagamentoTenant();
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
                        self.#buscarFormaPagamento(response.selected.id);
                    } else {
                        self.#buscarFormaPagamento();
                    }
                }
            } catch (error) {
                CommonFunctions.generateNotificationErrorCatch(error);
            } finally {
                CommonFunctions.simulateLoading(btn, false);
                await self._modalHideShow();
            }
        });
    }

    async #buscarFormaPagamento(selected_id = null) {
        try {
            const self = this;
            let options = {
                outInstanceParentBln: true,
                firstOptionName: 'Forma de pagamento padrão do Pagamento',
            };
            selected_id ? options.selectedIdOption = selected_id : null;
            const select = $(self.getIdModal).find('select[name="forma_pagamento_id"]');
            await CommonFunctions.fillSelect(select, self._objConfigs.url.baseFormaPagamento, options);
            return true;
        } catch (error) {
            return false;
        }
    }

    async #buscarDados() {
        const self = this;

        try {
            self._clearForm();
            self._action = EnumAction.PUT;
            const response = await self._getRecurse();
            if (response?.data) {
                const responseData = response.data;
                const data_vencimento = DateTimeHelper.retornaDadosDataHora(responseData.data_vencimento, 2);
                const valor_esperado = CommonFunctions.formatWithCurrencyCommasOrFraction(responseData.valor_esperado);

                self._updateModalTitle(`Alterar: <b>${responseData.descricao_automatica}</b>`);
                const form = $(self.getIdModal).find('.formRegistration');
                form.find('.pDataVencimento').html(data_vencimento);
                form.find('.pValor').html(valor_esperado);
                form.find('input[name="observacao"]').val(responseData.observacao);
                form.find('select[name="forma_pagamento_id"]').val(responseData.forma_pagamento_id ?? 0).trigger('change');
                return true;
            }
            return false;
        } catch (error) {
            CommonFunctions.generateNotificationErrorCatch(error);
            return false;
        }
    }

    saveButtonAction() {
        const self = this;
        const formRegistration = $(self.getIdModal).find('.formRegistration');
        let data = CommonFunctions.getInputsValues(formRegistration[0]);

        if (data.forma_pagamento_id == 0) {
            delete data.forma_pagamento_id;
        }
        self._save(data, self._objConfigs.url.base);
    }
}
