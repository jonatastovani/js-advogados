import { CommonFunctions } from "../../commons/CommonFunctions";
import { EnumAction } from "../../commons/EnumAction";
import { ModalRegistrationAndEditing } from "../../commons/modal/ModalRegistrationAndEditing";
import { DateTimeHelper } from "../../helpers/DateTimeHelper";
import { UUIDHelper } from "../../helpers/UUIDHelper";
import { ModalFormaPagamentoTenant } from "../tenant/ModalFormaPagamentoTenant";

export class ModalServicoPagamentoLancamento extends ModalRegistrationAndEditing {

    #objConfigs = {
        url: {
            base: undefined,
            baseFormaPagamento: window.apiRoutes.baseFormaPagamento,
        },
        sufixo: 'ModalServicoPagamentoLancamento',
        data: {},
        domainCustom: {
            applyBln: true,
        },
        modeReturn: 'object',
    };

    constructor(options = {}) {
        super({
            idModal: "#ModalServicoPagamentoLancamento",
        });

        CommonFunctions.deepMergeObject(this._objConfigs, this.#objConfigs);

        this._objConfigs.url.base = options.urlApi ?? undefined;
    }

    async modalOpen() {
        const self = this;

        if (self._dataEnvModal.idRegister) {
            await CommonFunctions.loadingModalDisplay();
            if (!await self.#buscarDados()) {
                await CommonFunctions.loadingModalDisplay(false);
                return await self._returnPromisseResolve();
            }
            self._objConfigs.modeReturn = false;

            // Verifica domínio customizado somente quando é uma edição de registro já salvo
            self._queueCheckDomainCustom.setReady();

        } else if (self._dataEnvModal.register) {
            if (!await self.#preencherDados(self._dataEnvModal.register)) {
                await CommonFunctions.loadingModalDisplay(false);
                return await self._returnPromisseResolve();
            }
            self._objConfigs.modeReturn = 'object';

        } else {
            CommonFunctions.generateNotification('ID de Lançamento ou Objeto de Lançamento não informado. Caso o erro persista, contate o desenvolvedor.', 'error');
            return await self._returnPromisseResolve();
        }

        this.#addEventosPadrao();

        await CommonFunctions.loadingModalDisplay(false);
        await self._modalHideShow();
        return await self._modalOpen();
    }

    #addEventosPadrao() {
        const self = this;
        const modal = $(self._idModal);

        CommonFunctions.handleModal(self, modal.find('.openModalFormaPagamento'), ModalFormaPagamentoTenant, self.#buscarFormaPagamento.bind(self));

        CommonFunctions.applyCustomNumberMask(modal.find('input[name="valor_esperado"]'), { format: '#.##0,00', reverse: true });

        const camposPersonalizarLancamento = modal.find('.campos-personalizar-lancamento');
        const isModoObjeto = self._objConfigs.modeReturn === 'object';

        if (isModoObjeto) {
            const categoriaId = self._dataEnvModal?.register?.lancamento_categoria_id;
            const categoria = window.Details.LancamentosCategoriaEnum.find(x => x.id == categoriaId);
            const camposPermitidos = categoria?.configuracao?.personalizar_lancamento?.campos_permitidos || [];

            const liberarTodos = camposPermitidos.includes('*');

            camposPersonalizarLancamento.each((_, element) => {
                const podeEditar = liberarTodos || camposPermitidos.includes(element.name);
                $(element).prop('readonly', !podeEditar);
            });
            modal.find('.divInfoLancamentoPersonalizado').html(categoria?.descricao ? `<div class="form-text mt-1 fst-italic">${categoria?.descricao}</div>` : '');
        } else {
            camposPersonalizarLancamento.prop('readonly', true);
            modal.find('.divInfoLancamentoPersonalizado').html('');
        }

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
                return self.#preencherDados(response.data);
            }
            return false;
        } catch (error) {
            CommonFunctions.generateNotificationErrorCatch(error);
            return false;
        }
    }

    async #preencherDados(lancamento) {
        const self = this;

        try {
            const data_vencimento = lancamento.data_vencimento ?? DateTimeHelper.retornaDadosDataHora(new Date(), 1);
            const valor_esperado = CommonFunctions.formatWithCurrencyCommasOrFraction(lancamento.valor_esperado ?? 0);

            self._updateModalTitle(`Alterar: <b>${lancamento.descricao_automatica}</b>`);
            const form = $(self.getIdModal).find('.formRegistration');
            form.find('input[name="data_vencimento"]').val(data_vencimento);
            form.find('input[name="valor_esperado"]').val(valor_esperado).trigger('input');
            form.find('input[name="observacao"]').val(lancamento.observacao);
            self.#buscarFormaPagamento(lancamento.forma_pagamento_id);
            return true;
        } catch (error) {
            CommonFunctions.generateNotificationErrorCatch(error);
            return false;
        }
    }

    saveButtonAction() {
        const self = this;
        const formRegistration = $(self.getIdModal).find('.formRegistration');
        let data = CommonFunctions.getInputsValues(formRegistration[0]);

        if (self.#saveVerifications(data)) {
            switch (self._objConfigs.modeReturn) {
                case 'object':
                    CommonFunctions.deepMergeObject(self._dataEnvModal.register, data);
                    self._promisseReturnValue.refresh = true;
                    self._promisseReturnValue.register = self._dataEnvModal.register;
                    self._endTimer = true;
                    break;

                default:
                    self._save(data, self._objConfigs.url.base);
            }
        }
    }

    #saveVerifications(data) {
        const self = this;
        const formRegistration = $(self.getIdModal).find('.formRegistration');
        let blnSave = true;

        if (self._objConfigs.modeReturn === 'object') {
            const categoriaId = self._dataEnvModal?.register?.lancamento_categoria_id;
            const categoriaEnum = window.Details.LancamentosCategoriaEnum.find(x => x.id == categoriaId);
            const camposPermitidos = categoriaEnum?.configuracao?.personalizar_lancamento?.campos_permitidos || [];

            const liberarTodos = camposPermitidos.includes('*');

            if (liberarTodos || camposPermitidos.includes('data_vencimento')) {
                blnSave = CommonFunctions.verificationData(data.data_vencimento, {
                    field: formRegistration.find('input[name="data_vencimento"]'),
                    messageInvalid: 'A <b>Data de Vencimento</b> deve ser informada.',
                    setFocus: true
                });
            }

            if (liberarTodos || camposPermitidos.includes('valor_esperado')) {
                data.valor_esperado = CommonFunctions.removeCommasFromCurrencyOrFraction(data.valor_esperado);
                blnSave = CommonFunctions.verificationData(data.valor_esperado, {
                    field: formRegistration.find('input[name="valor_esperado"]'),
                    messageInvalid: 'O <b>Valor</b> deve ser informado.',
                    setFocus: blnSave === true,
                    returnForcedFalse: blnSave === false
                });
            }

            // Se quiser, já remove os campos não permitidos aqui também:
            Object.keys(data).forEach(key => {
                if (!liberarTodos && !camposPermitidos.includes(key)) {
                    delete data[key];
                }
            });

        } else {
            // Se não for objeto, restringe à forma_pagamento_id e observacao
            Object.keys(data).forEach(key => {
                if (!['forma_pagamento_id', 'observacao'].includes(key)) {
                    delete data[key];
                }
            });

            if (!UUIDHelper.isValidUUID(data.forma_pagamento_id)) {
                delete data.forma_pagamento_id;
            }
        }


        // if (self._objConfigs.modeReturn == 'object' && window.Statics.PagamentoTipoCategoriaLancamentosPersonalizaveis.includes(self._dataEnvModal?.register.lancamento_categoria_id)) {

        //     blnSave = CommonFunctions.verificationData(data.data_vencimento, {
        //         field: formRegistration.find('input[name="data_vencimento"]'),
        //         messageInvalid: 'A <b>Data de Vencimento</b> deve ser informada.',
        //         setFocus: true
        //     });

        //     data.valor_esperado = CommonFunctions.removeCommasFromCurrencyOrFraction(data.valor_esperado);
        //     blnSave = CommonFunctions.verificationData(data.valor_esperado, {
        //         field: formRegistration.find('input[name="valor_esperado"]'),
        //         messageInvalid: 'O <b>Valor</b> deve ser informado.',
        //         setFocus: blnSave === true,
        //         returnForcedFalse: blnSave === false
        //     });

        // } else {
        //     // Retorna somente os campos permitidos a serem alterados por padrão
        //     Object.keys(data).forEach(key => {
        //         if (!['forma_pagamento_id', 'observacao'].includes(key)) {
        //             delete data[key];
        //         }
        //     });

        //     if (!UUIDHelper.isValidUUID(data.forma_pagamento_id)) {
        //         delete data.forma_pagamento_id;
        //     }
        // }

        return blnSave;
    }
}
