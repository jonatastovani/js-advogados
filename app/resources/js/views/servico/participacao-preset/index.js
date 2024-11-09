import { templateSearch } from "../../../commons/templates/templateSearch";
import { BootstrapFunctionsHelper } from "../../../helpers/BootstrapFunctionsHelper";
import { DateTimeHelper } from "../../../helpers/DateTimeHelper";
import { ServicoParticipacaoHelpers } from "../../../helpers/ServicoParticipacaoHelpers";

class PageServicoParticipacaoIndex extends templateSearch {

    #objConfigs = {
        querys: {
            consultaFiltros: {
                name: 'consulta-filtros',
                url: window.apiRoutes.baseServicoParticipacaoPreset,
                urlSearch: `${window.apiRoutes.baseServicoParticipacaoPreset}/consulta-filtros`,
                baseFront: window.frontRoutes.baseFront,
            }
        },
    };

    constructor() {
        super({ sufixo: 'PageServicoParticipacaoIndex' });
        this._objConfigs = Object.assign(this._objConfigs, this.#objConfigs);
        this.initEvents();
    }

    initEvents() {
        const self = this;
        self.#addEventosBotoes();
        self._setTypeCurrentSearch = self._objConfigs.querys.consultaFiltros.name;
        self._generateQueryFilters()
    }

    #addEventosBotoes() {
        const self = this;

        $(`#formDataSearch${self.getSufixo}`).find('.btnBuscar').on('click', async function (e) {
            e.preventDefault();
            BootstrapFunctionsHelper.removeEventPopover();
            self._setTypeCurrentSearch = self._objConfigs.querys.consultaFiltros.name;
            self._generateQueryFilters()
        });
    }

    async insertTableData(item, options = {}) {
        const self = this;
        const {
            tbody,
        } = options;

        let strBtns = self.#HtmlBtnEdit(item);
        strBtns += self.#HtmlBtnDelete(item);

        const arrays = ServicoParticipacaoHelpers.htmlRenderParticipantesEIntegrantes(item.participantes);

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

    #HtmlBtnEdit(item) {
        const self = this;
        return `<a href="${self._objConfigs.querys.consultaFiltros.baseFront}/form/${item.id}" class="btn btn-outline-primary btn-sm btn-edit" title="Editar registro"><i class="bi bi-pencil"></i></a>`;
    }

    #HtmlBtnDelete(item) {
        const self = this;
        return `<button type="button" class="btn btn-outline-danger btn-sm btn-delete" title="Excluir serviço ${item.nome}"><i class="bi bi-trash"></i></button>`
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
    new PageServicoParticipacaoIndex();
});