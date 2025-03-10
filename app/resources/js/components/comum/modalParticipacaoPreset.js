import { commonFunctions } from "../../commons/commonFunctions";
import { ModalSearchAndFormRegistration } from "../../commons/modal/ModalSearchAndFormRegistration";
import { BootstrapFunctionsHelper } from "../../helpers/BootstrapFunctionsHelper";
import { DateTimeHelper } from "../../helpers/DateTimeHelper";
import { ParticipacaoHelpers } from "../../helpers/ParticipacaoHelpers";

export class modalParticipacaoPreset extends ModalSearchAndFormRegistration {

    /**
     * Configuração local do modal
     */
    #objConfigs = {
        formRegister: false,
        querys: {
            consultaFiltros: {
                name: 'consulta-filtros',
                url: window.apiRoutes.baseParticipacaoPreset,
                urlSearch: `${window.apiRoutes.baseParticipacaoPreset}/consulta-filtros`,
            }
        },
        sufixo: 'ModalParticipacaoPreset',
    };

    /** 
     * Conteúdo a ser retornado na promisse como resolve()
    */
    #promisseReturnValue = {
        selecteds: [],
    };

    constructor() {
        super({
            idModal: "#modalParticipacaoPreset",
        });

        this._objConfigs = commonFunctions.deepMergeObject(this._objConfigs, this.#objConfigs);
        this._promisseReturnValue = commonFunctions.deepMergeObject(this._promisseReturnValue, this.#promisseReturnValue);

        this.#addEventosPadrao();
    }

    async modalOpen() {
        const self = this;
        await self._modalHideShow();
        return await self._modalOpen();
    }

    #addEventosPadrao() {
        const self = this;
        self._executarBusca();
    }

    async _executarBusca() {
        const self = this;
        BootstrapFunctionsHelper.removeEventPopover();
        super._executarBusca();
    }

    async insertTableData(item, options = {}) {
        const self = this;
        const {
            tbody,
        } = options;

        let btnsDropDown = `
            <div class="btn-group">
                <button type="button" class="btn btn-outline-success btn-sm btn-select" title="Selecionar registro"><i class="bi bi-check-lg"></i></button>
            </div>`;

        const arrays = ParticipacaoHelpers.htmlRenderParticipantesEIntegrantes(item.participantes);

        const created_at = DateTimeHelper.retornaDadosDataHora(item.created_at, 12);

        $(tbody).append(`
            <tr id="${item.idTr}" data-id="${item.id}">
                <td class="text-center">
                    <div class="btn-group btnsAcao" role="group">
                        ${btnsDropDown}
                    </div>
                </td>
                <td class="text-nowrap" title="${item.nome ?? ''}">${item.nome}</td>
                <td class="text-nowrap text-truncate" style="max-width: 10rem" title="${item.descricao ?? ''}">${item.descricao ?? ''}</td>
                <td><button type="button" class="btn btn-sm btn-outline-info border-0" data-bs-toggle="popover" data-bs-title="Participantes do ${item.nome}" data-bs-html="true" data-bs-content="${arrays.arrayParticipantes.join("<hr class='my-1'>")}">Ver mais</button></td>
                <td><button type="button" class="btn btn-sm btn-outline-info border-0" data-bs-toggle="popover" data-bs-title="Integrantes de Grupos" data-bs-html="true" data-bs-content="${arrays.arrayIntegrantes.join("<hr class='my-1'>")}">Ver mais</button></td>
                <td class="text-nowrap" title="${created_at ?? ''}">${created_at ?? ''}</td>
            </tr>
        `);

        BootstrapFunctionsHelper.addEventPopover();
        self.#addEventosRegistrosConsulta(item);
        return true;
    }

    #addEventosRegistrosConsulta(item) {
        const self = this;

        $(`#${item.idTr}`).find(`.btn-select`).on('click', async function () {
            if (self._dataEnvModal?.attributes?.select) {
                const select = self._dataEnvModal.attributes.select;
                const promisseReturnValue = self._promisseReturnValue;

                const pushSelected = (item) => {

                    if (select?.quantity && select.quantity == 1) {
                        promisseReturnValue.selected = item;
                    } else {
                        promisseReturnValue.selecteds.push(item);
                    }
                    promisseReturnValue.refresh = true;

                    if (select?.autoReturn && select.autoReturn &&
                        (select?.quantity && promisseReturnValue.selecteds.length == select.quantity ||
                            (select?.quantity && select.quantity == 1 && promisseReturnValue.selected))
                    ) {
                        self._setEndTimer = true;
                    }
                }
                pushSelected(item);
            }
        });
    }
}