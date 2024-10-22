import { modalMessage } from "../../components/comum/modalMessage";
import { URLHelper } from "../../helpers/URLHelper";
import { UUIDHelper } from "../../helpers/UUIDHelper";
import { commonFunctions } from "../commonFunctions";
import { connectAjax } from "../connectAjax";
import { enumAction } from "../enumAction";
import { modalDefault } from "./modalDefault";

export class modalSearchAndFormRegistration extends modalDefault {

    /**
     * Variável para reservar a ação a ser executada
     */
    _action;

    /**
     * ID do cadastro que está sendo alterado
     */
    _idRegister;

    constructor(objSuper) {
        let objConfigsAppend = {
            formRegister: true,
            runningSearchBln: false,
            typeCurrentSearch: undefined,
        };

        let promisseReturnValueAppend = {
            selecteds: []
        };

        objSuper.objConfigs = Object.assign(
            objConfigsAppend,
            objSuper.objConfigs ?? {}
        );

        objSuper.promisseReturnValue = Object.assign(
            promisseReturnValueAppend,
            objSuper.promisseReturnValue ?? {}
        );

        super(objSuper);

        this.#addEventsDefault();
    }

    #addEventsDefault() {
        const self = this;
        const modal = $(self.getIdModal);
        this.#addEventBtnNewRegister();
        this.#addDefaultSearchModalEvents();
    }

    //#region Getters e Setters

    set _setTypeCurrentSearch(type) {
        this._objConfigs.typeCurrentSearch = type;
    }

    //#endregion

    //#region Botões padrão

    _btnNewRegister = ".btn-new-register";
    #addEventBtnNewRegister() {
        const self = this;
        $(self.getIdModal).find(this._btnNewRegister).on("click", () => {
            self._action = enumAction.POST;
            self._actionsHideShowRegistrationFields(true);
            self._executeFocusElementOnModal($(self.getIdModal).find('.focusRegister'));
        });
    }

    //#endregion

    #addDefaultSearchModalEvents() {
        const self = this;
        if (self._objConfigs.modalSearch) {
            const inputsSearchs = self._objConfigs.modalSearch.inputsSearchs ?? null;
            if (inputsSearchs) {
                const event = self._objConfigs.modalSearch.event ?? 'input';
                inputsSearchs.on(event, function () {
                    clearTimeout(self._timerSearch);
                    self._timerSearch = setTimeout(function () {
                        self.generateFilters();
                    }, 1000);
                });
            }
        }
    }

    _actionsHideShowRegistrationFields(status = false) {
        const self = this;
        const modal = $(self.getIdModal);
        if (status) {
            modal.find('.divBtnAdd').slideUp();
            modal.find(".divRegistrationFields").slideDown();
        } else {
            modal.find('.divBtnAdd').slideDown();
            modal.find(".divRegistrationFields").slideUp();
        }

    }

    modalCancel() {
        const self = this;
        self._clearForm();
        self._actionsHideShowRegistrationFields();
        self._executeFocusElementOnModal($(self.getIdModal).find('.formDataSearch').find('input[name="texto"]'));
    }

    _modalReset() {
        const self = this;
        self._clearForm();
        $(self.getIdModal).find('.formDataSearch')[0].reset();
        self._actionsHideShowRegistrationFields();
    }

    _clearForm() {
        const self = this;
        const modal = $(self.getIdModal);
        const formRegistration = modal.find('.formRegistration');
        if (formRegistration.length > 0) {
            self._idRegister = undefined;
            self._action = enumAction.POST;
            formRegistration.find('select').val(0);
            formRegistration[0].reset();
            formRegistration.find('input, select, textarea').removeClass('is-valid').removeClass('is-invalid');
        }
    }

    //#region Campos de busca padrão

    async _generateQueryFilters(options = {}) {
        const self = this;
        const { formDataSearch = $(options.formDataSearch ?? $(self.getIdModal).find('.formDataSearch')) } = options;
        const texto = formDataSearch.find('input[name="texto"]').val();
        let arrayMensagens = [];

        let data = {
            texto: texto,
            parametros_like: self._returnQueryParameters(formDataSearch.find('select[name="selFormaBusca"]').val()),
            ordenacao: [
                {
                    campo: formDataSearch.find(`select[name="selCampoOrdenacao"]`).val() ?? 'nome',
                    direcao: formDataSearch.find(`input[name="direcaoConsulta"]:checked`).val()
                },
            ],
            texto_tratamento: {
                tratamento: formDataSearch.find('select[name="selTratamentoTexto"]').val(),
            },
            filtros: {
                campos_busca: [],
            },
            page: 1
        };

        const searchFields = commonFunctions.getInputsValues(formDataSearch.find('.searchFields'));
        Object.keys(searchFields).forEach(element => {
            if (searchFields[element] === true) {
                data.filtros.campos_busca.push(element);
            }
        });

        if (arrayMensagens.length > 0) {
            return commonFunctions.generateNotification("Não foi possivel realizar a busca. Verifique as seguintes recomendações:", 'info', { itemsArray: arrayMensagens });
        }
        await self._getData(data);
    }

    _returnQueryParameters(forma) {
        switch (forma) {
            case 'iniciado_por':
                return {
                    curinga_inicio_bln: false,
                    curinga_final_bln: true,
                }

            case 'terminado_por':
                return {
                    curinga_inicio_bln: true,
                    curinga_final_bln: false,
                }

            case 'qualquer_incidencia':
                return {
                    curinga_inicio_bln: true,
                    curinga_final_bln: true,
                }
            default:
                return {
                    curinga_inicio_bln: false,
                    curinga_final_bln: false,
                }
        }
    }

    #getConfigType() {
        const self = this;
        for (const query of Object.values(self._objConfigs.querys)) {
            if (query.name == self._objConfigs.typeCurrentSearch) {
                return query;
            }
        }

        commonFunctions.generateNotification('O tipo de busca informado não foi encontrado.', 'error');
        return false;
    }

    async _getData(data, page = 1) {
        const self = this;
        if (self._objConfigs.runningSearchBln) {
            commonFunctions.generateNotification('Busca em andamento. Aguarde...', 'info');
            return;
        }

        let config = self.#getConfigType();
        if (!config) { return; }

        const modal = $(self.getIdModal);
        const buttonSearch = modal.find(config.btnSearch ?? '.btnBuscar');
        const tbody = modal.find(config.tbody ?? `.tableData tbody`);
        const footerPagination = modal.find(config.footerPagination ?? `.footerPagination`);

        self._objConfigs.runningSearchBln = true;
        try {
            commonFunctions.simulateLoading(buttonSearch);
            self._paginationDefault({ footerPagination: footerPagination });
            tbody.html('');
            self._refreshQueryQuantity('Consultando...', { footerPagination: footerPagination });
            self._refreshQueryStatus('Efetuando busca. Aguarde...', { footerPagination: footerPagination });

            const objConn = await new connectAjax(config.urlSearch);
            data.page = page;
            objConn.setAction(enumAction.POST);
            objConn.setData(data);
            const response = await objConn.envRequest();

            self._refreshQueryStatus('Busca concluída. Preenchendo os dados...', { footerPagination: footerPagination });
            if (response.data) {
                const responseData = response.data;
                let recordsOnScreen = [];

                for (let item of responseData.data) {
                    const idTr = UUIDHelper.generateUUID();
                    item = Object.assign(item, { idTr: idTr });

                    // Verifica se a propriedade `insertTableData` está definida no `config`
                    const functionName = config.insertTableData ? config.insertTableData : 'insertTableData';

                    // Chama dinamicamente a função especificada em `config.insertTableData` ou a padrão `insertTableData`
                    const responseInsert = await self[functionName](item, { config: config, tbody: tbody });
                    recordsOnScreen.push(typeof responseInsert === 'object' ? responseInsert : item);
                }

                self._refreshQueryQuantity(responseData.total, { footerPagination: footerPagination });
                self.#fillPagination(responseData, { footerPagination: footerPagination, dataPost: data });
                config.dataPost = data;
                config.recordsOnScreen = recordsOnScreen;
            } else {
                self._refreshQueryQuantity(0, { footerPagination: footerPagination });
                self._paginationDefault({ footerPagination: footerPagination });
                config.dataPost = data;
                config.recordsOnScreen = [];
            }
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
            footerPagination.find('.totalRegistros').html(0);
        } finally {
            commonFunctions.simulateLoading(buttonSearch, false);
            self._refreshQueryStatus('Aguardando comando do usuário...', { footerPagination: footerPagination });
            self._objConfigs.runningSearchBln = false;
        }
    }

    async #fillPagination(data, options) {
        const self = this;
        const {
            footerPagination = $(options.footerPagination ?? $(self.getIdModal).find('.footerPagination')),
            selector = $(options.pagination ?? $(footerPagination).find('.pagination')),
        } = options;

        selector.html('');

        for (const link of data.links) {
            let listItem = '';
            const idBtn = UUIDHelper.generateUUID();

            switch (link.label) {
                case "&laquo; Previous":
                    listItem = `
                    <li class="page-item ${link.url ? '' : 'disabled'}">
                        <button id="${idBtn}" type="button" class="page-link" aria-label="Anterior">
                            <span aria-hidden="true">&laquo; Anterior</span>
                        </button>
                    </li>`;
                    break;

                case "Next &raquo;":
                    listItem = `
                    <li class="page-item ${link.url ? '' : 'disabled'}">
                        <button id="${idBtn}" type="button" class="page-link" aria-label="Próximo">
                            <span aria-hidden="true">Pr&oacute;xima &raquo;</span>
                        </button>
                    </li>`;
                    break;

                case "...":
                    listItem = `
                    <li class="page-item disabled">
                        <button id="${idBtn}" type="button" class="page-link">${link.label}</button>
                    </li>`;
                    break;

                default:
                    if (data.total) {
                        listItem = `
                    <li class="page-item ${link.active ? 'active' : ''}">
                        <button id="${idBtn}" type="button" class="page-link">${link.label}</button>
                    </li>`;
                    }
                    break;
            }

            selector.append(listItem);
            self.#addEventsQueryPagination(idBtn, link, options);
        }
    }

    #addEventsQueryPagination(idBtn, link, options) {
        const self = this;
        const { dataPost = options.dataPost } = options;

        if (link.url) {
            const page = URLHelper.getParameterURL('page', link.url);
            if (page) {
                const typeCurrentSearch = self._objConfigs.typeCurrentSearch;
                $(`#${idBtn}`).on('click', function () {
                    self._objConfigs.typeCurrentSearch = typeCurrentSearch;
                    self._getData(dataPost, page);
                });
            }
        }
    }

    _paginationDefault(options = {}) {
        const self = this;
        const {
            footerPagination = $(options.footerPagination ?? $(self.getIdModal).find('.footerPagination')),
            pagination = $(options.pagination ?? $(footerPagination).find('.pagination')),
        } = options;

        pagination.html(`
            <li class="page-item disabled">
                <button type="button" class="page-link" aria-label="Anterior">
                    <span aria-hidden="true">&laquo; Anterior</span>
                </button>
            </li>
            <li class="page-item disabled">
                <button type="button" class="page-link" aria-label="Próximo">
                    <span aria-hidden="true">Pr&oacute;xima &raquo;</span>
                </button>
            </li>
        `);
    }

    _refreshQueryStatus(html, options = {}) {
        const self = this;
        const {
            footerPagination = $(options.footerPagination ?? $(self.getIdModal).find('.footerPagination')),
            selector = options.selector ?? footerPagination.find('.queryStatus'),
        } = options;

        if (selector) {
            selector.html(html);
        }
    }

    _refreshQueryQuantity(html, options = {}) {
        const self = this;
        const {
            footerPagination = $(options.footerPagination ?? $(self.getIdModal).find('.footerPagination')),
            selector = options.selector ?? footerPagination.find('.totalRegisters'),
        } = options;

        $(selector).html(html);
    }

    async _getRecurse(options = {}) {
        const self = this;
        const { idRegister = self._idRegister } = options;

        let config = self.#getConfigType();
        if (!config) { return; }

        try {
            const obj = new connectAjax(config.url);
            obj.setParam(idRegister);
            return await obj.getRequest();
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
            return false;
        }
    }

    //#endregion

    //#region Campos de salvamento padrão

    async _save(data, urlApi, options = {}) {
        const self = this;
        const modal = $(self.getIdModal);
        const {
            btnSave = modal.find('.btn-save'),
        } = options;

        try {
            commonFunctions.simulateLoading(btnSave);
            const obj = new connectAjax(urlApi);
            obj.setAction(self._action)
            obj.setData(data);
            if (self._action === enumAction.PUT) {
                obj.setParam(self._idRegister);
            }
            const response = await obj.envRequest();
            if (response) {
                commonFunctions.generateNotification(`Dados enviados com sucesso!`, 'success');
                self._promisseReturnValue.refresh = true;
                self._generateQueryFilters();
                if (self._action === enumAction.PUT) {
                    self.modalCancel();
                } else {
                    self._clearForm();
                    self._executeFocusElementOnModal($(self.getIdModal).find('.formDataSearch').find('.focusRegister'));
                }
            }
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
        }
        finally {
            commonFunctions.simulateLoading(btnSave, false);
        };
    }

    //#endregion

    //#region Metodos de exclusão padrão

    async _delButtonAction(idDel, nameDel, options = {}) {
        const self = this;
        const { button = null,
            title = 'Exclusão de Registro',
            message = `Confirma a exclusão do registro <b>${nameDel}</b>?`,
            success = `Registro excluído com sucesso!`,
        } = options;

        try {
            const obj = new modalMessage();
            obj.setDataEnvModal = {
                title: title,
                message: message,
            };
            obj.setFocusElementWhenClosingModal = button;
            await self._modalHideShow(false);
            const result = await obj.modalOpen();
            if (result) {
                if (await self._delRecurse(idDel, options)) {
                    commonFunctions.generateNotification(success, 'success');
                    self.modalCancel();
                    self._generateQueryFilters();
                };
            }
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
        } finally {
            await self._modalHideShow(true);
        }
    }

    async _delRecurse(idDel, options = {}) {
        const self = this;

        let config = self.#getConfigType();
        if (!config) { return; }

        try {
            const obj = new connectAjax(config.url);
            obj.setParam(idDel);
            const response = await obj.deleteRequest();
            return true;
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
            return false;
        }
    }

    //#endregion

    //#region Metodos auxiliares
    _updateTitleRegistration(html, options = {}) {
        const self = this;
        const {
            formRegistration = $(self.getIdModal).find('.formRegistration'),
            title = $(options.title ?? $(self.getIdModal).find('.registration-title')),
        } = options;
        title.html(html);
    }
    //#endregion
}