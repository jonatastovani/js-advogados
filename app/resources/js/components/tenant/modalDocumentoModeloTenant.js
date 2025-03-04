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
        },
        quillEditor: {
            objetosRequisitados: [],
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
            self._objConfigs.quillEditor.objetosRequisitados = response.data;
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

    async #inserirObjeto(item) {
        const self = this;
        const divObjetos = $(`#divObjetos${self._objConfigs.sufixo}`);

        let title = '';
        let htmlColsEspecifico = '';
        let htmlAppend = '';

        switch (item.identificador) {

            case 'ClientePF':
                title = item.dados.nome;
                htmlColsEspecifico = self.#htmlColsEspecificosClientePF(item);
                htmlAppend = `<p class="mb-0 text-truncate">
                        <b>Endereços disponíveis:</b> ${item.dados.endereco.length}
                    </p>`;

                break;

            case 'ClientePJ':
                title = item.dados.nome_fantasia;
                htmlColsEspecifico = self.#htmlColsEspecificosClientePF(item);
                htmlAppend = `<p class="mb-0 text-truncate">
                        <b>Endereços disponíveis:</b> ${item.dados.endereco.length}
                    </p>`;

                break;

            default:
                break;
        }


        if (!item?.idCol) {

            item.idCol = UUIDHelper.generateUUID();
            let strCard = `
                <div id="${item.idCol}" class="card p-0" >
                    <div class="card-body">
                        <div class="row">
                            <h5 class="card-title d-flex align-items-center justify-content-between">
                                <span class="spanTitle">${title}</span>
                                <div>
                                    <div class="dropdown">
                                        <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><button type="button" class="dropdown-item fs-6 btn-edit" title="Editar endereço">Editar</button></li>
                                            <li><button type="button" class="dropdown-item fs-6 btn-delete" title="Excluir endereço">Excluir</button></li>
                                        </ul>
                                    </div>
                                </div>
                            </h5>
                            <div class="row">
                                <div class="col">
                                    <p class="mb-0 text-truncate">
                                        Nenhum objeto vinculado
                                    </p>
                                </div>
                                <div class="col">
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
            // self.#addEventosEndereco(item);
            self._objConfigs.data.objetosNaTela.push(item);

        } else {
            $(`#${item.idCol} `).find('.spanTitle').html(title);
            $(`#${item.idCol} `).find('.rowColsEspecifico').html(htmlColsEspecifico);
            $(`#${item.idCol} `).find('.divAppend').html(htmlAppend);

            const indexDoc = self.#pesquisaIndexObjetosNaTela(item);
            if (indexDoc != -1) {
                self._objConfigs.data.objetosNaTela[indexDoc] = item;
            }
        }

        return true;
    }

    #htmlDropDownObjetosRequisitados() {
        const self = this;
        let html = '';

        self._objConfigs.quillEditor.objetosRequisitados.map(item => {
            html += `li><button class="dropdown-item" type="button">Action</button></li>`;
        });

        return `
            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    Objetos Requisitados
                </button>
                <ul class="dropdown-menu">${html}</ul>
            </div>`;
    }

    #htmlColsEspecificosClientePF(item) {
        const self = this;
        let htmlColsEspecifico = '';

        return '';

        if (item?.complemento) {
            htmlColsEspecifico += `
                    <div div class="col" >
                    <div class="form-text mt-0">Complemento</div>
                    <p class="text-truncate" title="${item.complemento}">${item.complemento}</p>
                </div > `;
        }

        if (item?.bairro) {
            htmlColsEspecifico += `
                    <div div class="col" >
                    <div class="form-text mt-0">Bairro</div>
                    <p class="text-truncate" title="${item.bairro}">${item.bairro}</p>
                </div > `;
        }

        if (item.referencia) {
            htmlColsEspecifico += `
                    <div div class="col" >
                    <div class="form-text mt-0">Referência</div>
                    <p class="text-truncate" title="${item.referencia}">${item.referencia}</p>
                </div > `;
        }

        if (item.cidade) {
            htmlColsEspecifico += `
                    <div div class="col" >
                    <div class="form-text mt-0">Cidade</div>
                    <p class="text-truncate" title="${item.cidade}">${item.cidade}</p>
                </div > `;
        }

        if (item.entrada_valor) {
            const valorEntrada = commonFunctions.formatWithCurrencyCommasOrFraction(item.entrada_valor);
            htmlColsEspecifico += `
                    <div div class="col" >
                    <div class="form-text mt-0">Valor Entrada</div>
                    <p class="">${valorEntrada}</p>
                </div > `;
        }

        if (item.estado) {
            htmlColsEspecifico += `
                    <div div class="col" >
                    <div class="form-text mt-0">Estado</div>
                    <p class="text-truncate" title="${item.estado}">${item.estado}</p>
                </div > `;
        }

        if (item.parcela_data_inicio) {
            htmlColsEspecifico += `
                    <div div class="col" >
                    <div class="form-text mt-0">Primeira Parcela</div>
                    <p class="">${DateTimeHelper.retornaDadosDataHora(item.parcela_data_inicio, 2)}</p>
                </div > `;
        }

        if (item.pais) {
            htmlColsEspecifico += `
                    <div div class="col" >
                    <div class="form-text mt-0">País</div>
                    <p class="text-truncate" title="${item.pais}">${item.pais}</p>
                </div > `;
        }

        if (item.cep) {
            const cep = MasksAndValidateHelpers.formatCep(item.cep);
            htmlColsEspecifico += `
                    <div div class="col" >
                    <div class="form-text mt-0">CEP</div>
                    <p class="text-truncate" title="${cep}">${cep}</p>
                </div > `;
        }
        return htmlColsEspecifico;
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