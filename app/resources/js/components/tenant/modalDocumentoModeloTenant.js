import { commonFunctions } from "../../commons/commonFunctions";
import { connectAjax } from "../../commons/connectAjax";
import { enumAction } from "../../commons/enumAction";
import instanceManager from "../../commons/instanceManager";
import { modalRegistrationAndEditing } from "../../commons/modal/modalRegistrationAndEditing";
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

    constructor(urlApi) {
        super({
            idModal: "#modalDocumentoModeloTenant",
        });

        this._objConfigs.url.base = urlApi;
        this._objConfigs = commonFunctions.deepMergeObject(this._objConfigs, this.#objConfigs);
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
    }

    _modalClose() {
        const self = this;
        const modal = $(self.getIdModal);
        console.log(modal.find(`#painelConteudo${self.getSufixo}-tab`));
        modal.find(`#painelConteudo${self.getSufixo}-tab`).trigger('click');
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
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
        }
    }

    async #inserirTodosObjetos(objetos) {
        const self = this;
        try {
            const objConn = new connectAjax(`${self._objConfigs.url.baseDocumentoModeloTenantHelper}/render-objetos`);
            objConn.setData({ objetos });
            objConn.setAction(enumAction.POST);
            const response = await objConn.envRequest();
            response.data.map(async (objeto) => {
                await self.#inserirObjeto(objeto);
            });
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
        }
    }

    async #inserirObjeto(objeto) {
        const self = this;
        const divObjetos = $(`#divObjetos${self._objConfigs.sufixo}`);

        let title = '';
        let htmlColsEspecifico = '';
        let htmlAppend = '';

        switch (objeto.identificador) {

            case 'ClientePF':
                title = objeto.dados.nome;
                // htmlColsEspecifico = self.#htmlColsEspecificosClientePF(item);
                // htmlAppend = `<p class="mb-0 text-truncate">
                //         <b>Endereços disponíveis:</b> ${item.dados.endereco.length}
                //     </p>`;

                break;

            case 'ClientePJ':
                title = objeto.dados.nome_fantasia;
                // htmlColsEspecifico = self.#htmlColsEspecificosClientePF(item);
                // htmlAppend = `<p class="mb-0 text-truncate">
                //         <b>Endereços disponíveis:</b> ${item.dados.endereco.length}
                //     </p>`;

                break;

            default:
                break;
        }


        if (!objeto?.idCol) {

            objeto.idCol = UUIDHelper.generateUUID();
            let strCard = `
                <div id="${objeto.idCol}" class="card p-0" >
                    <div class="card-body">
                        <div class="row">
                            <h5 class="card-title d-flex align-items-center justify-content-between">
                                <span class="spanTitle">${title}</span>
                            </h5>
                            <div class="row">
                                <div class="col">
                                    ${self.#htmlDropDownObjetosRequisitados(objeto)}
                                </div>
                            </div>
                            <div class="rowColsEspecifico row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-2 row-cols-xl-3 row-cols-xxl-4 align-items-end">
                                ${htmlColsEspecifico}
                            </div>
                            <div class="divAppend">
                                ${htmlAppend}
                            </div>
                        </div>
                    </div>
                </div > `;

            divObjetos.append(strCard);
            self.#addEventosObjetos(objeto);
            self._objConfigs.data.objetosNaTela.push(objeto);

        } else {
            $(`#${objeto.idCol} `).find('.spanTitle').html(title);
            $(`#${objeto.idCol} `).find('.rowColsEspecifico').html(htmlColsEspecifico);
            $(`#${objeto.idCol} `).find('.divAppend').html(htmlAppend);

            const indexDoc = self.#pesquisaIndexObjetosNaTela(objeto);
            if (indexDoc != -1) {
                self._objConfigs.data.objetosNaTela[indexDoc] = objeto;
            }
        }

        return true;
    }

    #htmlDropDownObjetosRequisitados(objeto) {
        const self = this;
        let html = '';

        // Adicionar no objeto a propriedade objetos_compativeis
        objeto.objetos_compativeis ??= [];
        objeto.objeto_vinculado = null;

        const objetosMesmoIdentificador = self._objConfigs.data.documento_modelo_tenant.objetos.filter(obj => obj.identificador === objeto.identificador);

        if (objetosMesmoIdentificador.length) {

            objetosMesmoIdentificador.map(obj => {

                obj.idBtn = UUIDHelper.generateUUID();
                html += `<li><button class="dropdown-item fs-6" type="button" id="${obj.idBtn}">${obj.identificador}.${obj.contador}</button></li>`;
                objeto.objetos_compativeis.push(obj);
            });
        } else {

            html = `<li><button class="dropdown-item fs-6" type="button">Nenhum objeto compatível</button></li>`;
        }

        return `
            <div class="btn-group">
                 <button type="button" class="btn btn-outline-primary dropdown-toggle btnDropdownObjeto" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="htmlDropdown" data-default-value="Nenhum objeto vinculado">
                        Nenhum objeto vinculado
                    </span>
                </button>
                <ul class="dropdown-menu">${html}</ul>
            </div>`;
    }

    #addEventosObjetos(objeto) {
        const self = this;

        objeto.objetos_compativeis.map(compativel => {

            $(`#${compativel.idBtn}`).on('click', async () => {
                const btns = $(`${self.getIdModal} .btnDropdownObjeto`);

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
                    // Atualiza o dropdown
                    $(`#${objeto.idCol} .htmlDropdown`).html(`${compativel.identificador}.${compativel.contador}`);

                    // Remove o vínculo caso outro esteja com o mesmo identificador e contador
                    objetosNaTela.map(obj => {
                        if (obj.objeto_vinculado && obj.objeto_vinculado.identificador === compativel.identificador && obj.objeto_vinculado.contador === compativel.contador && obj.idCol != objeto.idCol) {
                            obj.objeto_vinculado = null;

                            // Atualiza o dropdown
                            $(`#${obj.idCol} .htmlDropdown`).html($(`#${obj.idCol} .htmlDropdown`).data('default-value'));
                        }
                    });

                    self.#verificaInconsistenciasObjetosVinculados();

                } catch (error) {
                    commonFunctions.generateNotification(error.message);
                } finally {
                    commonFunctions.simulateLoading(btns, false);
                }
            });
        });
    }

    async #verificaInconsistenciasObjetosVinculados(options = {}) {
        try {
            const self = this;
            let {
                divRevisao = `#divRevisao${self.getSufixo}`,
            } = options;

            divRevisao = $(divRevisao);

            const resultado = await self.#executaVerificacaoInconsistenciasObjetosVinculado({
                objetos_vinculados: self._getObjetosNaTelaVinculados(),
                documento_modelo_tenant_id: self._dataEnvModal.documento_modelo_tenant.id,
            });

            divRevisao.html('');

            // resultado.objetos_nao_utilizados.forEach(item => {
            //     item.uuid = UUIDHelper.generateUUID();
            //     divRevisao.append(`
            //              <div class="alert alert-warning py-1" role="alert">
            //                  <p class="m-0">Um objeto que foi adicionado e não está sendo referenciado: <span class="fw-bolder">${item.nome}</span></p>
            //                  <hr class="my-1">
            //                  <button type="button" id="btnRemoverReferencia${item.uuid}" class="btn btn-outline-primary border-0 btn-sm">
            //                      Remover referencia
            //                  </button>
            //              </div>
            //          `);

            //     $(`#btnRemoverReferencia${item.uuid}`).on('click', async function () {
            //         self.#deleteObjetoNaTela(item);
            //         await self._verificarInconsistenciasObjetos();
            //     });
            // });

            // resultado.marcacoes_sem_referencia.forEach(item => {
            //     item.uuid = UUIDHelper.generateUUID();
            //     divRevisao.append(`
            //              <div class="alert alert-warning py-1" role="alert">
            //                  <p class="m-0">Uma marcação foi adicionada e não possui referência: <a href="#" id="btnVerMarcacao${item.uuid}" class="link-offset-2 link-underline link-underline-opacity-0">${item.marcacao}</a></p>
            //              </div>
            //          `);

            //     $(`#btnVerMarcacao${item.uuid}`).on('click', function () {
            //         self.#selecionarMarcacaoQuill(self.getQuill, item.marcacao, item.indice);
            //     });
            // });

            // resultado.objetos_utilizados.forEach(item => {
            //     item.uuid = UUIDHelper.generateUUID();
            //     let strMarcacaoUsada = item.marcadores_usados.map(m => m.display).join(', ');
            //     divRequisitos.append(`
            //              <div class="alert alert-success py-1" role="alert">
            //                  <p class="m-0">Objeto <span class="fw-bolder">${item.nome}</span></p>
            //                  <hr class="my-1">
            //                  <p class="m-0">Campo(s) usado(s): <span class="fw-bolder">${strMarcacaoUsada}</span>.</p>
            //              </div>
            //          `);
            // });

            return resultado;
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
            return false;
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
        const formRegistration = $(self.getIdModal).find('.formRegistration');
        let data = commonFunctions.getInputsValues(formRegistration[0]);

        if (self.#saveVerifications(data, formRegistration)) {
            self._save(data, self._objConfigs.url.base);
        }
    }

    #saveVerifications(data, formRegistration) {
        let blnSave = commonFunctions.verificationData(data.titulo, { field: formRegistration.find('input[name="titulo"]'), messageInvalid: 'O título deve ser informado.', setFocus: true });
        blnSave = commonFunctions.verificationData(data.descricao, { field: formRegistration.find('textarea[name="descricao"]'), messageInvalid: 'Uma descrição deve ser adicionada.', setFocus: blnSave == true, returnForcedFalse: blnSave == false });
        return blnSave;
    }

}