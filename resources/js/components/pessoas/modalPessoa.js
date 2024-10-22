import { commonFunctions } from "../../commons/commonFunctions";
import { modalSearchAndFormRegistration } from "../../commons/modal/modalSearchAndFormRegistration";
import { functionsQueryCriteria } from "../../helpers/functionsQueryCriteria";
import { UUIDHelper } from "../../helpers/UUIDHelper";

export class modalPessoa extends modalSearchAndFormRegistration {

    /**
     * Configuração local do modal
     */
    #objConfigs = {
        formRegister: false,
        querys: {
            consultaFiltrosFisica: {
                name: 'consulta-filtros-fisica',
                url: window.apiRoutes.basePessoas,
                urlSearch: `${window.apiRoutes.basePessoas}/consulta-filtros/pessoa-fisica`,
                tbody: $('#tableDataModalPessoaFisica').find('tbody'),
                footerPagination: $('#footerPaginationModalPessoaFisica'),
                formDataSearch: $('#formDataSearchModalPessoaFisica'),
            },
            consultaFiltrosJuridica: {
                name: 'consulta-filtros-juridica',
                url: window.apiRoutes.basePessoas,
                urlSearch: `${window.apiRoutes.basePessoas}/consulta-filtros/pessoa-juridica`,
                tbody: $('#tableDataModalPessoaJuridica').find('tbody'),
                footerPagination: $('#footerPaginationModalPessoaJuridica'),
                formDataSearch: $('#formDataSearchModalPessoaJuridica'),
            },
            consultaFisicaCriterios: {
                name: 'consulta-criterios',
                url: window.apiRoutes.basePessoas,
                urlSearch: `${window.apiRoutes.basePessoas}/consulta-criterios/pessoa-fisica`,
                tbody: $('#tableDataModalPessoaFisicaCriterios').find('tbody'),
                footerPagination: $('#footerPaginationModalPessoaFisicaCriterios'),
                formDataSearch: $('#formDataSearchModalPessoaFisicaCriterios'),
            },
            consultaJuridicaCriterios: {
                name: 'consulta-criterios',
                url: window.apiRoutes.basePessoas,
                urlSearch: `${window.apiRoutes.basePessoas}/consulta-criterios/pessoa-juridica`,
                tbody: $('#tableDataModalPessoaJuridicaCriterios').find('tbody'),
                footerPagination: $('#footerPaginationModalPessoaJuridicaCriterios'),
                formDataSearch: $('#formDataSearchModalPessoaJuridicaCriterios'),
            },
        },
        sufixo: 'ModalPessoa',
    };

    /** 
     * Conteúdo a ser retornado na promisse como resolve()
    */
    #promisseReturnValue = {
    };

    constructor() {
        super({
            idModal: "#modalPessoa",
        });

        this._objConfigs = Object.assign(this._objConfigs, this.#objConfigs);
        this._promisseReturnValue = Object.assign(this._promisseReturnValue, this.#promisseReturnValue);
        this.functionsCriteria = new functionsQueryCriteria(this, this._idModal);
        this.#addEventosPadrao();
    }

    async modalOpen() {
        const self = this;
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

        formPessoaFisica.find('.btnBuscar').on('click', function (e) {
            e.preventDefault();
            self._setTypeCurrentSearch = self._objConfigs.querys.consultaFiltrosFisica.name;
            self._generateQueryFilters({ formDataSearch: self._objConfigs.querys.consultaFiltrosFisica.formDataSearch });
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
    }

    _modalClose() {
        const self = this;
        const modal = $(self.getIdModal);
        modal.find('#consultaPessoas-tab').trigger('click');
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

    async insertTableData(item, options = {}) {
        const self = this;
        const {
            tbody,
        } = options;

        const cpf = item.cpf ? commonFunctions.formatCPF(item.cpf) : '';

        const itemSelecionado = self.#verificaRegistroSelecionado(item);
        let botoes = '';
        if (itemSelecionado) {
            botoes = self.#htmlBtnRemover();
            item['idTrSelecionado'] = itemSelecionado.idTrSelecionado;
        } else {
            botoes = self.#htmlBtnSelecionar();
        }

        $(tbody).append(`
            <tr id=${item.idTr}>
                <td class="text-center text-nowrap">
                <div class="btn-group btnsAcao" role="group">
                        ${botoes}
                    </div>
                </td>
                <td class="text-nowrap text-truncate" style="max-width: 20rem" title="${item.nome}">${item.nome}</td>
                <td class="text-nowrap text-truncate" style="max-width: 20rem" title="${item.nome_social ?? ''}">${item.nome_social ?? ''}</td>
                <td class="text-center text-nowrap">${cpf}</td>
                <td class="text-center text-nowrap">${item.rg ?? ''}</td>
                <td class="text-nowrap text-truncate" style="max-width: 20rem" title="${item.pai ?? ''}">${item.pai ?? ''}</td>
                <td class="text-nowrap text-truncate" style="max-width: 20rem" title="${item.mae ?? ''}">${item.mae ?? ''}</td>
                <td class="text-center text-nowrap ">${item.nascimento_data ?? ''}</td>
                <td class="text-nowrap">${item.perfis ?? ''}</td>
            </tr>
        `);

        self.#addEventosRegistrosConsulta(item);
        return item;
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
        const tabelaSelecionados = $(`#tableData${self._objConfigs.sufixo}Selecionados tbody`);

        let item = JSON.parse(JSON.stringify(registro));

        const inserirSelecionado = (item) => {
            const select = self._dataEnvModal.attributes.select;
            const promisseReturnValue = self._promisseReturnValue;

            if (select?.quantity && select.quantity == 1) {
                promisseReturnValue.selected = item;
            } else {
                promisseReturnValue.selecteds.push(item);
            }
            promisseReturnValue.refresh = true;

            if (select?.autoReturn && select.autoReturn &&
                (
                    // select?.max && promisseReturnValue.selecteds.length == select.max ||
                    select?.quantity && promisseReturnValue.selecteds.length == select.quantity ||
                    (
                        select?.quantity && select.quantity == 1 && promisseReturnValue.selected
                    )
                    // select?.min && promisseReturnValue.selecteds.length >= select.min
                )
            ) {
                self._setEndTimer = true;
            }
        }

        //#region Eventos de botões
        const adicionaEventoSelecionar = (itemEnv) => {
            let item = JSON.parse(JSON.stringify(itemEnv));
            const tr = $(`#${item.idTr}`);
            tr.find('.btn-select').on("click", async function () {
                for (const query of Object.values(self._objConfigs.querys)) {
                    if (!query.recordsOnScreen) continue;
                    for (let element of query.recordsOnScreen) {
                        element = JSON.parse(JSON.stringify(element));
                        if (element.id == item.id
                        ) {
                            const selecionado = self.#verificaRegistroSelecionado(element);
                            if (!selecionado) {
                                element.idTrSelecionado = UUIDHelper.generateUUID();
                                const tempIdTr = element.idTr;
                                element.idTr = element.idTrSelecionado;
                                const returnInsert = await self.insertTableData(element, { tbody: tabelaSelecionados });
                                element.idTr = tempIdTr;
                                element.idsTrs = [element.idTr];
                                inserirSelecionado(element);
                                $(`#${element.idTr}, #${element.idTrSelecionado}`).find('.btnsAcao').prepend(self.#htmlBtnRemover());
                                adicionaEventoVisualizar(element, element.idTrSelecionado);
                            } else {
                                element.idTrSelecionado = selecionado.idTrSelecionado;
                                $(`#${element.idTr}`).find('.btnsAcao').prepend(self.#htmlBtnRemover());
                            }

                            $(`#${element.idTr}, #${element.idTrSelecionado}`).find('.btn-select').remove();
                            adicionaEventoDeletar(element);
                        }
                    }
                }
                self.#atualizaBadge();
            });
        }

        const adicionaEventoDeletar = (itemEnv) => {
            let item = JSON.parse(JSON.stringify(itemEnv));
            $(`#${item.idTrSelecionado}`).find('.btn-delete').off('click');
            const trs = $(`#${item.idTr}, #${item.idTrSelecionado}`);
            trs.find('.btn-delete').on("click", async function () {
                const selecionado = self._promisseReturnValue.selecteds.filter((selecionados) => selecionados.idTrSelecionado == item.idTrSelecionado);
                self._promisseReturnValue.selecteds = self._promisseReturnValue.selecteds.filter((selecionados) => selecionados.idTrSelecionado != item.idTrSelecionado);

                $(`#${item.idTrSelecionado}`).remove();
                $(`#${selecionado[0].idsTrs.join(', #')}`).find('.btn-delete').remove();
                $(`#${selecionado[0].idsTrs.join(', #')}`).find('.btnsAcao').prepend(self.#htmlBtnSelecionar());
                for (const idTrConsulta of selecionado[0].idsTrs) {
                    if ($(`#${idTrConsulta}`).length) {
                        item.idTr = idTrConsulta;
                        adicionaEventoSelecionar({ ...item });
                    }
                }
                self.#atualizaBadge();
            });
        }

        const adicionaEventoVisualizar = (itemEnv, idTr) => {
            $(`#${idTr}`).find('.btn-view').on("click", async function () {
                commonFunctions.generateNotification('Funcionalidade para visualizar detalhes do preso, em desenvolvimento.', 'warning');
                // item['idTrSelecionado'] = await self.#inserirRegistroTabela(tabelaSelecionados, item);
                // self.#promisseReturnValue.selecteds.push(item);

                // $(this).remove();
                // $(`#${itemEnv.idTr}, #${item.idTrSelecionado}`).find('.btnsAcao').prepend(self.#htmlBtnRemover());
                // adicionaEventoDeletar(item);
            });
        }
        //#endregion

        if (registro.idTrSelecionado) {
            adicionaEventoDeletar(item);
        } else {
            adicionaEventoSelecionar(item);
        }
        adicionaEventoVisualizar(item, item.idTr);

        // tr.on('dblclick', function () {
        //     self.#promisseReturnValue.selected_id = item.id;
        //     self.#promisseReturnValue.refresh = true;
        //     self.#endTimer = true;
        // });
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