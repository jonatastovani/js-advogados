import { commonFunctions } from "../commonFunctions";

export class modalDefault {

    /**
     * URL do endpoint da Api
     */
    _urlApi;
    /**
     * ID do modal
     */
    _idModal;
    /** 
     * Conteúdo a ser retornado na promisse como resolve()
    */
    _promisseReturnValue;
    /**
     * Variável que executará o fim do setInterval de retoro da promisse com reject()
     */
    _endTimer;
    /**
     * Elemento foco ao fechar modal
     */
    _focusElementWhenClosingModal;
    /**
     * Variável para reservar o timeOut da consulta pelo search
     */
    _timerSearch;
    /** 
     * Dados ou parâmetros enviados para o modal.
     */
    _dataEnvModal;
    /**
     * Objeto para reservar configurações do modal
     */
    _objConfigs = {
        modalInitialized: false,
        sufixo: undefined,
        data: undefined,
        modalConfig: undefined
    };

    /**
     * Inicializa uma nova instância da classe modal.
     *
     * @param {Object} objSuper - O objeto contendo propriedades a serem atribuídas à instância do modal.
     * @param {String} objSuper.urlApi - A URL do endpoint da API.
     * @param {String} objSuper.idModal - O ID do modal.
     * @param {*} objSuper.promisseReturnValue - O valor a ser retornado na promessa.
     * @param {jQuery} objSuper.focusElementWhenClosingModal - O elemento a ser focado ao fechar o modal.
     * @param {Object} objSuper.dataEnvModal - Os dados ou parâmetros a serem passados ao modal.
     * @param {Object} objSuper.objConfigs - O objeto de configuração do modal.
     */
    constructor(objSuper) {
        this._urlApi = objSuper.urlApi ?? undefined;
        this._idModal = objSuper.idModal;

        this._promisseReturnValue = commonFunctions.deepMergeObject({
            refresh: false,
        }, objSuper.promisseReturnValue ?? {});

        this._focusElementWhenClosingModal = objSuper.focusElementWhenClosingModal ?? undefined;
        this._dataEnvModal = objSuper.dataEnvModal ?? {};
        this._objConfigs = commonFunctions.deepMergeObject(this._objConfigs, objSuper.objConfigs ?? {});

        this._endTimer = false;
        this.#addEventsDefault();
    }

    //#region Getters e Setters para propriedades privadas
    /**
     * Define a URL da API.
     * @param {String} url - URL da API.
     */
    set _setUrlApi(url) {
        this._urlApi = url;
    }

    /**
     * Retorna a URL da API.
     */
    get _getUrlApi() {
        return this._urlApi;
    }

    /**
     * Retorna o ID do Modal.
     */
    get getIdModal() {
        return this._idModal;
    }

    /**
     * Define o elemento de foco de fechamento.
     * @param {jQuery} elem - O elemento jQuery a ser definido como foco de fechamento.
     */
    set setFocusElementWhenClosingModal(elem) {
        this._focusElementWhenClosingModal = elem;
    }

    /**
     * Define o valor do timer de fim, utilizado na função modalOpen.
     * @param {Boolean} value - Novo valor para indicar o término do timer.
     */
    set _setEndTimer(value) {
        this._endTimer = value;
    }

    /**
     * Define o objeto de dados ou parâmetros enviados para o modal.
     * @param {Object} value - Novo valor para o objeto.
     */
    set setDataEnvModal(value) {
        this._dataEnvModal = value;
    }

    get getDataEnvModal() {
        return this._dataEnvModal;
    }

    /**
     * Retorna o sufixo da página.
     */
    get getSufixo() {
        return this._objConfigs.sufixo;
    }

    get modalInitialized() {
        return this._objConfigs.modalInitialized;
    }

    //#endregion

    #addEventsDefault() {
        this.#addEventBtnSave();
        this.#addEventBtnClose();
        this.#addEventBtnCancel();
        this.#addEventKeyDownEscape();
        this.#addEventClickBackdrop();
    }

    //#region Botões padrão

    _btnSave = ".btn-save";
    #addEventBtnSave() {
        const self = this;
        $(self.getIdModal).find(self._btnSave).on("click", function (e) {
            e.preventDefault();
            self.saveButtonAction();
        });
    }

    _btnClose = ".btn-close";
    #addEventBtnClose() {
        const self = this;
        $(self.getIdModal).find(self._btnClose).on("click", function () {
            self._setEndTimer = true;
        });
    }

    _btnCancel = ".btn-cancel";
    #addEventBtnCancel() {
        const self = this;
        $(self.getIdModal).find(self._btnCancel).on('click', function () {
            const formRegister = self._objConfigs.formRegister ?? false;
            if (formRegister == true) {
                if (typeof self.modalCancel === 'function') {
                    self.modalCancel();
                } else {
                    self._setEndTimer = true;
                }
            } else {
                self._setEndTimer = true;
            }
        });
    }

    _nameClassModal = "modal";
    #addEventClickBackdrop() {
        const self = this;

        const modalCloseOnClickBackdrop = self._objConfigs?.modalConfig?.modalCloseOnClickBackdrop;
        if (modalCloseOnClickBackdrop !== undefined || modalCloseOnClickBackdrop === false) {
            return;
        }

        $(self.getIdModal).on('click', function (e) {
            if ($(e.target).hasClass(self._nameClassModal)) {
                self._setEndTimer = true;
            }
        });
    }

    #addEventKeyDownEscape() {
        const self = this;

        const modalCloseOnEscape = self._objConfigs?.modalConfig?.modalCloseOnEscape;
        if (modalCloseOnEscape !== undefined || modalCloseOnEscape === false) {
            return;
        }

        $(self.getIdModal).on('keydown', function (e) {
            if (e.key === 'Escape') {
                e.stopPropagation();
                const formRegister = self._objConfigs.formRegister ?? false;
                if (formRegister == true) {
                    if (typeof self.modalCancel === 'function' && $(self.getIdModal).find('.divRegistrationFields').css('display') != 'none') {
                        self.modalCancel();
                    } else {
                        self._setEndTimer = true;
                    }
                } else {
                    self._setEndTimer = true;
                }
            }
        });
    }

    //#endregion

    /**
     * Executa o foco em um elemento dentro do modal após um tempo especificado.
     *
     * @param {jQuery} elem - O elemento jQuery no qual o foco será aplicado.
     * @param {number} [timeOut=500] - O tempo de espera em milissegundos antes de aplicar o foco.
     */
    _executeFocusElementOnModal(elem, timeOut = 500) {
        if (elem !== null && $(elem).length) {
            setTimeout(function () {
                $(elem).focus();
            }, timeOut);
        }
    }

    /**
     * Abre o modal atual e aguarda uma confirmação para fechamento.
     * 
     * A função inicializa o modal se ele ainda não estiver inicializado. 
     * Em seguida, ela cria um intervalo que verifica continuamente se a variável 
     * _endTimer foi definida como true, indicando que o modal deve ser fechado.
     * Quando o modal fecha, a função _modalClose é chamada e a promise é resolvida 
     * com o valor contido em _promisseReturnValue.
     * 
     * @return {Promise<*>} Uma promise que é resolvida com o valor de _promisseReturnValue
     * após o modal ser fechado.
     */
    async _modalOpen() {
        const self = this;
        return new Promise(function (resolve) {
            !self._objConfigs.modalInitialized ? self._objConfigs.modalInitialized = true : null;
            const checkConfirmation = setInterval(async function () {
                if (self._endTimer) {
                    clearInterval(checkConfirmation);
                    await self._modalClose();
                    resolve(self._promisseReturnValue);
                }
            }, 250);
        });
    }

    /**
     * Altera o estado do modal (mostra ou oculta).
     * Retorna uma promise que é resolvida quando o estado do modal é alterado.
     *
     * @param {boolean} [status=true] - Se true, o modal é exibido. Caso contrário, o modal é ocultado.
     *
     * @return {Promise<boolean>} - Uma promise que é resolvida com true se o estado do modal for alterado com sucesso.
     */
    async _modalHideShow(status = true) {
        return new Promise((resolve) => {
            const modalElement = document.querySelector(this._idModal);
            let modal = bootstrap.Modal.getOrCreateInstance(modalElement);

            // Verifica o estado atual do modal
            const isShown = modalElement.classList.contains('show');
            if ((status && isShown) || (!status && !isShown)) {
                // Se o estado já é o desejado, resolve imediatamente
                resolve(true);
                return;
            }

            // Define o evento correspondente
            const eventName = status ? 'shown.bs.modal' : 'hidden.bs.modal';

            // Callback para resolver a promise após o evento
            const callback = () => {
                modalElement.removeEventListener(eventName, callback);
                resolve(true);
            };

            // Adiciona o listener do evento
            modalElement.addEventListener(eventName, callback);

            // Executa a ação
            status ? modal.show() : modal.hide();
        });
    }

    /**
     * Fecha o modal atual, removendo eventos associados e resetando o estado conforme necessário.
     * 
     * A função verifica se o modal está visível e, se estiver, chama _modalHideShow para escondê-lo.
     * Remove todos os eventos associados ao modal e seus elementos filhos.
     * Se a função _modalReset existir, ela será chamada para resetar o estado do modal.
     * Finalmente, move o foco para o elemento especificado ao fechar o modal.
     */
    async _modalClose() {
        const self = this;
        const modal = $(self.getIdModal);
        if (modal.css('display') != 'none') {
            await self._modalHideShow(false);
        }
        modal.find("*").off();
        modal.off('keydown, click');
        if (typeof self._modalReset === 'function') {
            self._modalReset();
        }
        self._executeFocusElementOnModal(self._focusElementWhenClosingModal);
    }

    //#region Metodos auxiliares

    /**
     * Restaura o título do modal para o valor padrão.
     *
     * O valor padrão é obtido da propriedade `data-title-default` do elemento `.modal-title`.
     * Se o valor padrão for encontrado, a função `_updateModalTitle` é chamada para atualizar o título do modal.
     */
    _resetDefaultTitle() {
        const self = this;
        const titleDefault = $(self.getIdModal).find('.modal-title').data('title-default');
        if (titleDefault) {
            self._updateModalTitle(titleDefault);
        }
    }

    /**
     * Atualiza o título do modal com o valor especificado.
     * 
     * O título do modal é obtido da propriedade `title` do objeto `options` ou, se não for especificado, do primeiro elemento `.modal-title` encontrado no modal.
     * 
     * @param {string} html - O valor a ser atribuído ao título do modal.
     * @param {Object} [options={}] - O objeto com opções para personalizar a atualização do título do modal.
     * @param {jQuery} [options.title] - O elemento jQuery do título do modal a ser atualizado.
     */
    _updateModalTitle(html, options = {}) {
        const self = this;
        const {
            title = $(options.title ?? $(self.getIdModal).find('.modal-title')),
        } = options;
        title.html(html);
    }

    /**
     * Retorna uma promise que é resolvida com o valor de `_promisseReturnValue`.
     * 
     * Essa função é utilizada para retornar o valor de `_promisseReturnValue` em uma promise,
     * permitindo que o chamador espere a resolução da promise antes de continuar a execução.
     * 
     * @return {Promise<any>} - Uma promise que é resolvida com o valor de `_promisseReturnValue`.
     */
    async _returnPromisseResolve() {
        const self = this;
        return new Promise(function (resolve) { resolve(self._promisseReturnValue) });
    }

    //#endregion
}