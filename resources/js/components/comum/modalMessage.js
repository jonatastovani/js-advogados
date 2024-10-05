import { commonFunctions } from "../../commons/commonFunctions";
import { modalDefault } from "../../commons/modal/modalDefault";

/**
 * ModalMessage class.
 * 
 * Initializes the modal properties and adds event listeners to its buttons.
 */
export class modalMessage extends modalDefault {

    #dataEnvModal = {
        title: null,
        message: null,
        idDefaultButton: undefined
    };

    #promisseReturnValue = {
        confirmResult: undefined
    };

    constructor() {
        super({
            idModal: "#modalMessage",
        });

        this._dataEnvModal = Object.assign(this._dataEnvModal, this.#dataEnvModal);
        this._promisseReturnValue = Object.assign(this._promisseReturnValue, this.#promisseReturnValue);
        this.#addEventsDefault();
    }

    async modalOpen() {
        const self = this;
        const modal = $(self.getIdModal);
        console.log(modal)
        if (await self.fillInfo()) {
            await self._modalHideShow();
            if (([1, 2].findIndex((item) => item == self._dataEnvModal.idDefaultButton)) != -1) {
                if (self._dataEnvModal.idDefaultButton == 1) {
                    self._executeFocusElementOnModal(modal.find('.confirmYes'));
                } else {
                    self._executeFocusElementOnModal(modal.find('.confirmNo'));
                }
            } else {
                self._executeFocusElementOnModal(modal.find('.confirmNo'));
            }
            return await self._modalOpen();
        } else {
            await self._modalClose();
            return new Promise(function (reject) { reject(self._promisseReturnValue) });
        }
    }

    async fillInfo() {
        const self = this;
        const modal = $(self.getIdModal);

        let title = 'Confirmação de Ação';
        if (self._dataEnvModal.title !== null) {
            title = self._dataEnvModal.title;
        }
        modal.find('.modal-title').html(title);

        if (self._dataEnvModal.message !== null) {
            modal.find('.message').html(self._dataEnvModal.message);
            return true;
        } else {
            const message = 'Nenhuma mensagem foi definida';
            commonFunctions.generateNotification(`Não foi possível abrir a confirmação.`, 'error', { itemsArray: [message] });
            console.error(message, self._dataEnvModal);
            return false;
        }
    }

    #addEventsDefault() {
        const self = this;
        const modal = $(self.getIdModal);

        modal.find(".confirmYes").on('click', function () {
            self._promisseReturnValue.confirmResult = true;
            self._setEndTimer = true;
        });
        modal.find(".confirmNo, .btn-close").on('click', function () {
            self._promisseReturnValue.confirmResult = false;
            self._setEndTimer = true;
        });
        modal.on('keydown', function (e) {
            if (e.key === 'Escape') {
                modal.find(".confirmNo").trigger('click');
                e.stopPropagation();
            }
        });
    }
}
