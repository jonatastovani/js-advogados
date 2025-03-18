import { CommonFunctions } from "../../commons/CommonFunctions";
import { EnumAction } from "../../commons/EnumAction";
import { ModalRegistrationAndEditing } from "../../commons/modal/ModalRegistrationAndEditing";
import { DateTimeHelper } from "../../helpers/DateTimeHelper";

export class ModalAjustarSaldo extends ModalRegistrationAndEditing {

    /**
     * Configuração local do modal
     */
    #objConfigs = {
        url: {
            base: window.apiRoutes.baseContas,
            baseAtualizarSaldoConta: window.apiRoutes.baseAtualizarSaldoConta,
            baseDomains: window.apiRoutes.baseDomains,
        },
        data: {
            conta_id: undefined,
        },
        sufixo: 'ModalAjustarSaldo',
    };

    constructor() {
        super({
            idModal: "#ModalAjustarSaldo",
        });

        this._objConfigs = CommonFunctions.deepMergeObject(this._objConfigs, this.#objConfigs);
        this._action = EnumAction.POST;
    }

    async modalOpen() {
        const self = this;
        let blnOpen = false;
        await CommonFunctions.loadingModalDisplay(true, { message: 'Carregando dados da conta...', title: 'Aguarde...', elementFocus: null });

        try {
            blnOpen = await self.#buscarDados();
            self.#addEventosPadrao();
        } catch (error) {
            blnOpen = false;
            CommonFunctions.generateNotificationErrorCatch(error);
        } finally {
            await CommonFunctions.loadingModalDisplay(false);
        }

        if (!blnOpen) {
            return await self._returnPromisseResolve();
        }

        await self._modalHideShow();
        return await self._modalOpen();
    }

    #addEventosPadrao() {
        const self = this;

        CommonFunctions.applyCustomNumberMask($(self.getIdModal).find('.campo-monetario'), { format: '#.##0,00', reverse: true });

        return true;
    }

    async #buscarDados(selected_id = null) {
        try {
            const self = this;
            const response = await self._getRecurse();
            if (!response) return false;

            const responseData = response.data;
            self._objConfigs.data.conta_id = responseData.id;

            const nomeConta = responseData.nome;
            let saldo = CommonFunctions.formatNumberToCurrency(responseData.saldo_total);

            const modal = $(self.getIdModal);
            modal.find('.pNomeConta').html(nomeConta);
            modal.find('.pSaldoAtual').html(saldo);

            await self.#buscarDomains();
            self.#addEventosDomains();

            return true;
        } catch (error) {
            CommonFunctions.generateNotificationErrorCatch(error);
            return false;
        }
    }

    async #buscarDomains() {
        try {
            const self = this;
            return await CommonFunctions.fillSelect($(`#domain_id${self.getSufixo}`), self._objConfigs.url.baseDomains, { displayColumnName: 'name', outInstanceParentBln: true, insertFirstOption: false, });
        } catch (error) {
            return false;
        }
    }

    #addEventosDomains() {
        const self = this;

        $(`#domain_id${self.getSufixo}`).on('change', async function () {
            try {
                const selected = Number($(this).val());
                if (selected) {
                    const response = await self._getRecurse({ urlApi: `${self._objConfigs.url.base}/${self._dataEnvModal.idRegister}/conta-domain`, idRegister: selected });

                    if (response.data) {
                        const responseData = response.data;

                        let dataHoraUltimaAtualizacao = responseData?.conta_domain?.ultima_movimentacao?.created_at ?? null;
                        dataHoraUltimaAtualizacao = dataHoraUltimaAtualizacao ? DateTimeHelper.retornaDadosDataHora(dataHoraUltimaAtualizacao, 12) : '<span class="fst-italic">Nenhuma movimentação registrada</span>';

                        const modal = $(self.getIdModal);
                        modal.find('.pUltimaMovimentacao').html(dataHoraUltimaAtualizacao);
                        modal.find('input[name="novo_saldo"]').val(CommonFunctions.formatWithCurrencyCommasOrFraction(responseData?.conta_domain?.ultima_movimentacao?.saldo_atualizado ?? 0)).trigger('input');
                    }
                }
            } catch (error) {
                CommonFunctions.generateNotificationErrorCatch(error);
            }
        }).trigger('change');
    }

    async saveButtonAction() {
        const self = this;
        const formRegistration = $(self.getIdModal).find('.formRegistration');
        let data = CommonFunctions.getInputsValues(formRegistration[0]);
        data.conta_id = self._objConfigs.data.conta_id;
        data.novo_saldo = CommonFunctions.removeCommasFromCurrencyOrFraction(data.novo_saldo);

        if (self.#saveVerifications(data, formRegistration)) {
            await self._save(data, `${self._objConfigs.url.baseAtualizarSaldoConta}`);
        }
    }

    #saveVerifications(data, formRegistration) {
        let blnSave = CommonFunctions.verificationData(data.novo_saldo, {
            field: formRegistration.find('input[name="novo_saldo"]'),
            messageInvalid: 'Informe um novo saldo.', setFocus: true
        });

        blnSave = CommonFunctions.verificationData(data.domain_id, {
            field: formRegistration.find('select[name="domain_id"]'),
            messageInvalid: 'Selecione uma unidade.',
            setFocus: blnSave === true,
            returnForcedFalse: blnSave === false
        });

        return blnSave;
    }

}
