import { commonFunctions } from "../../commons/commonFunctions";
import { enumAction } from "../../commons/enumAction";
import { modalRegistrationAndEditing } from "../../commons/modal/modalRegistrationAndEditing";
import { DateTimeHelper } from "../../helpers/DateTimeHelper";

export class modalServicoPagamentoLancamento extends modalRegistrationAndEditing {

    #objConfigs = {
        url: {
            base: undefined,
            baseContas: window.apiRoutes.baseContas,
        },
        sufixo: 'ModalServicoPagamentoLancamento',
        data: {},
    };

    constructor(options = {}) {
        super({
            idModal: "#modalServicoPagamentoLancamento",
        });

        this._objConfigs = Object.assign(this._objConfigs, this.#objConfigs);
        this._action = enumAction.POST;
        this._objConfigs.url.base = options.urlApi;
        this.#addEventosPadrao();
    }

    async modalOpen() {
        const self = this;
        if (self._dataEnvModal.idRegister) {
            await commonFunctions.loadingModalDisplay();
            await self.#buscarContas();
            if (!await self.#buscarDados()) {
                await commonFunctions.loadingModalDisplay(false);
                return await self._returnPromisseResolve();
            }
        } else {
            commonFunctions.generateNotification('ID de Lancamento não informado. Caso o erro persista, contate o desenvolvedor.', 'error');
            return await self._returnPromisseResolve();
        }
        await commonFunctions.loadingModalDisplay(false);
        await self._modalHideShow();
        return await self._modalOpen();
    }

    #addEventosPadrao() {
        const self = this;
        const modal = $(self._idModal);

        modal.find('.openModalConta').on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
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
                    if (response.selecteds.length > 0) {
                        const item = response.selecteds[0];
                        self.#buscarContas(item.id);
                    } else {
                        self.#buscarContas();
                    }
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading(btn, false);
                await self._modalHideShow();
            }
        });
    }

    async #buscarContas(selected_id = null) {
        try {
            const self = this;
            const optionsDefault = { firstOptionName: 'Conta padrão do pagamento' };
            let options = selected_id ? { selectedIdOption: selected_id } : {};
            options = Object.assign(options, optionsDefault);
            const select = $(self.getIdModal).find('select[name="conta_id"]');
            await commonFunctions.fillSelect(select, self._objConfigs.url.baseContas, options);
            return true;
        } catch (error) {
            return false;
        }
    }

    async #buscarDados() {
        const self = this;

        try {
            self._clearForm();
            self._action = enumAction.PUT;
            const response = await self._getRecurse();
            if (response?.data) {
                const responseData = response.data;
                const data_vencimento = DateTimeHelper.retornaDadosDataHora(responseData.data_vencimento, 2);
                const valor_esperado = commonFunctions.formatWithCurrencyCommasOrFraction(responseData.valor_esperado);

                self._updateModalTitle(`Alterar: <b>${responseData.descricao_automatica}</b>`);
                const form = $(self.getIdModal).find('.formRegistration');
                form.find('.pDataVencimento').html(data_vencimento);
                form.find('.pValor').html(valor_esperado);
                form.find('input[name="observacao"]').val(responseData.observacao);
                form.find('select[name="conta_id"]').val(responseData.conta_id ?? 0).trigger('change');
                return true;
            }
            return false;
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
            return false;
        }
    }

    saveButtonAction() {
        const self = this;
        const formRegistration = $(self.getIdModal).find('.formRegistration');
        let data = commonFunctions.getInputsValues(formRegistration[0]);

        if (data.conta_id == 0) {
            delete data.conta_id;
        }
        self._save(data, self._objConfigs.url.base);
    }
}