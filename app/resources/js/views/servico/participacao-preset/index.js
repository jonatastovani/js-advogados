import { TemplateSearch } from "../../../commons/templates/TemplateSearch";
import { BootstrapFunctionsHelper } from "../../../helpers/BootstrapFunctionsHelper";
import { DateTimeHelper } from "../../../helpers/DateTimeHelper";
import { ParticipacaoHelpers } from "../../../helpers/ParticipacaoHelpers";

class PageParticipacaoIndex extends TemplateSearch {

    #objConfigs = {
        querys: {
            consultaFiltros: {
                name: 'consulta-filtros',
                url: window.apiRoutes.baseParticipacaoPreset,
                urlSearch: `${window.apiRoutes.baseParticipacaoPreset}/consulta-filtros`,
                baseFront: window.frontRoutes.baseFront,
            }
        },
    };

    constructor() {
        super({ sufixo: 'PageParticipacaoIndex' });
        this._objConfigs = Object.assign(this._objConfigs, this.#objConfigs);
        this.initEvents();
    }

    initEvents() {
        const self = this;
        self.#addEventosBotoes();
        self._executarBusca();
    }

    #addEventosBotoes() {
        const self = this;

        $(`#formDataSearch${self.getSufixo}`).find('.btnBuscar').on('click', async function (e) {
            e.preventDefault();
            await self._executarBusca();
        });
    }

    async _executarBusca() {
        BootstrapFunctionsHelper.removeEventPopover();
        super._executarBusca();
    }
    async insertTableData(item, options = {}) {
        const self = this;
        const {
            tbody,
        } = options;

        let strBtns = self.#htmlBtns(item);

        const arrays = ParticipacaoHelpers.htmlRenderParticipantesEIntegrantes(item.participantes);

        const created_at = DateTimeHelper.retornaDadosDataHora(item.created_at, 12);
        $(tbody).append(`
            <tr id=${item.idTr} data-id="${item.id}">
                <td class="text-center">
                    <div class="btn-group btnsAcao" role="group">
                        ${strBtns}
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

    #htmlBtns(item) {
        let strBtns = '';
        const self = this;

        strBtns = `
            <button class="btn dropdown-toggle btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-three-dots-vertical"></i>
            </button>
            <ul class="dropdown-menu">
                <li>
                    <a href="${self._objConfigs.querys.consultaFiltros.baseFront}/form/${item.id}" class="dropdown-item fs-6 btn-edit" title="Editar preset">
                        Editar
                    </a>
                </li>
                <li>
                    <button type="button" class="dropdown-item fs-6 btn-delete text-danger" title="Excluir preset ${item.nome}">
                        Excluir
                    </button>
                </li>
                <li>
                    <a href="${self._objConfigs.querys.consultaFiltros.baseFront}/form?preset_inherit=${item.id}" class="dropdown-item fs-6 btn-new-inherit" title="Criar novo Preset copiando todas as configurações do preset ${item.nome}">
                        Novo Preset a partir deste
                    </a>
                </li>
            </ul>`;

        return strBtns;
    }

    #addEventosRegistrosConsulta(item) {
        const self = this;

        $(`#${item.idTr}`).find(`.btn-delete`).click(async function () {
            self._delButtonAction(item.id, item.nome, {
                title: `Exclusão de Preset`,
                message: `Confirma a exclusão do Preset <b>${item.nome}</b>?`,
                success: `Preset excluído com sucesso!`,
                button: this
            });
        });
    }
}

$(function () {
    new PageParticipacaoIndex();
});