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

        console.log(self._dataEnvModal.ordem_custom_array);

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
            console.log('detalhes', JSON.parse(JSON.stringify(detalhes)));
            // Reordena os detalhes conforme a ordem definida
            const detalhesOrdenados = ordem
                .map(statusId => detalhes.find(detalhe => detalhe.id === statusId))
                .filter(Boolean);

            console.log('detalhesOrdenados', JSON.parse(JSON.stringify(detalhesOrdenados)));
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

        statusList.forEach((item) => {
            console.log('vai inserir no container o item', item);

            item.idCol = UUIDHelper.generateUUID();
            const card = `
                <div id="${item.idCol}" class="col item-status" data-id="${item.id}">
                    <div class="card">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div class="">
                                <p class="fw-bold mb-1">${item.nome}</p>
                                <p class="text-muted text-truncate text-wrap mb-1">
                                    ${item.descricao}
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
            self._objConfigs.data.ordem_custom_array.push(item);
            // adiciona eventos após renderizar
            self.#adicionarEventosOrdenacao(item, container);
        });

    }

    #adicionarEventosOrdenacao(item) {
        const self = this;

        const reorganizar = (idCol, sentido) => {
            const ordem = self._objConfigs.data.ordem_custom_array;

            const indexAtual = ordem.findIndex(obj => obj.idCol === idCol);

            // Define novo índice
            let novoIndex = sentido === 'cima' ? indexAtual - 1 : indexAtual + 1;

            // Verifica se é um movimento válido
            if (novoIndex < 0 || novoIndex >= ordem.length) return;

            // Troca os elementos de lugar no array
            const temp = ordem[indexAtual];
            ordem[indexAtual] = ordem[novoIndex];
            ordem[novoIndex] = temp;
        };

        const card = $(`#${item.idCol}`);

        card.off('click', '.btn-subir')
            .on('click', '.btn-subir', function () {
                const $item = $(this).closest('.item-status');
                const $anterior = $item.prev('.item-status');

                if ($anterior.length) {
                    $item.insertBefore($anterior);
                    reorganizar(item.idCol, 'cima');
                }
            });

        card.off('click', '.btn-descer')
            .on('click', '.btn-descer', function () {
                const $item = $(this).closest('.item-status');
                const $proximo = $item.next('.item-status');

                if ($proximo.length) {
                    $item.insertAfter($proximo);
                    reorganizar(item.idCol, 'baixo');
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
