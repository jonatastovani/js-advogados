import { commonFunctions } from "../../commons/commonFunctions";
import { connectAjax } from "../../commons/connectAjax";
import { enumAction } from "../../commons/enumAction";
import { modalRegistrationAndEditing } from "../../commons/modal/modalRegistrationAndEditing";
import { DateTimeHelper } from "../../helpers/DateTimeHelper";
import { Select2Helpers } from "../../helpers/Select2Helpers";
import { ParticipacaoModule } from "../../modules/ParticipacaoModule";
import { QueueManager } from "../../utils/QueueManager";
import { modalContaTenant } from "../tenant/modalContaTenant";
import { modalLancamentoCategoriaTipoTenant } from "../tenant/modalLancamentoCategoriaTipoTenant";
import { modalTagTenant } from "../tenant/modalTagTenant";

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
            baseLancamentoRessarcimento: window.apiRoutes.baseLancamentoRessarcimento,
            baseLancamentoGeral: window.apiRoutes.baseLancamentoGeral,
            baseContas: window.apiRoutes.baseContas,
            baseLancamentoCategoriaTipoTenant: window.apiRoutes.baseLancamentoCategoriaTipoTenant,
            baseParticipacaoTipo: window.apiRoutes.baseParticipacaoTipoTenant,
            basePessoaPerfil: window.apiRoutes.basePessoaPerfil,
            baseTagTenant: window.apiRoutes.baseTagTenant,
        },
        sufixo: 'ModalLancamentoGeral',
        data: {
            idRegister: undefined,
            participantesNaTela: [],
            cronExpressao: undefined,
        },
        modoOperacao: undefined,
        participacao: {
            perfis_busca: undefined,
            participacao_tipo_tenant: {
                configuracao_tipo: undefined,
            },
        }
    };

    /** 
     * Conteúdo a ser retornado na promisse como resolve()
    */
    #promisseReturnValue = {
        refresh: false,
    };

    #functionsParticipacao;
    #select2QueueManager;

    constructor(options = {}) {
        super({
            idModal: "#modalLancamentoGeral",
        });

        this._objConfigs = commonFunctions.deepMergeObject(this._objConfigs, this.#objConfigs);
        this._promisseReturnValue = commonFunctions.deepMergeObject(this._promisseReturnValue, this.#promisseReturnValue);
        this._dataEnvModal = commonFunctions.deepMergeObject(this._dataEnvModal, this.#dataEnvModal);
        this._action = enumAction.POST;
        const objData = {
            objConfigs: this._objConfigs,
            extraConfigs: {
                typeParent: 'modal',
                modeParent: 'searchAndUse',
            }
        }

        switch (options.modoOperacao) {
            case 'agendamento':
                this._objConfigs.modoOperacao = 'agendamento';
                this._objConfigs.url.base = this._objConfigs.url.baseLancamentoAgendamento;

                break;

            case 'ressarcimento':
                this._objConfigs.modoOperacao = 'ressarcimento';
                this._objConfigs.url.base = this._objConfigs.url.baseLancamentoRessarcimento;
                this._objConfigs.participacao.perfis_busca = window.Statics.PerfisPermitidoParticipacaoRessarcimento;
                this._objConfigs.participacao.participacao_tipo_tenant.configuracao_tipo = window.Enums.ParticipacaoTipoTenantConfiguracaoTipoEnum.LANCAMENTO_RESSARCIMENTO;
                // this._objConfigs.participacao.valor_tipo_permitido = ['valor_fixo'];
                break;

            default:
                this._objConfigs.url.base = this._objConfigs.url.baseLancamentoGeral;
                // this._objConfigs.participacao.participacao_tipo_tenant.configuracao_tipo = window.Enums.ParticipacaoTipoTenantConfiguracaoTipoEnum.LANCAMENTO_GERAL;
                break;
        }

        this.#functionsParticipacao = new ParticipacaoModule(this, objData);
        this.#select2QueueManager = new QueueManager();  // Cria a fila
    }

    async modalOpen() {
        const self = this;

        try {
            await commonFunctions.loadingModalDisplay(true, { message: 'Carregando informações do lançamento...' });

            await self.#addEventosPadrao();
            await self.#buscarContas();
            await self.#buscarMovimentacoesTipo();
            await self.#buscarLancamentoCategoriaTipoTenant();

            if (self._dataEnvModal.idRegister) {
                await self.#buscarDados();
            } else {
                if (self._objConfigs.modoOperacao != 'ressarcimento') {
                    self.#functionsParticipacao._inserirParticipanteObrigatorioEmpresaParticipacaoGeral();
                }
            }
            await commonFunctions.loadingModalDisplay(false);
            await self._modalHideShow();
            self.#addEventoSelect2();

            return await self._modalOpen();

        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
            await commonFunctions.loadingModalDisplay(false);
        }

        return await self._returnPromisseResolve();
    }

    async #addEventosPadrao() {
        const self = this;
        const modal = $(self._idModal);

        self._modalReset();

        commonFunctions.handleModal(self, modal.find('.openModalConta'), modalContaTenant, self.#buscarContas.bind(self));

        commonFunctions.handleModal(self, modal.find('.openModalLancamentoCategoriaTipoTenant'), modalLancamentoCategoriaTipoTenant, self.#buscarLancamentoCategoriaTipoTenant.bind(self));

        modal.find('.openModalTag').on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const objModal = new modalTagTenant();
                objModal.setDataEnvModal = {
                    tag_tipo: window.Enums.TagTipoTenantEnum.LANCAMENTO_GERAL,
                }
                await self._modalHideShow(false);
                const response = await objModal.modalOpen();
                self._executeFocusElementOnModal(this);
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading(btn, false);
                await self._modalHideShow();
            }
        });

        commonFunctions.applyCustomNumberMask(modal.find('.campo-monetario'), { format: '#.##0,00', reverse: true });

        switch (self._objConfigs.modoOperacao) {
            case 'agendamento':
                self._updateModalTitle('Agendamento');
                self.#visualizacaoModoOperacaoAgendamento(true);

                // Evento para monitorar mudanças no checkbox `ckbRecorrente`
                modal.find(`input[name="recorrente_bln"]`).on('change', function () {
                    self.#toggleCronInputs($(this).is(':checked'));
                    self.#gerarCronExpressao();
                });

                // Ação dos elementos inputs e selects do cron
                modal.find('.dadosCron .inputCron').on('change', function () {
                    self.#gerarCronExpressao();
                });

                break;

            case 'ressarcimento':
                self._updateModalTitle('Ressarcimento/Compensação');
                self.#visualizacaoModoOperacaoAgendamento(false);

                break;

            default:
                self.#visualizacaoModoOperacaoAgendamento(false);

                break;
        }

        self.#visualizacaoGuiaParticipantes();
    }

    /** Atribui o evento do select2 ao exibir o modal e sinaliza quando estiver pronto */
    #addEventoSelect2() {
        const self = this;
        const modal = $(self._idModal);

        modal.on('shown.bs.modal.addEventSelect2', function () {
            Select2Helpers.addEventsSelect2ApiMulti(modal.find('select[name="tags"]'), `${self._objConfigs.url.baseTagTenant}/select2`, {
                dataAppend: {
                    tipo: window.Enums.TagTipoTenantEnum.LANCAMENTO_GERAL,
                },
                dropdownParent: modal,
                onSelectionChange: function (selectedValues) {
                    // console.log('Seleções atuais:', selectedValues);
                }
            });

            self.#select2QueueManager.setReady();  // Informa que o select2 está pronto
            modal.off('shown.bs.modal.addEventSelect2');  // Remove o listener após a execução
        });

        // Se o modal já estiver visível, executa imediatamente
        if (modal.hasClass('show')) {
            modal.trigger('shown.bs.modal.addEventSelect2');
        }
    }

    #visualizacaoModoOperacaoAgendamento(status) {
        const self = this;

        if (status) {
            $(self.getIdModal).find(`.modoAgendamento`).css('display', '');
        } else {
            $(self.getIdModal).find(`.modoAgendamento`).hide('fast');
        }
    }

    #visualizacaoGuiaParticipantes() {
        const self = this;

        if (self.#functionsParticipacao.getExibirPainelParticipantesPersonalizaveisBln) {
            $(self.getIdModal).find(`.guiaParticipantes`).css('display', '');
        } else {
            $(self.getIdModal).find(`.guiaParticipantes`).hide('fast');
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
        $(self.getIdModal).find(`#dados-lancamento${self.getSufixo}-tab`).trigger('click');
        self.#agendamentoRecorrenteResetar(false);
        self.#toggleCronInputs(false);
    }

    async #buscarDados() {
        const self = this;

        self._clearForm();
        const objConn = new connectAjax(self._objConfigs.url.base);
        objConn.setParam(self._dataEnvModal.idRegister);
        const response = await objConn.getRequest();

        if (response?.data) {
            const responseData = response.data;

            self._action = enumAction.PUT;
            self._objConfigs.data.idRegister = self._dataEnvModal.idRegister;

            const descricao = responseData.descricao;
            const valor_esperado = commonFunctions.formatWithCurrencyCommasOrFraction(responseData.valor_esperado);
            const form = $(self.getIdModal).find('.formRegistration');

            switch (self._objConfigs.modoOperacao) {

                case 'agendamento':

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
                    responseData.participantes.map(item => self.#functionsParticipacao._inserirObjetoParticipanteNaTela(item));
                    break;

                case 'ressarcimento':
                    self._updateModalTitle(`Editar ressarcimento ${responseData.numero_lancamento ?? null}`);
                    self.#functionsParticipacao._inserirParticipantesEIntegrantes(responseData.participantes);

                    break;

                default:
                    self._updateModalTitle(`Editar lancamento ${responseData.numero_lancamento ?? null}`);
                    responseData.participantes.map(item => self.#functionsParticipacao._inserirObjetoParticipanteNaTela(item));
                    break;
            }

            form.find('select[name="conta_id"]').val(responseData.conta_id);
            form.find('select[name="categoria_id"]').val(responseData.categoria_id);
            form.find('select[name="movimentacao_tipo_id"]').val(responseData.movimentacao_tipo_id);
            form.find('input[name="descricao"]').val(descricao);
            form.find('input[name="valor_esperado').val(valor_esperado);
            form.find('input[name="data_vencimento"]').val(responseData.data_vencimento);
            form.find('input[name="observacao"]').val(responseData.observacao);

            // Adiciona o preenchimento das seleções múltiplas à fila
            self.#select2QueueManager.enqueue(() => {
                Select2Helpers.updateSelect2MultipleValues(
                    $(`#tags${self.getSufixo}`),
                    responseData.tags.map(item => {
                        return {
                            id: item.tag.id,
                            text: item.tag.nome,
                        };
                    })
                );
            });

            return true;
        }
        throw new Error("Erro ao preencher formulário: Dados não encontrados.");
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

    #getTags(options = {}) {
        const self = this;
        const {
            selector = `#tags${self.getSufixo}`,
        } = options;

        return $(selector).select2('data').map(item => {
            return item.id;
        });
    }

    saveButtonAction() {
        const self = this;
        const formRegistration = $(self.getIdModal).find('.formRegistration');
        let data = commonFunctions.getInputsValues(formRegistration[0]);
        data.valor_esperado = commonFunctions.removeCommasFromCurrencyOrFraction(data.valor_esperado);
        data.participantes = self.#functionsParticipacao._getParticipantesNaTela();
        data.tags = self.#getTags();

        if (self.#saveVerifications(data)) {
            self._save(data, self._objConfigs.url.base);
        }
    }

    #saveVerifications(data) {
        const self = this;
        const formRegistration = $(self.getIdModal).find('.formRegistration');

        let blnSave = commonFunctions.verificationData(data.movimentacao_tipo_id, {
            field: formRegistration.find('select[name="movimentacao_tipo_id"]'),
            messageInvalid: 'O tipo de movimentação deve ser selecionado.',
        });

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

        if (self._objConfigs.modoOperacao == 'agendamento' && (data.recorrente_bln && data.recorrente_bln == true)) {
            data.cron_expressao = self._objConfigs.data.cronExpressao;
            if (!data.cron_expressao) {
                blnSave = commonFunctions.verificationData(data.cron_expressao, {
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

        blnSave = self.#functionsParticipacao._saveVerificationsParticipantes(data, blnSave);

        return blnSave;
    }
}