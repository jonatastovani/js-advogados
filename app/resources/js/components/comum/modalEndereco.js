import { commonFunctions } from "../../commons/commonFunctions";
import { enumAction } from "../../commons/enumAction";
import { modalRegistrationAndEditing } from "../../commons/modal/modalRegistrationAndEditing";
import { DateTimeHelper } from "../../helpers/DateTimeHelper";
import { MasksAndValidateHelpers } from "../../helpers/MasksAndValidateHelpers";

export class modalEndereco extends modalRegistrationAndEditing {

    /**
     * Configuração local do modal
     */
    #objConfigs = {
        url: {
            base: undefined,
            baseCep: window.apiRoutes.baseCep,
        },
        sufixo: 'ModalEndereco',
    };

    #dataEnvModal = {
        participacoes: [],
    }

    constructor() {
        super({
            idModal: "#modalEndereco",
        });

        this._objConfigs = commonFunctions.deepMergeObject(this._objConfigs, this.#objConfigs);
        this._dataEnvModal = commonFunctions.deepMergeObject(this._dataEnvModal, this.#dataEnvModal);
        this._action = enumAction.POST;
    }

    async modalOpen() {
        const self = this;
        let blnOpen = true;
        await commonFunctions.loadingModalDisplay(true, { message: 'Carregando dados do endereço...', title: 'Aguarde...', elementFocus: null });

        try {
            // blnOpen = await self._buscarDados();
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
        const modal = $(self.getIdModal);
        const inputCep = modal.find(`input[name="cep"]`);

        MasksAndValidateHelpers.cepMask(inputCep);
        inputCep.on('input', function () {
            if (inputCep.val().length == 9) {
                self._buscarCep(inputCep.val());
            }
        });
        return true;
    }

    async _buscarCep(cep) {
        const self = this;

        try {
            
            const response = await CepService.show(cep);
            if (!response) { return false; }

            const responseData = response.data;
            cep.val(responseData.cep);
            modal.find(`input[name="logradouro"]`).val(responseData.logradouro);
            modal.find(`input[name="bairro"]`).val(responseData.bairro);
            modal.find(`input[name="cidade"]`).val(responseData.localidade);
            modal.find(`input[name="uf"]`).val(responseData.uf);
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
            return false;
        }
    }

    async _buscarDados(selected_id = null) {
        try {
            const self = this;
            const response = await self._getRecurse();
            if (!response) { return false; }

            const responseData = response.data;
            self._objConfigs.data.conta_id = responseData.id;

            const nomeConta = responseData.nome;
            let saldo = responseData?.ultima_movimentacao?.saldo_atualizado ? responseData.ultima_movimentacao.saldo_atualizado : 0;
            let saldoFormatado = commonFunctions.formatNumberToCurrency(saldo);
            let dataHoraUltimaAtualizacao = responseData?.ultima_movimentacao?.created_at ? responseData.ultima_movimentacao.created_at : null;
            dataHoraUltimaAtualizacao = dataHoraUltimaAtualizacao ? DateTimeHelper.retornaDadosDataHora(responseData.ultima_movimentacao.created_at, 12) : '<span class="fst-italic">Nenhuma movimentação registrada</span>';

            const modal = $(self.getIdModal);
            modal.find('.pNomeConta').html(nomeConta);
            modal.find('.pSaldoAtual').html(saldoFormatado);
            modal.find('.pUltimaMovimentacao').html(dataHoraUltimaAtualizacao);
            modal.find('input[name="novo_saldo"]').val(commonFunctions.formatWithCurrencyCommasOrFraction(saldo)).trigger('input');

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
        data.conta_id = self._objConfigs.data.conta_id;
        data.novo_saldo = commonFunctions.removeCommasFromCurrencyOrFraction(data.novo_saldo);

        if (self.#saveVerifications(data, formRegistration)) {
            await self._save(data, `${self._objConfigs.url.baseAtualizarSaldoConta}`);
        }
    }

    #saveVerifications(data, formRegistration) {
        let blnSave = commonFunctions.verificationData(data.novo_saldo, {
            field: formRegistration.find('input[name="novo_saldo"]'),
            messageInvalid: 'Informe um novo saldo.', setFocus: true
        });

        return blnSave;
    }

}