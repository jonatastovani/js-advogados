import { commonFunctions } from "../../commons/commonFunctions";
import { modalSearchAndFormRegistration } from "../../commons/modal/modalSearchAndFormRegistration";
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
        await self._modalHideShow();
        return await self._modalOpen();
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
        const perfis_busca = self._dataEnvModal.perfis_busca.map(item => item.id);
        formPessoaFisica.find('.btnBuscar').on('click', function (e) {
            e.preventDefault();
            self._setTypeCurrentSearch = self._objConfigs.querys.consultaFiltrosFisica.name;
            self._generateQueryFilters({ formDataSearch: self._objConfigs.querys.consultaFiltrosFisica.formDataSearch, appendData: { perfis_busca: perfis_busca } });
        })
            .trigger('click');

        formPessoaJuridica.find('.btnBuscar').on('click', function (e) {
            e.preventDefault();
            self._setTypeCurrentSearch = self._objConfigs.querys.consultaFiltrosJuridica.name;
            self._generateQueryFilters({ formDataSearch: self._objConfigs.querys.consultaFiltrosJuridica.formDataSearch });
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

        const pessoa_dados = item.pessoa_dados;
        const cpf = pessoa_dados.cpf ? commonFunctions.formatCPF(pessoa_dados.cpf) : '';

        const itemSelecionado = self.#verificaRegistroSelecionado(item);
        let botoes = '';
        if (itemSelecionado) {
            botoes = self.#htmlBtnRemover();
            item.idTrSelecionado = itemSelecionado.idTrSelecionado;
        } else {
            botoes = self.#htmlBtnSelecionar();
        }

        let perfis = 'N/C';
        if (item.pessoa_perfil) {
            perfis = item.pessoa_perfil.map(perfil => perfil.perfil_tipo.nome).join(', ');
        }

        $(tbody).append(`
            <tr id=${item.idTr}>
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

        self.#addEventosRegistrosConsulta(item);
        return item;
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

    #verificaRegistroSelecionado(item) {
        const self = this;

        for (const element of self._promisseReturnValue.selecteds) {
            if (element.pessoa.id == item.id) {
                element.pessoa.idsTrs.push(item.idTr);
                return element.pessoa; // Pessoa já está selecionada
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

        // const adicionaEventoVisualizar = (itemEnv, idTr) => {
        //     $(`#${idTr}`).find('.btn-view').on("click", async function () {
        //         commonFunctions.generateNotification('Funcionalidade para visualizar detalhes do preso, em desenvolvimento.', 'warning');
        //         // item['idTrSelecionado'] = await self.#inserirRegistroTabela(tabelaSelecionados, item);
        //         // self.#promisseReturnValue.selecteds.push(item);

        //         // $(this).remove();
        //         // $(`#${itemEnv.idTr}, #${item.idTrSelecionado}`).find('.btnsAcao').prepend(self.#htmlBtnRemover());
        //         // adicionaEventoDeletar(item);
        //     });
        // }
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