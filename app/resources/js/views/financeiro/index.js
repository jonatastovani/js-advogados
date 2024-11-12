import { commonFunctions } from "../../commons/commonFunctions";
import { templateSearch } from "../../commons/templates/templateSearch";
import { BootstrapFunctionsHelper } from "../../helpers/BootstrapFunctionsHelper";
import { DateTimeHelper } from "../../helpers/DateTimeHelper";
import { ServicoParticipacaoHelpers } from "../../helpers/ServicoParticipacaoHelpers";

class PageLancamentoServicoIndex extends templateSearch {

    #objConfigs = {
        querys: {
            consultaFiltros: {
                name: 'consulta-filtros',
                url: window.apiRoutes.baseLancamento,
                urlSearch: `${window.apiRoutes.baseLancamento}/consulta-filtros`,
                baseFront: window.frontRoutes.baseFront,
            }
        },
        url: {
            baseServico: window.apiRoutes.baseServico,
        }
    };

    constructor() {
        super({ sufixo: 'PageLancamentoServicoIndex' });
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

        let strBtns = self.#HtmlBtns(item);

        const numero_servico = item.pagamento.servico.numero_servico;
        const status = item.status.nome;
        const valorEsperado = commonFunctions.formatWithCurrencyCommasOrFraction(item.valor_esperado);
        const dataVencimento = DateTimeHelper.retornaDadosDataHora(item.data_vencimento, 2);
        const valorRecebido = item.data_recebimento ? commonFunctions.formatWithCurrencyCommasOrFraction(item.valor_recebido) : '***';
        const dataRecebimento = item.data_recebimento ? DateTimeHelper.retornaDadosDataHora(item.data_recebimento, 2) : '***';
        const descricaoAutomatica = item.descricao_automatica;
        const observacaoLancamento = item.observacao ?? '***';
        const valorPagamento = item.pagamento.valor_total ? commonFunctions.formatWithCurrencyCommasOrFraction(item.pagamento.valor_total) : '***';
        const tituloServico = item.pagamento.servico.titulo;
        const areaJuridica = item.pagamento.servico.area_juridica.nome;
        const valorLiquidado = item.pagamento.total_liquidado ? commonFunctions.formatWithCurrencyCommasOrFraction(item.pagamento.total_liquidado) : '***';
        const valorAguardando = item.pagamento.total_aguardando ? commonFunctions.formatWithCurrencyCommasOrFraction(item.pagamento.total_aguardando) : '***';
        const valorInadimplente = item.pagamento.total_inadimplente ? commonFunctions.formatWithCurrencyCommasOrFraction(item.pagamento.total_inadimplente) : '***';
        const pagamentoTipo = item.pagamento.pagamento_tipo_tenant.nome ?? item.pagamento.pagamento_tipo_tenant.pagamento_tipo.nome
        const observacaoPagamento = item.pagamento.observacao ?? '***';

        const arrays = ServicoParticipacaoHelpers.htmlRenderParticipantesEIntegrantes(
            item.participantes.length ? item.participantes :
                (item.pagamento.participantes.length ? item.pagamento.participantes :
                    (item.pagamento.servico.participantes.length ? item.pagamento.servico.participantes : [])
                )
        );

        const created_at = DateTimeHelper.retornaDadosDataHora(item.created_at, 12);
        $(tbody).append(`
            <tr id=${item.idTr} data-id="${item.id}">
                <td class="text-center">
                    <div class="btn-group btnsAcao" role="group">
                        ${strBtns}
                    </div>
                </td>
                <td class="text-nowrap" title="${numero_servico}">${numero_servico}</td>
                <td title="${status}">${status}</td>
                <td class="text-nowrap text-center" title="${valorEsperado}">${valorEsperado}</td>
                <td class="text-nowrap text-center" title="${dataVencimento}">${dataVencimento}</td>
                <td class="text-nowrap text-center" title="${valorRecebido}">${valorRecebido}</td>
                <td class="text-nowrap text-center" title="${dataRecebimento}">${dataRecebimento}</td>
                <td class="text-nowrap text-truncate" title="${descricaoAutomatica}">${descricaoAutomatica}</td>
                <td class="text-truncate" title="${observacaoLancamento}">${observacaoLancamento}</td>
                <td class="text-nowrap text-center" title="${valorPagamento}">${valorPagamento}</td>
                <td class="text-truncate" title="${tituloServico}">${tituloServico}</td>
                <td class="text-nowrap text-truncate" title="${areaJuridica}">${areaJuridica}</td>
                <td class="text-nowrap text-center" title="${valorLiquidado}">${valorLiquidado}</td>
                <td class="text-nowrap text-center" title="${valorAguardando}">${valorAguardando}</td>
                <td class="text-nowrap text-center" title="${valorInadimplente}">${valorInadimplente}</td>
                <td class="text-truncate" title="${pagamentoTipo}">${pagamentoTipo}</td>
                <td class="text-truncate" title="${observacaoPagamento}">${observacaoPagamento}</td>
                <td class="text-center"><button type="button" class="btn btn-sm btn-outline-info border-0" data-bs-toggle="popover" data-bs-title="Participantes do Lançamento ${descricaoAutomatica}" data-bs-html="true" data-bs-content="${arrays.arrayParticipantes.join("<hr class='my-1'>")}">Ver mais</button></td>
                <td class="text-center"><button type="button" class="btn btn-sm btn-outline-info border-0" data-bs-toggle="popover" data-bs-title="Integrantes de Grupos" data-bs-html="true" data-bs-content="${arrays.arrayIntegrantes.join("<hr class='my-1'>")}">Ver mais</button></td>
                <td class="text-nowrap" title="${created_at ?? ''}">${created_at ?? ''}</td>
            </tr>
        `);

        self.#addEventosRegistrosConsulta(item);
        BootstrapFunctionsHelper.addEventPopover();
        return true;
    }

    #HtmlBtns(item) {
        const self = this;

        let strBtns = `
            <div class="dropdown">
                <button class="btn dropdown-toggle btn-sm btn-outline-success text-nowrap rounded-end-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-box-arrow-in-down"></i>
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <button type="button" class="dropdown-item fs-6 btn-receber-analise" title="Receber lançamento ${item.descricao_automatica} com status Em Análise."><i class="bi bi-check2"></i> Receber Em Análise</button>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item fs-6 btn-receber-liquidado" title="Receber lançamento ${item.descricao_automatica} com status Liquidado."><i class="bi bi-check2-all"></i> Receber Conferido</button>
                    </li>
                </ul>
            </div>`;

        // strBtns += `<button type="button" class="btn btn-outline-primary btn-sm btn-view" title="Visualizar pagamento"><i class="bi bi-eye"></i></button>`;

        strBtns += `
            <div class="dropdown">
                <button class="btn dropdown-toggle btn-sm btn-outline-warning text-nowrap rounded-start-0 rounded-end-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-exclamation-octagon"></i>
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <button type="button" class="dropdown-item fs-6 btn-receber-analise" title="Receber lançamento ${item.descricao_automatica} com status Em Análise.">
                            <i class="bi bi-check2"></i> Parcial em Análise
                        </button>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item fs-6 btn-receber-analise" title="Receber lançamento ${item.descricao_automatica} com status Liquidado.">
                            <i class="bi bi-check2-all"></i> Parcial Conferido
                        </button>
                    </li>
                </ul>
            </div>`;

        strBtns += `
            <div class="dropdown">
                <button class="btn dropdown-toggle btn-sm btn-outline-danger text-nowrap rounded-start-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-dash-square"></i>
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <button type="button" class="dropdown-item fs-6 btn-receber-analise" title="Receber lançamento ${item.descricao_automatica} com status Em Análise.">Cancelado</button>
                    </li>
                </ul>
            </div>`;

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
    new PageLancamentoServicoIndex();
});