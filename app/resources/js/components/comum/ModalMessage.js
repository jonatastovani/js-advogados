import { CommonFunctions } from "../../commons/CommonFunctions";
import { ModalDefault } from "../../commons/modal/ModalDefault";

/**
 * ModalMessage class.
 * 
 * Initializes the modal properties and adds event listeners to its buttons.
 */
export class ModalMessage extends ModalDefault {

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
            idModal: "#ModalMessage",
        });

        this._dataEnvModal = CommonFunctions.deepMergeObject(this._dataEnvModal, this.#dataEnvModal);
        this._promisseReturnValue = CommonFunctions.deepMergeObject(this._promisseReturnValue, this.#promisseReturnValue);
        this.#addEventsDefault();
    }

    async modalOpen() {
        const self = this;
        const modal = $(self.getIdModal);

        try {
            await self.fillInfo()

            if (([1, 2].findIndex((item) => item == self._dataEnvModal.idDefaultButton)) != -1) {
                if (self._dataEnvModal.idDefaultButton == 1) {
                    self.setActionQueueOpen = self._executeFocusElementOnModal(modal.find('.confirmYes')[0], 1000);
                } else {
                    self.setActionQueueOpen = self._executeFocusElementOnModal(modal.find('.confirmNo')[0], 1000);
                }
            } else {
                self.setActionQueueOpen = self._executeFocusElementOnModal(modal.find('.confirmNo')[0], 1000);
            }

            await self._modalHideShow();
            self.setReadyQueueOpen;
            return await self._modalOpen();
        } catch (error) {
            CommonFunctions.generateNotificationErrorCatch(error);
            return await self._returnPromisseResolve();
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
        } else {
            const message = 'Nenhuma mensagem foi definida';
            console.error(message, self._dataEnvModal);
            throw new Error(message);
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
