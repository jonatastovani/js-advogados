import { commonFunctions } from "../../commons/commonFunctions";
import { connectAjax } from "../../commons/connectAjax";
import { enumAction } from "../../commons/enumAction";
import { modalRegistrationAndEditing } from "../../commons/modal/modalRegistrationAndEditing";
import { DateTimeHelper } from "../../helpers/DateTimeHelper";
import { ServicoParticipacaoModule } from "../../modules/ServicoParticipacaoModule";
import { modalContaTenant } from "../tenant/modalContaTenant";

export class modalLancamentoGeralMovimentar extends modalRegistrationAndEditing {

    /**
     * Configuração local do modal
     */
    #objConfigs = {
        url: {
            base: window.apiRoutes.baseMovimentacaoContasGeral,
            baseLancamentoGeral: window.apiRoutes.baseLancamentoGeral,
            baseContas: window.apiRoutes.baseContas,
        },
        sufixo: 'ModalLancamentoGeralMovimentar',
        data: {
            lancamento_status_tipos: undefined,
            idRegister: undefined,
            status_id: undefined,
            participacao_tipo_tenant: {
                configuracao_tipo: window.Enums.ParticipacaoTipoTenantConfiguracaoTipoEnum.LANCAMENTO_GERAL,
            },
        },
    };

    #dataEnvModal = {
        idRegister: undefined,
        pagamento_id: undefined,
        status_id: undefined,
    }

    #functionsServicoParticipacao;

    constructor(options = {}) {
        super({
            idModal: "#modalLancamentoGeralMovimentar",
        });

        this._objConfigs = commonFunctions.deepMergeObject(this._objConfigs, this.#objConfigs);
        this._dataEnvModal = commonFunctions.deepMergeObject(this._dataEnvModal, this.#dataEnvModal);
        this._action = enumAction.POST;
        const objData = {
            objConfigs: this._objConfigs,
            extraConfigs: {
                typeParent: 'modal',
                modeParent: 'searchAndUse',
            }
        }
        this.#functionsServicoParticipacao = new ServicoParticipacaoModule(this, objData);

        this.#addEventosPadrao();
    }

    async modalOpen() {
        const self = this;
        let open = false;
        await commonFunctions.loadingModalDisplay(true, { message: 'Carregando informações do lançamento...' });

        if (self._dataEnvModal.idRegister) {
            await this.#buscarContas();
            open = await self.#buscarDados();
        } else {
            commonFunctions.generateNotification('ID do Lançamento não informado. Caso o problema persista, contate o desenvolvedor.', 'error');
        }

        await commonFunctions.loadingModalDisplay(false);
        if (open) {
            await self._modalHideShow();
            return await self._modalOpen();
        }
        return await self._returnPromisseResolve();
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
                    if (response.selected) {
                        self.#buscarContas(response.selected.id);
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

        commonFunctions.applyCustomNumberMask(modal.find('.campo-monetario'), { format: '#.##0,00', reverse: true });
    }

    _modalReset() {
        super._modalReset();
        const self = this;
        $(self.getIdModal).find(`#dados-lancamento${self._objConfigs.sufixo}-tab`).trigger('click');
        $(self.getIdModal).find('.btn-simular').show();
    }

    async #buscarContas(selected_id = null) {
        try {
            const self = this;
            let options = selected_id ? { selectedIdOption: selected_id } : {};
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
            const objConn = new connectAjax(self._objConfigs.url.baseLancamentoGeral);
            objConn.setParam(self._dataEnvModal.idRegister);
            const response = await objConn.getRequest();
            console.log(response);

            if (response?.data) {
                const responseData = response.data;

                self._objConfigs.data.idRegister = self._dataEnvModal.idRegister;
                self._objConfigs.data.status_id = self._dataEnvModal.status_id;
                self._objConfigs.data.valor_esperado = responseData.valor_esperado;

                const numero_lancamento = responseData.numero_lancamento;
                const descricao = responseData.descricao;
                const data_vencimento = DateTimeHelper.retornaDadosDataHora(responseData.data_vencimento, 2);
                const valor_esperado = commonFunctions.formatWithCurrencyCommasOrFraction(responseData.valor_esperado);
                const pValor = `${responseData.movimentacao_tipo.nome} - ${commonFunctions.formatNumberToCurrency(responseData.valor_esperado)}`;
                const conta_id = responseData.conta_id;
                const hoje = DateTimeHelper.retornaDadosDataHora(new Date(), 1);
                const diferenca_data = DateTimeHelper.retornaDiferencaDeDataEHora(hoje, responseData.data_vencimento, 1);
                const data_quitado = diferenca_data <= 0 ? responseData.data_vencimento : hoje;

                const form = $(self.getIdModal).find('.formRegistration');
                form.find('.pNumeroLancamento').html(numero_lancamento);
                form.find('.pDescricao').html(descricao);
                form.find('.pDataVencimento').html(data_vencimento);
                form.find('.pValor').html(pValor);
                form.find('input[name="observacao"]').val(responseData.observacao);
                form.find('select[name="conta_id"]').val(conta_id);
                form.find('input[name="valor_quitado"]').val(valor_esperado);
                form.find('input[name="data_quitado"]').val(data_quitado);

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
        let data = commonFunctions.getInputsValues($(self.getIdModal).find('.divDadosLancamento')[0]);
        data.referencia_id = self._objConfigs.data.idRegister;
        data.status_id = self._objConfigs.data.status_id;
        data.valor_quitado = data.valor_quitado ? commonFunctions.removeCommasFromCurrencyOrFraction(data.valor_quitado) : null;

        if (self.#saveVerifications(data)) {
            self._save(data, self._objConfigs.url.base);
        }
    }

    #saveVerifications(data) {
        const self = this;
        const formRegistration = $(self.getIdModal).find('.formRegistration');

        let blnSave = commonFunctions.verificationData(data.conta_id, {
            field: formRegistration.find('select[name="conta_id"]'),
            messageInvalid: 'A <b>Conta</b> deve ser selecionada.',
            setFocus: true
        });

        blnSave = commonFunctions.verificationData(data.data_quitado, {
            field: formRegistration.find('input[name="data_quitado"]'),
            messageInvalid: '<b>Data quitado</b> deve ser informada.',
            setFocus: blnSave === true,
            returnForcedFalse: blnSave === false
        });

        blnSave = commonFunctions.verificationData(data.valor_quitado, {
            field: formRegistration.find('input[name="valor_quitado"]'),
            messageInvalid: '<b>Valor quitado</b> deve ser informado.',
            setFocus: blnSave === true,
            returnForcedFalse: blnSave === false
        });

        return blnSave;
    }
}