import { commonFunctions } from "../../../commons/commonFunctions";
import { TemplateSearch } from "../../../commons/templates/TemplateSearch";
import { DateTimeHelper } from "../../../helpers/DateTimeHelper";

class PageDocumentoModeloTenantIndex extends TemplateSearch {

    #objConfigs = {
        querys: {
            consultaFiltros: {
                name: 'consulta-filtros',
                url: window.apiRoutes.baseDocumentoModeloTenant,
                urlSearch: `${window.apiRoutes.baseDocumentoModeloTenant}/consulta-filtros`,
                baseFront: window.frontRoutes.baseFront,
            }
        },
    };

    constructor() {
        super({ sufixo: 'PageDocumentoModeloTenantIndex' });
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

        self.#buscarDocumentoModeloTipo();

        $(`#formDataSearch${self.getSufixo}`).find('.btnBuscar').on('click', async function (e) {
            e.preventDefault();
            await self._executarBusca();
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
                <td class="text-nowrap text-truncate campo-tabela-truncate-35" title="${item.nome ?? ''}">${item.nome}</td>
                <td class="text-nowrap text-truncate campo-tabela-truncate-35" title="${item.descricao ?? ''}">${item.descricao ?? ''}</td>
                <td class="text-nowrap text-truncate campo-tabela-truncate-35" title="${item.documento_modelo_tipo.nome ?? ''}">${item.documento_modelo_tipo.nome ?? ''}</td>
                <td class="text-nowrap" title="${item.ativo_bln ? 'Modelo ativo' : 'Modelo inativo'}">${item.ativo_bln ? 'Sim' : 'Nao'}</td>
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
                    <a href="${self._objConfigs.querys.consultaFiltros.baseFront}/${item.documento_modelo_tipo_id}/form/${item.id}" class="dropdown-item fs-6 btn-edit" title="Editar modelo">
                        Editar
                    </a>
                </li>
                <li>
                    <button type="button" class="dropdown-item fs-6 btn-delete text-danger" title="Excluir modelo ${item.nome}">
                        Excluir
                    </button>
                </li>
            </ul>`;

        return strBtns;
    }

    #addEventosRegistrosConsulta(item) {
        const self = this;

        $(`#${item.idTr} .btn-delete`).on('click', async function () {
            self._delButtonAction(item.id, item.name, {
                title: `Exclusão de Modelo`,
                message: `Confirma a exclusão do Modelo <b>${item.nome}</b>?`,
                success: `Modelo excluído com sucesso!`,
                button: this
            });
        });
    }

    async #buscarDocumentoModeloTipo(selected_id = null) {
        console.log('Vai consultar')
        try {
            const self = this;
            let options = {
                insertFirstOption: true,
                firstOptionName: 'Todos os tipos',
            };
            if (selected_id) Object.assign(options, { selectedIdOption: selected_id });
            const select = $(`#documento_modelo_tipo_id${self.getSufixo}`);
            await commonFunctions.fillSelect(select, self._objConfigs.url.baseDocumentoModeloTipo, options);
            return true;
        } catch (error) {
            return false;
        }
    }
}

$(function () {
    new PageDocumentoModeloTenantIndex();
});