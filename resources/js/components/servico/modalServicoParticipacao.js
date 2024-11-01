import { commonFunctions } from "../../commons/commonFunctions";
import { modalRegistrationAndEditing } from "../../commons/modal/modalRegistrationAndEditing";
import { RequestsHelpers } from "../../helpers/RequestsHelpers";
import { ServicoParticipacaoModule } from "../../modules/ServicoParticipacaoModule";
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
            base: undefined,
            baseParticipacaoPreset: window.apiRoutes.baseParticipacaoPreset,
            baseParticipacaoTipo: window.apiRoutes.baseServicoParticipacaoTipoTenant,
        },
        sufixo: 'ModalServicoParticipacao',
        data: {
            porcentagemOcupada: 0,
            participantesNaTela: [],
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
            idModal: "#modalServicoParticipacao",
        });

        this._objConfigs = Object.assign(this._objConfigs, this.#objConfigs);
        this._promisseReturnValue = Object.assign(this._promisseReturnValue, this.#promisseReturnValue);
        this._dataEnvModal = Object.assign(this._dataEnvModal, this.#dataEnvModal);
        this._objConfigs.url.base = options.urlApi;
        const objData = {
            objConfigs: this._objConfigs,
            extraConfigs: {
                typeParent: 'modal',
            }
        }
        this.#functionsServicoParticipacao = new ServicoParticipacaoModule(this, objData);

        this.#addEventosPadrao();
    }

    async modalOpen() {
        const self = this;

        await commonFunctions.loadingModalDisplay(true, { message: 'Carregando informações da participação...' });

        await self.#buscarPresetParticipacaoTenant();
        if (! await self.#buscarDados()) {
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
                        $(self.getIdModal).find('select[name="preset_id"]').val(response.selected.id);
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

    _clearForm() {
        const self = this;
        $(`#divParticipantes${self._objConfigs.sufixo}`).html('');
        self.#functionsServicoParticipacao._atualizaPorcentagemLivre();
    }

    async #buscarDados() {
        const self = this;

        try {
            self._clearForm();
            const response = await RequestsHelpers.get({
                urlApi: self._objConfigs.url.base,
            });
            if (response?.data) {
                const responseData = response.data;

                await Promise.all(
                    responseData.map(async (participante) => {
                        const integrantes = participante.integrantes ?? [];
                        delete participante.integrantes;
                        const item = await self.#functionsServicoParticipacao._inserirParticipanteNaTela(participante);
                        await Promise.all(
                            integrantes.map(async (integrante) => {
                                await self.#functionsServicoParticipacao._inserirIntegrante(item, integrante);
                            })
                        );
                    })
                );
            }
            return true;
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
            return false;
        }
    }

    async #buscarPresetParticipacaoTenant(selected_id = null) {
        const self = this;
        let options = selected_id ? { selectedIdOption: selected_id } : {};
        const selModulo = $(self.getIdModal).find('select[name="preset_id"]');
        await commonFunctions.fillSelect(selModulo, self._objConfigs.url.baseParticipacaoPreset, options);
    }

    async saveButtonAction() {
        const self = this;
        let data = {
            participantes: self._objConfigs.data.participantesNaTela,
        }

        if (self.#saveVerifications(data)) {
            await self._save(data, self._objConfigs.url.base, { fieldRegisterName: 'registers' });
        }
    }

    #saveVerifications(data) {
        const self = this;
        let blnSave = true;

        let porcentagemOcupada = self._objConfigs.data.porcentagem_ocupada;
        if (porcentagemOcupada > 0 && porcentagemOcupada < 100 || porcentagemOcupada > 100) {
            commonFunctions.generateNotification(`As somas das porcentagens deve ser igual a 100%. Porcentagem informada ${commonFunctions.formatWithCurrencyCommasOrFraction(porcentagemOcupada)}%.`, 'warning');
            blnSave = false;
        }
        if (!data.participantes || data.participantes.length == 0) {
            commonFunctions.generateNotification('E necessário informar pelo menos um participante.', 'warning');
            blnSave = false;
        } else {
            for (const participante of data.participantes) {
                if (participante.participacao_registro_tipo_id == window.Enums.ParticipacaoRegistroTipoEnum.GRUPO && (!participante.integrantes || participante.integrantes.length == 0)) {
                    commonFunctions.generateNotification('E necessário informar pelo menos um integrante no grupo.', 'warning');
                    blnSave = false;
                    break;
                }
            }
        }

        return blnSave;
    }

}