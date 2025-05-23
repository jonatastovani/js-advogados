import { CommonFunctions } from "../../commons/CommonFunctions";
import { ModalSearchAndFormRegistration } from "../../commons/modal/ModalSearchAndFormRegistration";
import { DateTimeHelper } from "../../helpers/DateTimeHelper";
import { functionsQueryCriteria } from "../../helpers/functionsQueryCriteria";
import { MasksAndValidateHelpers } from "../../helpers/MasksAndValidateHelpers";
import { UUIDHelper } from "../../helpers/UUIDHelper";
import { ModalSelecionarPerfil } from "./ModalSelecionarPerfil";

export class ModalPessoa extends ModalSearchAndFormRegistration {

    // #dataEnvModal = {
    //     attributes: {
    //         select: {
    //             quantity: 1,
    //             autoReturn: true,
    //         }
    //     },
    // };

    /**
     * Constructor da classe ModalPessoa.
     * 
     * @param {Object} objData - Objeto com dados para inicializar o modal.
     * @param {Object} [objData.objConfigs] - Configurações adicionais para o modal. Pode conter propriedades adicionais para o modal.
     * @param {Object} [objData.dataEnvModal] - Dados para inicializar o modal.
     */
    constructor(objData = {}) {
        const envSuper = {
            idModal: "#ModalPessoa",
        };

        envSuper.objConfigs = CommonFunctions.deepMergeObject({
            formRegister: false,
            modalSearch: {
                disableSearchDefault: true,
            },
            querys: {
                consultaFiltrosFisica: {
                    name: 'consulta-filtros-fisica',
                    url: window.apiRoutes.basePessoaFisica,
                    urlSearch: `${window.apiRoutes.basePessoaFisica}/consulta-filtros`,
                    tbody: '#tableDataModalPessoaFisica tbody',
                    footerPagination: '#footerPaginationModalPessoaFisica',
                    formDataSearch: '#formDataSearchModalPessoaFisica',
                    insertTableData: 'insertTableDataPessoaFisica',
                },
                consultaFiltrosJuridica: {
                    name: 'consulta-filtros-juridica',
                    url: window.apiRoutes.basePessoaJuridica,
                    urlSearch: `${window.apiRoutes.basePessoaJuridica}/consulta-filtros`,
                    tbody: '#tableDataModalPessoaJuridica tbody',
                    footerPagination: '#footerPaginationModalPessoaJuridica',
                    formDataSearch: '#formDataSearchModalPessoaJuridica',
                    insertTableData: 'insertTableDataPessoaJuridica',
                },
                consultaFisicaCriterios: {
                    name: 'consulta-criterios',
                    url: window.apiRoutes.basePessoaFisica,
                    urlSearch: `${window.apiRoutes.basePessoaFisica}/consulta-criterios`,
                    tbody: '#tableDataModalPessoaFisicaCriterios tbody',
                    footerPagination: '#footerPaginationModalPessoaFisicaCriterios',
                    formDataSearch: '#formDataSearchModalPessoaFisicaCriterios',
                },
                consultaJuridicaCriterios: {
                    name: 'consulta-criterios',
                    url: window.apiRoutes.basePessoaJuridica,
                    urlSearch: `${window.apiRoutes.basePessoaJuridica}/consulta-criterios`,
                    tbody: '#tableDataModalPessoaJuridicaCriterios tbody',
                    footerPagination: '#footerPaginationModalPessoaJuridicaCriterios',
                    formDataSearch: '#formDataSearchModalPessoaJuridicaCriterios',
                },
            },
            sufixo: 'ModalPessoa',
        }, objData.objConfigs ?? {});

        envSuper.dataEnvModal = CommonFunctions.deepMergeObject({
            attributes: {
                select: {
                    quantity: 1,
                    autoReturn: true,
                }
            },
        }, objData.dataEnvModal ?? {});

        super(envSuper);

        this.functionsCriteria = new functionsQueryCriteria(this, this._idModal);
        this.setReadyQueueOpen();
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
        await self.#executaBuscaFiltroPessoaFisica();
        await self.#executaBuscaFiltroPessoaJuridica();
    }

    async #executaBuscaFiltroPessoaFisica() {
        const self = this;
        const perfis_busca = self._dataEnvModal.perfis_busca.map(item => item.id);
        self._setTypeCurrentSearch = self._objConfigs.querys.consultaFiltrosFisica.name;
        await self._generateQueryFilters({ formDataSearch: self._objConfigs.querys.consultaFiltrosFisica.formDataSearch, appendData: { perfis_busca: perfis_busca } });
    }

    async #executaBuscaFiltroPessoaJuridica() {
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
            CommonFunctions.generateNotification('Perfis de busca não definidos.', 'warning');
            return false;
        } else {
            let perfis_busca = '';
            self._dataEnvModal.perfis_busca.forEach(item => {
                if (perfis_busca != '') perfis_busca += ', ';
                perfis_busca += item.nome;
            });
            perfis_busca = `${self._dataEnvModal.perfis_busca.length > 1 ? 'Perfis de busca:' : 'Perfil de busca:'} ${perfis_busca}`;
            modal.find('.perfisBusca').html(perfis_busca);
        }

        formPessoaFisica.find('.btnBuscar').on('click', async function (e) {
            e.preventDefault();
            await self.#executaBuscaFiltroPessoaFisica();
        });

        formPessoaJuridica.find('.btnBuscar').on('click', async function (e) {
            e.preventDefault();
            await self.#executaBuscaFiltroPessoaJuridica();
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

        const cpf = pessoa_dados.cpf ? MasksAndValidateHelpers.formatCPF(pessoa_dados.cpf) : '';

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
        const cpfResponsavel = pessoa_dados?.cpf_responsavel ? MasksAndValidateHelpers.formatCPF(pessoa_dados.cpf_responsavel) : '***';
        const capitalSocial = pessoa_dados.capital_social ? CommonFunctions.formatNumberToCurrency(pessoa_dados.capital_social) : '***';
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

    // #addEventosRegistrosConsulta(registro) {
    //     const self = this;

    //     let item = CommonFunctions.clonePure(registro);

    //     const selecionaUnicoPerfil = async (item) => {

    //         const selecionaPrimeiro = (perfis) => {
    //             return CommonFunctions.clonePure(perfis[0]);
    //         }

    //         let objPerfil = undefined;
    //         if (item.pessoa_perfil.length > 1) {
    //             const perfis_busca = self._dataEnvModal.perfis_busca.map(item => item.id);
    //             const perfisExibir = item.pessoa_perfil.filter(perfil => perfis_busca.includes(perfil.perfil_tipo_id));

    //             if (perfisExibir.length > 1) {

    //                 try {
    //                     const objModal = new ModalSelecionarPerfil();
    //                     objModal.setDataEnvModal = {
    //                         perfis_opcoes: perfisExibir,
    //                     };
    //                     await self._modalHideShow(false);
    //                     const response = await objModal.modalOpen();
    //                     objPerfil = response.register;
    //                     objPerfil.pessoa = item;
    //                 } catch (error) {
    //                     CommonFunctions.generateNotificationErrorCatch(error);
    //                 } finally {
    //                     await self._modalHideShow();
    //                 }
    //             } else {

    //                 objPerfil = selecionaPrimeiro(perfisExibir);
    //                 objPerfil.pessoa = item;
    //             }
    //         } else {
    //             objPerfil = selecionaPrimeiro(item.pessoa_perfil);
    //             objPerfil.pessoa = item;
    //         }
    //         return objPerfil;
    //     }

    //     const inserirSelecionado = (item) => {
    //         const select = self._dataEnvModal?.attributes?.select ?? {};
    //         const promisseReturnValue = self._promisseReturnValue;

    //         if (select?.quantity && select.quantity == 1) {
    //             promisseReturnValue.selected = item;
    //         } else {
    //             promisseReturnValue.selecteds.push(item);
    //         }
    //         promisseReturnValue.refresh = true;

    //         if (select?.autoReturn && select.autoReturn &&
    //             (
    //                 select?.quantity && promisseReturnValue.selecteds.length == select.quantity ||
    //                 (
    //                     select?.quantity && select.quantity == 1 && promisseReturnValue.selected
    //                 )
    //             )
    //         ) {
    //             self._setEndTimer = true;
    //         }
    //     }

    //     //#region Eventos de botões
    //     const adicionaEventoSelecionar = (itemEnv) => {
    //         let itemSelecionar = CommonFunctions.clonePure(itemEnv);

    //         const tr = $(`#${itemSelecionar.idTr}`);
    //         tr.find('.btn-select').on("click", async function () {

    //             for (const query of Object.values(self._objConfigs.querys)) {

    //                 if (!query.recordsOnScreen) continue;
    //                 for (let element of query.recordsOnScreen) {
    //                     element = CommonFunctions.clonePure(element);

    //                     if (element.id == itemSelecionar.id) {

    //                         let elementPerfil = await selecionaUnicoPerfil(element);
    //                         console.warn(elementPerfil);

    //                         let elementPessoa = elementPerfil.pessoa;

    //                         const selecionado = self.#verificaRegistroSelecionado(elementPerfil);
    //                         if (!selecionado) {

    //                             elementPessoa.idTrSelecionado = UUIDHelper.generateUUID();

    //                             const tempIdTr = elementPessoa.idTr;
    //                             elementPessoa.idTr = elementPessoa.idTrSelecionado;

    //                             const returnInsert = await self.insertTableDataSelecionados(elementPessoa);
    //                             elementPessoa.idTr = tempIdTr;
    //                             elementPessoa.idsTrs = [elementPessoa.idTr];

    //                             inserirSelecionado(elementPerfil);
    //                             $(`#${elementPessoa.idTr}, #${elementPessoa.idTrSelecionado}`).find('.btnsAcao').prepend(self.#htmlBtnRemover());
    //                         } else {

    //                             elementPessoa.idTrSelecionado = selecionado.idTrSelecionado;
    //                             $(`#${elementPessoa.idTr}`).find('.btnsAcao').prepend(self.#htmlBtnRemover());
    //                         }

    //                         $(`#${elementPessoa.idTr}, #${elementPessoa.idTrSelecionado}`).find('.btn-select').remove();
    //                         adicionaEventoDeletar(elementPessoa);

    //                         // const selecionado = self.#verificaRegistroSelecionado(element);
    //                         // if (!selecionado) {
    //                         //     element.idTrSelecionado = UUIDHelper.generateUUID();

    //                         //     const tempIdTr = element.idTr;
    //                         //     element.idTr = element.idTrSelecionado;

    //                         //     const returnInsert = await self.insertTableDataSelecionados(element);
    //                         //     element.idTr = tempIdTr;
    //                         //     element.idsTrs = [element.idTr];

    //                         //     inserirSelecionado(element);
    //                         //     $(`#${element.idTr}, #${element.idTrSelecionado}`).find('.btnsAcao').prepend(self.#htmlBtnRemover());
    //                         // } else {
    //                         //     element.idTrSelecionado = selecionado.idTrSelecionado;
    //                         //     $(`#${element.idTr}`).find('.btnsAcao').prepend(self.#htmlBtnRemover());
    //                         // }

    //                         // $(`#${element.idTr}, #${element.idTrSelecionado}`).find('.btn-select').remove();
    //                         // adicionaEventoDeletar(element);
    //                     }
    //                 }
    //             }
    //             self.#atualizaBadge();
    //         });
    //     }

    //     const adicionaEventoDeletar = (itemEnv) => {
    //         let itemDeletar = CommonFunctions.clonePure(itemEnv);

    //         $(`#${itemDeletar.idTrSelecionado}`).find('.btn-delete').off('click');
    //         const trs = $(`#${itemDeletar.idTr}, #${itemDeletar.idTrSelecionado}`);
    //         trs.find('.btn-delete').on("click", async function () {
    //             const selecionado = self._promisseReturnValue.selecteds.filter((selecionados) => selecionados.pessoa.idTrSelecionado == itemDeletar.idTrSelecionado);

    //             self._promisseReturnValue.selecteds = self._promisseReturnValue.selecteds.filter((selecionados) => selecionados.pessoa.idTrSelecionado != itemDeletar.idTrSelecionado);

    //             $(`#${itemDeletar.idTrSelecionado}`).remove();
    //             $(`#${selecionado[0].pessoa.idsTrs.join(', #')}`).find('.btn-delete').remove();
    //             $(`#${selecionado[0].pessoa.idsTrs.join(', #')}`).find('.btnsAcao').prepend(self.#htmlBtnSelecionar());
    //             for (const idTrConsulta of selecionado[0].pessoa.idsTrs) {
    //                 if ($(`#${idTrConsulta}`).length) {
    //                     itemDeletar.idTr = idTrConsulta;
    //                     adicionaEventoSelecionar({ ...itemDeletar });
    //                 }
    //             }
    //             self.#atualizaBadge();
    //         });
    //     }

    //     //#endregion

    //     if (registro.idTrSelecionado) {
    //         adicionaEventoDeletar(item);
    //     } else {
    //         adicionaEventoSelecionar(item);
    //     }
    //     // adicionaEventoVisualizar(item, item.idTr);
    // }

    #addEventosRegistrosConsulta(registro) {
        const self = this;
        let item = CommonFunctions.clonePure(registro);

        const selecionaUnicoPerfil = async (pessoa) => {
            const perfisPermitidos = self._dataEnvModal.perfis_busca.map(p => p.id);
            const perfisFiltrados = pessoa.pessoa_perfil.filter(p => perfisPermitidos.includes(p.perfil_tipo_id));

            const selecionarPrimeiroPerfil = (perfis) => CommonFunctions.clonePure(perfis[0]);

            let perfilSelecionado;

            if (pessoa.pessoa_perfil.length > 1 && perfisFiltrados.length > 1) {
                try {
                    const modal = new ModalSelecionarPerfil();
                    modal.setDataEnvModal = { perfis_opcoes: perfisFiltrados };
                    await self._modalHideShow(false);
                    const result = await modal.modalOpen();
                    perfilSelecionado = result.register;
                } catch (e) {
                    CommonFunctions.generateNotificationErrorCatch(e);
                } finally {
                    await self._modalHideShow();
                }
            } else {
                perfilSelecionado = selecionarPrimeiroPerfil(perfisFiltrados.length ? perfisFiltrados : pessoa.pessoa_perfil);
            }

            perfilSelecionado.pessoa = CommonFunctions.clonePure(pessoa);
            return perfilSelecionado;
        };

        const inserirSelecionado = (perfil) => {
            const select = self._dataEnvModal?.attributes?.select ?? {};
            const promisseReturnValue = self._promisseReturnValue;

            if (select?.quantity === 1) {
                promisseReturnValue.selected = perfil;
            } else {
                promisseReturnValue.selecteds.push(perfil);
            }

            promisseReturnValue.refresh = true;

            const atingeLimite = select?.autoReturn &&
                (
                    (select.quantity && promisseReturnValue.selecteds.length === select.quantity) ||
                    (select.quantity === 1 && promisseReturnValue.selected)
                );

            if (atingeLimite) self._setEndTimer = true;
        };

        const adicionaEventoSelecionar = (registroOriginal) => {
            const item = CommonFunctions.clonePure(registroOriginal);
            const tr = $(`#${item.idTr}`);

            tr.find('.btn-select').on('click', async function () {
                for (const query of Object.values(self._objConfigs.querys)) {
                    if (!query.recordsOnScreen) continue;

                    for (let element of query.recordsOnScreen) {
                        if (element.id !== item.id) continue;

                        const perfil = await selecionaUnicoPerfil(CommonFunctions.clonePure(element));
                        const pessoa = perfil.pessoa;

                        if (!self.#verificaRegistroSelecionado(perfil)) {
                            pessoa.idTrSelecionado = UUIDHelper.generateUUID();
                            const originalId = pessoa.idTr;
                            pessoa.idTr = pessoa.idTrSelecionado;

                            await self.insertTableDataSelecionados(pessoa);

                            pessoa.idTr = originalId;
                            pessoa.idsTrs = [originalId];

                            inserirSelecionado(perfil);

                            $(`#${originalId}, #${pessoa.idTrSelecionado}`).find('.btnsAcao').prepend(self.#htmlBtnRemover());
                        } else {
                            pessoa.idTrSelecionado = self.#verificaRegistroSelecionado(perfil).idTrSelecionado;
                            $(`#${pessoa.idTr}`).find('.btnsAcao').prepend(self.#htmlBtnRemover());
                        }

                        $(`#${pessoa.idTr}, #${pessoa.idTrSelecionado}`).find('.btn-select').remove();
                        adicionaEventoDeletar(pessoa);
                    }
                }

                self.#atualizaBadge();
            });
        };

        const adicionaEventoDeletar = (item) => {
            const itemClone = CommonFunctions.clonePure(item);
            const trs = $(`#${itemClone.idTr}, #${itemClone.idTrSelecionado}`);

            trs.find('.btn-delete').off('click').on('click', () => {
                const selecionados = self._promisseReturnValue.selecteds.filter(p => p.pessoa.idTrSelecionado === itemClone.idTrSelecionado);
                self._promisseReturnValue.selecteds = self._promisseReturnValue.selecteds.filter(p => p.pessoa.idTrSelecionado !== itemClone.idTrSelecionado);

                $(`#${itemClone.idTrSelecionado}`).remove();

                const idsTrs = selecionados[0]?.pessoa?.idsTrs ?? [];
                $(`#${idsTrs.join(', #')}`).find('.btn-delete').remove().end().find('.btnsAcao').prepend(self.#htmlBtnSelecionar());

                for (const idTr of idsTrs) {
                    if ($(`#${idTr}`).length) {
                        adicionaEventoSelecionar({ ...itemClone, idTr });
                    }
                }

                self.#atualizaBadge();
            });
        };

        if (registro.idTrSelecionado) {
            adicionaEventoDeletar(item);
        } else {
            adicionaEventoSelecionar(item);
        }
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
