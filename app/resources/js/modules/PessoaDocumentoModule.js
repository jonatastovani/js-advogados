import { CommonFunctions } from "../commons/CommonFunctions";
import { ModalMessage } from "../components/comum/ModalMessage";
import { ModalPessoaDocumento } from "../components/pessoas/ModalPessoaDocumento";
import { ModalSelecionarDocumentoTipo } from "../components/pessoas/ModalSelecionarDocumentoTipo";
import { UUIDHelper } from "../helpers/UUIDHelper";

export class PessoaDocumentoModule {

    _objConfigs;
    _parentInstance;
    _extraConfigs;

    constructor(parentInstance, objData) {
        this._objConfigs = objData.objConfigs;
        this._parentInstance = parentInstance;
        this._extraConfigs = objData.extraConfigs;
        this.#addEventosBotoes();
    }

    //#region Getters e Setters

    set setDocumentosNaTela(documentosNaTela) {
        // Inicializa a estrutura se não existir
        if (!this._objConfigs.data) this._objConfigs.data = {};
        // Seta o valor mantendo a referência
        this._objConfigs.data.documentosNaTela = documentosNaTela;
    }

    get getDocumentosNaTela() {
        // Inicializa se não existir e retorna a referência
        if (!this._objConfigs.data) this._objConfigs.data = {};
        if (!this._objConfigs.data.documentosNaTela) this._objConfigs.data.documentosNaTela = [];
        return this._objConfigs.data.documentosNaTela;
    }

    //#endregion

    #addEventosBotoes() {
        const self = this;

        $(`#btnAdicionarDocumento${self._objConfigs.sufixo}`).on('click', async function () {
            const btn = $(this);
            CommonFunctions.simulateLoading(btn);
            try {
                const objModal = new ModalSelecionarDocumentoTipo();
                objModal.setDataEnvModal = {
                    pessoa_tipo_aplicavel: self._objConfigs.data.pessoa_tipo_aplicavel,
                };
                objModal.setFocusElementWhenClosingModal = btn;
                const response = await objModal.modalOpen();
                if (response.refresh && response.register) {
                    await self._inserirDocumento(response.register, true);
                }
            } catch (error) {
                CommonFunctions.generateNotificationErrorCatch(error);
            } finally {
                CommonFunctions.simulateLoading(btn, false);
            }
        });

    }

    #verificaDocumentoQuantidadePermitida(documentoAInserir) {
        const self = this;
        const docsNaTela = self.getDocumentosNaTela;

        // Obtém a quantidade permitida para este tipo de documento
        const quantidadePermitida = documentoAInserir.documento_tipo_tenant.quantidade_permitida;

        if (quantidadePermitida) {
            // Filtra os documentos na tela que possuem o mesmo documento_tipo_tenant_id
            const documentosComMesmoTipo = docsNaTela.filter(
                doc => doc.documento_tipo_tenant_id === documentoAInserir.documento_tipo_tenant_id
            );

            // Verifica se ultrapassou o limite permitido
            if (documentosComMesmoTipo.length >= quantidadePermitida) {
                CommonFunctions.generateNotification(
                    quantidadePermitida == 1 ?
                        `Este documento já foi adicionado.` :
                        `O limite de ${quantidadePermitida} documentos para este tipo foi atingido.`, 'warning');
                return false;
            }
        }
        return true;
    }

    async _inserirDocumento(item, validarLimite = false) {
        const self = this;
        const divDocumentoPessoa = $(`#divDocumentoPessoa${self._objConfigs.sufixo}`);

        const nomeDoc = item.documento_tipo_tenant.documento_tipo.nome;
        const numero = item.numero;
        let camposAdicionais = '';

        if (!item?.idCol) {
            if (validarLimite) {
                if (!self.#verificaDocumentoQuantidadePermitida(item)) {
                    return false;
                }
            }

            item.idCol = UUIDHelper.generateUUID();

            let strCard = `
            <div id="${item.idCol}" class="col">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title d-flex align-items-center justify-content-between mb-0">
                            <span class="text-truncate spanTitle">${nomeDoc}</span>
                            <div>
                                <div class="dropdown">
                                    <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><button type="button" class="dropdown-item fs-6 btn-edit" title="Editar documento ${nomeDoc}">Editar</button></li>
                                        <li><button type="button" class="dropdown-item fs-6 btn-delete" title="Excluir documento ${nomeDoc}">Excluir</button></li>
                                    </ul>
                                </div>
                            </div>
                        </h5>
                        <p class="card-text pNumero">${numero}</p>
                        ${camposAdicionais}
                    </div>
                </div>
            </div>`;

            divDocumentoPessoa.append(strCard);
            self.#addEventosDocumento(item);
            self.getDocumentosNaTela.push(item);
        } else {
            $(`#${item.idCol}`).find('.spanTitle').html(nomeDoc);
            $(`#${item.idCol}`).find('.pNumero').html(numero);

            const indexDoc = self.#pesquisaIndexDocumentoNaTela(item);
            if (indexDoc != -1) {
                self.getDocumentosNaTela[indexDoc] = item;
            }
        }

        return true;
    }

    #pesquisaIndexDocumentoNaTela(item, prop = 'idCol') {
        const self = this;
        return self.getDocumentosNaTela.findIndex(doc => doc[prop] === item[prop]);
    }

    async #addEventosDocumento(item) {
        const self = this;

        $(`#${item.idCol}`).find('.btn-edit').on('click', async function () {
            const docNaTela = self.getDocumentosNaTela;
            const btn = $(this);
            CommonFunctions.simulateLoading(btn);
            try {
                const indexDoc = self.#pesquisaIndexDocumentoNaTela(item);
                if (indexDoc != -1) {
                    const doc = docNaTela[indexDoc];

                    const objModal = new ModalPessoaDocumento();
                    objModal.setDataEnvModal = {
                        register: doc,
                    };
                    const response = await objModal.modalOpen();
                    if (response.refresh && response.register) {
                        await self._inserirDocumento(response.register);
                    }
                } else {
                    console.error('Documento na tela não encontrado. Docs:', docNaTela);
                    console.error('Item buscado:', item);
                    CommonFunctions.generateNotification('Documento na tela não encontrado.', 'error');
                    return false;
                }

            } catch (error) {
                CommonFunctions.generateNotificationErrorCatch(error);
            } finally {
                CommonFunctions.simulateLoading(btn, false);
            }
        });

        $(`#${item.idCol}`).find(`.btn-delete`).click(async function () {
            try {
                const docNaTela = self.getDocumentosNaTela;
                const indexDoc = self.#pesquisaIndexDocumentoNaTela(item);
                if (indexDoc != -1) {
                    const doc = docNaTela[indexDoc] = item;

                    const objMessage = new ModalMessage();
                    objMessage.setDataEnvModal = {
                        title: `Exclusão de Documento`,
                        message: `Confirma a exclusão do documento <b>${doc.documento_tipo_tenant.nome}</b>?`,
                    };
                    objMessage.setFocusElementWhenClosingModal = $(this);
                    const result = await objMessage.modalOpen();
                    if (result.confirmResult) {
                        docNaTela.splice(indexDoc, 1);
                        $(`#${doc.idCol}`).remove();
                    }
                }
            } catch (error) {
                CommonFunctions.generateNotificationErrorCatch(error);
            }
        });
    }

    _retornaDocumentosNaTelaSaveButonAction() {
        const self = this;
        const copia = CommonFunctions.clonePure(self.getDocumentosNaTela);
        if (!Array.isArray(copia)) return [];

        return copia.map(i => {
            delete i.documento_tipo_tenant;
            delete i.documento_tipo;
            return i;
        });
    }
}