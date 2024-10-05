import { templateSearch } from "../../commons/templates/templateSearch";
import { DateTimeHelper } from "../../helpers/DateTimeHelper";

class PageServico extends templateSearch {

    #objConfigs = {
        querys: {
            consultaFiltros: {
                name: 'consulta-filtros',
                url: window.apiRoutes.baseServico,
                urlSearch: `${window.apiRoutes.baseServico}/consulta-filtros`,
                baseFront: window.frontRoutes.baseFront,
            }
        },
    };

    constructor() {
        super({ sufixo: 'PageServicoIndex' });
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

        const created_at = DateTimeHelper.retornaDadosDataHora(item.created_at, 12);
        $(tbody).append(`
            <tr id=${item.idTr} data-id="${item.id}">
                <td class="text-center">
                    <div class="btn-group btnsAcao" role="group">
                        ${strBtns}
                    </div>
                </td>
                <td class="text-nowrap" title="${item.numero_servico ?? ''}">${item.numero_servico}</td>
                <td class="text-nowrap text-truncate" style="max-width: 10rem" title="${item.titulo ?? ''}">${item.titulo}</td>
                <td class="text-nowrap text-truncate" style="max-width: 10rem" title="${item.area_juridica.nome ?? ''}">${item.area_juridica.nome ?? ''}</td>
                <td class="text-nowrap text-truncate" style="max-width: 10rem" title="${item.descricao ?? ''}">${item.descricao ?? ''}</td>
                <td class="text-nowrap" title="${created_at ?? ''}">${created_at ?? ''}</td>
            </tr>
        `);

        self.#addEventosRegistrosConsulta(item);
        return true;
    }

    #HtmlBtnEdit(item) {
        const self = this;
        return `<a href="${self._objConfigs.querys.consultaFiltros.baseFront}/form/${item.id}" class="btn btn-outline-primary btn-sm btn-edit" title="Editar registro"><i class="bi bi-pencil"></i></a>`;
    }

    #HtmlBtnDelete(item) {
        const self = this;
        return `<button type="button" class="btn btn-outline-danger btn-sm btn-delete" title="Excluir serviço ${item.numero_servico}"><i class="bi bi-trash"></i></button>`
    }

    #addEventosRegistrosConsulta(item) {
        const self = this;

        $(`#${item.idTr}`).find(`.btn-delete`).click(async function () {
            self._delButtonAction(item.id, item.name, {
                title: `Exclusão de Serviço`,
                message: `Confirma a exclusão do Serviço <b>${item.numero_servico}</b>?`,
                success: `Serviço excluído com sucesso!`,
                button: this
            });
        });
    }
}

$(function () {
    new PageServico();
});