import { commonFunctions } from "../../commons/commonFunctions";
import { enumAction } from "../../commons/enumAction";
import { modalRegistrationAndEditing } from "../../commons/modal/modalRegistrationAndEditing";

export class modalLancamentoReagendar extends modalRegistrationAndEditing {

    #dataEnvModal = {
        title: undefined,
        mensagem: undefined,
        nome: undefined
    }

    /**
     * Configuração local do modal
     */
    #objConfigs = {
        sufixo: 'ModalLancamentoReagendar',
    };

    /** 
     * Conteúdo a ser retornado na promisse como resolve()
    */
    #promisseReturnValue = {
        refresh: false,
    };

    constructor(options = {}) {
        super({
            idModal: "#modalLancamentoReagendar",
        });
        this._objConfigs = Object.assign(this._objConfigs, this.#objConfigs);
        this._promisseReturnValue = Object.assign(this._promisseReturnValue, this.#promisseReturnValue);
        this._dataEnvModal = Object.assign(this._dataEnvModal, this.#dataEnvModal);
        this._objConfigs.url.base = options.urlApi;
        this._action = enumAction.PUT;

        this.#addEventosPadrao();
    }

    async modalOpen() {
        const self = this;
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
        let data = commonFunctions.getInputsValues(formRegistration[0]);

        if (self.#saveVerifications(data)) {
            self._save(data, self._objConfigs.url.base);
        }
    }

    #saveVerifications(data) {
        const self = this;
        const formRegistration = $(self.getIdModal).find('.formRegistration');

        let blnSave = commonFunctions.verificationData(data.data_vencimento, { field: formRegistration.find('input[name="data_vencimento"]'), messageInvalid: "A data de vencimento deve ser informada.", setFocus: true });
        return blnSave;
    }

}