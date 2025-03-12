import { modalMessage } from "../../components/comum/modalMessage";
import TenantTypeDomainCustomHelper from "../../helpers/TenantTypeDomainCustomHelper";
import { URLHelper } from "../../helpers/URLHelper";
import { UUIDHelper } from "../../helpers/UUIDHelper";
import { QueueManager } from "../../utils/QueueManager";
import { commonFunctions } from "../commonFunctions";
import { connectAjax } from "../connectAjax";
import { enumAction } from "../enumAction";
import { modalDefault } from "./modalDefault";

export class ModalSearchAndFormRegistration extends modalDefault {

    /**
     * Variável para reservar a ação a ser executada
     */
    _action;

    /**
     * ID do cadastro que está sendo alterado
     */
    _idRegister;

    constructor(objSuper) {
        objSuper.objConfigs = commonFunctions.deepMergeObject({
            formRegister: true,
            modalSearch: {
                disableSearchDefault: false,
            },
            runningSearchBln: false,
            typeCurrentSearch: null,
        }, objSuper.objConfigs ?? {});

        objSuper.promisseReturnValue = commonFunctions.deepMergeObject({
            selecteds: []
        }, objSuper.promisseReturnValue ?? {});

        super(objSuper);

        this.#addEventsDefault();

        if (this._objConfigs?.formRegister || this._objConfigs?.domainCustom?.applyBln) {
            this._queueCheckDomainCustom = new QueueManager();
            this._queueCheckDomainCustom.enqueue(() => TenantTypeDomainCustomHelper.checkElementsDomainCustom(this, { stop_variable: true }));
        }
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

    /**
     * Define o ID forçado de domínio e marca as alterações como bloqueadas.
     * 
     * @param {number|string} id - ID do domínio a ser definido.
     */
    set setForcedDomainIdBlockedChanges(id) {
        this._objConfigs.domainCustom.blocked_changes = true;
        this._objConfigs.domainCustom.domain_id = id;
    }

    /**
     * Retorna o ID do domínio forçado que está bloqueado de alterações.
     * 
     * @returns {number|string|undefined} ID do domínio forçado e bloqueado, se definido. Caso contrário, retorna `undefined`.
     */
    get getForcedDomainIdBlockedChanges() {
        return this._objConfigs.domainCustom.domain_id;
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
        // if (self._objConfigs.modalSearch) {
        //     const inputsSearchs = self._objConfigs.modalSearch.inputsSearchs ?? null;
        //     if (inputsSearchs) {
        //         const event = self._objConfigs.modalSearch.event ?? 'input';
        //         inputsSearchs.on(event, function () {
        //             clearTimeout(self._timerSearch);
        //             self._timerSearch = setTimeout(function () {
        //                 self.generateFilters();
        //             }, 1000);
        //         });
        //     }
        // }

        if (!self._objConfigs?.modalSearch?.disableSearchDefault) {

            $(`${self.getIdModal} #formDataSearch${self.getSufixo}`)
                .find('.btnBuscar').on('click', async (e) => {
                    e.preventDefault();
                    await self._executarBusca();
                });

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

    async _executarBusca() {
        const self = this;
        self._setTypeCurrentSearch = self._objConfigs.querys.consultaFiltros.name;
        await self._generateQueryFilters();
    }

    /**
   * Gera os filtros de consulta para envio a uma API ou outra fonte de dados.
   *
   * Este método coleta os dados do formulário de busca e constrói um objeto com os parâmetros necessários
   * para realizar uma consulta. Ele também permite a personalização por meio do parâmetro `options`.
   *
   * @param {Object} options - Opções adicionais para personalizar o comportamento da função.
   * @param {jQuery} [options.formDataSearch] - O elemento do formulário contendo os campos de busca. Se não fornecido,
   *                                            será buscado automaticamente pelo seletor `#formDataSearch`.
   * @param {Object} [options.appendData] - Dados adicionais que serão mesclados ao objeto `data` antes do envio.
   *                                        Útil para adicionar filtros ou parâmetros que não estão no formulário.
   *
   * @returns {Promise<void>} - Retorna uma Promise que executa a busca quando concluída.
   *
   * ## Estrutura de `options`
   * - `formDataSearch`: O formulário base de onde serão coletados os valores dos filtros. Caso omisso,
   *    a função buscará automaticamente o formulário com o ID `formDataSearch${self.getSufixo}`.
   * - `appendData`: Um objeto contendo dados extras que serão adicionados ao objeto `data` antes de enviá-lo.
   *
   * ## Estrutura do Objeto `data` Gerado
   * - `texto`: Valor do campo de texto para busca (input `name="texto"`).
   * - `parametros_like`: Parâmetros de busca parcial gerados pela função `_returnQueryParameters`.
   * - `ordenacao`: Array com informações de ordenação. Inclui o campo (`campo`) e a direção (`direcao`).
   * - `texto_tratamento`: Opção de tratamento do texto selecionada (ex.: texto dividido ou completo).
   * - `filtros.campos_busca`: Array com os campos de busca selecionados dinamicamente na classe `.searchFields`.
   * - `datas_intervalo`: (Opcional) Objeto contendo:
   *    - `campo_data`: Campo da tabela relacionado à data.
   *    - `data_inicio`: Data inicial do intervalo.
   *    - `data_fim`: Data final do intervalo.
   * - `mes_ano`: (Opcional) Campo no formato `YYYY-MM` gerado a partir do input `name="mesAno"`.
   * - `page`: Padrão inicial definido como `1`.
   *
   * ## Exemplo de Uso do Parâmetro `options`
   * ```javascript
   * await _generateQueryFilters({
   *     formDataSearch: $('#meuFormulario'),
   *     appendData: {
   *         adicionalFiltro: 'valorExtra'
   *     }
   * });
   * ```
   *
   * ## Notificações
   * - Se alguma validação falhar, uma notificação será gerada para o usuário com recomendações
   *   sobre como corrigir o problema.
   *
   * @throws {Error} - Se ocorrer um erro durante o processo de obtenção dos dados via `_getData`.
   */
    async _generateQueryFilters(options = {}) {
        const self = this;
        const formDataSearch = $(options.formDataSearch ?? `${self.getIdModal} .formDataSearch`);
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

        if (formDataSearch.find(`select[name="selCampoDataIntervalo"]`).length > 0 &&
            formDataSearch.find(`input[name="data_inicio"]`).length > 0 &&
            formDataSearch.find(`input[name="data_fim"]`).length > 0) {
            data.datas_intervalo = {};
            data.datas_intervalo.campo_data = formDataSearch.find(`select[name="selCampoDataIntervalo"]`).val();
            data.datas_intervalo.data_inicio = formDataSearch.find(`input[name="data_inicio"]`).val();
            data.datas_intervalo.data_fim = formDataSearch.find(`input[name="data_fim"]`).val();
        }

        if (formDataSearch.find(`input[name="mesAno"]`).length > 0) {
            data.mes_ano = formDataSearch.find(`input[name="mesAno"]`).val();
        }

        const searchFields = commonFunctions.getInputsValues(formDataSearch.find('.searchFields'));
        Object.keys(searchFields).forEach(element => {
            if (searchFields[element] === true) {
                data.filtros.campos_busca.push(element);
            }
        });

        if (arrayMensagens.length > 0) {
            return commonFunctions.generateNotification("Não foi possivel realizar a busca. Verifique as seguintes recomendações:", 'info', { itemsArray: arrayMensagens });
        }

        if (options.appendData) {
            commonFunctions.deepMergeObject(data, options.appendData);
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
            self._refreshQueryQuantity('Consultando...', { footerPagination: footerPagination });
            self._refreshQueryStatus('Efetuando busca. Aguarde...', { footerPagination: footerPagination });

            const forcedDomainId = TenantTypeDomainCustomHelper.checkDomainCustomForcedDomainId(self);
            const objConn = await new connectAjax(config.urlSearch);
            if (forcedDomainId) {
                objConn.setForcedDomainCustomId = forcedDomainId;
            }

            data.page = page;
            objConn.setAction(enumAction.POST);
            objConn.setData(data);

            const response = await objConn.envRequest();

            self._refreshQueryStatus('Busca concluída. Preenchendo os dados...', { footerPagination: footerPagination });
            tbody.html('');
            if (response.data) {
                const responseData = response.data;
                let recordsOnScreen = [];

                for (let item of responseData.data) {
                    const idTr = UUIDHelper.generateUUID();
                    item = commonFunctions.deepMergeObject(item, { idTr });

                    // Verifica se a propriedade `insertTableData` está definida no `config`
                    const functionName = config.insertTableData ? config.insertTableData : 'insertTableData';

                    // Chama dinamicamente a função especificada em `config.insertTableData` ou a padrão `insertTableData`
                    const responseInsert = await self[functionName](item, { config, tbody });

                    // Verifica se insere ou não a coluna de identificação de domínio no registro
                    TenantTypeDomainCustomHelper.checkInsertTdDomainInTable(item, { tbody });
                    recordsOnScreen.push(typeof responseInsert === 'object' ? responseInsert : item);
                }

                if (!responseData.data.length) {
                    TenantTypeDomainCustomHelper.checkThIfNoRecords({ tbody });
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

                TenantTypeDomainCustomHelper.checkThIfNoRecords({ tbody });
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

        const footerPagination = $(options.footerPagination ?? $(self.getIdModal).find('.footerPagination'));
        const selector = $(options.pagination ?? $(footerPagination).find('.pagination'));

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
        const dataPost = options.dataPost;

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
        const footerPagination = $(options.footerPagination ?? $(self.getIdModal).find('.footerPagination'));
        const pagination = $(options.pagination ?? $(footerPagination).find('.pagination'));

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
        const footerPagination = $(options.footerPagination ?? `${self.getIdModal} .footerPagination`);
        const selector = options.selector ?? footerPagination.find('.queryStatus');

        if (selector) {
            selector.html(html);
        }
    }

    _refreshQueryQuantity(html, options = {}) {
        const self = this;
        const footerPagination = $(options.footerPagination ?? `${self.getIdModal} .footerPagination`);
        const selector = options.selector ?? footerPagination.find('.totalRegisters');

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
            const response = await obj.getRequest();
            TenantTypeDomainCustomHelper.checkDomainCustomBlockedChangesDomainId(self, response.data);
            return response;
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
            functionExecuteAfterSuccess = '_executarBusca',
        } = options;

        try {
            commonFunctions.simulateLoading(btnSave);

            const forcedDomainId = TenantTypeDomainCustomHelper.checkDomainCustomForcedDomainId(self);
            const obj = new connectAjax(urlApi);
            if (forcedDomainId) {
                obj.setForcedDomainCustomId = forcedDomainId;
            }
            obj.setAction(self._action)
            obj.setData(data);
            if (self._action === enumAction.PUT) {
                obj.setParam(self._idRegister);
            }

            if (forcedDomainId) {

                const instance = TenantTypeDomainCustomHelper.getInstanceTenantTypeDomainCustom;

                if (instance && instance.getSelectedValue && forcedDomainId != instance.getSelectedValue) {
                    const nameSelected = TenantTypeDomainCustomHelper.getDomainNameById(instance.getDataCurrentDomain.id);
                    const nameCurrent = TenantTypeDomainCustomHelper.getDomainNameById(forcedDomainId);

                    const objMessage = new modalMessage();
                    objMessage.setDataEnvModal = {
                        title: 'Atenção',
                        message: `<p>A unidade de visualização é <b>${nameSelected}</b> e este registro pertence a <b>${nameCurrent}</b>. Os dados serão salvos corretamente, mas o redirecionamento pode não encontrá-lo.</p><p>Deseja continuar?</p>`,
                    };
                    const result = await objMessage.modalOpen();
                    if (!result.confirmResult) {
                        return false;
                    }
                }
            }

            const response = await obj.envRequest();
            if (response) {
                commonFunctions.generateNotification(`Dados enviados com sucesso!`, 'success');
                self._promisseReturnValue.refresh = true;
                if (functionExecuteAfterSuccess) {
                    self[functionExecuteAfterSuccess]();
                }
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

        let blnModalLoading = false;
        try {
            const obj = new modalMessage();
            obj.setDataEnvModal = {
                title: title,
                message: message,
            };
            obj.setFocusElementWhenClosingModal = button;
            await self._modalHideShow(false);
            const result = await obj.modalOpen();
            if (result.confirmResult) {
                await commonFunctions.loadingModalDisplay(true, { message: 'Excluindo registro...', title: 'Aguarde...' });
                blnModalLoading = true;
                if (await self._delRecurse(idDel, options)) {
                    commonFunctions.generateNotification(success, 'success');
                    self.modalCancel();
                    self._generateQueryFilters();
                };
            }
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
        } finally {
            if (blnModalLoading) await commonFunctions.loadingModalDisplay(false);
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
        const title = $(options.title ?? `${self.getIdModal} .registration-title`);
        title.html(html);
    }
    //#endregion


    //#region Verificações de domínio customizado

    /**
     * Verifica e herda o ID de domínio customizado para envio ao modal.
     * 
     * Esta função verifica se o tenant tem um domínio customizado configurado.
     * Se sim, define o `inherit_domain_id` dentro do objeto `dataEnvModal` 
     *
     * @param {Object} dataEnvModal - Objeto contendo os dados a serem enviados ao modal.
     *                                Caso `inherit_domain_id` ainda não exista, será inserido.
     * @throws {Error} Se o domínio customizado exigir um ID, mas ele não for encontrado.
     * @private
     */
    _checkDomainCustomInheritDataEnvModal(dataEnvModal = {}) {
        const self = this;

        // Verifica se há uma instância de domínio customizado ativa
        const instance = TenantTypeDomainCustomHelper.getInstanceTenantTypeDomainCustom;
        if (instance) {

            // Obtém o ID do domínio customizado que deve ser herdado
            const inherit_domain_id = self.getForcedDomainIdBlockedChanges;

            // Se o ID não existir, lança um erro
            if (!inherit_domain_id) {
                console.error(self._objConfigs.domainCustom);
                throw new Error('O ID de Unidade de Domínio a ser herdado não foi informado. Contate o suporte.');
            }

            // Define o inherit_domain_id no objeto
            dataEnvModal.inherit_domain_id ??= undefined;
            dataEnvModal.inherit_domain_id = inherit_domain_id;
        }
        return dataEnvModal;
    }

    /**
     * Verifica se o domínio customizado deve ser herdado e aplica a configuração.
     * 
     * Esta função verifica se o domínio customizado deve ser herdado (`inheritedBln`).
     * Se essa configuração estiver ativa, ela obtém o `inherit_domain_id` da variável
     * `dataEnvModal` e o define como o domínio forçado (`setForcedDomainIdBlockedChanges`).
     * 
     * Caso o `inherit_domain_id` não esteja presente, gera uma notificação de erro
     * e retorna `false`, impedindo a continuidade da lógica.
     *
     * @returns {boolean} Retorna `true` se o domínio for herdado corretamente, `false` caso contrário.
     * @private
     */
    _checkDomainCustomInherited() {
        const self = this;

        // Obtém a instância do gerenciador de domínio customizado
        const instance = TenantTypeDomainCustomHelper.getInstanceTenantTypeDomainCustom;
        if (!instance) return false; // Se não houver instância, encerra a função

        // Verifica se a configuração de herança de domínio está ativada
        if (self._objConfigs?.domainCustom?.inheritedBln) {

            // Obtém o ID do domínio herdado da variável `dataEnvModal`
            const domainId = self._dataEnvModal.inherit_domain_id;

            // Se não houver ID de domínio herdado, exibe erro e retorna falso
            if (!domainId) {
                commonFunctions.generateNotification(
                    'O ID da Unidade de domínio herdada não foi enviado. Caso o erro persista, contate o suporte.',
                    'error'
                );
                console.error(self._dataEnvModal);
                return false;
            }

            // Define o domínio herdado como o domínio forçado
            self.setForcedDomainIdBlockedChanges = domainId;
        }

        return true; // Retorna `true` caso a herança de domínio tenha sido aplicada com sucesso
    }

    //#endregion
}