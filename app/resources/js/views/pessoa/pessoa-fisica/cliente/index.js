import { commonFunctions } from "../../../../commons/commonFunctions";
import { TemplateSearch } from "../../../../commons/templates/TemplateSearch";
import { BootstrapFunctionsHelper } from "../../../../helpers/BootstrapFunctionsHelper";
import { DateTimeHelper } from "../../../../helpers/DateTimeHelper";

class PageClientePFIndex extends TemplateSearch {

    #objConfigs = {
        querys: {
            consultaFiltros: {
                name: 'consulta-filtros',
                url: window.apiRoutes.basePessoaFisica,
                urlSearch: `${window.apiRoutes.basePessoaFisica}/consulta-filtros`,
            }
        },
        url: {
            base: window.apiRoutes.basePessoaFisica,
            baseFrontPessoaFisicaClienteForm: window.frontRoutes.baseFrontPessoaFisicaClienteForm
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
        super({ sufixo: 'PageClientePFIndex' });
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
            let data = commonFunctions.getInputsValues(formData[0]);

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
        const nome = pessoaDados.nome;
        const mae = pessoaDados.mae ?? '***';
        const pai = pessoaDados.pai ?? '***';
        const estadoCivil = pessoaDados?.estado_civil?.nome ?? '***';
        const escolaridade = pessoaDados?.escolaridade?.nome ?? '***';
        const genero = pessoaDados?.genero?.nome ?? '***';
        const dataNascimento = pessoaDados.nascimento_data ? DateTimeHelper.retornaDadosDataHora(pessoaDados.nascimento_data, 2) : '***';
        const naturalidade = pessoaDados.naturalidade ?? '***';
        const nacionalidade = pessoaDados.nacionalidade ?? '***';

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
                <td class="text-nowrap text-truncate ${classCor}" title="${nome}">${nome}</td>
                <td class="text-nowrap text-truncate ${classCor}" title="${mae}">${mae}</td>
                <td class="text-nowrap text-center ${classCor}" title="${pai}">${pai}</td>
                <td class="text-nowrap text-center ${classCor}" title="${estadoCivil}">${estadoCivil}</td>
                <td class="text-nowrap text-center ${classCor}" title="${escolaridade}">${escolaridade}</td>
                <td class="text-nowrap text-center ${classCor}" title="${genero}">${genero}</td>
                <td class="text-nowrap text-center ${classCor}" title="${dataNascimento}">${dataNascimento}</td>
                <td class="text-nowrap text-truncate ${classCor}" title="${naturalidade}">${naturalidade}</td>
                <td class="text-nowrap text-truncate ${classCor}" title="${nacionalidade}">${nacionalidade}</td>
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

        // let strBtns = `
        //     <li>
        //         <a href="${self._objConfigs.url.baseFrontPessoaFisicaClienteForm}/${pessoaDados.pessoa_perfil_referencia.id}" class="dropdown-item fs-6 btn-edit" title="Editar pessoa física ${pessoaDados.nome}.">
        //             Editar
        //         </a>
        //     </li>
        //     <li>
        //         <button type="button" class="dropdown-item fs-6 btn-delete text-danger" title="Excluir pessoa física ${pessoaDados.nome}.">
        //             Excluir
        //         </button>
        //     </li>`;

        let strBtns = `
            <li>
                <a href="${self._objConfigs.url.baseFrontPessoaFisicaClienteForm}/${pessoaDados.pessoa_perfil_referencia.id}" class="dropdown-item fs-6 btn-edit" title="Editar pessoa física ${pessoaDados.nome}.">
                    Editar
                </a>
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
            commonFunctions.generateNotification('Funcionalidade para excluir pessoa fisica, em desenvolvimento.', 'warning');
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
    new PageClientePFIndex();
});