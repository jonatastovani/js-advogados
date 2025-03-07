import { commonFunctions } from "../../../commons/commonFunctions";
import { TemplateSearch } from "../../../commons/templates/TemplateSearch";
import { modalCode } from "../../../components/admin/modalCode";
import { modalPermissao } from "../../../components/admin/modalPermissao";

class PagePermissoes extends TemplateSearch {

    #objConfigs = {
        querys: {
            consultaFiltros: {
                name: 'consulta-filtros',
                url: window.apiRoutes.basePermissoes,
                urlSearch: `${window.apiRoutes.basePermissoes}/consulta-filtros`,
            }
        },
    };

    constructor() {
        super({ sufixo: 'PagePermissoes' });
        this._objConfigs = Object.assign(this._objConfigs, this.#objConfigs);
        this.initEvents();
    }

    initEvents() {
        const self = this;
        self.#addEventosBotoes();
        self._executarBusca();
    }

    #addEventosBotoes() {
        const self = this;

        $(`#formDataSearch${self.getSufixo}`).find('.btnBuscar').on('click', async function (e) {
            e.preventDefault();
            await self._executarBusca();
        });

        $('#btnInserirPermissao').on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const objModal = new modalPermissao();
                const response = await objModal.modalOpen();
                if (response.refresh) {
                    self._generateQueryFilters();
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading(btn, false);
            }
        });

        const openModalTest = () => {
            const objCode = new modalCode();
            objCode.setDataEnvModal = {
                idRegister: 5,
                url: `${window.apiRoutes.basePermissoes}/php`,
            };
            objCode.modalOpen();
        }
        // openModalTest();
    }

    async insertTableData(item, options = {}) {
        const self = this;
        const {
            tbody,
        } = options;

        const ativo = item.ativo ? 'Sim' : 'Não';

        $(tbody).append(`
            <tr id=${item.idTr}>
                <td class="text-center">
                    <div class="btn-group btnsAcao" role="group">
                        <button type="button" class="btn btn-outline-primary btn-sm btn-edit" title="Editar registro"><i class="bi bi-pencil"></i></button>
                        <button type="button" class="btn btn-outline-secondary btn-sm btn-php" title="Renderizar dados para backend PHP"><i class="bi bi-filetype-php"></i></button>
                        <button type="button" class="btn btn-outline-info btn-sm btn-view" title="Visualizar detalhes"><i class="fa-solid fa-circle-info"></i></button>
                    </div>
                </td>
                <td class="text-center text-nowrap">${item.id}</td>
                <td class="text-nowrap text-truncate" style="max-width: 20rem" title="${item.nome ?? ''}">${item.nome}</td>
                <td class="text-nowrap text-truncate" style="max-width: 20rem" title="${item.nome_completo ?? ''}">${item.nome_completo ?? ''}</td>
                <td class="text-nowrap text-truncate" style="max-width: 20rem" title="${item.descricao ?? ''}">${item.descricao ?? ''}</td>
                <td class="text-nowrap text-truncate" style="max-width: 20rem" title="${item.grupo?.nome ?? ''}">${item.grupo?.nome ?? ''}</td>
                <td class="text-nowrap text-truncate" style="max-width: 20rem" title="${item.grupo?.modulo?.nome ?? ''}">${item.grupo?.modulo?.nome ?? ''}</td>
                <td class="text-nowrap">${ativo}</td>
            </tr>
        `);

        self.#addEventosRegistrosConsulta(item);
        return true;
    }

    #addEventosRegistrosConsulta(item) {
        const self = this;
        $(`#${item.idTr}`).find(`.btn-edit`).on('click', async function () {
            commonFunctions.simulateLoading($(this));
            try {
                const objModal = new modalPermissao();
                objModal.setDataEnvModal = {
                    idRegister: item.id
                };
                const response = await objModal.modalOpen();
                if (response.refresh) {
                    self._setTypeCurrentSearch = self._objConfigs.querys.consultaFiltros.name;
                    self._generateQueryFilters();
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading($(this), false);
            }
        });

        $(`#${item.idTr}`).find(`.btn-php`).on('click', async function () {
            commonFunctions.simulateLoading($(this));
            try {
                const objModal = new modalCode();
                objModal.setDataEnvModal = {
                    idRegister: item.id,
                    url: `${window.apiRoutes.basePermissoes}/php`,
                };
                const response = await objModal.modalOpen();
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading($(this), false);
            }
        });

        // tr.find(`.btn-delete`).click(async function () {
        //     self.#delButtonAction(item.id, item.name, this);
        // });

        // modal.find('.btnBuscarPessoas').on('click', async function () {
        //     const btn = $(this);
        //     commonFunctions.simulateLoading(btn);
        //     try {
        //         const obj = new modalConsultaPessoas();
        //         await self._modalHideShow(false);
        //         const response = await obj.modalOpen();
        //         console.log(response);
        //     } catch (error) {
        //         commonFunctions.generateNotificationErrorCatch(error);
        //     } finally {
        //         await self._modalHideShow();
        //         commonFunctions.simulateLoading(btn, false);
        //     }
        // });

    }

}

$(function () {
    new PagePermissoes();
});