import { commonFunctions } from "../../commons/commonFunctions";
import { connectAjax } from "../../commons/connectAjax";
import { enumAction } from "../../commons/enumAction";
import { modalRegistrationAndEditing } from "../../commons/modal/modalRegistrationAndEditing";
import { DateTimeHelper } from "../../helpers/DateTimeHelper";
import { UUIDHelper } from "../../helpers/UUIDHelper";
import { ServicoParticipacaoModule } from "../../modules/ServicoParticipacaoModule";
import { modalConta } from "./modalConta";

export class modalLancamentoServicoMovimentar extends modalRegistrationAndEditing {

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
        sufixo: 'ModalLancamentoServicoMovimentar',
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
            idModal: "#modalLancamentoServicoMovimentar",
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

        const btnAddDiluicao = $(self.getIdModal).find('.btn-add-diluicao');
        if (btnAddDiluicao) {
            self._objConfigs.data.contador_diluicao = 0;

            btnAddDiluicao.on('click', function () {

                self._objConfigs.data.contador_diluicao++;
                let contador = self._objConfigs.data.contador_diluicao;

                const rowDiluicao = $(self.getIdModal).find('.rowDiluicao');
                const newUuid = UUIDHelper.generateUUID();
                rowDiluicao.append(`
                    <div id="${newUuid}" class="col diluicao_adicionada">
                        <input type="hidden" name="nome" value="diluição #${contador}">
                        <input type="hidden" name="sufixo" value="${newUuid}">
                        <div class="row align-items-end">
                            <div class="col-5 mt-2">
                                <label for="diluicao_data${newUuid}" class="form-label">Vencimento diluição #${contador}</label>
                                <input type="date" id="diluicao_data${newUuid}" name="diluicao_data" class="form-control text-center">
                            </div>
                            <div class="col">
                                <div class="row align-items-end">
                                    <div class="col-9 mt-2">
                                        <label for="diluicao_valor${newUuid}" class="form-label">Valor diluição #${contador}</label>
                                        <div class="input-group">
                                            <div class="input-group-text"><label for="diluicao_valor${newUuid}">R$</label>
                                            </div>
                                            <input type="text" id="diluicao_valor${newUuid}" name="diluicao_valor"
                                                class="form-control text-end campo-monetario">
                                        </div>
                                    </div>
                                    <div class="col px-1 mt-2">
                                        <button type="button" class="btn btn-outline-primary border-0 btn-remove-diluicao">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `);

                $(`#${newUuid} input[name="diluicao_data"]`).trigger('focus');

                commonFunctions.applyCustomNumberMask($(`#${newUuid} input[name="diluicao_valor"]`), { format: '#.##0,00', reverse: true });

                $(`#${newUuid}`).find('.btn-remove-diluicao').on('click', function () {
                    $(`#${newUuid}`).remove();
                })
            })
        }
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

                await self.#functionsServicoParticipacao._inserirParticipantesEIntegrantes(participantes);

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

    saveButtonAction() {
        const self = this;
        const configuracao = self._objConfigs.data.lancamento_status_tipos.configuracao;
        const rowContaData = commonFunctions.getInputsValues($(self.getIdModal).find('.rowConta')[0]);
        const rowObservacaoData = commonFunctions.getInputsValues($(self.getIdModal).find('.rowObservacao')[0]);
        const rowRecebimentoData = commonFunctions.getInputsValues($(self.getIdModal).find('.rowRecebimento')[0]);

        let data = Object.assign(rowContaData, rowObservacaoData, rowRecebimentoData);
        if (configuracao.campos_opcionais) {

            for (const opcionais of Object.values(configuracao.campos_opcionais)) {

                const nomeClassRow = opcionais.row_class_name;
                switch (opcionais.parent_type) {
                    case 'array':

                        const nameChildClass = opcionais.children_class_name;
                        const nameParent = opcionais.parent_name;
                        const childrens = $(self.getIdModal).find(`.${nomeClassRow} .${nameChildClass}`);
                        let arrayData = [];
                        childrens.each((index, element) => {
                            let opcionalData = commonFunctions.getInputsValues(element);
                            arrayData.push(opcionalData);
                        });
                        data[nameParent] = arrayData;
                        break;

                    default:
                        const message = `Formato de opcional não reconhecido.`;
                        console.error(message);
                        commonFunctions.generateNotification(message);
                        return false;
                }
            }
        }

        data.participantes = self._objConfigs.data.participantesNaTela;
        data.referencia_id = self._objConfigs.data.idRegister;
        data.status_id = self._objConfigs.data.status_id;

        if (self.#saveVerifications(data)) {
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
            const rules = campo.form_request_rule.split('|');
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

        if (configuracao.campos_opcionais) {

            for (const opcional of Object.values(configuracao.campos_opcionais)) {

                switch (opcional.parent_type) {
                    case 'array':
                        const parent = data[opcional.parent_name];
                        if (parent) {
                            console.log(parent)
                            parent.forEach(conjunto => {
                                let nomeExibirCustom = conjunto.nome;
                                let sufixoCustom = conjunto.sufixo;
                                delete conjunto.nome;
                                delete conjunto.sufixo;

                                for (let campo of Object.keys(conjunto)) {
                                    const fieldParams = opcional.fields.find(item => item.nome == campo);

                                    const rules = fieldParams.form_request_rule.split('|');
                                    if (rules.find(rule => rule === 'numeric' || rule === 'integer')) {
                                        conjunto[fieldParams.nome] = commonFunctions.removeCommasFromCurrencyOrFraction(conjunto[fieldParams.nome]);
                                    }

                                    const blnSaveConjunto = commonFunctions.verificationData(conjunto[fieldParams.nome], {
                                        field: formRegistration.find(`#${fieldParams.nome}${sufixoCustom}`),
                                        messageInvalid: `Os campo <b>${fieldParams.nome_exibir}</b> da <b>${nomeExibirCustom}</b> deve ser informado.`,
                                        setFocus: blnSave === true,
                                        returnForcedFalse: blnSave === false
                                    });
                                }
                            });


                        }
                        // const nameChildClass = opcionais.children_class_name;
                        // const nameParent = opcionais.parent_name;
                        // const childrens = $(self.getIdModal).find(`.${nomeClassRow} .${nameChildClass}`);
                        // let arrayData = [];
                        // childrens.each((index, element) => {
                        //     let opcionalData = commonFunctions.getInputsValues(element);
                        //     arrayData.push(opcionalData);
                        // });
                        // data[nameParent] = arrayData;
                        break;
                }
            }
        }

        return blnSave;
    }
}