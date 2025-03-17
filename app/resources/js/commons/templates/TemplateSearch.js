import { ModalMessage } from "../../components/comum/ModalMessage";
import TenantTypeDomainCustomHelper from "../../helpers/TenantTypeDomainCustomHelper";
import { URLHelper } from "../../helpers/URLHelper";
import { UUIDHelper } from "../../helpers/UUIDHelper";
import { CommonFunctions } from "../CommonFunctions";
import { ConnectAjax } from "../ConnectAjax";
import { EnumAction } from "../EnumAction";

export class TemplateSearch {

    /**
     * Sufixo da template
     */
    _sufixo;

    /**
     * Objeto para reservar configurações do template
     */
    _objConfigs = {
        runningSearchBln: false,
        typeCurrentSearch: undefined,
    };

    /**
     * Constructor da classe.
     * 
     * @param {Object} objSuper - Objeto com as configurações da superclasse.
     * @param {string} objSuper.sufixo - Sufixo da página.
     * @param {Object} [objSuper.objConfigs] - Configurações do template.
     * @param {boolean} [objSuper.withOutVerifyDomainCustom=false] - Indica se a verificação de TenantTypeDomainCustom deve ser ignorada. Por padrão, a verificação de TenantTypeDomainCustom é feita.
     */
    constructor(objSuper) {
        this._sufixo = objSuper.sufixo;
        this._objConfigs = Object.assign(this._objConfigs, objSuper.objConfigs ?? {});
        this.#addEventsDefault();

        if (!objSuper?.withOutVerifyDomainCustom) {
            this.#verificarTenantTypeDomainCustom()
        };
    }

    #addEventsDefault() {
        const self = this;
    }

    //#region Getters e Setters

    /**
     * Retorna o sufixo da página.
     */
    get getSufixo() {
        return this._sufixo;
    }

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

    //#region Campos para verificação de TenantTypeDomainCustom

    #verificarTenantTypeDomainCustom() {
        if (TenantTypeDomainCustomHelper.getInstanceTenantTypeDomainCustom) {
            this.#addQueueExecutaBusca();
        }
    }

    #addQueueExecutaBusca() {
        const self = this;
        const custom = TenantTypeDomainCustomHelper.getInstanceTenantTypeDomainCustom;

        if (custom.getStatusBlnCustom) {
            custom.setEnqueueAction(self._executarBusca.bind(self));
        }
    }

    //#endregion

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
        const { formDataSearch = options.formDataSearch ?? $(`#formDataSearch${self.getSufixo}`) } = options;
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

        const searchFields = CommonFunctions.getInputsValues(formDataSearch.find('.searchFields'));
        Object.keys(searchFields).forEach(element => {
            if (searchFields[element] === true) {
                data.filtros.campos_busca.push(element);
            }
        });

        if (arrayMensagens.length > 0) {
            return CommonFunctions.generateNotification("Não foi possivel realizar a busca. Verifique as seguintes recomendações:", 'info', { itemsArray: arrayMensagens });
        }

        if (options.appendData) {
            CommonFunctions.deepMergeObject(data, options.appendData);
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

        CommonFunctions.generateNotification('O tipo de busca informado não foi encontrado.', 'error');
        return false;
    }

    async _getData(data, page = 1) {
        const self = this;
        if (self._objConfigs.runningSearchBln) {
            CommonFunctions.generateNotification('Busca em andamento. Aguarde...', 'info');
            return;
        }

        let config = self.#getConfigType();
        if (!config) { return; }

        const buttonSearch = $(config.btnSearch ?? `#btnBuscar${self.getSufixo}`);
        const tbody = $(config.tbody ?? `#tableData${self.getSufixo} tbody`);
        const footerPagination = $(config.footerPagination ?? `#footerPagination${self.getSufixo}`);

        self._objConfigs.runningSearchBln = true;
        try {
            CommonFunctions.simulateLoading(buttonSearch);
            self._paginationDefault({ footerPagination });
            self._refreshQueryQuantity('Consultando...', { footerPagination });
            self._refreshQueryStatus('Efetuando busca. Aguarde...', { footerPagination });

            const forcedDomainId = TenantTypeDomainCustomHelper.checkDomainCustomForcedDomainId(self);
            const objConn = new ConnectAjax(config.urlSearch);
            data.page = page;
            if (forcedDomainId) {
                objConn.setForcedDomainCustomId = forcedDomainId;
            }

            objConn.setAction(EnumAction.POST);
            objConn.setData(data);
            const response = await objConn.envRequest();

            self._refreshQueryStatus('Busca concluída. Preenchendo os dados...', { footerPagination });
            tbody.html('');
            if (response.data) {
                const responseData = response.data;
                let recordsOnScreen = [];

                for (let item of responseData.data) {
                    const idTr = UUIDHelper.generateUUID();
                    item = CommonFunctions.deepMergeObject(item, { idTr });

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

                self._refreshQueryQuantity(responseData.total, { footerPagination });
                self.#fillPagination(responseData, { footerPagination, dataPost: data });
                config.dataPost = data;
                config.recordsOnScreen = recordsOnScreen;

                // Verifica se a propriedade `functionExecuteOnSuccess` está definida no `config`
                const functionName = config.functionExecuteOnSuccess ?? 'functionExecuteOnSuccess';
                if (typeof self[functionName] === 'function') {
                    // Chama dinamicamente a função especificada em `config.functionExecuteOnSuccess` ou a padrão `functionExecuteOnSuccess`
                    await self[functionName](response);
                }

            } else {

                self._refreshQueryQuantity(0, { footerPagination });
                self._paginationDefault({ footerPagination });
                config.dataPost = data;
                config.recordsOnScreen = [];

                TenantTypeDomainCustomHelper.checkThIfNoRecords({ tbody });
            }

        } catch (error) {
            tbody.html('');
            CommonFunctions.generateNotificationErrorCatch(error);
            footerPagination.find('.totalRegistros').html(0);

            // Verifica se a propriedade `functionExecuteOnError` está definida no `config`
            const functionName = config.functionExecuteOnError ? config.functionExecuteOnError : 'functionExecuteOnError';
            if (typeof self[functionName] === 'function') {
                // Chama dinamicamente a função especificada em `config.functionExecuteOnError` ou a padrão `functionExecuteOnError`
                await self[functionName](error);
            }
        } finally {
            CommonFunctions.simulateLoading(buttonSearch, false);
            self._refreshQueryStatus('Aguardando comando do usuário...', { footerPagination });
            self._objConfigs.runningSearchBln = false;
        }
    }

    async #fillPagination(data, options) {
        const self = this;
        const { footerPagination = $(options.footerPagination ?? `#footerPagination${self.getSufixo}`),
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
            footerPagination = $(options.footerPagination ?? $(`#footerPagination${self.getSufixo}`)),
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
            footerPagination = $(options.footerPagination ?? $(`#footerPagination${self.getSufixo}`)),
            selector = options.selector ?? footerPagination.find('.queryStatus'),
        } = options;

        if (selector) {
            selector.html(html);
        }
    }

    _refreshQueryQuantity(html, options = {}) {
        const self = this;
        const {
            footerPagination = $(options.footerPagination ?? $(`#footerPagination${self.getSufixo}`)),
            selector = options.selector ?? footerPagination.find('.totalRegisters'),
        } = options;

        $(selector).html(html);
    }

    // async _getRecurse(options = {}) {
    //     const self = this;
    //     const { idRegister = self._idRegister } = options;

    //     let config = self.#getConfigType();
    //     if (!config) { return; }

    //     try {
    //         const obj = new ConnectAjax(config.url);
    //         obj.setParam(idRegister);
    //         return await obj.getRequest();
    //     } catch (error) {
    //         CommonFunctions.generateNotificationErrorCatch(error);
    //         return false;
    //     }
    // }

    async _delButtonAction(idDel, nameDel, options = {}) {
        const self = this;
        const { button = null,
            title = 'Exclusão de Registro',
            message = `Confirma a exclusão do registro <b>${nameDel}</b>?`,
            success = `Registro excluído com sucesso!`,
        } = options;

        try {
            const obj = new ModalMessage();
            obj.setDataEnvModal = {
                title: title,
                message: message,
            };
            obj.setFocusElementWhenClosingModal = button;
            const result = await obj.modalOpen();
            if (result.confirmResult) {
                if (await self._delRecurse(idDel, options)) {
                    CommonFunctions.generateNotification(success, 'success');
                    self._generateQueryFilters();
                }
            }
        } catch (error) {
            CommonFunctions.generateNotificationErrorCatch(error);
        }
    }

    async _delRecurse(idDel, options = {}) {
        const self = this;

        let config = self.#getConfigType();
        if (!config) { return; }

        try {
            const obj = new ConnectAjax(config.url);
            obj.setParam(idDel);
            obj.setAction(EnumAction.DELETE)
            await obj.deleteRequest();
            return true;
        } catch (error) {
            CommonFunctions.generateNotificationErrorCatch(error);
            return false;
        }
    }

    //#endregion
}
