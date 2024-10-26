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
            baseParticipacaoTipo: window.apiRoutes.baseServicoParticipacaoTipoTenant,
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
        if (! await self.#preencherDados()) {
            await commonFunctions.loadingModalDisplay(false);
            return await self._returnPromisseResolve();
        };

        await commonFunctions.loadingModalDisplay(false);
        await self._modalHideShow();
        return await self._modalOpen();
    }

    #addEventosPadrao() {
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
                modal.find('.divTextPorcentagemLivre, .btnAplicarRestante').show('fast');
            } else {
                modal.find('.divTextPorcentagemLivre, .btnAplicarRestante').hide('fast');
            }
        }

        const valor = modal.find('input[name="valor"]');
        modal.find('input[name="valor_tipo"]').on('click', async function () {
            valor.off('input');
            valor.val('');
            valor.unmask();

            if ($(this).val() == 'porcentagem') {
                aplicarMascaraPorcentagem(valor);
                visibilidadeDadosPorcentagem(true);
            } else {
                commonFunctions.applyCustomNumberMask(valor, { format: '#.##0,00', reverse: true });
                visibilidadeDadosPorcentagem(false);
            }
        });

        aplicarMascaraPorcentagem(valor);

        modal.find('.btnAplicarRestante').on('click', async function () {
            const porcentagem_livre = self._objConfigs.data.porcentagem_livre;
            if (porcentagem_livre == 100) {
                valor.val(porcentagem_livre);
            } else if (porcentagem_livre > 0) {
                valor.val(commonFunctions.formatWithCurrencyCommasOrFraction(porcentagem_livre));
            }
        });
    }

    async #preencherDados() {
        const self = this;
        try {
            const modal = $(self.getIdModal);
            const dados = self._dataEnvModal.dados_participacao;
            let nome = '';

            switch (dados.participacao_registro_tipo_id) {
                case window.Enums.ParticipacaoRegistroTipoEnum.PERFIL:
                    modal.find('.lblTipoParticipante').html('Pessoa');
                    nome = dados.pessoa_perfil.pessoa.pessoa_dados.nome;
                    break;

                case window.Enums.ParticipacaoRegistroTipoEnum.GRUPO:
                    modal.find('.lblTipoParticipante').html('Grupo');
                    nome = dados.nome_grupo;
                    break;
            }

            const ocupada = self._dataEnvModal.porcentagem_ocupada ?? 0;
            const livre = (100 - ocupada);
            const valor = commonFunctions.formatWithCurrencyCommasOrFraction(dados.valor ?? 0);

            if (!livre) {
                modal.find('.btnAplicarRestante').attr('disabled', true);
            } else {
                modal.find('.btnAplicarRestante').removeAttr('disabled');
                self._objConfigs.data.porcentagem_livre = livre;
            }

            modal.find('.lblNome').html(nome);
            modal.find('.lblPorcentagemLivre').html(commonFunctions.formatWithCurrencyCommasOrFraction(livre));
            modal.find(`input[name="valor_tipo"][value="${dados.valor_tipo ?? 'porcentagem'}"]`).prop('checked', true).trigger('click');
            modal.find('input[name="valor"]').val(valor).trigger('input');
            modal.find('input[name="observacao"]').val(dados.observacao ?? '');
            modal.find('select[name="participacao_tipo_id"]').val(dados.participacao_tipo_id ?? 0);
            return true;
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
            return false;
        }
    }

    async #buscarTipoParticipacaoTenant(selected_id = null) {
        const self = this;
        let options = selected_id ? { selectedIdOption: selected_id } : {};
        const selModulo = $(self.getIdModal).find('select[name="participacao_tipo_id"]');
        await commonFunctions.fillSelect(selModulo, self._objConfigs.url.baseParticipacaoTipo, options);
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

        data.valor = commonFunctions.removeCommasFromCurrencyOrFraction(data.valor);

        if (data.valor > 0) {
            if (data.valor_tipo == 'porcentagem' && data.valor > self._objConfigs.data.porcentagem_livre) {
                commonFunctions.generateNotification('O <b>valor da participação</b> ultrapassa o valor da porcentagem livre.', 'warning');
                if (blnSave === true) {
                    self._executeFocusElementOnModal(formRegistration.find('input[name="valor"]'));
                }
                blnSave = false;
            }
        } else {
            commonFunctions.generateNotification('O <b>valor da participação</b> deve ser informado.', 'warning');
            if (blnSave === true) {
                self._executeFocusElementOnModal(formRegistration.find('input[name="valor"]'));
            }
            blnSave = false;
        }

        return blnSave;
    }

}