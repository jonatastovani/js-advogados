import { modalDefault } from "../../commons/modal/modalDefault";

export class modalLoading extends modalDefault {

    #openTimer;
    #endTimer;
    #dataEnvModal = {
        title: null,
        message: null,
    };

    constructor() {
        super({
            idModal: "#modalLoading",
        });

        this._dataEnvModal = Object.assign(this._dataEnvModal, this.#dataEnvModal);
        this.#openTimer = false;
        this.#endTimer = false;
        this.#addEventsDefault();
    }

    async modalOpen() {
        const self = this;
        await self.initEvents();
        await self._modalHideShow();
        return new Promise(function (resolve) {
            resolve({ status: 'open' });
        });
    }

    async modalClose() {
        const self = this;
        self._executeFocusElementOnModal(self._focusElementWhenClosingModal)

        await self._modalHideShow(false);
        return new Promise(function (resolve) {
            resolve({ status: 'close' });
        });
    }

    async initEvents() {
        const self = this;
        self.#inserirTitulo();
        self.#inserirMensagem();
        if (self._dataEnvModal.elementFocus) {
            self.setFocusElementWhenClosingModal = self._dataEnvModal.elementFocus
        }
    }

    #inserirTitulo() {
        const self = this;
        let title = 'Carregando...';
        if (self._dataEnvModal.title !== null) {
            title = self._dataEnvModal.title;
        }
        $(self.getIdModal).find('.modal-title').html(title);
    }

    #inserirMensagem() {
        const self = this;
        let message = 'Aguarde enquanto os dados estão sendo carregados...';
        if (self._dataEnvModal.message !== null) {
            message = self._dataEnvModal.message;
        }
        $(self.getIdModal).find('.modal-message').html(message);
    }

    #addEventsDefault() {
        const self = this;
        $(self.getIdModal).on('shown.bs.modal', function () {
            self.#endTimer = false;
            self.#openTimer = true;
        });
        $(self.getIdModal).on('hidden.bs.modal', function () {
            self.#openTimer = false;
            self.#endTimer = true;
        });
    }

}