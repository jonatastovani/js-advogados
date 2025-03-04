import { commonFunctions } from "../commons/commonFunctions";
import { connectAjax } from "../commons/connectAjax";
import { enumAction } from "../commons/enumAction";
import instanceManager from "../commons/instanceManager";
import { modalEndereco } from "../components/comum/modalEndereco";
import { modalMessage } from "../components/comum/modalMessage";
import { MasksAndValidateHelpers } from "../helpers/MasksAndValidateHelpers";
import { UUIDHelper } from "../helpers/UUIDHelper";
import { QueueManager } from "../utils/QueueManager";
import { QuillEditorModule } from "./QuillEditorModule";

export class DocumentoModeloTenantRenderModule {

    _objConfigs;
    _parentInstance;
    _extraConfigs;

    _quillQueueManager;

    constructor(parentInstance, objData) {
        this._objConfigs = objData.objConfigs;
        this._parentInstance = parentInstance;
        this._extraConfigs = objData.extraConfigs;

        this._quillQueueManager = new QueueManager();  // Cria a fila

        this.#addEventosBotoes();
    }

    #addEventosBotoes() {
        const self = this;

        const instanceName = `QuillEditor${self._parentInstance.getSufixo}`;
        /** @type {QuillEditorModule} */
        self._classQuillEditor = instanceManager.instanceVerification(instanceName);
        if (!self._classQuillEditor) {
            self._classQuillEditor = instanceManager.setInstance(instanceName, new QuillEditorModule(`#conteudo${self._parentInstance.getSufixo}`, { exclude: ['image', 'scriptSub', 'scriptSuper', 'code', 'link'] }));
        }

        self._classQuillEditor.getQuill.setContents([]);
        self._quillQueueManager.setReady();  // Informa que o quill está pronto
    }

    async _inserirTodosObjetos(objetos) {
        const self = this;
        try {
            // console.log('objetos enviados', objetos);
            const objConn = new connectAjax(`${self._objConfigs.url.baseDocumentoModeloTenantHelper}/render-objetos`);
            objConn.setData({ objetos });
            objConn.setAction(enumAction.POST);
            const response = await objConn.envRequest();
            response.data.map(async (objeto) => {
                console.log(`Objeto:`, objeto);
                await self._inserirObjeto(objeto);
            });
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
        }
    }

    async _inserirObjeto(item) {
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
                    <div div id = "${item.idCol}" class="card p-0" >
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
            self.#addEventosEndereco(item);
            self._objConfigs.data.enderecosNaTela.push(item);
        } else {
            $(`#${item.idCol} `).find('.spanTitle').html(title);
            $(`#${item.idCol} `).find('.rowColsEspecifico').html(htmlColsEspecifico);
            $(`#${item.idCol} `).find('.divAppend').html(htmlAppend);

            const indexDoc = self.#pesquisaIndexEnderecoNaTela(item);
            if (indexDoc != -1) {
                self._objConfigs.data.enderecosNaTela[indexDoc] = item;
            }
        }

        return true;
    }

    async #verificaEnderecoRepetido(enderecoAInserir) {
        const self = this;
        const enderecosNaTela = self._objConfigs.data.enderecosNaTela;

        let blnInserir = true;
        for (let i = 0; i < enderecosNaTela.length; i++) {
            const endereco = enderecosNaTela[i];

            if (endereco.logradouro === enderecoAInserir.logradouro &&
                endereco.numero === enderecoAInserir.numero &&
                endereco.cidade === enderecoAInserir.cidade &&
                (endereco?.id && endereco.id != enderecoAInserir.id ||
                    endereco.idCol && endereco.idCol != enderecoAInserir.idCol)
            ) {
                // Chamando um modal para comparar os dois endereços
                blnInserir = await self.#abrirModalConfirmacaoEndereco(endereco, enderecoAInserir);
            }

            // Se for false, retornar imediatamente.
            if (!blnInserir) { return false; }
        }

        return blnInserir;
    }

    async #abrirModalConfirmacaoEndereco(enderecoRepetido, enderecoAInserir) {
        const self = this;

        const renderEndereco = (item) => {
            let strEndereco = `${item.logradouro}, ${item.numero} `
            item.complemento ? strEndereco += `, ${item.complemento} ` : '';
            item.bairro ? strEndereco += `, ${item.bairro} ` : '';
            item.referencia ? strEndereco += `, ${item.referencia} ` : '';
            item.cidade ? strEndereco += ` - ${item.cidade} ` : '';
            item.estado ? strEndereco += `- ${item.estado} ` : '';
            item.pais ? strEndereco += `, ${item.pais} ` : '';
            item.cep ? strEndereco += `, CEP ${item.cep} ` : '';
            return strEndereco;
        }

        const endereco1 = renderEndereco(enderecoRepetido);
        const endereco2 = renderEndereco(enderecoAInserir);

        const objModal = new modalMessage();
        objModal.setDataEnvModal = {
            title: 'Possibilidade de Endereço Repetido',
            message: `Verifique a possibilidade de um endereço repetido:
                <p class="py-2">Endereço existente: <span class="fst-italic fw-bold">${endereco1}</span></p>
                <p>Endereço a ser inserido: <span class="fst-italic fw-bold">${endereco2}</span></p>
                <p>Deseja inserir o novo endereço?</p>`,
        };
        const result = await objModal.modalOpen();
        return result.confirmResult ?? false;
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

    #htmlAppendEndereco(item) {
        let htmlAppend = '';

        if (item.observacao) {
            htmlAppend += `
                    <p p class="mb-0 text-truncate" title = "${item.observacao}" >
                        <b>Observação:</b> ${item.observacao}
            </p > `;
        }

        return htmlAppend;
    }

    #pesquisaIndexEnderecoNaTela(item, prop = 'idCol') {
        const self = this;
        return self._objConfigs.data.enderecosNaTela.findIndex(doc => doc[prop] === item[prop]);
    }

    async #addEventosEndereco(item) {
        const self = this;

        $(`#${item.idCol} `).find('.btn-edit').on('click', async function () {
            const docNaTela = self._objConfigs.data.enderecosNaTela;
            const btn = $(this);
            commonFunctions.simulateLoading(btn);
            try {
                const indexDoc = self.#pesquisaIndexEnderecoNaTela(item);
                if (indexDoc != -1) {
                    const doc = docNaTela[indexDoc];

                    const objModal = new modalEndereco();
                    objModal.setDataEnvModal = {
                        register: doc,
                    };
                    const response = await objModal.modalOpen();
                    if (response.refresh && response.register) {
                        await self._inserirEndereco(response.register);
                    }
                } else {
                    console.error('Endereço na tela não encontrado. Docs:', docNaTela);
                    console.error('Item buscado:', item);
                    commonFunctions.generateNotification('Endereço na tela não encontrado.', 'error');
                    return false;
                }

            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading(btn, false);
            }
        });

        $(`#${item.idCol} `).find(`.btn - delete `).on('click', async function () {
            try {
                const docNaTela = self._objConfigs.data.enderecosNaTela;
                const indexDoc = self.#pesquisaIndexEnderecoNaTela(item);
                if (indexDoc != -1) {
                    const doc = docNaTela[indexDoc] = item;

                    const objMessage = new modalMessage();
                    objMessage.setDataEnvModal = {
                        title: `Exclusão de Endereço`,
                        message: `Confirma a exclusão do endereço <b b > ${doc.logradouro}, ${doc.numero}</b >? `,
                    };
                    objMessage.setFocusElementWhenClosingModal = $(this);
                    const result = await objMessage.modalOpen();
                    if (result.confirmResult) {
                        docNaTela.splice(indexDoc, 1);
                        $(`#${doc.idCol} `).remove();
                    }
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            }
        });
    }

    _retornaEnderecosNaTelaSaveButonAction() {
        const self = this;
        return self._objConfigs.data.enderecosNaTela;
    }
}