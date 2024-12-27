import { commonFunctions } from "../../commons/commonFunctions";
import { modalSearchAndFormRegistration } from "../../commons/modal/modalSearchAndFormRegistration";
import { DateTimeHelper } from "../../helpers/DateTimeHelper";
import { functionsQueryCriteria } from "../../helpers/functionsQueryCriteria";
import { UUIDHelper } from "../../helpers/UUIDHelper";
import { modalSelecionarPerfil } from "./modalSelecionarPerfil";

export class modalPessoa extends modalSearchAndFormRegistration {

    /**
     * Configuração local do modal
     */
    #objConfigs = {
        formRegister: false,
        querys: {
            consultaFiltrosFisica: {
                name: 'consulta-filtros-fisica',
                url: window.apiRoutes.basePessoaFisica,
                urlSearch: `${window.apiRoutes.basePessoaFisica}/consulta-filtros`,
                tbody: $('#tableDataModalPessoaFisica').find('tbody'),
                footerPagination: $('#footerPaginationModalPessoaFisica'),
                formDataSearch: $('#formDataSearchModalPessoaFisica'),
                insertTableData: 'insertTableDataPessoaFisica',
            },
            consultaFiltrosJuridica: {
                name: 'consulta-filtros-juridica',
                url: window.apiRoutes.basePessoaJuridica,
                urlSearch: `${window.apiRoutes.basePessoaJuridica}/consulta-filtros`,
                tbody: $('#tableDataModalPessoaJuridica').find('tbody'),
                footerPagination: $('#footerPaginationModalPessoaJuridica'),
                formDataSearch: $('#formDataSearchModalPessoaJuridica'),
                insertTableData: 'insertTableDataPessoaJuridica',
            },
            consultaFisicaCriterios: {
                name: 'consulta-criterios',
                url: window.apiRoutes.basePessoaFisica,
                urlSearch: `${window.apiRoutes.basePessoaFisica}/consulta-criterios`,
                tbody: $('#tableDataModalPessoaFisicaCriterios').find('tbody'),
                footerPagination: $('#footerPaginationModalPessoaFisicaCriterios'),
                formDataSearch: $('#formDataSearchModalPessoaFisicaCriterios'),
            },
            consultaJuridicaCriterios: {
                name: 'consulta-criterios',
                url: window.apiRoutes.basePessoaJuridica,
                urlSearch: `${window.apiRoutes.basePessoaJuridica}/consulta-criterios`,
                tbody: $('#tableDataModalPessoaJuridicaCriterios').find('tbody'),
                footerPagination: $('#footerPaginationModalPessoaJuridicaCriterios'),
                formDataSearch: $('#formDataSearchModalPessoaJuridicaCriterios'),
            },
        },
        sufixo: 'ModalPessoa',
    };

    #dataEnvModal = {
        attributes: {
            select: {
                quantity: 1,
                autoReturn: true,
            }
        },
    };

    constructor(objData = {}) {
        const envSuper = {
            idModal: "#modalPessoa",
        };
        if (objData.dataEnvModal) {
            envSuper.dataEnvModal = objData.dataEnvModal;
        }
        super(envSuper);

        this._objConfigs = Object.assign(this._objConfigs, this.#objConfigs);
        this._dataEnvModal = Object.assign(this._dataEnvModal, this.#dataEnvModal);
        this.functionsCriteria = new functionsQueryCriteria(this, this._idModal);
    }

    async modalOpen() {
        const self = this;
        self.#addEventosPadrao();
        self.#atualizaBadge();
        self.#consultaInicial();
        await self._modalHideShow();
        return await self._modalOpen();
    }

    async #consultaInicial() {
        const self = this;
        await self.#buscaFiltroPessoaFisica();
        await self.#buscaFiltroPessoaJuridica();
    }

    async #buscaFiltroPessoaFisica() {
        const self = this;
        const perfis_busca = self._dataEnvModal.perfis_busca.map(item => item.id);
        self._setTypeCurrentSearch = self._objConfigs.querys.consultaFiltrosFisica.name;
        await self._generateQueryFilters({ formDataSearch: self._objConfigs.querys.consultaFiltrosFisica.formDataSearch, appendData: { perfis_busca: perfis_busca } });
    }

    async #buscaFiltroPessoaJuridica() {
        const self = this;
        const perfis_busca = self._dataEnvModal.perfis_busca.map(item => item.id);
        self._setTypeCurrentSearch = self._objConfigs.querys.consultaFiltrosJuridica.name;
        await self._generateQueryFilters({ formDataSearch: self._objConfigs.querys.consultaFiltrosJuridica.formDataSearch, appendData: { perfis_busca: perfis_busca } });
    }

    #addEventosPadrao() {
        const self = this;
        const modal = $(self._idModal);
        const formPessoaFisica = modal.find(`#formDataSearch${self._objConfigs.sufixo}Fisica`);
        const formPessoaJuridica = modal.find(`#formDataSearch${self._objConfigs.sufixo}Juridica`);
        const formFisicaCriterios = modal.find(`#formDataSearch${self._objConfigs.sufixo}FisicaCriterios`);
        const formJuridicaCriterios = modal.find(`#formDataSearch${self._objConfigs.sufixo}JuridicaCriterios`);

        if (!self._dataEnvModal.perfis_busca) {
            commonFunctions.generateNotification('Perfis de busca não definidos.', 'warning');
            return false;
        }

        formPessoaFisica.find('.btnBuscar').on('click', async function (e) {
            e.preventDefault();
            await self.#buscaFiltroPessoaFisica();
        });

        formPessoaJuridica.find('.btnBuscar').on('click', async function (e) {
            e.preventDefault();
            await self.#buscaFiltroPessoaJuridica();
        });

        formFisicaCriterios.find('.btnBuscar').on('click', async function (e) {
            e.preventDefault();
            self._setTypeCurrentSearch = self._objConfigs.querys.consultaFisicaCriterios.name;
            const data = await self.functionsCriteria.generateQueryFiltersCriteria();
            if (!data) return;
            await self._getData(data);
        });

        formJuridicaCriterios.find('.btnBuscar').on('click', async function (e) {
            e.preventDefault();
            self._setTypeCurrentSearch = self._objConfigs.querys.consultaJuridicaCriterios.name;
            const data = await self.functionsCriteria.generateQueryFiltersCriteria();
            if (!data) return;
            await self._getData(data);
        });

        modal.find('.btn-return').on('click', function () {
            if (self._promisseReturnValue.selecteds.length > 0) {
                self._promisseReturnValue.refresh = true;
            }
            self._setEndTimer = true;
        });

        modal.find(`#consultaPessoaFisica-tab,
            #consultaPessoaJuridica-tab,
            #consultaPessoaFisicaCriterios-tab,
            #consultaPessoaJuridicaCriterios-tab,
            #registrosSelecionados-tab`
        ).on('click', function () {
            const tabPanel = $(this).attr('aria-controls');
            $(`#${tabPanel}`).parent().children('.tab-pane').removeClass('d-flex').removeClass('flex-column');
            $(`#${tabPanel}`).addClass('d-flex flex-column');
        });
    }

    _modalClose() {
        const self = this;
        const modal = $(self.getIdModal);
        modal.find('#consultaPessoaFisica-tab').trigger('click');
        if (modal.find(`#formDataSearch${self._objConfigs.sufixo}Criterios`).length) {
            modal.find('.btnLimparCriterios').trigger('click');
        }
        super._modalClose();
    }

    _modalReset() {
        const self = this;
        const modal = $(self.getIdModal);
        super._modalReset();
        modal.find('tbody').html('');
        self._paginationDefault();
    }

    async insertTableDataPessoaFisica(item, options = {}) {
        const self = this;
        const {
            tbody,
        } = options;

        let pessoa = undefined;

        // Quando vem da consulta
        if (item.pessoa) {
            pessoa = item.pessoa;
            delete item.pessoa;
            pessoa.idTr = item.idTr;
            delete item.idTr;
            pessoa.pessoa_dados = item;
        } else {
            // Quando está sendo selecionado
            pessoa = item;
        }
        const pessoa_dados = pessoa.pessoa_dados;

        const cpf = pessoa_dados.cpf ? commonFunctions.formatCPF(pessoa_dados.cpf) : '';

        const itemSelecionado = self.#verificaRegistroSelecionado(pessoa);
        let botoes = '';
        if (itemSelecionado) {
            botoes = self.#htmlBtnRemover();
            pessoa.idTrSelecionado = itemSelecionado.idTrSelecionado;
        } else {
            botoes = self.#htmlBtnSelecionar();
        }

        let perfis = 'N/C';
        if (pessoa.pessoa_perfil) {
            perfis = pessoa.pessoa_perfil.map(perfil => perfil.perfil_tipo.nome).join(', ');
        }

        $(tbody).append(`
            <tr id=${pessoa.idTr}>
                <td class="text-center text-nowrap">
                    <div class="btn-group btnsAcao" role="group">
                        ${botoes}
                    </div>
                </td>
                <td class="text-nowrap text-truncate" style="max-width: 20rem" title="${pessoa_dados.nome}">${pessoa_dados.nome}</td>
                <td class="text-nowrap text-truncate" style="max-width: 20rem" title="${pessoa_dados.nome_social ?? ''}">${pessoa_dados.nome_social ?? ''}</td>
                <td class="text-center text-nowrap">${cpf}</td>
                <td class="text-center text-nowrap">${pessoa_dados.rg ?? ''}</td>
                <td class="text-nowrap text-truncate" style="max-width: 20rem" title="${pessoa_dados.pai ?? ''}">${pessoa_dados.pai ?? ''}</td>
                <td class="text-nowrap text-truncate" style="max-width: 20rem" title="${pessoa_dados.mae ?? ''}">${pessoa_dados.mae ?? ''}</td>
                <td class="text-center text-nowrap ">${pessoa_dados.nascimento_data ?? ''}</td>
                <td class="text-nowrap">${perfis}</td>
            </tr>
        `);

        self.#addEventosRegistrosConsulta(pessoa);
        return pessoa;
    }

    async insertTableDataSelecionados(item, options = {}) {
        const self = this;
        let result = false;

        switch (item.pessoa_dados_type) {
            case window.Enums.PessoaTipoEnum.PESSOA_FISICA:
                result = await self.insertTableDataPessoaFisica(item, { tbody: $(`#tableData${self._objConfigs.sufixo}SelecionadosFisica tbody`) });
                break;

            case window.Enums.PessoaTipoEnum.PESSOA_JURIDICA:
                result = await self.insertTableDataPessoaJuridica(item, { tbody: $(`#tableData${self._objConfigs.sufixo}SelecionadosJuridica tbody`) });
                break;
        }
        return result;
    }

    async insertTableDataPessoaJuridica(item, options = {}) {
        const self = this;
        const {
            tbody,
        } = options;

        let pessoa = undefined;

        // Quando vem da consulta
        if (item.pessoa) {
            pessoa = item.pessoa;
            delete item.pessoa;
            pessoa.idTr = item.idTr;
            delete item.idTr;
            pessoa.pessoa_dados = item;
        } else {
            // Quando está sendo selecionado
            pessoa = item;
        }
        const pessoa_dados = pessoa.pessoa_dados;

        const itemSelecionado = self.#verificaRegistroSelecionado(pessoa);
        let botoes = '';
        if (itemSelecionado) {
            botoes = self.#htmlBtnRemover();
            pessoa.idTrSelecionado = itemSelecionado.idTrSelecionado;
        } else {
            botoes = self.#htmlBtnSelecionar();
        }

        let perfis = 'N/C';
        if (pessoa.pessoa_perfil) {
            perfis = pessoa.pessoa_perfil.map(perfil => perfil.perfil_tipo.nome).join(', ');
        }

        const razaoSocial = pessoa_dados.razao_social;
        const nomeFantasia = pessoa_dados.nome_fantasia ?? '***';
        const naturezaJuridica = pessoa_dados.natureza_juridica ?? '***';
        const regimeTributario = pessoa_dados?.regime_tributario ?? '***';
        const responsavelLegal = pessoa_dados?.responsavel_legal ?? '***';
        const cpfResponsavel = pessoa_dados?.cpf_responsavel ? commonFunctions.formatCPF(pessoa_dados.cpf_responsavel) : '***';
        const capitalSocial = pessoa_dados.capital_social ? commonFunctions.formatNumberToCurrency(pessoa_dados.capital_social) : '***';
        const dataFundacao = pessoa_dados.data_fundacao ? DateTimeHelper.retornaDadosDataHora(pessoa_dados.data_fundacao, 2) : '***';

        const ativo = pessoa_dados.ativo_bln ? 'Ativo' : 'Inativo';
        const created_at = DateTimeHelper.retornaDadosDataHora(pessoa_dados.created_at, 12);

        $(tbody).append(`
                <tr id=${pessoa.idTr}>
                    <td class="text-center text-nowrap">
                        <div class="btn-group btnsAcao" role="group">
                            ${botoes}
                        </div>
                    </td>
                    <td class="text-nowrap text-truncate" title="${razaoSocial}">${razaoSocial}</td>
                    <td class="text-nowrap text-truncate" title="${nomeFantasia}">${nomeFantasia}</td>
                    <td class="text-nowrap text-center" title="${naturezaJuridica}">${naturezaJuridica}</td>
                    <td class="text-nowrap text-center" title="${dataFundacao}">${dataFundacao}</td>
                    <td class="text-nowrap text-center" title="${capitalSocial}">${capitalSocial}</td>
                    <td class="text-nowrap text-truncate" title="${regimeTributario}">${regimeTributario}</td>
                    <td class="text-nowrap text-truncate" title="${responsavelLegal}">${responsavelLegal}</td>
                    <td class="text-nowrap text-truncate" title="${cpfResponsavel}">${cpfResponsavel}</td>
                    <td class="text-nowrap text-truncate" title="${perfis}">${perfis}</td>
                    <td class="text-nowrap text-truncate" title="${ativo}">${ativo}</td>
                    <td class="text-nowrap" title="${created_at ?? ''}">${created_at ?? ''}</td>
                </tr>
            `);

        self.#addEventosRegistrosConsulta(pessoa);
        return pessoa;
    }

    #verificaRegistroSelecionado(item) {
        const self = this;

        for (const element of self._promisseReturnValue.selecteds) {
            if (element.id == item.id) {
                element.idsTrs.push(item.idTr);
                return element; // Pessoa já está selecionada
            }
        }
        return null; // Pessoa não selecionada
    }

    #addEventosRegistrosConsulta(registro) {
        const self = this;

        let item = JSON.parse(JSON.stringify(registro));

        const selecionaUnicoPerfil = async (item) => {

            const selecionaPrimeiro = (perfis) => {
                return JSON.parse(JSON.stringify(perfis[0]));
            }

            let objPerfil = undefined;
            if (item.pessoa_perfil.length > 1) {
                const perfis_busca = self._dataEnvModal.perfis_busca.map(item => item.id);
                const perfisExibir = item.pessoa_perfil.filter(perfil => perfis_busca.includes(perfil.perfil_tipo_id));
                if (perfisExibir.length > 1) {
                    try {
                        const objModal = new modalSelecionarPerfil();
                        objModal.setDataEnvModal = {
                            perfis_opcoes: perfisExibir,
                        };
                        await self._modalHideShow(false);
                        const response = await objModal.modalOpen();
                        objPerfil = response.register;
                        objPerfil.pessoa = item;
                    } catch (error) {
                        commonFunctions.generateNotificationErrorCatch(error);
                    } finally {
                        await self._modalHideShow();
                    }
                } else {
                    objPerfil = selecionaPrimeiro(perfisExibir);
                    objPerfil.pessoa = item;
                }
            } else {
                objPerfil = selecionaPrimeiro(item.pessoa_perfil);
                objPerfil.pessoa = item;
            }
            return objPerfil;
        }

        const inserirSelecionado = (item) => {
            const select = self._dataEnvModal?.attributes?.select ?? {};
            const promisseReturnValue = self._promisseReturnValue;

            if (select?.quantity && select.quantity == 1) {
                promisseReturnValue.selected = item;
            } else {
                promisseReturnValue.selecteds.push(item);
            }
            promisseReturnValue.refresh = true;

            if (select?.autoReturn && select.autoReturn &&
                (
                    select?.quantity && promisseReturnValue.selecteds.length == select.quantity ||
                    (
                        select?.quantity && select.quantity == 1 && promisseReturnValue.selected
                    )
                )
            ) {
                self._setEndTimer = true;
            }
        }

        //#region Eventos de botões
        const adicionaEventoSelecionar = (itemEnv) => {
            let itemSelecionar = JSON.parse(JSON.stringify(itemEnv));

            const tr = $(`#${itemSelecionar.idTr}`);
            tr.find('.btn-select').on("click", async function () {

                for (const query of Object.values(self._objConfigs.querys)) {

                    if (!query.recordsOnScreen) continue;
                    for (let element of query.recordsOnScreen) {
                        element = JSON.parse(JSON.stringify(element));

                        if (element.id == itemSelecionar.id) {

                            let elementPerfil = await selecionaUnicoPerfil(element);
                            let elementPessoa = elementPerfil.pessoa;

                            const selecionado = self.#verificaRegistroSelecionado(elementPerfil);
                            if (!selecionado) {

                                elementPessoa.idTrSelecionado = UUIDHelper.generateUUID();

                                const tempIdTr = elementPessoa.idTr;
                                elementPessoa.idTr = elementPessoa.idTrSelecionado;

                                const returnInsert = await self.insertTableDataSelecionados(elementPessoa);
                                elementPessoa.idTr = tempIdTr;
                                elementPessoa.idsTrs = [elementPessoa.idTr];

                                inserirSelecionado(elementPerfil);
                                $(`#${elementPessoa.idTr}, #${elementPessoa.idTrSelecionado}`).find('.btnsAcao').prepend(self.#htmlBtnRemover());
                            } else {

                                elementPessoa.idTrSelecionado = selecionado.idTrSelecionado;
                                $(`#${elementPessoa.idTr}`).find('.btnsAcao').prepend(self.#htmlBtnRemover());
                            }

                            $(`#${elementPessoa.idTr}, #${elementPessoa.idTrSelecionado}`).find('.btn-select').remove();
                            adicionaEventoDeletar(elementPessoa);

                            // const selecionado = self.#verificaRegistroSelecionado(element);
                            // if (!selecionado) {
                            //     element.idTrSelecionado = UUIDHelper.generateUUID();

                            //     const tempIdTr = element.idTr;
                            //     element.idTr = element.idTrSelecionado;

                            //     const returnInsert = await self.insertTableDataSelecionados(element);
                            //     element.idTr = tempIdTr;
                            //     element.idsTrs = [element.idTr];

                            //     inserirSelecionado(element);
                            //     $(`#${element.idTr}, #${element.idTrSelecionado}`).find('.btnsAcao').prepend(self.#htmlBtnRemover());
                            // } else {
                            //     element.idTrSelecionado = selecionado.idTrSelecionado;
                            //     $(`#${element.idTr}`).find('.btnsAcao').prepend(self.#htmlBtnRemover());
                            // }

                            // $(`#${element.idTr}, #${element.idTrSelecionado}`).find('.btn-select').remove();
                            // adicionaEventoDeletar(element);
                        }
                    }
                }
                self.#atualizaBadge();
            });
        }

        const adicionaEventoDeletar = (itemEnv) => {
            let itemDeletar = JSON.parse(JSON.stringify(itemEnv));

            $(`#${itemDeletar.idTrSelecionado}`).find('.btn-delete').off('click');
            const trs = $(`#${itemDeletar.idTr}, #${itemDeletar.idTrSelecionado}`);
            trs.find('.btn-delete').on("click", async function () {
                const selecionado = self._promisseReturnValue.selecteds.filter((selecionados) => selecionados.pessoa.idTrSelecionado == itemDeletar.idTrSelecionado);

                self._promisseReturnValue.selecteds = self._promisseReturnValue.selecteds.filter((selecionados) => selecionados.pessoa.idTrSelecionado != itemDeletar.idTrSelecionado);

                $(`#${itemDeletar.idTrSelecionado}`).remove();
                $(`#${selecionado[0].pessoa.idsTrs.join(', #')}`).find('.btn-delete').remove();
                $(`#${selecionado[0].pessoa.idsTrs.join(', #')}`).find('.btnsAcao').prepend(self.#htmlBtnSelecionar());
                for (const idTrConsulta of selecionado[0].pessoa.idsTrs) {
                    if ($(`#${idTrConsulta}`).length) {
                        itemDeletar.idTr = idTrConsulta;
                        adicionaEventoSelecionar({ ...itemDeletar });
                    }
                }
                self.#atualizaBadge();
            });
        }

        //#endregion

        if (registro.idTrSelecionado) {
            adicionaEventoDeletar(item);
        } else {
            adicionaEventoSelecionar(item);
        }
        // adicionaEventoVisualizar(item, item.idTr);
    }

    #htmlBtnSelecionar() {
        return `<button type="button" class="btn btn-success btn-sm btn-select" title="Selecionar"><i class="bi bi-check2-square"></i></button>`
    }

    #htmlBtnRemover() {
        return `<button type="button" class="btn btn-danger btn-sm btn-delete" title="Remover"><i class="bi bi-trash"></i></button>`
    }

    #atualizaBadge() {
        const self = this;
        $(self._idModal).find('.qtdRegistrosSelecionados').html(self._promisseReturnValue.selecteds.length);
    }
}