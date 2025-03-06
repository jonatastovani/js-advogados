import { commonFunctions } from "../../commons/commonFunctions";
import { connectAjax } from "../../commons/connectAjax";
import { enumAction } from "../../commons/enumAction";
import instanceManager from "../../commons/instanceManager";
import { modalRegistrationAndEditing } from "../../commons/modal/modalRegistrationAndEditing";
import { DateTimeHelper } from "../../helpers/DateTimeHelper";
import { UUIDHelper } from "../../helpers/UUIDHelper";
import { QuillEditorModule } from "../../modules/QuillEditorModule";
import { QueueManager } from "../../utils/QueueManager";

export class modalDocumentoModeloTenant extends modalRegistrationAndEditing {

    /**
     * Configuração local do modal
     */
    #objConfigs = {
        url: {
            base: undefined,
            baseDocumentoModeloTenant: window.apiRoutes.baseDocumentoModeloTenant,
            baseDocumentoModeloTenantHelper: window.apiRoutes.baseDocumentoModeloTenantHelper,
        },
        data: {
            objetosNaTela: [],
            documento_modelo_tenant: undefined
        },
        sufixo: 'ModalDocumentoModeloTenant',
    };

    /**
     * Configuração local do modal
     */
    #dataEnvModal = {
        documento_modelo_tenant: undefined,
        objetos: [],
    };
    _quillQueueManager;
    _verificacoesInconsistenciasQueueManager;

    constructor(urlApi) {
        super({
            idModal: "#modalDocumentoModeloTenant",
        });

        this._objConfigs = commonFunctions.deepMergeObject(this._objConfigs, this.#objConfigs);
        this._objConfigs.url.base = urlApi;
        this._dataEnvModal = commonFunctions.deepMergeObject(this._dataEnvModal, this.#dataEnvModal);
        this._action = enumAction.POST;

        const objData = {
            objConfigs: this._objConfigs,
            extraConfigs: {
                modeParent: 'searchAndUse',
            }
        }
        // this._functionsDocumentoModeloTenantRenderModule = new DocumentoModeloTenantRenderModule(this, objData);

        this._quillQueueManager = new QueueManager();  // Cria a fila
        this._verificacoesInconsistenciasQueueManager = new QueueManager();  // Cria a fila
        this._verificacoesInconsistenciasQueueManager.setDeduplicationMode('last');

        this.#addEventosBotoes();
    }

    async modalOpen() {
        const self = this;

        await commonFunctions.loadingModalDisplay(true, { message: 'Carregando dados do modelo...' });

        await self.#buscarDadosDocumentoModeloTenant();
        self.#inserirTodosObjetos(self._dataEnvModal.objetos);

        await commonFunctions.loadingModalDisplay(false);
        await self._modalHideShow();

        return await self._modalOpen();
    }

    #addEventosBotoes() {
        const self = this;

        const instanceName = `QuillEditor${self.getSufixo}`;
        /** @type {QuillEditorModule} */
        self._classQuillEditor = instanceManager.instanceVerification(instanceName);
        if (!self._classQuillEditor) {
            self._classQuillEditor = instanceManager.setInstance(instanceName, new QuillEditorModule(`#conteudo${self.getSufixo}`, { exclude: ['image', 'scriptSub', 'scriptSuper', 'code', 'link'] }));
        }

        self._classQuillEditor.getQuill.setContents([]);
        self._quillQueueManager.setReady();  // Informa que o quill está pronto

        const btnRefresh = $(self.getIdModal).find('.btn-refresh');
        btnRefresh.on('click', async () => {
            commonFunctions.simulateLoading(btnRefresh);
            await self.#verificaInconsistenciasObjetosVinculados();
            commonFunctions.simulateLoading(btnRefresh, false);
        });
    }

    _modalClose() {
        const self = this;
        const modal = $(self.getIdModal);
        modal.find(`#painelObjetos${self.getSufixo}-tab`).trigger('click');
        super._modalClose();
    }

    async #buscarDados() {
        const self = this;

        await commonFunctions.loadingModalDisplay();
        try {
            self._clearForm();
            self._action = enumAction.PUT;
            const response = await self._getRecurse();
            if (response?.data) {
                const responseData = response.data;
                self._updateModalTitle(`Alterar: <b>${responseData.titulo}</b>`);
                const form = $(self.getIdModal).find('.formRegistration');
                form.find('input[name="titulo"]').val(responseData.titulo);
                form.find('textarea[name="descricao"]').val(responseData.descricao).focus();
                return true;
            }
            return false;
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
            return false;
        } finally {
            await commonFunctions.loadingModalDisplay(false);
        }
    }

    async #buscarDadosDocumentoModeloTenant() {
        const self = this;
        try {
            const objConn = new connectAjax(self._objConfigs.url.baseDocumentoModeloTenant);
            objConn.setParam(self._dataEnvModal.documento_modelo_tenant.id);
            const response = await objConn.getRequest();
            self._objConfigs.data.documento_modelo_tenant = response.data;
            $(`${self.getIdModal} input[name="nome"]`).val(response.data.nome);
            self.#inserirObjetosPredefinidos();
            self.#verificaInconsistenciasObjetosVinculados();
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
        }
    }

    #inserirObjetosPredefinidos() {
        const self = this;
        const rowObjetosSistema = $(`#rowObjetosSistema${self._objConfigs.sufixo}`);
        const objetosSistema = self._objConfigs.data.documento_modelo_tenant.objetos;

        objetosSistema.map((objeto) => {

            switch (objeto.identificador) {

                case 'DataDocumento':
                    const newObjeto = JSON.parse(JSON.stringify(objeto));
                    newObjeto.idObj = UUIDHelper.generateUUID();
                    const valor_objeto = DateTimeHelper.retornaDadosDataHora(new Date(), 1);
                    rowObjetosSistema.append(`
                        <div class="col-6 col-sm-4 col-md-3 col-xl-2 text-end mt-2">
                            <label for="${newObjeto.idObj}" class="form-label">Data do documento</label>
                            <input type="date" class="form-control text-center" name="dataDocumento" id="${newObjeto.idObj}" value="${valor_objeto}">
                        </div>
                    `);

                    newObjeto.metadata = { valor_objeto };
                    newObjeto.objeto_vinculado = objeto;

                    self._objConfigs.data.objetosNaTela.push(newObjeto);

                    $(`#${newObjeto.idObj}`).on('change', async () => {

                        newObjeto.metadata.valor_objeto = $(`#${newObjeto.idObj}`).val();
                        await self.#verificaInconsistenciasObjetosVinculados();
                    })

                    break;
            }
        });
    }

    async #inserirTodosObjetos(objetos) {
        const self = this;
        try {
            $(`#divObjetos${self._objConfigs.sufixo}`).html('');

            self._verificacoesInconsistenciasQueueManager.setNoReady();

            const objConn = new connectAjax(`${self._objConfigs.url.baseDocumentoModeloTenantHelper}/render-objetos`);
            objConn.setData({ objetos });
            objConn.setAction(enumAction.POST);
            const response = await objConn.envRequest();
            response.data.map(async (objeto) => {
                await self.#inserirObjeto(objeto);
            });

            self._verificacoesInconsistenciasQueueManager.enqueue(self.#verificaInconsistenciasObjetosVinculados.bind(self));

        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
        } finally {
            self._verificacoesInconsistenciasQueueManager.setReady();
        }
    }

    async #inserirObjeto(objeto) {
        const self = this;
        const divObjetos = $(`#divObjetos${self._objConfigs.sufixo}`);

        let title = self._getNomeObjetoPorIdentificador(objeto);
        let htmlDropDown = self.#htmlDropDownEscolhasMultiplas(objeto);

        if (!objeto.idCol) {

            objeto.idCol = UUIDHelper.generateUUID();
            let strCard = `
            <div id="${objeto.idCol}" class="card p-0" >
                <div class="card-body">
                    <div class="row">
                        <h5 class="card-title d-flex align-items-center justify-content-between">
                            <span class="spanTitle">${title}</span>
                            <div>
                                <div class="dropdown dropdown-acoes-objeto">
                                    <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><button type="button" class="dropdown-item fs-6 btn-atualizar-objeto">Atualizar objeto</button></li>
                                    </ul>
                                </div>
                            </div>
                        </h5>
                        <div class="d-grid gap-2 divDropDown">
                            ${htmlDropDown}
                        </div>
                    </div>
                </div>
            </div > `;

            divObjetos.append(strCard);
            self._objConfigs.data.objetosNaTela.push(objeto);
        } else {
            $(`#${objeto.idCol} .spanTitle`).html(title);
            $(`#${objeto.idCol} .divDropDown`).html(htmlDropDown);
        }

        await self.#addEventosObjetos(objeto);

        return true;
    }

    #htmlDropDownEscolhasMultiplas(objeto) {
        const self = this;
        let htmlObjeto = '';
        let htmlEndereco = '';
        let htmlDocumento = '';

        // Garante que `selecoes` exista no objeto
        objeto.selecoes ??= {};
        objeto.selecoes.endereco_id = null;
        objeto.selecoes.documento_rg_id = null;

        // Adicionar no objeto a propriedade objetos_compativeis
        objeto.objetos_compativeis ??= [];
        objeto.objeto_vinculado = null;

        // Dropdown para objetos compatíveis
        const objetosMesmoIdentificador = self._objConfigs.data.documento_modelo_tenant.objetos.filter(obj => obj.identificador === objeto.identificador);

        if (objetosMesmoIdentificador.length) {

            objetosMesmoIdentificador.map(obj => {
                obj.idBtn = UUIDHelper.generateUUID();
                htmlObjeto += `<li><button class="dropdown-item fs-6" type="button" id="${obj.idBtn}">${obj.identificador}.${obj.contador}</button></li>`;
                objeto.objetos_compativeis.push(obj);
            });
        } else {

            htmlObjeto = `<li><button class="dropdown-item fs-6" type="button">Nenhum objeto compatível</button></li>`;
        }

        // Dropdown para endereços (se existir)
        if (Array.isArray(objeto?.dados?.enderecos)) {

            objeto.dados.enderecos.map((endereco) => {

                const nomeEndereco = `${endereco.logradouro}, ${endereco.numero}`;
                endereco.idBtn = UUIDHelper.generateUUID();
                htmlEndereco += `<li><button class="dropdown-item fs-6" type="button" id="${endereco.idBtn}">${nomeEndereco}</button></li>`;
            });
        }

        // Dropdown para documentos (se existir)
        let blnInserirDropDocumentoRG = Array.isArray(objeto?.dados?.documentos) &&
            objeto.dados.documentos.some(doc => doc.documento_tipo_tenant.documento_tipo_id === window.Enums.DocumentoTipoEnum.RG);
        if (blnInserirDropDocumentoRG) {

            objeto.dados.documentos.filter(doc => doc.documento_tipo_tenant.documento_tipo_id === window.Enums.DocumentoTipoEnum.RG).map((documento) => {

                const nomeDocumento = `${documento.documento_tipo_tenant.documento_tipo.nome} - ${documento.numero}`;
                documento.idBtn = UUIDHelper.generateUUID();
                htmlDocumento += `<li><button class="dropdown-item fs-6" type="button" id="${documento.idBtn}">${nomeDocumento}</button></li>`;
            });
        }

        // Criando os dropdowns HTML
        const dropDownObjeto = `
        <div class="btn-group">
            <button type="button" class="btn btn-outline-primary dropdown-toggle btnDropdownObjeto" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="htmlDropdown" data-default-value="Nenhum objeto vinculado">
                    Nenhum objeto vinculado
                </span>
            </button>
            <ul class="dropdown-menu">${htmlObjeto}</ul>
        </div>`;

        const dropDownEndereco = Array.isArray(objeto?.dados?.enderecos) && objeto.dados.enderecos.length ? `
        <div class="btn-group">
            <button type="button" class="btn btn-outline-info dropdown-toggle btnDropdownEndereco" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="htmlDropdown" data-default-value="Nenhum endereço vinculado">
                    Nenhum endereço vinculado
                </span>
            </button>
            <ul class="dropdown-menu">${htmlEndereco}</ul>
        </div>` :
            `<button type="button" class="btn btn-outline-secondary" disabled>
                Nenhum endereço encontrado
            </button>`;

        const dropDownDocumentoRG = blnInserirDropDocumentoRG && objeto.dados.documentos.length ? `
        <div class="btn-group">
            <button type="button" class="btn btn-outline-info dropdown-toggle btnDropdownDocumentoRG" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="htmlDropdown" data-default-value="Nenhum documento vinculado">
                    Nenhum RG vinculado
                </span>
            </button>
            <ul class="dropdown-menu">${htmlDocumento}</ul>
        </div>` :
            `<button type="button" class="btn btn-outline-secondary" disabled>
                Nenhum RG encontrado
            </button>`;

        return dropDownObjeto + dropDownEndereco + dropDownDocumentoRG;
    }

    async #addEventosObjetos(objeto) {
        const self = this;

        // Remover eventos anteriores
        $(`#${objeto.idCol} button`).off('click');

        // Configurar evento para atualizar o objeto
        $(`#${objeto.idCol} .btn-atualizar-objeto`).on('click', async () => {
            await self.#atualizarObjeto(objeto);
        });

        self.#eventosSelecaoObjeto(objeto);

        self.#eventosEnderecos(objeto);

        self.#eventosDocumentos(objeto);

        self._verificacoesInconsistenciasQueueManager.enqueue(self.#verificaInconsistenciasObjetosVinculados.bind(self));
    }

    #eventosSelecaoObjeto(objeto) {
        const self = this;

        // Configurar eventos para seleção de objeto compatível
        objeto.objetos_compativeis.map(compativel => {

            $(`#${compativel.idBtn}`).on('click', async () => {

                const btns = $(`${self.getIdModal} .dropdown-toggle`);
                try {
                    commonFunctions.simulateLoading(btns);

                    const objetosNaTela = self._objConfigs.data.objetosNaTela;
                    const indexObjetoNaTela = self.#pesquisaIndexObjetosNaTela(objeto);

                    if (indexObjetoNaTela == -1) {
                        commonFunctions.generateNotification('Erro ao vincular o objeto. Caso o erro persista, contate o suporte.', 'error');
                        return;
                    }

                    // Vincula o objeto
                    objetosNaTela[indexObjetoNaTela].objeto_vinculado = compativel;

                    // Atualiza apenas o dropdown correspondente ao objeto
                    $(`#${objeto.idCol} .btnDropdownObjeto .htmlDropdown`).html(`${compativel.identificador}.${compativel.contador}`);

                    // Remove o vínculo caso outro esteja com o mesmo identificador e contador
                    objetosNaTela.map(obj => {
                        if (
                            obj.objeto_vinculado &&
                            obj.objeto_vinculado.identificador === compativel.identificador &&
                            obj.objeto_vinculado.contador === compativel.contador &&
                            obj.idCol != objeto.idCol
                        ) {
                            obj.objeto_vinculado = null;

                            // Atualiza o dropdown do objeto desvinculado para o valor padrão
                            $(`#${obj.idCol} .btnDropdownObjeto .htmlDropdown`).html($(`#${obj.idCol} .btnDropdownObjeto .htmlDropdown`).data('default-value'));
                        }
                    });

                    await self.#verificaInconsistenciasObjetosVinculados();

                } catch (error) {
                    commonFunctions.generateNotification(error.message);
                } finally {
                    commonFunctions.simulateLoading(btns, false);
                }
            });
        });
    }

    #eventosEnderecos(objeto) {
        const self = this;

        // Configurar eventos para seleção de endereço (se existir)
        if (Array.isArray(objeto?.dados?.enderecos)) {
            let selected = objeto.selecoes.endereco_id;

            const acoesAlteracaoEndereco = (idCol, endereco) => {
                // Atualiza apenas o dropdown de endereços correspondente ao objeto
                $(`#${idCol} .btnDropdownEndereco .htmlDropdown`).html(`${endereco.logradouro}, ${endereco.numero}`);
                let indexObjetoNaTela = self.#pesquisaIndexObjetosNaTela(objeto);

                if (indexObjetoNaTela == -1) {
                    commonFunctions.generateNotification('Erro ao vincular o endereço. Caso o erro persista, contate o suporte.', 'error');
                    return;
                }

                // Vincula o endereço
                self._objConfigs.data.objetosNaTela[indexObjetoNaTela].selecoes.endereco_id = endereco.id;
            }

            objeto.dados.enderecos.map(endereco => {

                $(`#${endereco.idBtn}`).on('click', async () => {
                    const btns = $(`${self.getIdModal} .dropdown-toggle`);
                    try {
                        commonFunctions.simulateLoading(btns);

                        acoesAlteracaoEndereco(objeto.idCol, endereco);
                        await self.#verificaInconsistenciasObjetosVinculados();

                    } catch (error) {
                        commonFunctions.generateNotification(error.message);
                    } finally {
                        commonFunctions.simulateLoading(btns, false);
                    }
                });

                if (!selected) {
                    acoesAlteracaoEndereco(objeto.idCol, endereco);
                    selected = endereco.id;
                } else {
                    if (selected == endereco.id) {
                        acoesAlteracaoEndereco(objeto.idCol, endereco);
                    }
                };
            });
        }

    }

    #eventosDocumentos(objeto) {
        const self = this;

        // Configurar eventos para seleção de documento (se existir)
        if (Array.isArray(objeto?.dados?.documentos)) {
            let selected = objeto.selecoes.documento_rg_id;

            objeto.dados.documentos.filter(doc => doc.documento_tipo_tenant.documento_tipo_id === window.Enums.DocumentoTipoEnum.RG)
                .map(documento => {

                    const acoesAlteracaoDocumentoRG = (idCol, documentoRG) => {
                        // Atualiza apenas o dropdown de endereços correspondente ao objeto
                        $(`#${idCol} .btnDropdownDocumentoRG .htmlDropdown`).html(`${documento.documento_tipo_tenant.documento_tipo.nome} - ${documento.numero}`);
                        let indexObjetoNaTela = self.#pesquisaIndexObjetosNaTela(objeto);

                        if (indexObjetoNaTela == -1) {
                            commonFunctions.generateNotification('Erro ao vincular o endereço. Caso o erro persista, contate o suporte.', 'error');
                            return;
                        }

                        // Vincula o endereço
                        self._objConfigs.data.objetosNaTela[indexObjetoNaTela].selecoes.documento_rg_id = documentoRG.id;
                    }

                    $(`#${documento.idBtn}`).on('click', async () => {
                        const btns = $(`${self.getIdModal} .dropdown-toggle`);
                        try {
                            commonFunctions.simulateLoading(btns);

                            acoesAlteracaoDocumentoRG(objeto.idCol, documento);
                            await self.#verificaInconsistenciasObjetosVinculados();

                        } catch (error) {
                            commonFunctions.generateNotification(error.message);
                        } finally {
                            commonFunctions.simulateLoading(btns, false);
                        }
                    });

                    if (!selected) {
                        acoesAlteracaoDocumentoRG(objeto.idCol, documento);
                        selected = documento.id;
                    } else {
                        if (selected == documento.id) {
                            acoesAlteracaoDocumentoRG(objeto.idCol, documento);
                        }
                    };

                });

        }
    }

    async #atualizarObjeto(objeto) {
        const self = this;

        try {
            self._verificacoesInconsistenciasQueueManager.setNoReady();

            const indexObjetoNaTela = self.#pesquisaIndexObjetosNaTela(objeto);
            const objetoNaTela = self._objConfigs.data.objetosNaTela[indexObjetoNaTela];

            const objConn = new connectAjax(`${self._objConfigs.url.baseDocumentoModeloTenantHelper}/render-objetos`);
            objConn.setData({
                objetos: [{
                    id: objeto.id,
                    identificador: objeto.identificador,
                }]
            });
            objConn.setAction(enumAction.POST);
            const response = await objConn.envRequest();
            commonFunctions.deepMergeObject(objetoNaTela, response.data[0]);
            await self.#inserirObjeto(objetoNaTela);

        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
        } finally {
            self._verificacoesInconsistenciasQueueManager.setReady();
        }
    }

    async #verificaInconsistenciasObjetosVinculados(options = {}) {
        try {
            const self = this;
            let {
                divRevisao = `#divRevisao${self.getSufixo}`,
                badgePendencias = `#badgePendencias${self.getSufixo}`,
            } = options;

            divRevisao = $(divRevisao);
            badgePendencias = $(badgePendencias);

            const resultado = await self.#executaVerificacaoInconsistenciasObjetosVinculado({
                objetos_vinculados: self._getObjetosNaTelaVinculados(),
                documento_modelo_tenant_id: self._dataEnvModal.documento_modelo_tenant.id,
            });

            divRevisao.html('');
            badgePendencias.html('0');

            let contadorPendencias = 0;
            resultado.objetos_nao_vinculados.forEach(item => {

                divRevisao.append(`
                    <div class="alert alert-warning py-1" role="alert">
                        <p class="m-0">Objeto requisitado não vinculado: <span class="fw-bolder">${item.nome}</span></p>
                    </div>
                `);

                contadorPendencias++;
            });

            resultado.objetos_campos_ausentes.forEach(objeto => {

                let indexObjetoNaTela = self.#pesquisaIndexObjetosNaTela(objeto, 'id');
                const objetosNaTela = self._objConfigs.data.objetosNaTela;
                let nomeVinculado = self._getNomeObjetoPorIdentificador(objetosNaTela[indexObjetoNaTela]);

                let strMarcacaoFaltante = objeto.campos_faltantes.map(m => m.display).join(', ');
                divRevisao.append(`
                    <div class="alert alert-warning py-1" role="alert">
                        <p class="m-0">Objeto <span class="fw-bolder">${objeto.nome}</span> - Vinculado a <span class="fw-bolder">${nomeVinculado}</span></p>
                        <hr class="my-1">
                        <p class="m-0">Campo(s) faltante(s): <span class="fw-bolder">${strMarcacaoFaltante}</span>.</p>
                    </div>
                `);

                contadorPendencias++;
            });

            if (resultado.conteudo) {
                self._classQuillEditor.getQuill.setContents(resultado.conteudo);
            }

            badgePendencias.html(contadorPendencias);
            return resultado;
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
            return false;
        }
    }

    _getNomeObjetoPorIdentificador(objeto) {
        switch (objeto.identificador) {
            case 'ClientePF':
                return objeto.dados.nome;

            case 'ClientePJ':
                return objeto.dados.nome_fantasia;

            default:
                commonFunctions.generateNotification('Identificador não configurado', 'warning');
                return 'Identificador nao configurado.';
        }
    }

    _getObjetosNaTelaVinculados() {
        const self = this;

        // Filtrar apenas os que possuem objeto_vinculado
        return self._objConfigs.data.objetosNaTela
            .filter(obj => obj.objeto_vinculado !== null)
            .map(obj => ({
                identificador: obj.identificador,
                id: obj.id,
                objeto_vinculado: obj.objeto_vinculado,
                selecoes: obj.selecoes,
                metadata: obj.metadata,
            }));
    }

    /**
     * Verifica inconsistências nas marcações do texto.
     * @param {Object} data - O objeto contendo o conteúdo do Quill e a lista de clientes.
     * @returns {Object} - Um objeto contendo as marcações sem referência e os objetos não utilizados.
     */
    async #executaVerificacaoInconsistenciasObjetosVinculado(data) {
        const self = this;
        const objConn = new connectAjax(`${self._objConfigs.url.baseDocumentoModeloTenantHelper}/render-documento`);
        objConn.setAction(enumAction.POST);
        objConn.setData(data);
        const response = await objConn.envRequest();
        return response.data;
    }

    #pesquisaIndexObjetosNaTela(item, prop = 'idCol') {
        const self = this;
        return self._objConfigs.data.objetosNaTela.findIndex(doc => doc[prop] === item[prop]);
    }

    saveButtonAction() {
        const self = this;
        let data = {
            nome: $(`#nome${self.getSufixo}`).val(),
            conteudo: JSON.stringify(self._classQuillEditor.getQuill.getContents()),
            documento_modelo_tenant_id: self._dataEnvModal.documento_modelo_tenant.id,
        };

        if (self.#saveVerifications(data)) {

            self._save(data, self._objConfigs.url.base);
        }
    }

    #saveVerifications(data) {
        const self = this;
        let blnSave = commonFunctions.verificationData(data.nome, {
            field: $(`#nome${self.getSufixo}`),
            messageInvalid: 'O nome do documento deve ser informado.',
            setFocus: true
        });

        if (self._classQuillEditor.getQuill.getText().replace(/\n/g, '').trim().length === 0) {
            commonFunctions.generateNotification('Nenhum conteúdo foi adicionado ao documento.', 'warning');
            blnSave = false;
        }
        return blnSave;
    }

}