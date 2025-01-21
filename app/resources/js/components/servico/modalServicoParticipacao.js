import { commonFunctions } from "../../commons/commonFunctions";
import { enumAction } from "../../commons/enumAction";
import { modalRegistrationAndEditing } from "../../commons/modal/modalRegistrationAndEditing";
import { RequestsHelpers } from "../../helpers/RequestsHelpers";
import { ParticipacaoModule } from "../../modules/ParticipacaoModule";
import { modalParticipacaoTipoTenant } from "../tenant/modalParticipacaoTipoTenant";

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
            baseParticipacaoTipo: window.apiRoutes.baseParticipacaoTipoTenant,
        },
        sufixo: 'ModalServicoParticipacao',
        data: {
            porcentagemOcupada: 0,
            participantesNaTela: [],
            participacao_tipo_tenant: {
                configuracao_tipo: window.Enums.ParticipacaoTipoTenantConfiguracaoTipoEnum.LANCAMENTO_SERVICO,
            },
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
        this._action = enumAction.POST;
        this._objConfigs.url.base = options.urlApi;
        const objData = {
            objConfigs: this._objConfigs,
            extraConfigs: {
                typeParent: 'modal',
                modeParent: 'searchAndUse',
            }
        }
        this.#functionsServicoParticipacao = new ParticipacaoModule(this, objData);

        this.#addEventosPadrao();
    }

    async modalOpen() {
        const self = this;

        await commonFunctions.loadingModalDisplay(true, { message: 'Carregando informações da participação...' });

        await self.#functionsServicoParticipacao._buscarPresetParticipacaoTenant();
        if (! await self._buscarDados()) {
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
                const objModal = new modalParticipacaoTipoTenant();
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
    }

    _clearForm() {
        const self = this;
        $(`#divParticipantes${self._objConfigs.sufixo}`).html('');
        self.#functionsServicoParticipacao._atualizaPorcentagemLivre();
    }

    async _buscarDados() {
        const self = this;

        try {
            self._clearForm();
            const response = await RequestsHelpers.get({
                urlApi: self._objConfigs.url.base,
            });
            if (response?.data) {
                self.#functionsServicoParticipacao._inserirParticipantesEIntegrantes(response.data);
            }
            return true;
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
            return false;
        }
    }

    async saveButtonAction() {
        const self = this;
        let data = {
            participantes: self._objConfigs.data.participantesNaTela,
        }

        if (self.#functionsServicoParticipacao._saveVerificationsParticipantes(data)) {
            await self._save(data, self._objConfigs.url.base, { fieldRegisterName: 'registers' });
        }
    }
}