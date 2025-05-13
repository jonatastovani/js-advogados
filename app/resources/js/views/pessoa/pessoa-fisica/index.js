import { CommonFunctions } from "../../../commons/CommonFunctions";
import { TemplateSearch } from "../../../commons/templates/TemplateSearch";
import { BootstrapFunctionsHelper } from "../../../helpers/BootstrapFunctionsHelper";
import { DateTimeHelper } from "../../../helpers/DateTimeHelper";

class PagePessoaFisicaIndex extends TemplateSearch {

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
            basePessoa: window.apiRoutes.basePessoa,
            baseFrontPessoaFisicaForm: window.frontRoutes.baseFrontPessoaFisicaForm,
        },
        data: {
            perfil_referencia_id: window.Enums.PessoaPerfilTipoEnum.CLIENTE,
            perfis_busca: [],
            // perfis_busca: window.Statics.PessoaPerfilTipoPerfisParaPessoaFisica.map((p) => p.id),
            // perfis_busca: [
            //     window.Enums.PessoaPerfilTipoEnum.CLIENTE,
            //     window.Enums.PessoaPerfilTipoEnum.USUARIO,
            //     window.Enums.PessoaPerfilTipoEnum.PARCEIRO,
            //     window.Enums.PessoaPerfilTipoEnum.TERCEIRO,
            // ],
            // Pré carregamento de dados vindo da URL
            preload: {}
        }
    };

    constructor() {
        super({ sufixo: 'PagePessoaFisicaIndex' });
        this._objConfigs = CommonFunctions.deepMergeObject(this._objConfigs, this.#objConfigs);
        this.initEvents();
    }

    get getPerfisBusca() {
        return this._objConfigs.data.perfis_busca;
    }

    set setPerfisBusca(val) {
        this._objConfigs.data.perfis_busca = val;
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

        self.#addEventosPerfisBusca();
    }

    /**
 * Inicializa eventos dos checkboxes de perfis com a classe 'perfis-busca'.
 * Impede que todos sejam desmarcados e filtra apenas os IDs permitidos.
 * Atualiza self._objConfigs.data.perfis_busca com os valores válidos.
 */
    #addEventosPerfisBusca() {
        const self = this;
        const idsPermitidos = window.Statics.PessoaPerfilTipoPerfisParaPessoaFisica.map(p => p.id);

        // Estado inicial com todos os válidos marcados
        self.setPerfisBusca = CommonFunctions.clonePure(idsPermitidos);

        const atualizarBloqueioUltimoCheckbox = () => {
            $('.perfis-busca').prop('disabled', false); // Libera todos
            const marcados = $('.perfis-busca:checked');
            if (marcados.length === 1) {
                marcados.prop('disabled', true);
            }
        };

        $(`#${self.getSufixo} .perfis-busca`).each(function () {
            const $checkbox = $(this);
            const valor = Number($checkbox.val());

            $checkbox.prop('disabled', false);

            $checkbox.on('change', function (e) {
                const estaMarcado = $checkbox.is(':checked');

                // Validação: o ID deve estar dentro dos permitidos
                if (!idsPermitidos.includes(valor)) {
                    CommonFunctions.generateNotification(
                        'Perfil inválido selecionado.',
                        'warning',
                        { itensArray: [`O ID ${valor} não é um perfil permitido para Pessoa Física.`] }
                    );
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    $checkbox.prop('checked', !estaMarcado);
                    return;
                }

                // Atualização do array de perfis marcados
                let perfisAtuais = new Set(self.getPerfisBusca);

                if (estaMarcado) {
                    perfisAtuais.add(valor);
                } else {
                    if (perfisAtuais.size <= 1) {
                        $checkbox.prop('checked', true);
                        $checkbox.prop('disabled', true);
                        return;
                    }
                    perfisAtuais.delete(valor);
                }

                self.setPerfisBusca = Array.from(perfisAtuais);

                atualizarBloqueioUltimoCheckbox();
            });
        });

        atualizarBloqueioUltimoCheckbox();
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
            appendData.include_perfis_inativos = true;
            return { appendData };
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
            <tr id=${pessoaDados.idTr}>
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

        self.#addEventosRegistrosConsulta(pessoaDados);
        BootstrapFunctionsHelper.addEventPopover();
        return true;
    }

    #htmlBtns(pessoaDados) {
        const self = this;
        const perfil = pessoaDados.pessoa_perfil_referencia;

        let strBtns = `
            <li>
                <a href="${self._objConfigs.url.baseFrontPessoaFisicaForm}/${pessoaDados.pessoa.id}" class="dropdown-item fs-6 btn-edit" title="Editar pessoa física ${pessoaDados.nome}.">
                    Editar
                </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <button type="button" class="dropdown-item fs-6 btn-delete-pessoa text-bg-danger" title="Excluir pessoa física ${pessoaDados.nome}.">
                    Excluir Pessoa
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

    #addEventosRegistrosConsulta(pessoaDados) {
        const self = this;

        $(`#${pessoaDados.idTr}`).find(`.btn-delete-pessoa`).click(async function () {

            const perfis = pessoaDados.pessoa.pessoa_perfil.map(perfil => perfil.perfil_tipo.nome).join(', ');

            self._delButtonAction(pessoaDados.pessoa.id, pessoaDados.nome, {
                title: `Exclusão de Pessoa Física`,
                message: `
                    <p>Tem certeza de que deseja excluir a Pessoa Física <b>${pessoaDados.nome}</b>?</p>
                    <div class="alert alert-danger blink-75">
                        Atenção: todos os perfis vinculados a esta pessoa também serão removidos.
                    </div>
                    <p><b>Perfis vinculados:</b> ${perfis}</p>`,
                success: `Pessoa Física <b>${pessoaDados.nome}</b> excluída com sucesso!`,
                button: this,
                urlApi: self._objConfigs.url.basePessoa,
            });

        });
    }
}

$(function () {
    new PagePessoaFisicaIndex();
});