import { commonFunctions } from "../../commons/commonFunctions";
import { ModalRegistrationAndEditing } from "../../commons/modal/ModalRegistrationAndEditing";

export class modalCode extends ModalRegistrationAndEditing {

    /**
     * Configuração local do modal
     */
    #objConfigs = {
        url: {
            base: undefined,
        },
    };

    #dataEnvModal = {
        idRegister: undefined
    }

    constructor() {
        super({
            idModal: "#modalCode",
        });

        this._objConfigs = Object.assign(this._objConfigs, this.#objConfigs);
        this._dataEnvModal = Object.assign(this._dataEnvModal, this.#dataEnvModal);
    }

    async modalOpen() {
        const self = this;
        this.#addEventosPadrao();
        if (await self.#buscarDados()) {
            await self._modalHideShow();
            return await self._modalOpen();
        }
        return await self._modalOpen();
    }

    #addEventosPadrao() {
        const self = this;
        if (self._dataEnvModal.url) {
            self._objConfigs.url.base = self._dataEnvModal.url;
        }

        $(self.getIdModal).find('.btn-refresh').on('click', function () {
            self.#buscarDados();
            commonFunctions.generateNotification('Registro atualizado com sucesso', 'success');
        });

    }

    async #buscarDados() {
        const self = this;
        const dataEnv = self._dataEnvModal;
        let setMessage = new Set();

        if (!self._objConfigs.url.base) {
            setMessage.add('URL não informada');
        }

        if (!dataEnv.idRegister) {
            setMessage.add('ID não informado');
        }

        if (setMessage.size) {
            commonFunctions.generateNotification('Não foi possível carregar o registro. Verifique os seguintes erros:', 'warning', { itemsArray: setMessage.values() });
            return false;
        }

        try {
            const response = await self._getRecurse();
            if (response) {
                $(self.getIdModal).find('.codeBlock').html(response.data.code);
                $(self.getIdModal).find('.class').html(response.data.class);
                $(self.getIdModal).find('.path').html(response.data.path);
            }
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
        }
        return true;
    }

    _modalReset() {
        const self = this;
        const modal = $(self.getIdModal);
        modal.find('.codeBlock, .class, .path').html('');
    }
}