import { commonFunctions } from "../../../../commons/commonFunctions";
import { templateSearch } from "../../../../commons/templates/templateSearch";
import { BootstrapFunctionsHelper } from "../../../../helpers/BootstrapFunctionsHelper";
import { DateTimeHelper } from "../../../../helpers/DateTimeHelper";
import { UUIDHelper } from "../../../../helpers/UUIDHelper";

class PageClientePFIndex extends templateSearch {

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
        await self.#executarBusca();
    }

    #addEventosBotoes() {
        const self = this;

        $(`#formDataSearch${self.getSufixo}`).find('.btnBuscar').on('click', async function (e) {
            e.preventDefault();
            self.#executarBusca();
        });

        // const openModal = async () => {
        //     try {
        //         const objModal = new modalLancamentoServicoMovimentar({
        //             urlApi: `${self._objConfigs.url.baseServico}/`
        //         });
        //         objModal.setDataEnvModal = {
        //             idRegister: "9d7f9116-eb25-4090-993d-cdf0ae143c03",
        //             pagamento_id: "9d7f9116-d30a-4559-9231-3083ad482553",
        //             status_id: window.Enums.LancamentoStatusTipoEnum.LIQUIDADO_EM_ANALISE
        //         }
        //         const response = await objModal.modalOpen();
        //         console.log(response);

        //     } catch (error) {
        //         commonFunctions.generateNotificationErrorCatch(error);
        //     }
        // }

        // openModal();
    }

    async #executarBusca() {
        const self = this;

        const getAppendDataQuery = () => {
            const formData = $(`#formDataSearch${self.getSufixo}`);
            let appendData = {};
            let data = commonFunctions.getInputsValues(formData[0]);

            if (data.conta_id && UUIDHelper.isValidUUID(data.conta_id)) {
                appendData.conta_id = data.conta_id;
            }

            if (data.movimentacao_tipo_id && Number(data.movimentacao_tipo_id) > 0) {
                appendData.movimentacao_tipo_id = data.movimentacao_tipo_id;
            }

            if (data.movimentacao_status_tipo_id && Number(data.movimentacao_status_tipo_id) > 0) {
                appendData.movimentacao_status_tipo_id = data.movimentacao_status_tipo_id;
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

        if (!self.#objConfigs.data?.console) {
            self.#objConfigs.data.console = true;
            console.log(item);
        }

        const pessoaDados = item.pessoa_dados;
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
        if (item.pessoa_perfil) {
            perfis = item.pessoa_perfil.map(perfil => perfil.perfil_tipo.nome).join(', ');
        }

        // Seleciona somente o perfil de referência desta página
        item.pessoa_perfil_referencia = item.pessoa_perfil.filter(perfil => perfil.perfil_tipo_id == self.#objConfigs.data.perfil_referencia_id)[0];

        let strBtns = self.#htmlBtns(item);

        const ativo = pessoaDados.ativo_bln ? 'Ativo' : 'Inativo';
        const created_at = DateTimeHelper.retornaDadosDataHora(item.created_at, 12);

        let classCor = !ativo ? 'text-danger' : '';

        $(tbody).append(`
            <tr id=${item.idTr} data-id="${item.id}">
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

    #htmlBtns(item) {
        const self = this;
        let strBtns = `
            <li>
                <a href="${self._objConfigs.url.baseFrontPessoaFisicaClienteForm}/${item.pessoa_perfil_referencia.id}" class="dropdown-item fs-6 btn-edit" title="Editar pessoa física ${item.nome}.">
                    Editar
                </a>
            </li>
            <li>
                <button type="button" class="dropdown-item fs-6 btn-delete text-danger" title="Excluir pessoa física ${item.nome}.">
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
            self._delButtonAction(item.id, item.pessoa_dados.nome, {
                title: `Exclusão de Pessoa Física`,
                message: `
                Confirma a exclusão da Pessoa Física <b>${item.pessoa_dados.nome}</b>?
                <br><br>
                <div class="alert alert-danger blink-75">Atenção: Esta exclusão excluirá todos os perfis associados a ela.</div>`,
                success: `Pessoa Física excluída com sucesso!`,
                button: this
            });
        });
    }
}

$(function () {
    new PageClientePFIndex();
});