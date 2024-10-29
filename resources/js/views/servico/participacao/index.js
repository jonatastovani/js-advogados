import { commonFunctions } from "../../../commons/commonFunctions";
import { templateSearch } from "../../../commons/templates/templateSearch";
import { BootstrapFunctionsHelper } from "../../../helpers/BootstrapFunctionsHelper";
import { DateTimeHelper } from "../../../helpers/DateTimeHelper";

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

        const arrayParticipantes = [];
        const arrayIntegrantes = [];

        for (const participante of item.participantes) {
            let nomeParticipante = '';
            let valor = commonFunctions.formatWithCurrencyCommasOrFraction(participante.valor);
            let participacao = participante.participacao_tipo.nome;

            switch (participante.valor_tipo) {
                case 'porcentagem':
                    valor = `${valor}%`;
                    break;
                case 'valor_fixo':
                    valor = `R$ ${valor}`;
                    break;
            }

            switch (participante.participacao_registro_tipo_id) {
                case window.Enums.ParticipacaoRegistroTipoEnum.PERFIL:
                    nomeParticipante = `<b>${participante.referencia.perfil_tipo.nome}</b> - ${participante.referencia.pessoa.pessoa_dados.nome}`;
                    break;

                case window.Enums.ParticipacaoRegistroTipoEnum.GRUPO:
                    nomeParticipante = `<b>Grupo</b> - ${participante.nome_grupo}</b>`;
                    for (const integrante of participante.integrantes) {
                        let nomeIntegrante = '';
                        switch (integrante.participacao_registro_tipo_id) {
                            case window.Enums.ParticipacaoRegistroTipoEnum.PERFIL:
                                nomeIntegrante = integrante.referencia.pessoa.pessoa_dados.nome;

                                break;
                        }

                        arrayIntegrantes.push(`<b>${participante.nome_grupo}</b> - ${nomeIntegrante}`);
                    }
                    break;
            }
            nomeParticipante += ` > <b>${participacao}</b> - <b>${valor}</b>`;

            arrayParticipantes.push(`${nomeParticipante}`);
        }

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
                <td><button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="popover" data-bs-title="Participantes do ${item.nome}" data-bs-html="true" data-bs-content="${arrayParticipantes.join("<hr class='my-1'>")}">Ver mais</button></td>
                <td><button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="popover" data-bs-title="Integrantes de Grupos" data-bs-html="true" data-bs-content="${arrayIntegrantes.join("<hr class='my-1'>")}">Ver mais</button></td>
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