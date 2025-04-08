import { HotkeyManagerHelper } from "../../helpers/HotkeyManagerHelper";
import TenantTypeDomainCustomHelper from "../../helpers/TenantTypeDomainCustomHelper";
import { QueueManager } from "../../utils/QueueManager";
import { CommonFunctions } from "../CommonFunctions";
import InstanceManager from "../InstanceManager";

export class ModalDefault {

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
        modalConfig: undefined,
        /** Quando os campos de cadastro estiverem ativos.
        * Variável importante para os shortcuts do HotKey */
        modeNewOrEditingRegister: false,
        queues: {
            /** @type {QueueManager} */
            queueOpen: new QueueManager(),
        },
        domainCustom: {
            applyBln: false,
            domain_id: undefined,
            blocked_changes: false,
        },
    };

    /** @type {HotkeyManagerHelper} */
    _hotkeyManager;

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

        this._promisseReturnValue = CommonFunctions.deepMergeObject({
            refresh: false,
        }, objSuper.promisseReturnValue ?? {});

        this._focusElementWhenClosingModal = objSuper.focusElementWhenClosingModal ?? undefined;
        this._dataEnvModal = objSuper.dataEnvModal ?? {};
        this._objConfigs = CommonFunctions.deepMergeObject(this._objConfigs, objSuper.objConfigs ?? {});

        this._endTimer = false;
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

    /**
     * Ativa ou desativa o modo de cadastro ou edição de registros.
     * @param {boolean} bln - `true` para ativar o modo de cadastro/edição, `false` para desativar.
     */
    set setModeNewOrEditingRegister(bln) {
        this._objConfigs.modeNewOrEditingRegister = bln;
    }

    /**
     * Retorna o valor do modo de cadastro ou edição de registros.
     * @returns {boolean} `true` se o modo de cadastro/edição estiver ativo, `false` caso contrário.
     */
    get getModeNewOrEditingRegister() {
        return this._objConfigs.modeNewOrEditingRegister;
    }

    set _setTypeCurrentSearch(type) {
        this._objConfigs.typeCurrentSearch = type;
    }

    /**
         * Adiciona uma ação à fila.
         * 
         * @param {Function} action - Função a ser executada quando estiver pronto.
         */
    setActionQueueOpen(enqueue) {
        this._objConfigs.queues.queueOpen.enqueue(enqueue);
    }

    /**
     * Define que está pronto e processa a fila.
     * @param {Boolean} isReady - Indica se está pronto para processar a fila. Padrão é true.
     */
    setReadyQueueOpen() {
        this._objConfigs.queues.queueOpen.setReady();
    }

    /**
     * Define o ID forçado de domínio e marca as alterações como bloqueadas.
     * 
     * @param {number|string} id - ID do domínio a ser definido.
     */
    set setForcedDomainIdBlockedChanges(id) {
        this._objConfigs.domainCustom.blocked_changes = true;
        this._objConfigs.domainCustom.domain_id = id;
    }

    /**
     * Retorna o ID do domínio forçado que está bloqueado de alterações.
     * 
     * @returns {number|string|undefined} ID do domínio forçado e bloqueado, se definido. Caso contrário, retorna `undefined`.
     */
    get getForcedDomainIdBlockedChanges() {
        return this._objConfigs.domainCustom.domain_id;
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
        const btnSave = $(self.getIdModal).find(self._btnSave);

        const acaoSalvar = async () => {
            btnSave?.trigger('click');
        };

        btnSave.on("click", function (e) {
            e.preventDefault();
            if (self.getModeNewOrEditingRegister) {
                self.saveButtonAction();
            }
        });

        if (self._hotkeyManager) {
            self._hotkeyManager.registrar(self.getIdModal, ['ctrl+s', 'ctrl+shift+s'], acaoSalvar);
            self._hotkeyManager.ativarEscopo(self.getIdModal);
        }
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
     * @param {jQuery|string|HTMLElement} elem - O elemento (ou seletor jQuery) no qual o foco será aplicado.
     * @param {number} [timeOut=500] - O tempo de espera em milissegundos antes de aplicar o foco.
     */
    _executeFocusElementOnModal(elem, timeOut = 500) {
        if (!elem) return;

        const $el = $(elem);
        if (!$el.length) return;

        setTimeout(() => {
            const el = $el[0];
            if (el && typeof el.focus === 'function') {
                el.focus(); // preferível, mais confiável
            } else {
                $el.trigger('focus'); // fallback
            }
        }, timeOut);
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

        // InstanceManager.setVerboseTrueAutoFalse = true;
        this._hotkeyManager = InstanceManager.getOrCreateInstance('HotkeyManagerHelper', () => new HotkeyManagerHelper());
        this._hotkeyManager.setDebug;
        this.#addEventsDefault();

        return new Promise(function (resolve) {
            !self._objConfigs.modalInitialized ? self._objConfigs.modalInitialized = true : null;
            const checkConfirmation = setInterval(async function () {
                if (self._endTimer) {
                    clearInterval(checkConfirmation);

                    // Remover o escopo do HotkeyManager
                    if (self._hotkeyManager) self._hotkeyManager.removerEscopo(self.getIdModal);

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

    /**
     * Retorna uma promise que é rejeitada com o valor de `_promisseReturnValue`.
     * 
     * Essa função é utilizada para retornar o valor de `_promisseReturnValue` em uma promise rejeitada,
     * permitindo que o chamador trate a rejeição da promise antes de continuar a execução.
     * 
     * @return {Promise<never>} - Uma promise que é rejeitada com o valor de `_promisseReturnValue`.
     */
    async _returnPromisseReject() {
        const self = this;
        return new Promise(function (resolve, reject) { reject(self._promisseReturnValue) });
    }

    //#endregion

    //#region Verificações de domínio customizado

    /**
     * Verifica e herda o ID de domínio customizado para envio ao modal.
     * 
     * Esta função verifica se o tenant tem um domínio customizado configurado.
     * Se sim, define o `inherit_domain_id` dentro do objeto `dataEnvModal` 
     *
     * @param {Object} dataEnvModal - Objeto contendo os dados a serem enviados ao modal.
     *                                Caso `inherit_domain_id` ainda não exista, será inserido.
     * @throws {Error} Se o domínio customizado exigir um ID, mas ele não for encontrado.
     * @private
     */
    _checkDomainCustomInheritDataEnvModal(dataEnvModal = {}) {
        const self = this;

        // Verifica se há uma instância de domínio customizado ativa
        const instance = TenantTypeDomainCustomHelper.getInstanceTenantTypeDomainCustom;
        if (instance) {

            // Obtém o ID do domínio customizado que deve ser herdado
            const inherit_domain_id = self.getForcedDomainIdBlockedChanges;

            // Se o ID não existir, lança um erro
            if (!inherit_domain_id) {
                console.error(self._objConfigs.domainCustom);
                throw new Error('O ID de Unidade de Domínio a ser herdado não foi informado. Contate o suporte.');
            }

            // Define o inherit_domain_id no objeto
            dataEnvModal.inherit_domain_id ??= undefined;
            dataEnvModal.inherit_domain_id = inherit_domain_id;
        }
        return dataEnvModal;
    }

    /**
     * Verifica se o domínio customizado deve ser herdado e aplica a configuração.
     * 
     * Esta função verifica se o domínio customizado deve ser herdado (`inheritedBln`).
     * Se essa configuração estiver ativa, ela obtém o `inherit_domain_id` da variável
     * `dataEnvModal` e o define como o domínio forçado (`setForcedDomainIdBlockedChanges`).
     * 
     * Caso o `inherit_domain_id` não esteja presente, gera uma notificação de erro
     * e retorna `false`, impedindo a continuidade da lógica.
     *
     * @returns {boolean} Retorna `true` se o domínio for herdado corretamente, `false` caso contrário.
     * @private
     */
    _checkDomainCustomInherited() {
        const self = this;

        // Obtém a instância do gerenciador de domínio customizado
        const instance = TenantTypeDomainCustomHelper.getInstanceTenantTypeDomainCustom;
        if (!instance) return false; // Se não houver instância, encerra a função

        // Verifica se a configuração de herança de domínio está ativada
        if (self._objConfigs?.domainCustom?.inheritedBln) {

            // Obtém o ID do domínio herdado da variável `dataEnvModal`
            const domainId = self._dataEnvModal.inherit_domain_id;

            // Se não houver ID de domínio herdado, exibe erro e retorna falso
            if (!domainId) {
                CommonFunctions.generateNotification(
                    'O ID da Unidade de domínio herdada não foi enviado. Caso o erro persista, contate o suporte.',
                    'error'
                );
                console.error(self._dataEnvModal);
                return false;
            }

            // Define o domínio herdado como o domínio forçado
            self.setForcedDomainIdBlockedChanges = domainId;
        }

        return true; // Retorna `true` caso a herança de domínio tenha sido aplicada com sucesso
    }

    //#endregion
}
