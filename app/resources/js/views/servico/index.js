import { CommonFunctions } from "../../commons/CommonFunctions";
import { TemplateSearch } from "../../commons/templates/TemplateSearch";
import { BootstrapFunctionsHelper } from "../../helpers/BootstrapFunctionsHelper";
import { DateTimeHelper } from "../../helpers/DateTimeHelper";

class PageServico extends TemplateSearch {

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
        const self = this;
        BootstrapFunctionsHelper.removeEventPopover();
        self._setTypeCurrentSearch = self._objConfigs.querys.consultaFiltros.name;
        await self._generateQueryFilters();
    }

    async insertTableData(item, options = {}) {
        const self = this;
        const {
            tbody,
        } = options;

        let strBtns = self.#htmlBtns(item);
        const htmlCliente = self.#htmlRenderCliente(item);
        const created_at = DateTimeHelper.retornaDadosDataHora(item.created_at, 12);
        const valorFinal = item.valor_final ? CommonFunctions.formatNumberToCurrency(item.valor_final) : '***';

        $(tbody).append(`
            <tr id=${item.idTr} data-id="${item.id}">
                <td class="text-center">
                    <div class="btn-group btnsAcao" role="group">
                        ${strBtns}
                    </div>
                </td>
                <td class="text-nowrap">${htmlCliente}</td>
                <td class="text-nowrap text-truncate campo-tabela-truncate-35" title="${item.titulo ?? ''}">${item.titulo}</td>
                <td class="text-nowrap text-truncate campo-tabela-truncate-35" title="${item.area_juridica.nome ?? ''}">${item.area_juridica.nome ?? ''}</td>
                <td class="text-nowrap text-center" title="${valorFinal}">${valorFinal}</td>
                <td class="text-nowrap text-center" title="${item.numero_servico ?? ''}">${item.numero_servico}</td>
                <td class="text-nowrap" title="${created_at ?? ''}">${created_at ?? ''}</td>
            </tr>
        `);

        self.#addEventosRegistrosConsulta(item);
        BootstrapFunctionsHelper.addEventPopover();
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


    #htmlRenderCliente(item) {
        const self = this;

        const arrayCliente = item?.cliente;
        if (!arrayCliente.length) {
            return '<span class="fst-italic" title="Nenhum cliente encontrado">Nenhum cliente encontrado</span>';
        }

        let nomes = [];

        arrayCliente.map(cliente => {
            console.warn(cliente);
            const pessoa = cliente.perfil.pessoa;
            let nome = '';

            switch (pessoa.pessoa_dados_type) {
                case window.Enums.PessoaTipoEnum.PESSOA_FISICA:
                    nome = pessoa.pessoa_dados.nome;
                    break;
                case window.Enums.PessoaTipoEnum.PESSOA_JURIDICA:
                    nome = pessoa.pessoa_dados.nome_fantasia;
                    break;

                default:
                    nome = `Não implementado - ${pessoa.pessoa_dados_type}`;
                    console.error(`O tipo de pessoa <b>${pessoa.pessoa_dados_type}</b> ainda não foi implementado.`, cliente);

            }
            nomes.push(nome);
        });

        if (nomes.length > 1) {
            const total = nomes.length;
            return `<button type="button" class="btn btn-sm btn-outline-info border-0 text-nowrap text-reset" data-bs-toggle="popover" data-bs-title="Cliente(s)" data-bs-html="true" data-bs-content="${nomes.join("<hr class='my-1'>")}">${nomes[0]} + ${total - 1}</button>`
        }
        return `<span class="text-nowrap">${nomes[0]}</span>`;
    };
}

$(function () {
    new PageServico();
});