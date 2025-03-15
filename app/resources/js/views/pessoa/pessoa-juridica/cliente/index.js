import { CommonFunctions } from "../../../../commons/CommonFunctions";
import { TemplateSearch } from "../../../../commons/templates/TemplateSearch";
import { BootstrapFunctionsHelper } from "../../../../helpers/BootstrapFunctionsHelper";
import { DateTimeHelper } from "../../../../helpers/DateTimeHelper";

class PageClientePJIndex extends TemplateSearch {

    #objConfigs = {
        querys: {
            consultaFiltros: {
                name: 'consulta-filtros',
                url: window.apiRoutes.basePessoaJuridica,
                urlSearch: `${window.apiRoutes.basePessoaJuridica}/consulta-filtros`,
            }
        },
        url: {
            base: window.apiRoutes.basePessoaJuridica,
            baseFrontPessoaJuridicaClienteForm: window.frontRoutes.baseFrontPessoaJuridicaClienteForm
        },
        data: {
            perfil_referencia_id: window.Enums.PessoaPerfilTipoEnum.CLIENTE,
            perfis_busca: [
                window.Enums.PessoaPerfilTipoEnum.CLIENTE,
            ],
            // Pré carregamento de dados vindo da URL
            preload: {}
        }
    };

    constructor() {
        super({ sufixo: 'PageClientePJIndex' });
        this._objConfigs = Object.assign(this._objConfigs, this.#objConfigs);
        this.initEvents();
    }

    async initEvents() {
        const self = this;

        self.#addEventosBotoes();
        await self._executarBusca();
    }

    #addEventosBotoes() {
        const self = this;

        $(`#formDataSearch${self.getSufixo}`).find('.btnBuscar').on('click', async function (e) {
            e.preventDefault();
            self._executarBusca();
        });
    }

    async _executarBusca() {
        const self = this;

        const getAppendDataQuery = () => {
            const formData = $(`#formDataSearch${self.getSufixo}`);
            let appendData = {};
            let data = CommonFunctions.getInputsValues(formData[0]);

            if (data.ativo_bln && [1, 0].includes(Number(data.ativo_bln))) {
                appendData.ativo_bln = data.ativo_bln;
            }

            appendData.perfis_busca = self._objConfigs.data.perfis_busca;
            return { appendData: appendData };
        }

        BootstrapFunctionsHelper.removeEventPopover();
        self._setTypeCurrentSearch = self._objConfigs.querys.consultaFiltros.name;
        await self._generateQueryFilters(getAppendDataQuery());
    }

    async insertTableData(item, options = {}) {
        const self = this;
        const {
            tbody,
        } = options;

        const pessoa = item.pessoa;
        const pessoaDados = item;
        const razaoSocial = pessoaDados.razao_social;
        const nomeFantasia = pessoaDados.nome_fantasia ?? '***';
        const naturezaJuridica = pessoaDados.natureza_juridica ?? '***';
        const regimeTributario = pessoaDados?.regime_tributario ?? '***';
        const responsavelLegal = pessoaDados?.responsavel_legal ?? '***';
        const cpfResponsavel = pessoaDados?.cpf_responsavel ? CommonFunctions.formatCPF(pessoaDados.cpf_responsavel) : '***';
        const capitalSocial = pessoaDados.capital_social ? CommonFunctions.formatNumberToCurrency(pessoaDados.capital_social) : '***';
        const dataFundacao = pessoaDados.data_fundacao ? DateTimeHelper.retornaDadosDataHora(pessoaDados.data_fundacao, 2) : '***';

        let perfis = 'N/C';
        if (pessoa.pessoa_perfil) {
            perfis = pessoa.pessoa_perfil.map(perfil => perfil.perfil_tipo.nome).join(', ');
        }

        // Seleciona somente o perfil de referência desta página
        pessoaDados.pessoa_perfil_referencia = pessoa.pessoa_perfil.filter(perfil => perfil.perfil_tipo_id == self.#objConfigs.data.perfil_referencia_id)[0];

        let strBtns = self.#htmlBtns(pessoaDados);

        const ativo = pessoaDados.ativo_bln ? 'Ativo' : 'Inativo';
        const created_at = DateTimeHelper.retornaDadosDataHora(pessoaDados.created_at, 12);

        let classCor = !ativo ? 'text-danger' : '';

        $(tbody).append(`
            <tr id=${pessoaDados.idTr} data-id-perfil="${pessoaDados.pessoa_perfil_referencia.id}">
                <td class="text-center">
                    <div class="btn-group btnsAcao" role="group">
                        ${strBtns}
                    </div>
                </td>
                <td class="text-nowrap text-truncate ${classCor}" title="${razaoSocial}">${razaoSocial}</td>
                <td class="text-nowrap text-truncate ${classCor}" title="${nomeFantasia}">${nomeFantasia}</td>
                <td class="text-nowrap text-center ${classCor}" title="${naturezaJuridica}">${naturezaJuridica}</td>
                <td class="text-nowrap text-center ${classCor}" title="${dataFundacao}">${dataFundacao}</td>
                <td class="text-nowrap text-center ${classCor}" title="${capitalSocial}">${capitalSocial}</td>
                <td class="text-nowrap text-truncate ${classCor}" title="${regimeTributario}">${regimeTributario}</td>
                <td class="text-nowrap text-truncate ${classCor}" title="${responsavelLegal}">${responsavelLegal}</td>
                <td class="text-nowrap text-truncate ${classCor}" title="${cpfResponsavel}">${cpfResponsavel}</td>
                <td class="text-nowrap text-truncate ${classCor}" title="${perfis}">${perfis}</td>
                <td class="text-nowrap text-truncate ${classCor}" title="${ativo}">${ativo}</td>
                <td class="text-nowrap ${classCor}" title="${created_at ?? ''}">${created_at ?? ''}</td>
            </tr>
        `);

        self.#addEventosRegistrosConsulta(item);
        BootstrapFunctionsHelper.addEventPopover();
        return true;
    }

    #htmlBtns(pessoaDados) {
        const self = this;

        let strBtns = `
            <li>
                <a href="${self._objConfigs.url.baseFrontPessoaJuridicaClienteForm}/${pessoaDados.pessoa_perfil_referencia.id}" class="dropdown-item fs-6 btn-edit" title="Editar pessoa física ${pessoaDados.nome}.">
                    Editar
                </a>
            </li>
            <li>
                <button type="button" class="dropdown-item fs-6 btn-delete text-danger" title="Excluir pessoa física ${pessoaDados.nome}.">
                    Excluir
                </button>
            </li>`;

        strBtns = `
            <button class="btn dropdown-toggle btn-sm ${!strBtns ? 'disabled border-0' : ''}" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-three-dots-vertical"></i>
            </button>
            <ul class="dropdown-menu">
                ${strBtns}
            </ul>`;

        return strBtns;
    }

    #addEventosRegistrosConsulta(item) {
        const self = this;

        $(`#${item.idTr}`).find(`.btn-delete`).click(async function () {
            CommonFunctions.generateNotification('Funcionalidade para excluir pessoa juridica, em desenvolvimento.', 'warning');
            // self._delButtonAction(item.id, item.pessoa_dados.nome, {
            //     title: `Exclusão de Pessoa Física`,
            //     message: `
            //     Confirma a exclusão da Pessoa Física <b>${item.pessoa_dados.nome}</b>?
            //     <br><br>
            //     <div class="alert alert-danger blink-75">Atenção: Esta exclusão excluirá todos os perfis associados a ela.</div>`,
            //     success: `Pessoa Física excluída com sucesso!`,
            //     button: this
            // });
        });
    }
}

$(function () {
    new PageClientePJIndex();
});