import { commonFunctions } from "../../commons/commonFunctions";
import { enumAction } from "../../commons/enumAction";
import { modalRegistrationAndEditing } from "../../commons/modal/modalRegistrationAndEditing";
import { modalServicoParticipacaoTipoTenant } from "./modalServicoParticipacaoTipoTenant";

export class modalServicoParticipacao extends modalRegistrationAndEditing {

    #dataEnvModal = {
        idRegister: undefined,
        pagamento_tipo_tenant_id: undefined
    }

    /**
     * Configuração local do modal
     */
    #objConfigs = {
        url: {
            baseServicoParticipacaoTipoTenant: window.apiRoutes.baseServicoParticipacaoTipoTenant,
        },
        sufixo: 'ModalServicoParticipacao',
        data: {
            porcentagem_livre: 0,
        },
    };

    /** 
     * Conteúdo a ser retornado na promisse como resolve()
    */
    #promisseReturnValue = {
        refresh: false,
    };

    constructor(urlApi) {
        super({
            idModal: "#modalServicoParticipacao",
        });

        this._objConfigs = Object.assign(this._objConfigs, this.#objConfigs);
        this._promisseReturnValue = Object.assign(this._promisseReturnValue, this.#promisseReturnValue);
        this._dataEnvModal = Object.assign(this._dataEnvModal, this.#dataEnvModal);
        this._objConfigs.url.base = urlApi;
        this._action = enumAction.POST;

        this.#addEventosPadrao();
    }

    async modalOpen() {
        const self = this;
        await commonFunctions.loadingModalDisplay(true, { message: 'Carregando informações da participação...' });

        if (!self._dataEnvModal.dados_participacao.participacao_registro_tipo_id) {
            commonFunctions.generateNotification('Tipo de registro de participação não informado.', 'error');
            return await self._returnPromisseResolve();
        }

        await self.#buscarTipoParticipacaoTenant();
        await self.#preencherDados();

        await commonFunctions.loadingModalDisplay(false);
        await self._modalHideShow();
        return await self._modalOpen();
    }

    #addEventosPadrao() {
        this.#eventosBotoes();
    }

    #eventosBotoes() {
        const self = this;
        const modal = $(self._idModal);

        modal.find('.btnOpenModalTipoParticipacao').on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const objModal = new modalServicoParticipacaoTipoTenant();
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
                        self.#buscarTipoParticipacaoTenant(response.selected.id);
                    } else {
                        self.#buscarTipoParticipacaoTenant();
                    }
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading(btn, false);
                await self._modalHideShow();
            }
        });

        const aplicarMascaraPorcentagem = (campo) => {
            const regexPorcentagem = /^(100|[0-9]{1,2}(,\d{0,2})?)$/;
            campo.on('input', function () {
                if (!regexPorcentagem.test($(this).val())) {
                    $(this).val('');
                }
            });
        }

        const visibilidadeDadosPorcentagem = (status = true) => {
            if (status == true) {
                modal.find('.lblPorcentagemLivre, .btnAplicarRestante').show('fast');
            } else {
                modal.find('.lblPorcentagemLivre, .btnAplicarRestante').hide('fast');
            }
        }

        const participacao_valor = modal.find('input[name="participacao_valor"]');
        modal.find('input[name="tipo_valor_participacao"]').on('click', async function () {
            participacao_valor.off('input');
            participacao_valor.val('');
            participacao_valor.unmask();

            if ($(this).val() == 'porcentagem') {
                aplicarMascaraPorcentagem(participacao_valor);
                visibilidadeDadosPorcentagem(true);
            } else {
                commonFunctions.applyCustomNumberMask(participacao_valor, { format: '#.##0,00', reverse: true });
                visibilidadeDadosPorcentagem(false);
            }
        });

        aplicarMascaraPorcentagem(participacao_valor);

        modal.find('.btnAplicarRestante').on('click', async function () {
            const porcentagem_livre = self._objConfigs.data.porcentagem_livre;
            console.log(porcentagem_livre);
            if (porcentagem_livre == 100) {
                participacao_valor.val(porcentagem_livre);
            } else if (porcentagem_livre > 0) {
                participacao_valor.val(commonFunctions.formatWithCurrencyCommasOrFraction(porcentagem_livre));
            }
        });
    }

    async #preencherDados() {
        const self = this;
        const modal = $(self.getIdModal);
        const dados = self._dataEnvModal.dados_participacao;

        switch (dados.participacao_registro_tipo_id) {
            case 1:
                modal.find('.lblTipoParticipante').html('Pessoa');
                break;

            case 2:
                modal.find('.lblTipoParticipante').html('Grupo');
                break;
        }

        const ocupada = commonFunctions.removeCommasFromCurrencyOrFraction(String(self._dataEnvModal.porcentagem_ocupada ?? 0));
        const livre = (100 - ocupada);

        if (!livre) {
            modal.find('.btnAplicarRestante').attr('disabled', true);
        } else {
            modal.find('.btnAplicarRestante').removeAttr('disabled');
            self._objConfigs.data.porcentagem_livre = livre;
        }

        modal.find('.lblNome').html(dados.nome);
        modal.find('.lblPorcentagemLivre').html(commonFunctions.formatWithCurrencyCommasOrFraction(livre));
        modal.find(`input[name="tipo_valor_participacao"][value="${dados.tipo_valor ?? 'porcentagem'}"]`).prop('checked', true).trigger('input');
        modal.find('input[name="participacao_valor"]').val(dados.participacao_valor ?? '').trigger('input');
        modal.find('input[name="observacao"]').val(dados.observacao ?? '');
        modal.find('select[name="participacao_tipo_id"]').val(dados.participacao_tipo_id ?? 0);
    }

    async #buscarTipoParticipacaoTenant(selected_id = null) {
        const self = this;
        let options = selected_id ? { selectedIdOption: selected_id } : {};
        const selModulo = $(self.getIdModal).find('select[name="participacao_tipo_id"]');
        await commonFunctions.fillSelect(selModulo, self._objConfigs.url.baseServicoParticipacaoTipoTenant, options);
    }

    saveButtonAction() {
        const self = this;
        const formRegistration = $(self.getIdModal).find('.formRegistration');
        let data = commonFunctions.getInputsValues(formRegistration[0]);

        if (self.#saveVerifications(data)) {
            self._promisseReturnValue.register = data;
            self._promisseReturnValue.refresh = true;
            self._endTimer = true;
        }
    }

    #saveVerifications(data) {
        const self = this;
        const formRegistration = $(self.getIdModal).find('.formRegistration');

        let blnSave = commonFunctions.verificationData(data.participacao_tipo_id, { field: formRegistration.find('select[name="participacao_tipo_id"]'), messageInvalid: 'O <b>tipo de participação</b> deve ser informado.', setFocus: true });

        data.participacao_valor = commonFunctions.removeCommasFromCurrencyOrFraction(data.participacao_valor);

        if (data.participacao_valor > 0) {
            if (data.participacao_valor > self._objConfigs.data.porcentagem_livre) {
                commonFunctions.generateNotification('O <b>valor da participação</b> ultrapassa o valor da porcentagem livre.', 'warning');
                if (blnSave === true) {
                    self._executeFocusElementOnModal(formRegistration.find('input[name="participacao_valor"]'));
                }
                blnSave = false;
            } else {
                data[data.tipo_valor_participacao] = data.participacao_valor;
            }

        } else {
            commonFunctions.generateNotification('O <b>valor da participação</b> deve ser informado.', 'warning');
            if (blnSave === true) {
                self._executeFocusElementOnModal(formRegistration.find('input[name="participacao_valor"]'));
            }
            blnSave = false;
        }

        return blnSave;
    }

}