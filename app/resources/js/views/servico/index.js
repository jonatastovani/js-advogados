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

        let strBtns = self.#htmlBtns(item);

        const created_at = DateTimeHelper.retornaDadosDataHora(item.created_at, 12);
        $(tbody).append(`
            <tr id=${item.idTr} data-id="${item.id}">
                <td class="text-center">
                    <div class="btn-group btnsAcao" role="group">
                        ${strBtns}
                    </div>
                </td>
                <td class="text-nowrap text-center" title="${item.numero_servico ?? ''}">${item.numero_servico}</td>
                <td class="text-nowrap text-truncate campo-tabela-truncate-35" title="${item.titulo ?? ''}">${item.titulo}</td>
                <td class="text-nowrap text-truncate campo-tabela-truncate-35" title="${item.area_juridica.nome ?? ''}">${item.area_juridica.nome ?? ''}</td>
                <td class="text-nowrap" title="${created_at ?? ''}">${created_at ?? ''}</td>
            </tr>
        `);

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
                    <a href="${self._objConfigs.querys.consultaFiltros.baseFront}/form/${item.id}" class="dropdown-item fs-6 btn-edit" title="Editar serviço">
                        Editar
                    </a>
                </li>
                <li>
                    <button type="button" class="dropdown-item fs-6 btn-delete text-danger" title="Excluir serviço ${item.numero_servico}">
                        Excluir
                    </button>
                </li>
            </ul>`;

        return strBtns;
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