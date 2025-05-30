import { CommonFunctions } from "../../commons/CommonFunctions";
import { EnumAction } from "../../commons/EnumAction";
import { ModalRegistrationAndEditing } from "../../commons/modal/ModalRegistrationAndEditing";
import { DateTimeHelper } from "../../helpers/DateTimeHelper";
import { Select2Helpers } from "../../helpers/Select2Helpers";
import { TenantDataHelper } from "../../helpers/TenantDataHelper";
import { ParticipacaoModule } from "../../modules/ParticipacaoModule";
import { QueueManager } from "../../utils/QueueManager";
import { ModalContaTenant } from "../tenant/ModalContaTenant";
import { ModalLancamentoCategoriaTipoTenant } from "../tenant/ModalLancamentoCategoriaTipoTenant";
import { ModalTagTenant } from "../tenant/ModalTagTenant";

export class ModalLancamentoGeral extends ModalRegistrationAndEditing {

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
            liquidado_migracao_bln: false,
            lancamento_tipo: undefined,
        },
        modoOperacao: undefined,
        participacao: {
            perfis_busca: undefined,
            participacao_tipo_tenant: {
                configuracao_tipo: undefined,
            },
        },
        domainCustom: {
            applyBln: true,
        },
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
            idModal: "#ModalLancamentoGeral",
        });

        this._objConfigs = CommonFunctions.deepMergeObject(this._objConfigs, this.#objConfigs);
        this._promisseReturnValue = CommonFunctions.deepMergeObject(this._promisseReturnValue, this.#promisseReturnValue);
        this._dataEnvModal = CommonFunctions.deepMergeObject(this._dataEnvModal, this.#dataEnvModal);
        this._action = EnumAction.POST;
        const objData = {
            objConfigs: this._objConfigs,
            extraConfigs: {
                typeParent: 'modal',
                modeParent: 'searchAndUse',
            }
        }

        switch (options.modoOperacao) {
            case window.Enums.LancamentoTipoEnum.LANCAMENTO_GERAL:
                this._objConfigs.url.base = this._objConfigs.url.baseLancamentoGeral;
                // this._objConfigs.participacao.participacao_tipo_tenant.configuracao_tipo = window.Enums.ParticipacaoTipoTenantConfiguracaoTipoEnum.LANCAMENTO_GERAL;
                break;

            case window.Enums.LancamentoTipoEnum.LANCAMENTO_AGENDAMENTO:
                this._objConfigs.url.base = this._objConfigs.url.baseLancamentoAgendamento;
                break;

            case window.Enums.LancamentoTipoEnum.LANCAMENTO_RESSARCIMENTO:
                this._objConfigs.url.base = this._objConfigs.url.baseLancamentoRessarcimento;
                break;

            default:
                throw new Error(`Modo de operação (${options.modoOperacao ?? 'indefinido'}) inválido.`);
        }

        this._objConfigs.modoOperacao = options.modoOperacao;

        this.#functionsParticipacao = new ParticipacaoModule(this, objData);
        this.#select2QueueManager = new QueueManager();  // Cria a fila
    }

    #configuracaoParticipacaoRessarcimento() {
        this._objConfigs.participacao.perfis_busca = window.Statics.PerfisPermitidoParticipacaoRessarcimento;
        this._objConfigs.participacao.participacao_tipo_tenant.configuracao_tipo = window.Enums.ParticipacaoTipoTenantConfiguracaoTipoEnum.LANCAMENTO_RESSARCIMENTO;
        // this._objConfigs.participacao.valor_tipo_permitido = ['valor_fixo'];
    }

    async modalOpen() {
        const self = this;

        try {
            await CommonFunctions.loadingModalDisplay(true, { message: 'Carregando informações do lançamento...' });

            await self.#addEventosPadrao();
            await self.#buscarMovimentacoesTipo();

            if (self._dataEnvModal.idRegister) {
                await self.#buscarDados();
            } else {

                await self.#verificaLiquidadoMigracao();
                await self.#buscarContas();
                await self.#buscarLancamentoCategoriaTipoTenant();

                if (!self.#functionsParticipacao.getExibirPainelParticipantesPersonalizaveisBln) {
                    self.#functionsParticipacao._inserirParticipanteObrigatorioEmpresaParticipacaoGeral();
                }
            }

            self._queueCheckDomainCustom.setReady();
            await CommonFunctions.loadingModalDisplay(false);
            await self._modalHideShow();
            self.#addEventoSelect2();

            return await self._modalOpen();

        } catch (error) {
            CommonFunctions.generateNotificationErrorCatch(error);
            await CommonFunctions.loadingModalDisplay(false);
        }

        return await self._returnPromisseResolve();
    }

    async #addEventosPadrao() {
        const self = this;
        const modal = $(self._idModal);

        self._modalReset();

        CommonFunctions.handleModal(self, modal.find('.openModalConta'), ModalContaTenant, self.#buscarContas.bind(self));

        CommonFunctions.handleModal(self, modal.find('.openModalLancamentoCategoriaTipoTenant'), ModalLancamentoCategoriaTipoTenant, self.#buscarLancamentoCategoriaTipoTenant.bind(self));

        modal.find('.openModalTag').on('click', async function () {
            const btn = $(this);
            CommonFunctions.simulateLoading(btn);
            try {
                const objModal = new ModalTagTenant();
                objModal.setDataEnvModal = {
                    tag_tipo: window.Enums.TagTipoTenantEnum.LANCAMENTO_GERAL,
                }
                await self._modalHideShow(false);
                const response = await objModal.modalOpen();
                self._executeFocusElementOnModal(this);
            } catch (error) {
                CommonFunctions.generateNotificationErrorCatch(error);
            } finally {
                CommonFunctions.simulateLoading(btn, false);
                await self._modalHideShow();
            }
        });

        CommonFunctions.applyCustomNumberMask(modal.find('.campo-monetario'), { format: '#.##0,00', reverse: true });

        self._objConfigs.data.lancamento_tipo = self._objConfigs.modoOperacao;
        switch (self._objConfigs.modoOperacao) {
            case window.Enums.LancamentoTipoEnum.LANCAMENTO_AGENDAMENTO:

                switch (self._dataEnvModal.agendamento_tipo) {

                    case window.Enums.LancamentoTipoEnum.LANCAMENTO_GERAL:
                        self._updateModalTitle('Agendamento Comum');
                        break;

                    case window.Enums.LancamentoTipoEnum.LANCAMENTO_RESSARCIMENTO:
                        self._updateModalTitle('Agendamento Ressarcimento');
                        self.#configuracaoParticipacaoRessarcimento();
                        break;

                    default:
                        throw new Error(`Tipo de agendamento (${self._dataEnvModal.agendamento_tipo ?? 'indefinido'}) inválido.`);
                }

                self._objConfigs.data.lancamento_tipo = self._dataEnvModal.agendamento_tipo;
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

            case window.Enums.LancamentoTipoEnum.LANCAMENTO_RESSARCIMENTO:
                self._updateModalTitle('Ressarcimento/Compensação');
                self.#configuracaoParticipacaoRessarcimento();
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
            Select2Helpers.addEventsSelect2Api(modal.find('select[name="tags"]'), `${self._objConfigs.url.baseTagTenant}/select2`, {
                dataAppend: {
                    tipo: window.Enums.TagTipoTenantEnum.LANCAMENTO_GERAL,
                },
                dropdownParent: modal,
                multiple: true,
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
        $(self.getIdModal).find('.div-liquidado-migracao').hide();
        $(`#liquidado_migracao_bln${self.getSufixo}`).prop('checked', false).attr('disabled', true);
    }

    async #buscarDados() {
        const self = this;

        self._clearForm();
        const response = await self._getRecurse();

        if (response?.data) {
            const responseData = response.data;

            self._action = EnumAction.PUT;
            self._objConfigs.data.idRegister = self._dataEnvModal.idRegister;

            const descricao = responseData.descricao;
            const valor_esperado = CommonFunctions.formatWithCurrencyCommasOrFraction(responseData.valor_esperado);
            const form = $(self.getIdModal).find('.formRegistration');

            switch (self._objConfigs.modoOperacao) {

                case window.Enums.LancamentoTipoEnum.LANCAMENTO_AGENDAMENTO:

                    switch (self._dataEnvModal.agendamento_tipo) {

                        case window.Enums.LancamentoTipoEnum.LANCAMENTO_GERAL:
                            self._updateModalTitle('Editar Agendamento Comum');
                            responseData.participantes.map(item => self.#functionsParticipacao._pushObjetoParticipanteNaTela(item));
                            break;

                        case window.Enums.LancamentoTipoEnum.LANCAMENTO_RESSARCIMENTO:
                            self._updateModalTitle('Editar Agendamento Ressarcimento');
                            self.#functionsParticipacao._inserirParticipantesEIntegrantes(responseData.participantes);
                            break;

                        default:
                            throw new Error(`Tipo de agendamento (${self._dataEnvModal.agendamento_tipo ?? 'indefinido'}) inválido.`);
                    }

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
                    break;

                case window.Enums.LancamentoTipoEnum.LANCAMENTO_RESSARCIMENTO:
                    self._updateModalTitle(`Editar ressarcimento ${responseData.numero_lancamento ?? null}`);
                    self.#functionsParticipacao._inserirParticipantesEIntegrantes(responseData.participantes);

                    break;

                default:
                    self._updateModalTitle(`Editar lancamento ${responseData.numero_lancamento ?? null}`);
                    responseData.participantes.map(item => self.#functionsParticipacao._pushObjetoParticipanteNaTela(item));
                    break;
            }

            await self.#buscarContas(responseData.conta_id);
            await self.#buscarLancamentoCategoriaTipoTenant(responseData.categoria_id)

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

    /**
     * Verifica se o tenant tem a opção de lancamento_liquidado_migracao_sistema_bln ativa e define a visibilidade da opção de liquidado migração.
     * Esta execução deve ser realizada depois do buscarDadosPagamentoTipo, pois verifica também o tipo do pagamento.
     */
    async #verificaLiquidadoMigracao() {
        const self = this;
        const tenantData = await TenantDataHelper.getTenantData();

        const permiteLiquidadoMigracao = window.Statics.LancamentoTipoQuePermiteLiquidadoMigracao.includes(self._objConfigs.data.lancamento_tipo);

        self.#visibilidadeLiquidadoMigracao(tenantData?.lancamento_liquidado_migracao_sistema_bln && permiteLiquidadoMigracao);
    }

    #visibilidadeLiquidadoMigracao(statusVisibilidade = false) {
        const self = this;

        const ckbLiquidadoMigracao = $(`#liquidado_migracao_bln${self.getSufixo}`);

        if (statusVisibilidade) {
            $(self.getIdModal).find('.div-liquidado-migracao').show();

            ckbLiquidadoMigracao.prop('checked', false).attr('disabled', false);

            ckbLiquidadoMigracao.on('change', () => {
                const statusChecked = ckbLiquidadoMigracao.is(':checked');
                self._objConfigs.data.liquidado_migracao_bln = statusChecked;
            });
        } else {
            $(self.getIdModal).find('.div-liquidado-migracao').hide();
            ckbLiquidadoMigracao.prop('checked', false).attr('disabled', true);
            self._objConfigs.data.liquidado_migracao_bln = false;
        }
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

    async #buscarMovimentacoesTipo(selected_id = 2) {
        try {
            const arrayOpcoes = window.Statics.TiposMovimentacaoParaLancamentos;
            const self = this;
            const select = $(`#movimentacao_tipo_id${self.getSufixo}`);
            await CommonFunctions.fillSelectArray(select, arrayOpcoes, { selectedIdOption: selected_id });
            return true;
        } catch (error) {
            return false;
        }
    }

    async #buscarLancamentoCategoriaTipoTenant(selected_id = null) {
        try {
            const self = this;
            let options = { outInstanceParentBln: true };
            selected_id ? options.selectedIdOption = selected_id : null;
            const select = $(`#categoria_id${self.getSufixo}`);
            await CommonFunctions.fillSelect(select, self._objConfigs.url.baseLancamentoCategoriaTipoTenant, options);
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
        let data = CommonFunctions.getInputsValues(formRegistration[0]);
        data.valor_esperado = CommonFunctions.removeCommasFromCurrencyOrFraction(data.valor_esperado);
        data.participantes = self.#functionsParticipacao._getParticipantesNaTelaFiltrado();
        data.tags = self.#getTags();
        data.liquidado_migracao_bln = self._objConfigs.data.liquidado_migracao_bln;

        if (self.#saveVerifications(data)) {
            self._save(data, self._objConfigs.url.base);
        }
    }

    #saveVerifications(data) {
        const self = this;
        const formRegistration = $(self.getIdModal).find('.formRegistration');

        let blnSave = CommonFunctions.verificationData(data.movimentacao_tipo_id, {
            field: formRegistration.find('select[name="movimentacao_tipo_id"]'),
            messageInvalid: 'O tipo de movimentação deve ser selecionado.',
        });

        blnSave = CommonFunctions.verificationData(data.categoria_id, {
            field: formRegistration.find('select[name="categoria_id"]'),
            messageInvalid: 'A categoria deve ser selecionada.',
            setFocus: blnSave == true,
            returnForcedFalse: blnSave == false
        });

        blnSave = CommonFunctions.verificationData(data.conta_id, {
            field: formRegistration.find('select[name="conta_id"]'),
            messageInvalid: 'A conta deve ser selecionada.',
            setFocus: blnSave == true,
            returnForcedFalse: blnSave == false
        });

        blnSave = CommonFunctions.verificationData(data.descricao, {
            field: formRegistration.find('input[name="descricao"]'),
            messageInvalid: 'A descrição do lançamento deve ser informada.',
            setFocus: blnSave == true,
            returnForcedFalse: blnSave == false
        });

        blnSave = CommonFunctions.verificationData(data.valor_esperado, {
            field: formRegistration.find('input[name="valor_esperado"]'),
            messageInvalid: 'O valor do lançamento deve ser informado.',
            setFocus: blnSave == true,
            returnForcedFalse: blnSave == false
        });

        if (self._objConfigs.modoOperacao == window.Enums.LancamentoTipoEnum.LANCAMENTO_AGENDAMENTO) {

            data.agendamento_tipo = self._dataEnvModal.agendamento_tipo;

            if (data.recorrente_bln && data.recorrente_bln == true) {
                data.cron_expressao = self._objConfigs.data.cronExpressao;
                if (!data.cron_expressao) {
                    blnSave = CommonFunctions.verificationData(data.cron_expressao, {
                        field: formRegistration.find('select[name="cronDay"]'),
                        messageInvalid: 'Uma recorrência deve ser configurada.',
                        setFocus: blnSave == true,
                        returnForcedFalse: blnSave == false
                    });
                }
            }

        } else {
            blnSave = CommonFunctions.verificationData(data.data_vencimento, {
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
