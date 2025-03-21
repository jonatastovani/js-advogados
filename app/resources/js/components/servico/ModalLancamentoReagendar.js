import { CommonFunctions } from "../../commons/CommonFunctions";
import { EnumAction } from "../../commons/EnumAction";
import { ModalRegistrationAndEditing } from "../../commons/modal/ModalRegistrationAndEditing";

export class ModalLancamentoReagendar extends ModalRegistrationAndEditing {

    #dataEnvModal = {
        title: undefined,
        mensagem: undefined,
        nome: undefined
    }

    /**
     * Configuração local do modal
     */
    #objConfigs = {
        url: {
            base: undefined,
        },
        sufixo: 'ModalLancamentoReagendar',
        domainCustom: {
            applyBln: true,
            inheritedBln: true,
        },
    };

    constructor(options = {}) {
        super({
            idModal: "#ModalLancamentoReagendar",
        });
        this._objConfigs = CommonFunctions.deepMergeObject(this._objConfigs, this.#objConfigs);
        this._dataEnvModal = CommonFunctions.deepMergeObject(this._dataEnvModal, this.#dataEnvModal);
        this._objConfigs.url.base = options.urlApi;
        this._action = EnumAction.PUT;

        this.#addEventosPadrao();
    }

    async modalOpen() {
        const self = this;

        self._queueCheckDomainCustom.setReady();

        if (self._dataEnvModal.data_atual) {
            $(self.getIdModal).find('input[name="data_vencimento"]').val(self._dataEnvModal.data_atual);
        }

        if (!self._checkDomainCustomInherited()) return await self._returnPromisseResolve();

        await self._modalHideShow();
        $(self.getIdModal).find('.formRegistration .focusRegister').trigger('focus');
        return await self._modalOpen();
    }

    #addEventosPadrao() {
        this.#eventosBotoes();
    }

    #eventosBotoes() {
        const self = this;
    }

    saveButtonAction() {
        const self = this;
        const formRegistration = $(self.getIdModal).find('.formRegistration');
        let data = CommonFunctions.getInputsValues(formRegistration[0]);

        if (self.#saveVerifications(data)) {
            self._save(data, self._objConfigs.url.base);
        }
    }

    #saveVerifications(data) {
        const self = this;
        const formRegistration = $(self.getIdModal).find('.formRegistration');

        let blnSave = CommonFunctions.verificationData(data.data_vencimento, { field: formRegistration.find('input[name="data_vencimento"]'), messageInvalid: "A data de vencimento deve ser informada.", setFocus: true });
        return blnSave;
    }

}
