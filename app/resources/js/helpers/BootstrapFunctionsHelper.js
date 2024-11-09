/**
 * Classe que fornece métodos para criar notificações usando o Bootstrap.
 */
export class BootstrapFunctionsHelper {
    /**
     * Cria ou obtém o container para os toasts e retorna o elemento.
     * @returns {HTMLElement} O elemento do container de toasts.
     */
    static #createOrGetDivToastContainer() {
        let toastContainer = document.getElementById('toastContainer');
        if (!toastContainer) {
            const body = document.querySelector('body');
            body.insertAdjacentHTML("beforeend", '<div class="toast-container position-fixed mh-100 overflow-auto top-0 bottom-0 end-0 p-3" id="toastContainer"></div>');
            toastContainer = document.getElementById('toastContainer');
        }
        return toastContainer;
    }

    /**
     * Cria uma nova notificação de toast.
     * @param {string} messageHTML - O HTML da mensagem da notificação.
     * @param {Object} options - As opções para configurar a notificação.
     * @param {string} [options.type=''] - O tipo de notificação ('success', 'error', 'warning' ou 'info').
     * @param {string} [options.title=null] - O título da notificação (padrão: Notificação).
     * @param {string} [options.ico=null] - O ícone da notificação (padrão: sino).
     * @param {number} [options.delay=5000] - O tempo de exibição da notificação em milissegundos (padrão = 5000ms).
     * @param {boolean} [options.autoHide=true] - Indica se a notificação deve desaparecer automaticamente (padrão: true).
     * @param {string} [options.customClass=''] - Classes CSS personalizadas para a notificação.
     * @param {Function} [options.onClose=null] - Callback a ser chamado quando a notificação é fechada.
     * @param {boolean} [options.autoShow=true] - Indica se a notificação deve aparecer automaticamente (padrão: true).
     * @param {string} [options.traceId=undefined] - Código de erro para ser renderizado juntamente com a mensagem.
     */
    static createNotification(messageHTML, options = {}) {
        const {
            type = '',
            title = null,
            ico = null,
            delay = 5000,
            autoHide = true,
            customClass = '',
            onClose = null,
            autoShow = true,
            traceId = undefined,
        } = options;

        let thematic = '';
        let titleHeader = '';
        let icoHeader = '';
        switch (type) {
            case 'success':
                thematic = 'text-bg-success';
                titleHeader = 'Sucesso'
                icoHeader = 'bi bi-check2-circle'
                break;
            case 'error':
                thematic = 'text-bg-danger';
                titleHeader = 'Erro'
                icoHeader = 'bi bi-bug'
                break;
            case 'warning':
                thematic = 'text-bg-warning';
                titleHeader = 'Aviso'
                icoHeader = 'bi bi-exclamation-triangle'
                break;
            case 'info':
                thematic = 'text-bg-info';
                titleHeader = 'Informação'
                icoHeader = 'bi bi-info-circle'
                break;
            default:
                thematic = '';
                titleHeader = 'Notificação';
                icoHeader = 'bi bi-bell'
        }

        titleHeader = title ? title : titleHeader;
        icoHeader = ico ? ico : icoHeader;
        messageHTML += traceId ? `<hr class="m-1"><p class="mb-0 fst-italic fw-semibold">Código de erro: ${traceId}</p>` : '';

        const id = `toast${Date.now()}`;
        let toastHTML = `
            <div class="toast ${thematic} ${customClass}" id="${id}" role="alert" aria-live="assertive" aria-atomic="true"
                data-bs-config='{"autohide" : ${autoHide}, "delay": ${delay}}'>
                <div class="toast-header">
                    <i class="${icoHeader} me-1"></i>
                    <strong class="me-auto">${titleHeader}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Fechar"></button>
                </div>
                <div class="toast-body">
                    ${messageHTML}
                </div>
            </div>`;

        const container = BootstrapFunctionsHelper.#createOrGetDivToastContainer();
        container.insertAdjacentHTML("beforeend", toastHTML);

        const newToast = container.querySelector(`#${id}`);
        const bsToast = new bootstrap.Toast(newToast, { onClose: onClose });
        if (autoShow) {
            bsToast.show();
        }

        return id;
    }

    static addEventTooltip() {
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    }

    static addEventPopover() {
        const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]')
        const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl))
    }

    static removeEventPopover() {
        const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');

        popoverTriggerList.forEach(popoverTriggerEl => {
            const popoverInstance = bootstrap.Popover.getInstance(popoverTriggerEl);
            if (popoverInstance) {
                popoverInstance.dispose();
            }
        });
    }

}
