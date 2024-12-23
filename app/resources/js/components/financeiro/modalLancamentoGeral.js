import { commonFunctions } from "../../commons/commonFunctions";
import { connectAjax } from "../../commons/connectAjax";
import { enumAction } from "../../commons/enumAction";
import { modalRegistrationAndEditing } from "../../commons/modal/modalRegistrationAndEditing";
import { DateTimeHelper } from "../../helpers/DateTimeHelper";
import { modalContaTenant } from "../tenant/modalContaTenant";
import { modalLancamentoCategoriaTipoTenant } from "../tenant/modalLancamentoCategoriaTipoTenant";

export class modalLancamentoGeral extends modalRegistrationAndEditing {

    #dataEnvModal = {
        idRegister: undefined,
    }

    /**
     * Configuração local do modal
     */
    #objConfigs = {
        url: {
            base: undefined,
            baseLancamentoAgendamento: window.apiRoutes.baseLancamentoAgendamento,
            baseLancamentoGeral: window.apiRoutes.baseLancamentoGeral,
            baseContas: window.apiRoutes.baseContas,
            baseLancamentoCategoriaTipoTenant: window.apiRoutes.baseLancamentoCategoriaTipoTenant,
        },
        sufixo: 'ModalLancamentoGeral',
        data: {
            idRegister: undefined,
            cronExpressao: undefined,
        },
        modoAgendamento: false,
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
        if (options.modoAgendamento) {
            this._objConfigs.modoAgendamento = true;
            this._objConfigs.url.base = this._objConfigs.url.baseLancamentoAgendamento;
        } else {
            this._objConfigs.url.base = this._objConfigs.url.baseLancamentoGeral;
        }
    }

    async modalOpen() {
        const self = this;
        let open = true;

        await commonFunctions.loadingModalDisplay(true, { message: 'Carregando informações do lançamento...' });

        self.#addEventosPadrao();

        const carregamentosSelects = async () => {
            await self.#buscarContas();
            await self.#buscarMovimentacoesTipo();
            await self.#buscarLancamentoCategoriaTipoTenant();
        }

        await carregamentosSelects();
        if (self._dataEnvModal.idRegister) {
            open = await self.#buscarDados();
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

        self._modalReset();
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

        if (self._objConfigs.modoAgendamento) {
            self.#visualizacaoModoAgendamento(true);
            // Evento para monitorar mudanças no checkbox `ckbRecorrente`
            modal.find(`input[name="recorrente_bln"]`).on('change', function () {
                self.#toggleCronInputs($(this).is(':checked'));
                self.#gerarCronExpressao();
            });

            // aqui vai a ação dos elementos inputs e selects do cron
            modal.find('.dadosCron .inputCron').on('change', function () {
                self.#gerarCronExpressao();
            });
        } else {
            self.#visualizacaoModoAgendamento(false);
        }
    }

    #visualizacaoModoAgendamento(status) {
        const self = this;

        if (status) {
            $(self.getIdModal).find(`.modoAgendamento`).css('display', '');
        } else {
            $(self.getIdModal).find(`.modoAgendamento`).hide('fast');
        }
    }

    /**
     * Gera a expressão cron com base nos valores selecionados nos inputs e atualiza a visualização.
     */
    #gerarCronExpressao() {
        const self = this;

        const dadosCron = $(self.getIdModal).find('.dadosCron');
        const day = dadosCron.find('select[name="cronDay"]').val() || '*';
        const month = dadosCron.find('select[name="cronMonth"]').val() || '*';
        const weekday = dadosCron.find('select[name="cronWeekday"]').val() || '*';
        // const minute = dadosCron.find('select[name="cronMinute"]').val() || '*';
        // const hour = dadosCron.find('select[name="cronHour"]').val() || '*';

        // Monta a expressão cron
        const cronExpressao = `* * ${day} ${month} ${weekday}`;

        if (cronExpressao === '* * * * *') {
            $(self.getIdModal).find(`#cronExpression${self.getSufixo}`).val('Selecione a recorrência');
        } else {
            if (!self.#validarExpressaoCron(cronExpressao)) {
                // Atualiza a expressão e tradução no DOM
                $(self.getIdModal).find(`#cronExpression${self.getSufixo}`).val('Valores inválidos');
                self._objConfigs.data.cronExpressao = undefined;
                return false;
            }
        }

        self._objConfigs.data.cronExpressao = cronExpressao;

        // Exemplo de uso
        const cronParts = {
            day: day,
            month: month,
            weekday: weekday,
            hour: '*',
            minute: '*'
        };

        const traducao = self.#gerarTraducaoCron(cronParts);
        $(self.getIdModal).find(`#cronExpression${self.getSufixo}`).val(traducao);
    }

    /**
     * Habilita ou desabilita os campos dentro de `.dadosCron` com base no estado dos checkboxes.
     * @param {boolean} status - Estado do checkbox (`true` para habilitar, `false` para desabilitar`).
     */
    #toggleCronInputs(status) {
        const self = this;
        $(self.getIdModal).find('.dadosCron .inputCron').prop('disabled', !status);
        $(self.getIdModal).find('input[name="data_vencimento"]').prop('disabled', status);
    }

    #gerarTraducaoCron(cronParts) {
        const self = this;

        const dadosCron = $(self.getIdModal).find('.dadosCron');
        let traducao = [];

        if (cronParts.day !== '*') {
            traducao.push(`Todo dia ${cronParts.day}`);
        }
        if (cronParts.month !== '*') {
            if (!traducao.length) {
                traducao.push('Todo');
            } else {
                traducao.push('do');
            }
            traducao.push(`mês de ${dadosCron.find(`select[name="cronMonth"] option[value="${cronParts.month}"]`).text().trim()}`);
        }

        if (cronParts.weekday !== '*') {
            if (!traducao.length) {
                traducao.push('Todas(os) as(os)');
            } else {
                traducao.push('nas(os)');
            }
            traducao.push(`${dadosCron.find(`select[name="cronWeekday"] option[value="${cronParts.weekday}"]`).text().trim()}`);
        }

        if (traducao.length === 0) {
            traducao.push('Selecione a recorrência');
        }

        // Retorna a tradução como uma string formatada
        return traducao.join(' ').trim();
    }

    /**
     * Valida uma expressão cron com base no formato padrão (* * * * *).
     * @param {string} expressao - A expressão cron a ser validada.
     * @returns {boolean} Retorna `true` se a expressão for válida, caso contrário `false`.
     */
    #validarExpressaoCron(expressao) {
        const regex = /^(\*|([0-5]?\d)) (\*|([01]?\d|2[0-3])) (\*|([1-9]|[12]\d|3[01])) (\*|([1-9]|1[0-2])) (\*|[0-7])$/;

        if (regex.test(expressao)) {
            return true;
        } else {
            return false;
        }
    }

    #agendamentoRecorrenteResetar(status, dataUltimaExecucao = null) {
        const self = this;
        const divUltimaExecucao = $(self.getIdModal).find('.divUltimaExecucao');
        const spanUltimaExecucao = divUltimaExecucao.find('.spanUltimaExecucao');
        
        if (status) {
            divUltimaExecucao.show();
            if (dataUltimaExecucao) {
                spanUltimaExecucao.html(DateTimeHelper.retornaDadosDataHora(dataUltimaExecucao, 2));
            } else {
                spanUltimaExecucao.html('Nunca');
            }
        } else {
            divUltimaExecucao.hide();
        }
    }

    _modalReset() {
        super._modalReset();
        const self = this;
        $(self.getIdModal).find(`#dados-lancamento${self._objConfigs.sufixo}-tab`).trigger('click');
        self.#agendamentoRecorrenteResetar(false);
        self.#toggleCronInputs(false);
    }

    async #buscarDados() {
        const self = this;

        try {

            self._clearForm();
            const objConn = new connectAjax(self._objConfigs.url.base);
            objConn.setParam(self._dataEnvModal.idRegister);
            const response = await objConn.getRequest();

            if (response?.data) {
                const responseData = response.data;

                self._action = enumAction.PUT;
                self._objConfigs.data.idRegister = self._dataEnvModal.idRegister;

                const numero_lancamento = responseData.numero_lancamento ?? null;
                const descricao = responseData.descricao;
                const valor_esperado = commonFunctions.formatWithCurrencyCommasOrFraction(responseData.valor_esperado);
                const form = $(self.getIdModal).find('.formRegistration');

                if (self._objConfigs.modoAgendamento) {
                    self._updateModalTitle(`Editar agendamento`);
                    form.find('input[name="ativo_bln"]').prop('checked', responseData.ativo_bln);
                    form.find('input[name="recorrente_bln"]').prop('checked', responseData.recorrente_bln).trigger('change');
                    if (responseData.recorrente_bln) {
                        const arrCron = responseData.cron_expressao.split(' ');
                        if (arrCron.length !== 5 || (arrCron[2] === '*' && arrCron[3] === '*' && arrCron[4] === '*')) {
                            console.error('A expressão cron é inválida.', responseData.cron_expressao);
                        } else {
                            form.find('select[name="cronDay"]').val(arrCron[2]);
                            form.find('select[name="cronMonth"]').val(arrCron[3]);
                            form.find('select[name="cronWeekday"]').val(arrCron[4]);
                        }
                        self.#gerarCronExpressao();
                        form.find('input[name="cron_data_inicio"]').val(responseData.cron_data_inicio);
                        form.find('input[name="cron_data_fim"]').val(responseData.cron_data_fim);

                        self.#agendamentoRecorrenteResetar(true, responseData.cron_ultima_execucao);
                    }
                } else {
                    self._updateModalTitle(`Editar lancamento ${numero_lancamento}`);
                }

                form.find('select[name="conta_id"]').val(responseData.conta_id);
                form.find('select[name="categoria_id"]').val(responseData.categoria_id);
                form.find('select[name="movimentacao_tipo_id"]').val(responseData.movimentacao_tipo_id);
                form.find('input[name="descricao"]').val(descricao);
                form.find('input[name="valor_esperado').val(valor_esperado);
                form.find('input[name="data_vencimento"]').val(responseData.data_vencimento);
                form.find('input[name="observacao"]').val(responseData.observacao);

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
            const select = $(`#conta_id${self.getSufixo}`);
            await commonFunctions.fillSelect(select, self._objConfigs.url.baseContas, options);
            return true;
        } catch (error) {
            return false;
        }
    }

    async #buscarMovimentacoesTipo(selected_id = null) {
        try {
            const arrayOpcoes = window.Statics.TiposMovimentacaoParaLancamentos;
            const self = this;
            let options = selected_id ? { selectedIdOption: selected_id } : {};
            const select = $(`#movimentacao_tipo_id${self.getSufixo}`);
            await commonFunctions.fillSelectArray(select, arrayOpcoes, options);
            return true;
        } catch (error) {
            return false;
        }
    }

    async #buscarLancamentoCategoriaTipoTenant(selected_id = null) {
        try {
            const self = this;
            let options = selected_id ? { selectedIdOption: selected_id } : {};
            const select = $(`#categoria_id${self.getSufixo}`);
            await commonFunctions.fillSelect(select, self._objConfigs.url.baseLancamentoCategoriaTipoTenant, options);
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

        if (self._objConfigs.modoAgendamento) {
            data.cron_expressao = self._objConfigs.data.cronExpressao;
        }

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

        if (self._objConfigs.modoAgendamento && (data.recorrente_bln && data.recorrente_bln == true)) {
            if (!self._objConfigs.data.cronExpressao) {
                blnSave = commonFunctions.verificationData(self._objConfigs.data.cronExpressao, {
                    field: formRegistration.find('select[name="cronDay"]'),
                    messageInvalid: 'Uma recorrência deve ser configurada.',
                    setFocus: blnSave == true,
                    returnForcedFalse: blnSave == false
                });
            }
        } else {
            blnSave = commonFunctions.verificationData(data.data_vencimento, {
                field: formRegistration.find('input[name="data_vencimento"]'),
                messageInvalid: 'A data de vencimento deve ser informada.',
                setFocus: blnSave == true,
                returnForcedFalse: blnSave == false
            });
        }
        return blnSave;
    }
}