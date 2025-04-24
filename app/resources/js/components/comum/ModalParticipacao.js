import { CommonFunctions } from "../../commons/CommonFunctions";
import { EnumAction } from "../../commons/EnumAction";
import { ModalRegistrationAndEditing } from "../../commons/modal/ModalRegistrationAndEditing";
import { ParticipacaoModule } from "../../modules/ParticipacaoModule";

export class ModalParticipacao extends ModalRegistrationAndEditing {

    #dataEnvModal = {
        // idRegister: undefined,
        // pagamento_tipo_tenant_id: undefined,
        inherit_domain_id: undefined,
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
        sufixo: 'ModalParticipacao',
        data: {
            porcentagemOcupada: 0,
            participantesNaTela: [],
        },
        participacao: {
            participacao_tipo_tenant: {
                // Padrão
                configuracao_tipo: window.Enums.ParticipacaoTipoTenantConfiguracaoTipoEnum.LANCAMENTO_SERVICO,
            },
        },
        domainCustom: {
            applyBln: true,
            inheritedBln: true,
        }
    };

    /** 
     * Conteúdo a ser retornado na promisse como resolve()
    */
    #promisseReturnValue = {
        refresh: false,
    };

    #functionsParticipacao;

    constructor(options = {}) {
        super({
            idModal: "#ModalParticipacao",
        });

        CommonFunctions.deepMergeObject(this._objConfigs, this.#objConfigs);
        CommonFunctions.deepMergeObject(this._promisseReturnValue, this.#promisseReturnValue);
        CommonFunctions.deepMergeObject(this._dataEnvModal, this.#dataEnvModal);

        this._action = EnumAction.POST;
        this._objConfigs.url.base = options.urlApi;
        const objData = {
            objConfigs: this._objConfigs,
            extraConfigs: {
                typeParent: 'modal',
                modeParent: 'searchAndUse',
            }
        }
        this.#functionsParticipacao = new ParticipacaoModule(this, objData);
    }

    async modalOpen() {
        const self = this;

        self._queueCheckDomainCustom.setReady();

        await CommonFunctions.loadingModalDisplay(true, { message: 'Carregando informações da participação...' });
   
        if (!self._checkDomainCustomInherited()) {
            await CommonFunctions.loadingModalDisplay(false);
            return await self._returnPromisseResolve()
        };

        if (! await self._buscarDados()) {
            await CommonFunctions.loadingModalDisplay(false);
            return await self._returnPromisseResolve();
        };

        await self.#functionsParticipacao._buscarPresetParticipacaoTenant();

        await CommonFunctions.loadingModalDisplay(false);
        await self._modalHideShow();
        return await self._modalOpen();
    }

    _clearForm() {
        const self = this;
        $(`#divParticipantes${self._objConfigs.sufixo}`).html('');
        self.#functionsParticipacao._atualizaPorcentagemLivre();
    }

    async _buscarDados() {
        const self = this;

        try {
            self._clearForm();
            const response = await self._get({
                urlApi: self._objConfigs.url.base,
            });
            if (response?.data) {
                self.#functionsParticipacao._inserirParticipantesEIntegrantes(response.data);
            }
            return true;
        } catch (error) {
            CommonFunctions.generateNotificationErrorCatch(error);
            return false;
        }
    }

    async saveButtonAction() {
        const self = this;
        let data = {
            participantes: self.#functionsParticipacao._getParticipantesNaTelaFiltrado(),
        }

        if (self.#functionsParticipacao._saveVerificationsParticipantes(data)) {
            await self._save(data, self._objConfigs.url.base, { fieldRegisterName: 'registers' });
        }
    }
}
