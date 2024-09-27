import { commonFunctions } from "../../commons/commonFunctions";
import { templateSearch } from "../../commons/templates/templateSearch";
import { modalCode } from "../../components/admin/modalCode";
import { modalPermissao } from "../../components/admin/modalPermissao";

class PagePermissoes extends templateSearch {

    #objConfigs = {
        querys: {
            consultaFiltros: {
                name: 'consulta-filtros',
                url: window.apiRoutes.baseInfoSubj,
                urlSearch: `${window.apiRoutes.baseInfoSubj}/consulta-filtros`,
                baseFront: window.frontRoutes.baseFront,
            }
        },
    };

    constructor() {
        super({ sufixo: 'PageInformacaoSubjetivaIndex' });
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
            self._setTypeCurrentSearch = self._objConfigs.querys.consultaFiltros.name;
            self._generateQueryFilters()
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

        const btnEditHTML = `<a href="${self._objConfigs.querys.consultaFiltros.baseFront}/form/${item.id}" class="btn btn-outline-primary btn-sm btn-edit" title="Editar registro"><i class="bi bi-pencil"></i></a>`;

        $(tbody).append(`
            <tr id=${item.idTr} data-id="${item.id}">
                <td class="text-center">
                    <div class="btn-group btnsAcao" role="group">
                        ${btnEditHTML}
                    </div>
                </td>
                <td class="text-nowrap text-truncate" style="max-width: 10rem" title="${item.titulo ?? ''}">${item.titulo}</td>
                <td class="text-nowrap text-truncate" style="max-width: 10rem" title="${item.descricao ?? ''}">${item.descricao ?? ''}</td>
            </tr>
        `);

        self.#addEventosRegistrosConsulta(item);
        return true;
    }

    #addEventosRegistrosConsulta(item) {
        const self = this;
        // $(`#${item.idTr}`).find(`.btn-edit`).on('click', async function () {
        //     commonFunctions.simulateLoading($(this));
        //     try {
        //         const objModal = new modalPermissao();
        //         objModal.setDataEnvModal = {
        //             idRegister: item.id
        //         };
        //         const response = await objModal.modalOpen();
        //         if (response.refresh) {
        //             self._setTypeCurrentSearch = self._objConfigs.querys.consultaFiltros.name;
        //             self._generateQueryFilters();
        //         }
        //     } catch (error) {
        //         commonFunctions.generateNotificationErrorCatch(error);
        //     } finally {
        //         commonFunctions.simulateLoading($(this), false);
        //     }
        // });

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