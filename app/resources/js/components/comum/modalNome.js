import { commonFunctions } from "../../commons/commonFunctions";
import { enumAction } from "../../commons/enumAction";
import { modalRegistrationAndEditing } from "../../commons/modal/modalRegistrationAndEditing";

export class modalNome extends modalRegistrationAndEditing {

    #dataEnvModal = {
        title: undefined,
        mensagem: undefined,
        nome: undefined
    }

    /**
     * Configuração local do modal
     */
    #objConfigs = {
        sufixo: 'ModalNome',
    };

    /** 
     * Conteúdo a ser retornado na promisse como resolve()
    */
    #promisseReturnValue = {
        refresh: false,
    };

    constructor(urlApi) {
        super({
            idModal: "#modalNome",
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
        let blnOpen = true;

        if (!self._dataEnvModal.title) {
            blnOpen = false;
            commonFunctions.generateNotification('Título não informado.', 'error');
        }
        if (!self._dataEnvModal.mensagem) {
            blnOpen = false;
            commonFunctions.generateNotification('Mensagem não informada.', 'error');
        }

        if (!blnOpen) {
            return await self._returnPromisseResolve();
        } else {
            await self.#preencherDados();
        }

        await self._modalHideShow();
        return await self._modalOpen();
    }

    #addEventosPadrao() {
        this.#eventosBotoes();
    }

    #eventosBotoes() {
        const self = this;
    }

    async #preencherDados() {
        const self = this;
        const inputNome = $(self.getIdModal).find('input[name="nome"]');
        self._updateModalTitle(self._dataEnvModal.title);
        $(self.getIdModal).find('.lblMensagem').text(self._dataEnvModal.mensagem);
        if (self._dataEnvModal.nome) {
            inputNome.val(self._dataEnvModal.nome);
        }
        self._executeFocusElementOnModal(inputNome);
    }

    saveButtonAction() {
        const self = this;
        const formRegistration = $(self.getIdModal).find('.formRegistration');
        const data = commonFunctions.getInputsValues(formRegistration[0]);

        if (self.#saveVerifications(data)) {
            self._promisseReturnValue.name = data.nome;
            self._promisseReturnValue.refresh = true;
            self._endTimer = true;
        }
    }

    #saveVerifications(data) {
        const self = this;
        const formRegistration = $(self.getIdModal).find('.formRegistration');

        let blnSave = commonFunctions.verificationData(data.nome, { field: formRegistration.find('input[name="nome"]'), messageInvalid: "O Nome deve ser informado.", setFocus: true });
        return blnSave;
    }

}