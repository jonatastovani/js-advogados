import { commonFunctions } from "../commons/commonFunctions";
import { modalMessage } from "../components/comum/modalMessage";
import { modalPessoaDocumento } from "../components/pessoas/modalPessoaDocumento";
import { modalSelecionarDocumentoTipo } from "../components/pessoas/modalSelecionarDocumentoTipo";
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

    #addEventosBotoes() {
        const self = this;

        $(`#btnAdicionarDocumento${self._objConfigs.sufixo}`).on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const objModal = new modalSelecionarDocumentoTipo();
                objModal.setDataEnvModal = {
                    pessoa_tipo_aplicavel: self._objConfigs.data.pessoa_tipo_aplicavel,
                };
                objModal.setFocusElementWhenClosingModal = btn;
                const response = await objModal.modalOpen();
                if (response.refresh && response.register) {
                    await self._inserirDocumento(response.register, true);
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading(btn, false);
            }
        });

    }

    #verificaDocumentoQuantidadePermitida(documentoAInserir) {
        const self = this;
        const docsNaTela = self._objConfigs.data.documentosNaTela;

        // Obtém a quantidade permitida para este tipo de documento
        const quantidadePermitida = documentoAInserir.documento_tipo_tenant.configuracao?.quantidade_permitida;

        if (quantidadePermitida) {
            // Filtra os documentos na tela que possuem o mesmo documento_tipo_tenant_id
            const documentosComMesmoTipo = docsNaTela.filter(
                doc => doc.documento_tipo_tenant_id === documentoAInserir.documento_tipo_tenant_id
            );

            // Verifica se ultrapassou o limite permitido
            if (documentosComMesmoTipo.length >= quantidadePermitida) {
                if (quantidadePermitida === 1) {
                    commonFunctions.generateNotification(`Este documento já foi adicionado.`, 'warning');
                    return false;
                } else {
                    commonFunctions.generateNotification(`O limite de ${quantidadePermitida} documentos para este tipo foi atingido.`, 'warning');
                    return false;
                }
            }
        }
        return true;
    }

    async _inserirDocumento(item, validarLimite = false) {
        const self = this;
        const divDocumento = $(`#divDocumento${self._objConfigs.sufixo}`);

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

            divDocumento.append(strCard);
            self.#addEventosDocumento(item);
            self._objConfigs.data.documentosNaTela.push(item);
        } else {
            $(`#${item.idCol}`).find('.spanTitle').html(nomeDoc);
            $(`#${item.idCol}`).find('.pNumero').html(numero);

            const indexDoc = self.#pesquisaIndexDocumentoNaTela(item);
            if (indexDoc != -1) {
                self._objConfigs.data.documentosNaTela[indexDoc] = item;
            }
        }

        return true;
    }

    #pesquisaIndexDocumentoNaTela(item, prop = 'idCol') {
        const self = this;
        return self._objConfigs.data.documentosNaTela.findIndex(doc => doc[prop] === item[prop]);
    }

    async #addEventosDocumento(item) {
        const self = this;

        $(`#${item.idCol}`).find('.btn-edit').on('click', async function () {
            const docNaTela = self._objConfigs.data.documentosNaTela;
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const indexDoc = self.#pesquisaIndexDocumentoNaTela(item);
                if (indexDoc != -1) {
                    const doc = docNaTela[indexDoc];

                    const objModal = new modalPessoaDocumento();
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
                    commonFunctions.generateNotification('Documento na tela não encontrado.', 'error');
                    return false;
                }

            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading(btn, false);
            }
        });

        $(`#${item.idCol}`).find(`.btn-delete`).click(async function () {
            try {
                const docNaTela = self._objConfigs.data.documentosNaTela;
                const indexDoc = self.#pesquisaIndexDocumentoNaTela(item);
                if (indexDoc != -1) {
                    const doc = docNaTela[indexDoc] = item;

                    const objMessage = new modalMessage();
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
                commonFunctions.generateNotificationErrorCatch(error);
            }
        });
    }

    _retornaDocumentosNaTelaSaveButonAction() {
        const self = this;
        return self._objConfigs.data.documentosNaTela.map(item => {
            return {
                id: item.id,
                documento_tipo_tenant_id: item.documento_tipo_tenant_id,
                numero: item.numero,
                // campos_adicionais: item.campos_adicionais,
            }
        });
    }
}