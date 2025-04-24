import { CommonFunctions } from "../../commons/CommonFunctions";
import { EnumAction } from "../../commons/EnumAction";
import { ModalRegistrationAndEditing } from "../../commons/modal/ModalRegistrationAndEditing";
import { DateTimeHelper } from "../../helpers/DateTimeHelper";
import { ParticipacaoModule } from "../../modules/ParticipacaoModule";
import { ModalContaTenant } from "../tenant/ModalContaTenant";

export class ModalLancamentoGeralMovimentar extends ModalRegistrationAndEditing {

    /**
     * Configuração local do modal
     */
    #objConfigs = {
        url: {
            base: window.apiRoutes.baseMovimentacaoContasGeral,
            baseLancamentoGeral: window.apiRoutes.baseLancamentoGeral,
            baseContas: window.apiRoutes.baseContas,
            baseParticipacaoTipo: window.apiRoutes.baseParticipacaoTipoTenant,
        },
        sufixo: 'ModalLancamentoGeralMovimentar',
        data: {
            lancamento_status_tipos: undefined,
            idRegister: undefined,
            status_id: undefined,
        },
        participacao: {
            // perfis_busca: window.Statics.PerfisPermitidoParticipacaoRessarcimento,
            participacao_tipo_tenant: {
                configuracao_tipo: window.Enums.ParticipacaoTipoTenantConfiguracaoTipoEnum.LANCAMENTO_GERAL,
            },
        },
        domainCustom: {
            applyBln: true,
        },
    };

    #dataEnvModal = {
        idRegister: undefined,
        pagamento_id: undefined,
        status_id: undefined,
    }

    #functionsParticipacao;

    constructor(options = {}) {
        super({
            idModal: "#ModalLancamentoGeralMovimentar",
        });

        this._objConfigs = CommonFunctions.deepMergeObject(this._objConfigs, this.#objConfigs);
        this._dataEnvModal = CommonFunctions.deepMergeObject(this._dataEnvModal, this.#dataEnvModal);
        this._action = EnumAction.POST;
        const objData = {
            objConfigs: this._objConfigs,
            extraConfigs: {
                typeParent: 'modal',
                modeParent: 'searchAndUse',
            }
        }
        this.#functionsParticipacao = new ParticipacaoModule(this, objData);

        this.#addEventosPadrao();
    }

    async modalOpen() {
        const self = this;
        await CommonFunctions.loadingModalDisplay(true, { message: 'Carregando informações do lançamento...' });

        if (!self._dataEnvModal.idRegister) {
            CommonFunctions.generateNotification('ID do Lançamento não informado. Caso o problema persista, contate o desenvolvedor.', 'error');
            console.error(self._dataEnvModal);
            return self._returnPromisseResolve();
        }

        let open = await self.#buscarDados();

        self._queueCheckDomainCustom.setReady();

        await CommonFunctions.loadingModalDisplay(false);
        if (open) {
            await self._modalHideShow();
            return await self._modalOpen();
        }
        return await self._returnPromisseResolve();
    }

    #addEventosPadrao() {
        const self = this;
        const modal = $(self._idModal);

        CommonFunctions.handleModal(self, modal.find('.openModalConta'), ModalContaTenant, self.#buscarContas.bind(self));

        CommonFunctions.applyCustomNumberMask(modal.find('.campo-monetario'), { format: '#.##0,00', reverse: true });
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
            let options = { outInstanceParentBln: true };
            selected_id ? options.selectedIdOption = selected_id : null;
            const select = $(`#conta_id${self.getSufixo}`);
            await CommonFunctions.fillSelect(select, self._objConfigs.url.baseContas, options);
            return true;
        } catch (error) {
            return false;
        }
    }

    async #buscarDados() {
        const self = this;

        try {

            self._clearForm();
            const response = await self._getRecurse({ urlApi: self._objConfigs.url.baseLancamentoGeral });

            if (response?.data) {
                const responseData = response.data;

                self._objConfigs.data.idRegister = self._dataEnvModal.idRegister;
                self._objConfigs.data.status_id = self._dataEnvModal.status_id;
                self._objConfigs.data.valor_esperado = responseData.valor_esperado;

                const numero_lancamento = responseData.numero_lancamento;
                const categoria = responseData.categoria.nome;
                const descricao = responseData.descricao;
                const data_vencimento = DateTimeHelper.retornaDadosDataHora(responseData.data_vencimento, 2);
                const valor_esperado = CommonFunctions.formatWithCurrencyCommasOrFraction(responseData.valor_esperado);
                const pValor = `${responseData.movimentacao_tipo.nome} - ${CommonFunctions.formatNumberToCurrency(responseData.valor_esperado)}`;
                const conta_id = responseData.conta_id;
                const hoje = DateTimeHelper.retornaDadosDataHora(new Date(), 1);
                const diferenca_data = DateTimeHelper.retornaDiferencaDeDataEHora(hoje, responseData.data_vencimento, 1);
                const data_quitado = diferenca_data <= 0 ? responseData.data_vencimento : hoje;

                const form = $(self.getIdModal).find('.formRegistration');
                form.find('.pNumeroLancamento').attr('title', numero_lancamento).html(numero_lancamento);
                form.find('.pCategoria').attr('title', categoria).html(categoria);
                form.find('.pDataVencimento').attr('title', data_vencimento).html(data_vencimento);
                form.find('.pValor').attr('title', pValor).html(pValor);
                form.find('.pDescricao').attr('title', descricao).html(descricao);
                form.find('input[name="observacao"]').val(responseData.observacao);
                form.find('input[name="valor_quitado"]').val(valor_esperado);
                form.find('input[name="data_quitado"]').val(data_quitado);
                this.#buscarContas(conta_id);

                responseData.participantes.map(item => self.#functionsParticipacao._pushObjetoParticipanteNaTela(item));

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
        let data = CommonFunctions.getInputsValues($(self.getIdModal).find('.divDadosLancamento')[0]);
        data.referencia_id = self._objConfigs.data.idRegister;
        data.status_id = self._objConfigs.data.status_id;
        data.valor_quitado = data.valor_quitado ? CommonFunctions.removeCommasFromCurrencyOrFraction(data.valor_quitado) : null;
        data.participantes = self.#functionsParticipacao._getParticipantesNaTelaFiltrado();

        if (self.#saveVerifications(data)) {
            self._save(data, self._objConfigs.url.base);
        }
    }

    #saveVerifications(data) {
        const self = this;
        const formRegistration = $(self.getIdModal).find('.formRegistration');
        let blnSave = false;

        blnSave = self.#functionsParticipacao._saveVerificationsParticipantes(data);

        blnSave = CommonFunctions.verificationData(data.conta_id, {
            field: formRegistration.find('select[name="conta_id"]'),
            messageInvalid: 'A <b>Conta</b> deve ser selecionada.',
            setFocus: true
        });

        blnSave = CommonFunctions.verificationData(data.data_quitado, {
            field: formRegistration.find('input[name="data_quitado"]'),
            messageInvalid: '<b>Data quitado</b> deve ser informada.',
            setFocus: blnSave === true,
            returnForcedFalse: blnSave === false
        });

        blnSave = CommonFunctions.verificationData(data.valor_quitado, {
            field: formRegistration.find('input[name="valor_quitado"]'),
            messageInvalid: '<b>Valor quitado</b> deve ser informado.',
            setFocus: blnSave === true,
            returnForcedFalse: blnSave === false
        });

        return blnSave;
    }
}
