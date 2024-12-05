import { commonFunctions } from "../../commons/commonFunctions";
import { connectAjax } from "../../commons/connectAjax";
import { enumAction } from "../../commons/enumAction";
import { modalRegistrationAndEditing } from "../../commons/modal/modalRegistrationAndEditing";
import { DateTimeHelper } from "../../helpers/DateTimeHelper";
import { modalLancamentoCategoriaTipoTenant } from "../tenant/modalLancamentoCategoriaTipoTenant";
import { modalConta } from "./modalConta";

export class modalLancamentoGeral extends modalRegistrationAndEditing {

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
            base: window.apiRoutes.baseLancamentoGeral,
            baseContas: window.apiRoutes.baseContas,
            baseLancamentoCategoriaTipoTenant: window.apiRoutes.baseLancamentoCategoriaTipoTenant,
        },
        sufixo: 'ModalLancamentoGeral',
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

    constructor(options = {}) {
        super({
            idModal: "#modalLancamentoGeral",
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

        this.#addEventosPadrao();
    }

    async modalOpen() {
        const self = this;
        let open = false;
        let loadCarregamentos = false;
        await commonFunctions.loadingModalDisplay(true, { message: 'Carregando informações do lançamento...' });

        const carregamentosSelects = async () => {
            await self.#buscarContas();
            await self.#buscarMovimentacoesTipo();
            await self.#buscarLancamentoCategoriaTipoTenant();
            loadCarregamentos = true;
        }

        if (1 == 2) {
            if (self._dataEnvModal.idRegister) {
                await carregamentosSelects();
                open = await self.#buscarDados();
            } else {
                commonFunctions.generateNotification('ID do Lançamento não informado. Caso o problema persista, contate o desenvolvedor.', 'error');
            }
        } else {
            open = true;
        }

        await commonFunctions.loadingModalDisplay(false);
        if (open) {
            if (!loadCarregamentos) {
                carregamentosSelects();
            }
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

        modal.find('.openModalLancamentoCategoriaTipoTenant').on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const objModal = new modalLancamentoCategoriaTipoTenant();
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
                        self.#buscarLancamentoCategoriaTipoTenant(response.selected.id);
                    } else {
                        self.#buscarLancamentoCategoriaTipoTenant();
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

        modal.find(`#agendamento${self.getSufixo}-tab`).click();
    }

    _modalReset() {
        super._modalReset();
        const self = this;
        $(self.getIdModal).find(`#dados-lancamento${self._objConfigs.sufixo}-tab`).trigger('click');
        $(self.getIdModal).find('.btn-simular').show();
        self.#bloquearLiberarCamposParticipacao();
    }

    #bloquearLiberarCamposParticipacao(status = true) {
        const self = this;

        if (!status) {
            $(self.getIdModal).find('select[name="preset_id"]').attr('disabled', true).off('change');
            $(self.getIdModal).find(`
                #btnInserirPessoa${self._objConfigs.sufixo},
                #btnInserirGrupo${self._objConfigs.sufixo},
                .btnOpenModalPresetParticipacao
            `).attr('disabled', true).off('click');
        } else {
            $(self.getIdModal).find('select[name="preset_id"]').attr('disabled', false);
            $(self.getIdModal).find(`
                #btnInserirPessoa${self._objConfigs.sufixo},
                #btnInserirGrupo${self._objConfigs.sufixo},
                .btnOpenModalPresetParticipacao
            `).attr('disabled', false);
        }
    }

    async #buscarDados() {
        const self = this;
        commonFunctions.generateNotification('Buscando dados do Lançamento...', 'info');
        return;
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

                const numero_servico = responseData.pagamento.servico.numero_servico;
                const titulo_servico = responseData.pagamento.servico.titulo;
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
                form.find('.pNumeroServico').html(numero_servico).attr('title', titulo_servico);
                form.find('.pDescricao').html(descricao);
                form.find('.pDataVencimento').html(data_vencimento);
                form.find('.pValor').html(valor_esperado);
                form.find('input[name="observacao"]').val(responseData.observacao);
                form.find('select[name="conta_id"]').val(conta_id);
                form.find('input[name="data_recebimento"]').val(responseData.data_vencimento);

                if (responseData.parent_id && responseData.metadata?.diluicao_pagamento_parcial) {
                    self.#bloquearLiberarCamposParticipacao(false);

                    participantes.map(participante => {
                        const card = $(`#${participante.idCard}`);
                        card.find('.dropdown-acoes-participante').find('button').addClass('disabled border-0').off('click');
                        card.find('.card-integrante').find('button').addClass('disabled border-0').off('click');
                    })
                }
                return true;
            }
            return false;
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
            return false;
        }
    }

    async #buscarContas(selected_id = null) {
        try {
            const self = this;
            let options = selected_id ? { selectedIdOption: selected_id } : {};
            const selModulo = $(`#conta_id${self.getSufixo}`);
            await commonFunctions.fillSelect(selModulo, self._objConfigs.url.baseContas, options);
            return true;
        } catch (error) {
            return false;
        }
    }

    async #buscarMovimentacoesTipo(selected_id = null) {
        try {
            const arrayOpcoes = window.Statics.TiposMovimentacaoModalLancamentoGeral;
            const self = this;
            let options = selected_id ? { selectedIdOption: selected_id } : {};
            const selModulo = $(`#movimentacao_tipo_id${self.getSufixo}`);
            await commonFunctions.fillSelectArray(selModulo, arrayOpcoes, options);
            return true;
        } catch (error) {
            return false;
        }
    }

    async #buscarLancamentoCategoriaTipoTenant(selected_id = null) {
        try {
            const self = this;
            let options = selected_id ? { selectedIdOption: selected_id } : {};
            const selModulo = $(`#categoria_id${self.getSufixo}`);
            await commonFunctions.fillSelect(selModulo, self._objConfigs.url.baseLancamentoCategoriaTipoTenant, options);
            return true;
        } catch (error) {
            return false;
        }
    }

    saveButtonAction() {
        const self = this;
        const formRegistration = $(self.getIdModal).find('.formRegistration');
        let data = commonFunctions.getInputsValues(formRegistration[0]);
        data.valor_esperado = commonFunctions.removeCommasFromCurrencyOrFraction(data.valor_esperado);

        if (self.#saveVerifications(data)) {
            self._save(data, self._objConfigs.url.base);
        }
    }

    #saveVerifications(data) {
        const self = this;
        const formRegistration = $(self.getIdModal).find('.formRegistration');

        let blnSave = commonFunctions.verificationData(data.movimentacao_tipo_id, { field: formRegistration.find('select[name="movimentacao_tipo_id"]'), messageInvalid: 'O tipo de movimentação deve ser selecionado.', setFocus: true });

        blnSave = commonFunctions.verificationData(data.categoria_id, {
            field: formRegistration.find('select[name="categoria_id"]'),
            messageInvalid: 'A categoria deve ser selecionada.',
            setFocus: blnSave == true,
            returnForcedFalse: blnSave == false
        });

        blnSave = commonFunctions.verificationData(data.conta_id, {
            field: formRegistration.find('select[name="conta_id"]'),
            messageInvalid: 'A conta deve ser selecionada.',
            setFocus: blnSave == true,
            returnForcedFalse: blnSave == false
        });

        blnSave = commonFunctions.verificationData(data.descricao, {
            field: formRegistration.find('input[name="descricao"]'),
            messageInvalid: 'A descrição do lançamento deve ser informada.',
            setFocus: blnSave == true,
            returnForcedFalse: blnSave == false
        });

        blnSave = commonFunctions.verificationData(data.valor_esperado, {
            field: formRegistration.find('input[name="valor_esperado"]'),
            messageInvalid: 'O valor do lançamento deve ser informado.',
            setFocus: blnSave == true,
            returnForcedFalse: blnSave == false
        });

        blnSave = commonFunctions.verificationData(data.data_vencimento, {
            field: formRegistration.find('input[name="data_vencimento"]'),
            messageInvalid: 'A data de vencimento deve ser informada.',
            setFocus: blnSave == true,
            returnForcedFalse: blnSave == false
        });

        return blnSave;
    }
}