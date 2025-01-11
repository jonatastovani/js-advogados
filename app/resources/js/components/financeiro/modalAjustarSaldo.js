import { commonFunctions } from "../../commons/commonFunctions";
import { modalRegistrationAndEditing } from "../../commons/modal/modalRegistrationAndEditing";
import { DateTimeHelper } from "../../helpers/DateTimeHelper";

export class modalAjustarSaldo extends modalRegistrationAndEditing {

    /**
     * Configuração local do modal
     */
    #objConfigs = {
        url: {
            base: window.apiRoutes.baseContas,
        },
        sufixo: 'ModalAjustarSaldo',
    };

    #dataEnvModal = {
        participacoes: [],
    }

    constructor() {
        super({
            idModal: "#modalAjustarSaldo",
        });

        this._objConfigs = commonFunctions.deepMergeObject(this._objConfigs, this.#objConfigs);
        this._dataEnvModal = commonFunctions.deepMergeObject(this._dataEnvModal, this.#dataEnvModal);
    }

    async modalOpen() {
        const self = this;
        let blnOpen = false;
        await commonFunctions.loadingModalDisplay(true, { message: 'Carregando dados da conta...', title: 'Aguarde...', elementFocus: null });

        try {
            blnOpen = await self._buscarDados();
            self.#addEventosPadrao();
        } catch (error) {
            blnOpen = false;
            commonFunctions.generateNotificationErrorCatch(error);
        } finally {
            await commonFunctions.loadingModalDisplay(false);
        }

        if (!blnOpen) {
            return await self._returnPromisseResolve();
        }

        await self._modalHideShow();
        return await self._modalOpen();
    }

    #addEventosPadrao() {
        const self = this;

        commonFunctions.applyCustomNumberMask($(self.getIdModal).find('.campo-monetario'), { format: '#.##0,00', reverse: true });

        return true;
    }

    async _buscarDados(selected_id = null) {
        try {
            const self = this;
            const response = await self._getRecurse();
            if (!response) { return false; }

            const responseData = response.data;
            const nomeConta = responseData.nome;
            let saldo = responseData?.ultima_movimentacao?.saldo_atualizado ? responseData.ultima_movimentacao.saldo_atualizado : 0;
            let saldoFormatado = commonFunctions.formatNumberToCurrency(saldo);
            console.log(responseData.ultima_movimentacao)
            let dataHoraUltimaAtualizacao = responseData?.ultima_movimentacao?.created_at ? responseData.ultima_movimentacao.created_at : null;
            dataHoraUltimaAtualizacao = dataHoraUltimaAtualizacao ? DateTimeHelper.retornaDadosDataHora(responseData.ultima_movimentacao.created_at, 12) : '<spa    n class="fst-italic">Nenhuma movimentação registrada</spa>';

            const modal = $(self.getIdModal);
            modal.find('.pNomeConta').html(nomeConta);
            modal.find('.pSaldoAtual').html(saldoFormatado);
            modal.find('.pUltimaMovimentacao').html(dataHoraUltimaAtualizacao);
            modal.find('input[name="novo_saldo"]').val(saldo).trigger('input');

            return true;
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
            return false;
        }
    }

    async saveButtonAction() {
        const self = this;
        const formRegistration = $(self.getIdModal).find('.formRegistration');
        let data = commonFunctions.getInputsValues(formRegistration[0]);
        data.perfil_tipo_id = self._dataEnvModal.perfil.perfil_tipo_id;

        if (self.#saveVerifications(data, formRegistration)) {
            try {
                self._promisseReturnValue.register = data;
                self._promisseReturnValue.refresh = true;
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                self._setEndTimer = true;
            }
        }
    }

    #saveVerifications(data, formRegistration) {
        let blnSave = true;

        if (data.perfil_tipo_id != window.Enums.PessoaPerfilTipoEnum.EMPRESA) {
            if (data.conta_movimentar == 'conta_debito') {
                blnSave = commonFunctions.verificationData(data.conta_debito_id, {
                    field: formRegistration.find('select[name="conta_debito_id"]'),
                    messageInvalid: 'Selecione uma conta.', setFocus: true
                });
            }
        }

        return blnSave;
    }

}