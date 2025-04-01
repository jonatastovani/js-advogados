import { CommonFunctions } from "../../commons/CommonFunctions";
import { ModalRegistrationAndEditing } from "../../commons/modal/ModalRegistrationAndEditing";
import { UUIDHelper } from "../../helpers/UUIDHelper";

export class ModalOrdemLancamentoStatusTipoTenant extends ModalRegistrationAndEditing {

    #dataEnvModal = {
        ordem_custom_array: undefined,
    }

    /**
     * Configuração local do modal
     */
    #objConfigs = {
        sufixo: 'ModalOrdemLancamentoStatusTipoTenant',
        data: {
            tenant_data: undefined,
            ordem_custom_array: [],
        },
    };

    /** 
     * Conteúdo a ser retornado na promisse como resolve()
    */
    #promisseReturnValue = {
        refresh: false,
        ordem_custom_array: undefined
    };

    constructor() {
        super({
            idModal: "#ModalOrdemLancamentoStatusTipoTenant",
        });

        this._objConfigs = CommonFunctions.deepMergeObject(this._objConfigs, this.#objConfigs);
        this._promisseReturnValue = CommonFunctions.deepMergeObject(this._promisseReturnValue, this.#promisseReturnValue);
        this._dataEnvModal = CommonFunctions.deepMergeObject(this._dataEnvModal, this.#dataEnvModal);
    }

    async modalOpen() {
        const self = this;

        await CommonFunctions.loadingModalDisplay(true);
        let blnOpen = false;

        blnOpen = await self.#preencherDados()
        await CommonFunctions.loadingModalDisplay(false);

        if (!blnOpen) {
            return await self._returnPromisseResolve();
        }
        await self._modalHideShow();
        return await self._modalOpen();
    }

    _modalReset() {
        super._modalReset();
        const self = this;
        $(`#divStatus${self.getSufixo}`).html('');
    }

    async #preencherDados() {
        const self = this;

        try {
            self._clearForm();
            const ordem = self._dataEnvModal.ordem_custom_array ?? window.Statics.OrdemPadraoStatusLancamentoServico;

            const detalhes = window.Details.LancamentoStatusTipoEnum;
            // Reordena os detalhes conforme a ordem definida
            const detalhesOrdenados = ordem
                .map(statusId => detalhes.find(detalhe => detalhe.id === statusId))
                .filter(Boolean);

            self.#renderizarCardsStatus(detalhesOrdenados);
            return true;

        } catch (error) {
            CommonFunctions.generateNotificationErrorCatch(error);
            return false;
        }
    }

    #renderizarCardsStatus(statusList, options = {}) {
        const self = this;
        const { containerSelector = `#divStatus${self.getSufixo}` } = options;

        const container = $(containerSelector);
        container.html('');

        statusList.forEach((itemStatus) => {

            itemStatus.idCol = UUIDHelper.generateUUID();
            const card = `
                <div id="${itemStatus.idCol}" class="col item-status" data-id="${itemStatus.id}">
                    <div class="card">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div class="">
                                <p class="fw-bold mb-1">${itemStatus.nome}</p>
                                <p class="text-muted text-truncate text-wrap mb-1">
                                    ${itemStatus.descricao}
                                </p>
                            </div>
                            <div class="ms-3 d-flex flex-column">
                                <button type="button" class="btn btn-sm btn-light mb-1 btn-subir" title="Mover para cima">
                                    <i class="bi bi-arrow-up"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-light btn-descer" title="Mover para baixo">
                                    <i class="bi bi-arrow-down"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            container.append(card);

            // adiciona na ordem os status
            self._objConfigs.data.ordem_custom_array.push(itemStatus);
            // adiciona eventos após renderizar
            self.#adicionarEventosOrdenacao(itemStatus, container);
        });

    }

    #adicionarEventosOrdenacao(itemStatus) {
        const self = this;

        const reorganizar = (idCol, sentido) => {
            const ordem = self._objConfigs.data.ordem_custom_array;

            const indexAtual = ordem.findIndex(obj => obj.idCol === idCol);
            let novoIndex = sentido === 'cima' ? indexAtual - 1 : indexAtual + 1;

            if (novoIndex < 0 || novoIndex >= ordem.length) return;

            const temp = ordem[indexAtual];
            ordem[indexAtual] = ordem[novoIndex];
            ordem[novoIndex] = temp;
        };

        const card = $(`#${itemStatus.idCol}`);

        card.off('click', '.btn-subir')
            .on('click', '.btn-subir', function () {
                const item = $(this).closest('.item-status');
                const anterior = item.prev('.item-status');

                if (anterior.length) {
                    
                    item.addClass('fade-move fade-up fade-glow');
                    anterior.addClass('fade-down');

                    requestAnimationFrame(() => {
                        setTimeout(() => item.removeClass('fade-up'), 10);
                        setTimeout(() => item.removeClass('fade-move'), 300);
                        setTimeout(() => item.removeClass('fade-glow'), 300);
                    });

                    item.insertBefore(anterior);
                    reorganizar(itemStatus.idCol, 'cima');
                    anterior.addClass('fade-move');

                    requestAnimationFrame(() => {
                        setTimeout(() => anterior.removeClass('fade-down'), 10);
                        setTimeout(() => anterior.removeClass('fade-move'), 300);
                    });

                }
            });

        card.off('click', '.btn-descer')
            .on('click', '.btn-descer', function () {
                const item = $(this).closest('.item-status');
                const proximo = item.next('.item-status');

                if (proximo.length) {

                    item.addClass('fade-move fade-down fade-glow');
                    proximo.addClass('fade-up');
                    requestAnimationFrame(() => {
                        setTimeout(() => item.removeClass('fade-down'), 10);
                        setTimeout(() => item.removeClass('fade-move'), 300);
                        setTimeout(() => item.removeClass('fade-glow'), 300);
                    });

                    item.insertAfter(proximo);
                    reorganizar(itemStatus.idCol, 'baixo');
                    proximo.addClass('fade-move');

                    requestAnimationFrame(() => {
                        setTimeout(() => proximo.removeClass('fade-up'), 10);
                        setTimeout(() => proximo.removeClass('fade-move'), 300);
                    });

                }
            });
    }

    async saveButtonAction() {
        const self = this;
        const data = self.#obterDados();

        self._promisseReturnValue.refresh = true;
        self._promisseReturnValue.ordem_custom_array = data;
        self._endTimer = true;
    }

    #obterDados() {
        const self = this;
        let data = self._objConfigs.data.ordem_custom_array.map(item => item.id);
        console.log('data', data);
        return data;
    }
}
