import { commonFunctions } from "../../commons/commonFunctions";
import { connectAjax } from "../../commons/connectAjax";
import { enumAction } from "../../commons/enumAction";
import { modalRegistrationAndEditing } from "../../commons/modal/modalRegistrationAndEditing";
import { DateTimeHelper } from "../../helpers/DateTimeHelper";
import { ServicoParticipacaoModule } from "../../modules/ServicoParticipacaoModule";
import { modalConta } from "./modalConta";

export class modalLancamentoMovimentar extends modalRegistrationAndEditing {

    #dataEnvModal = {
        idRegister: undefined,
        pagamento_id: undefined,
        status_id: undefined,
    }

    /**
     * Configuração local do modal
     */
    #objConfigs = {
        url: {
            base: `${window.apiRoutes.baseMovimentacaoContas}/servicos`,
            baseLancamento: `${window.apiRoutes.baseLancamento}/servicos`,
            baseLancamentoStatusTipo: window.apiRoutes.baseLancamentoStatusTipo,
            baseParticipacaoPreset: window.apiRoutes.baseParticipacaoPreset,
            baseParticipacaoTipo: window.apiRoutes.baseServicoParticipacaoTipoTenant,
            baseContas: window.apiRoutes.baseContas,
        },
        sufixo: 'ModalLancamentoMovimentar',
        data: {
            lancamento_status_tipos: undefined,
            participantesNaTela: undefined,
            idRegister: undefined,
            status_id: undefined,
        },
    };

    /** 
     * Conteúdo a ser retornado na promisse como resolve()
    */
    #promisseReturnValue = {
        refresh: false,
    };

    #functionsServicoParticipacao;

    constructor(options = {}) {
        super({
            idModal: "#modalLancamentoMovimentar",
        });

        this._objConfigs = Object.assign(this._objConfigs, this.#objConfigs);
        this._promisseReturnValue = Object.assign(this._promisseReturnValue, this.#promisseReturnValue);
        this._dataEnvModal = Object.assign(this._dataEnvModal, this.#dataEnvModal);
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
            await self.#buscarDadosLancamentoStatusTipo();
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
                const objModal = new modalConta();
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

        self.#functionsServicoParticipacao._buscarPresetParticipacaoTenant();
    }

    _modalReset() {
        super._modalReset();
        const self = this;
        $(self.getIdModal).find(`#dados-pagamento${self._objConfigs.sufixo}-tab`).trigger('click');
        $(self.getIdModal).find('.btn-simular').show();
    }

    async #buscarDadosLancamentoStatusTipo() {
        const self = this;
        try {
            const objConn = new connectAjax(self._objConfigs.url.baseLancamentoStatusTipo);
            objConn.setParam(self._dataEnvModal.status_id);
            const response = await objConn.getRequest();
            self._updateModalTitle(response.data.nome);
            self._objConfigs.data.lancamento_status_tipos = response.data;
            $(self.getIdModal).find('.campos-personalizados').html(response.data.campos_html);
            self.#addEventosCamposPersonalizados();
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
        }
    }

    #addEventosCamposPersonalizados() {
        const self = this;
        const modal = $(self.getIdModal);

        commonFunctions.applyCustomNumberMask(modal.find('.campos-personalizados .campo-monetario'), { format: '#.##0,00', reverse: true });

        modal.find('.campos-personalizados .campo-dia-mes').mask('00', {
            onKeyPress: function (value, event, currentField) {
                const regex = /^(0?[1-9]|[12][0-9]|3[01])$/;
                if (!regex.test(value)) {
                    currentField.val('');
                }
            }
        });

        commonFunctions.applyCustomNumberMask(modal.find('.campos-personalizados .campo-numero'), { format: '#.##0', reverse: true });

    }

    async #buscarContas(selected_id = null) {
        try {
            const self = this;
            let options = selected_id ? { selectedIdOption: selected_id } : {};
            const selModulo = $(self.getIdModal).find('select[name="conta_id"]');
            await commonFunctions.fillSelect(selModulo, self._objConfigs.url.baseContas, options);
            return true;
        } catch (error) {
            return false;
        }
    }

    async #buscarDados() {
        const self = this;

        try {
            self._clearForm();
            const objConn = new connectAjax(self._objConfigs.url.baseLancamento);
            objConn.setParam(self._dataEnvModal.idRegister);
            objConn.setData({ pagamento_uuid: self._dataEnvModal.pagamento_id });
            objConn.setAction(enumAction.POST);
            const response = await objConn.envRequest();

            if (response?.data) {
                const responseData = response.data;

                self._objConfigs.data.idRegister = self._dataEnvModal.idRegister;
                self._objConfigs.data.status_id = self._dataEnvModal.status_id;

                const descricao = responseData.descricao_automatica;
                const data_vencimento = DateTimeHelper.retornaDadosDataHora(responseData.data_vencimento, 2);
                const valor_esperado = commonFunctions.formatWithCurrencyCommasOrFraction(responseData.valor_esperado);
                const conta_id = responseData.conta_id ?? responseData.pagamento.conta_id;

                let participantes = [];
                if (responseData.participantes.length) {
                    participantes = responseData.participantes;
                } else {
                    participantes = responseData.pagamento.participantes.length ? responseData.pagamento.participantes :
                        (responseData.pagamento.servico.participantes.length ? responseData.pagamento.servico.participantes : [])

                    participantes = participantes.map(participante => {
                        delete participante.id;
                        delete participante.parent_type;
                        delete participante.parent_id;
                        participante.integrantes = participante.integrantes.map(integrante => {
                            delete integrante.id;
                            delete integrante.parent_type;
                            delete integrante.parent_id;
                            return integrante;
                        });
                        return participante;
                    });
                }

                const form = $(self.getIdModal).find('.formRegistration');
                form.find('.pDescricao').html(descricao);
                form.find('.pDataVencimento').html(data_vencimento);
                form.find('.pValor').html(valor_esperado);
                form.find('input[name="observacao"]').val(responseData.observacao);
                form.find('select[name="conta_id"]').val(conta_id);
                form.find('input[name="data_recebimento"]').val(responseData.data_vencimento);

                self.#functionsServicoParticipacao._inserirParticipantesEIntegrantes(participantes);

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
        data.participantes = self._objConfigs.data.participantesNaTela;
        data.referencia_id = self._objConfigs.data.idRegister;
        data.status_id = self._objConfigs.data.status_id;

        console.log(data);
        if (self.#saveVerifications(data)) {
            commonFunctions.generateNotification('Lançamento será enviado para ser salvo.', 'success');
            self._save(data, self._objConfigs.url.base);
        }
    }

    #saveVerifications(data) {
        const self = this;
        const formRegistration = $(self.getIdModal).find('.formRegistration');
        const configuracao = self._objConfigs.data.lancamento_status_tipos.configuracao;
        let blnSave = false;

        blnSave = self.#functionsServicoParticipacao._saveVerificationsParticipantes(data);
        blnSave = commonFunctions.verificationData(data.conta_id, {
            field: formRegistration.find('select[name="conta_id"]'),
            messageInvalid: 'A <b>Conta</b> deve ser informada.',
            setFocus: blnSave === true,
            returnForcedFalse: blnSave === false
        });

        for (const campo of configuracao.campos_obrigatorios) {
            const rules = campo.formRequestRule.split('|');
            if (rules.find(rule => rule === 'numeric' || rule === 'integer')) {
                data[campo.nome] = commonFunctions.removeCommasFromCurrencyOrFraction(data[campo.nome]);
            }

            blnSave = commonFunctions.verificationData(data[campo.nome], {
                field: formRegistration.find(`#${campo.nome}${self._objConfigs.sufixo}`),
                messageInvalid: `O campo <b>${campo.nome_exibir}</b> deve ser informado.`,
                setFocus: blnSave === true,
                returnForcedFalse: blnSave === false
            });
        }

        return blnSave;
    }
}