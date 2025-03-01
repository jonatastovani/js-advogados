import { commonFunctions } from "../commons/commonFunctions";
import { connectAjax } from "../commons/connectAjax";
import { enumAction } from "../commons/enumAction";
import { UUIDHelper } from "../helpers/UUIDHelper";
import { QuillEditorModule } from "./QuillEditorModule";

export class DocumentoModeloQuillEditorModule extends QuillEditorModule {

    _objConfigs;
    _parentInstance;
    _documentoModeloTipoId;

    /**
     * Inicializa o editor Quill.js com suporte a marcações de clientes.
     * @param {string|jQuery} selector - Seletor do elemento onde o editor será aplicado.
     * @param {Object} options - Opções adicionais.
     */
    constructor(parentInstance, objData) {
        super(objData.quillEditor.selector, objData.quillEditor.options); // Chama o construtor da classe pai

        if (!this.getQuill) {
            console.error("Erro: Quill não foi inicializado corretamente.");
            return;
        }

        this._objConfigs = objData.objConfigs;
        this._parentInstance = parentInstance;

        this._objConfigs.quillEditor ??= {};
        this._objConfigs.quillEditor.objetosBase ??= [];
        this._objConfigs.quillEditor.objetosNaTela ??= [];

        // this.adicionarBotoesClientes();
    }

    set setDocumentoModeloTipoId(id) {
        this._documentoModeloTipoId = id;
    }

    get getDocumentoModeloTipoId() {
        return this._documentoModeloTipoId;
    }

    // /**
    //  * Retorna a configuração dos botões personalizados para clientes.
    //  * @returns {Array} Lista de botões a serem adicionados.
    //  */
    // #getBotoesClientesConfig() {
    //     return [
    //         {
    //             title: "Cliente Múltiplo",
    //             icone: '<i class="bi bi-people-fill"></i>',
    //             marcacao: "{{cliente[n].nome}}",
    //         },
    //         {
    //             title: "Cliente 1",
    //             icone: '<i class="bi bi-person-fill"></i>',
    //             marcacao: "{{cliente1.nome}}",
    //         },
    //         {
    //             title: "Cliente 2",
    //             icone: '<i class="bi bi-person-fill"></i>',
    //             marcacao: "{{cliente2.nome}}",
    //         }
    //     ];
    // }

    // /**
    //  * Adiciona botões personalizados para marcações de clientes no editor.
    //  */
    // adicionarBotoesClientes() {
    //     const self = this;

    //     const toolbarContainer = $(".ql-toolbar");
    //     if (!toolbarContainer.length) return;

    //     // Criar grupo de botões
    //     const grupoClientes = $('<span>');
    //     // .addClass('ql-formats');

    //     // Adicionar botões conforme configuração
    //     self.#getBotoesClientesConfig().forEach(botaoConfig => {
    //         const botao = self._criarBotao(botaoConfig);
    //         grupoClientes.append(botao);
    //     });

    //     // Adicionar grupo ao toolbar
    //     toolbarContainer.append(grupoClientes);

    // }

    async addObjetosModelo(modeloId) {
        const self = this;
        try {
            const modelo = await self.#getDocumentoModeloTipo(modeloId);
            self.setDocumentoModeloTipoId = modeloId;

            modelo.objetos.map(objeto => {
                self.#addBotoesDropdowns(objeto);
            });

            // self.addEventRemoverCliente();
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
            return null;
        }
    }

    async #getDocumentoModeloTipo(id) {
        const self = this;
        const objConn = new connectAjax(self._objConfigs.url.baseDocumentoModeloTipo);
        objConn.setParam(id);
        const response = await objConn.getRequest();
        return response.data;
    }

    #addBotoesDropdowns(objeto, options = {}) {
        const self = this;

        objeto.uuid = UUIDHelper.generateUUID();
        objeto.idAccordion = `accordions${objeto.identificador}${self._parentInstance.getSufixo}`;
        self.#addBotaoAdd(objeto, options);
        self.#addBotaoDrop(objeto, options);

        $(`#accordionsObjetos${self._parentInstance.getSufixo}`).append(`<div id="${objeto.idAccordion}"class="accordion mt-2"></div>`);
    }

    #addBotaoAdd(objeto, options = {}) {
        const self = this;
        const {
            dropdownMenuAddObjetos = `#dropdownMenuAddObjetos${self._parentInstance.getSufixo}`,
        } = options;

        let idBtn = `btnAdd${objeto.identificador}${objeto.uuid}`;
        $(dropdownMenuAddObjetos).append(`
            <li>
                <button type="button" id="${idBtn}"
                    class="dropdown-item">
                    ${objeto.display}
                </button>
            </li>`);

        self._objConfigs.quillEditor.objetosBase.push(objeto);

        $(`#${idBtn}`).on('click', async function () {
            const newObjeto = self._getObjetoBase(objeto.identificador);
            newObjeto.contador = self.#getContadorObjetoNaTela(objeto, true);

            self._inserirObjetoNaTela(newObjeto);
            const resultado = self._verificarInconsistenciasObjetos();
        });
    }

    #addBotaoDrop(objeto, options = {}) {
        const self = this;
        const {
            dropdownMenuDropObjetos = `#dropdownMenuDropObjetos${self._parentInstance.getSufixo}`,
        } = options;

        let idBtn = `btnDrop${objeto.identificador}${objeto.uuid}`;
        $(dropdownMenuDropObjetos).append(`
            <li>
                <button type="button" id="${idBtn}"
                    class="dropdown-item">
                    ${objeto.display}
                </button>
            </li>`);

        $(`#${idBtn}`).on('click', async function () {

            const ultimoContador = self.#getContadorObjetoNaTela(objeto);

            if (ultimoContador === 0) {
                commonFunctions.generateNotification(`Nenhum ${objeto.display} para remover.`, 'warning');
                return;
            }

            // Encontrar o último objeto na lista
            const ultimoObjeto = self._objConfigs.quillEditor.objetosNaTela.find(
                (item) => item.contador === ultimoContador && item.identificador === objeto.identificador
            );

            if (!ultimoObjeto) {
                commonFunctions.generateNotification(`Erro ao localizar o objeto ${objeto.display} para remover.`, 'warning');
                console.error('objetosNaTela', self._objConfigs.quillEditor.objetosNaTela);
                console.error('ultimoContador', ultimoContador);
                console.error('ultimoObjeto', ultimoObjeto);
                return;
            }

            // Remover do array de objetos na tela
            self.#deleteObjetoNaTela(ultimoObjeto);

            const resultado = self._verificarInconsistenciasObjetos();
        });
    }

    _inserirObjetoNaTela(item) {
        const self = this;

        $(`#${item.idAccordion}`).append(self.#getHTMLAccordionObjeto(item));
        self.#addEventMarcadoresObjeto(item.marcadores, { btnPrefixo: item.idAccordionNovoObjeto });
        self.#pushContadorObjetosNaTela(item);
    }

    _getObjetoBase(identificador) {
        const self = this;
        const objeto = self._objConfigs.quillEditor.objetosBase.find(item => item.identificador === identificador);
        return objeto ? JSON.parse(JSON.stringify(objeto)) : null;
    }

    #getContadorObjetoNaTela(objeto, addProximo = false) {
        const self = this;
        return self._objConfigs.quillEditor.objetosNaTela.filter(i => i.identificador === objeto.identificador).length + (addProximo ? 1 : 0);
    }

    #getHTMLAccordionObjeto(item) {
        const self = this;
        const contador = item.contador;

        item.idAccordionNovoObjeto = UUIDHelper.generateUUID();
        const sufixo = `${item.identificador}${item.idAccordionNovoObjeto}-${contador}`;

        const btns = self.#renderBtnMarcadoresObjeto(item, { btnPrefixo: item.idAccordionNovoObjeto });
        item.nome = `${item.identificador}.${contador}`;

        return `
            <div class="accordion-item" id="${item.idAccordionNovoObjeto}">
                <div class="accordion-header">
                    <button class="accordion-button py-1 collapsed" type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapse${sufixo}"
                        aria-expanded="true"
                        aria-controls="collapse${sufixo}">
                        <span class="spanClienteNumero">${item.nome}</span>
                    </button>
                </div>
                <div id="collapse${sufixo}"
                    class="accordion-collapse collapse"
                    data-bs-parent="#${item.idAccordion}">
                    <div class="accordion-body">
                        <div class="d-flex g-2 gap-1 d-md-grid d-lg-flex d-xl-grid flex-wrap">
                            ${btns}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    #renderBtnMarcadoresObjeto(item, options) {
        const btnPrefixo = options.btnPrefixo;
        let strBtns = '';

        item.marcadores.map(marcador => {
            marcador.idBtn = `${btnPrefixo}-${marcador.sufixo.replace('.', '-')}`;
            marcador.marcacao = `{{${item.marcador_prefixo.replace('{{contador}}', item.contador)}.${marcador.sufixo}}}`;
            strBtns += `
                <button id="${marcador.idBtn}" type="button" class="btn btn-outline-primary" data-marcacao="${marcador.marcacao}">
                    ${marcador.display}
                </button>`;
        });
        return strBtns;
    }

    #addEventMarcadoresObjeto(marcadores, options) {
        const self = this;
        const btnPrefixo = options.btnPrefixo;

        marcadores.map(marcador => {
            $(`#${marcador.idBtn}`).on('click', async function () {
                self._inserirMarcacaoNoEditor(marcador);
            })
        })
    }

    #pushContadorObjetosNaTela(item) {
        const self = this;
        self._objConfigs.quillEditor.objetosNaTela.push(item);
    }

    #deleteObjetoNaTela(objeto) {
        const self = this;
        // Remover o elemento do DOM
        $(`#${objeto.idAccordionNovoObjeto}`).remove();
        self._objConfigs.quillEditor.objetosNaTela = self._objConfigs.quillEditor.objetosNaTela.filter(i => i.idAccordionNovoObjeto !== objeto.idAccordionNovoObjeto);
    }

    /**
     * Retorna uma lista reduzida dos objetos na tela, contendo apenas as informações essenciais para a requisição.
     * @returns {Array} - Lista de objetos na tela com os campos essenciais.
     */
    _getObjetosNaTela() {
        const self = this;
        return self._objConfigs.quillEditor.objetosNaTela.map(item => ({
            uuid: item.uuid,
            idAccordionNovoObjeto: item.idAccordionNovoObjeto,
            contador: item.contador,
            identificador: item.identificador
        }));
    }

    async _verificarInconsistenciasObjetos(options = {}) {
        try {
            const self = this;
            let {
                divRevisao = `#divRevisao${self._parentInstance.getSufixo}`,
                divRequisitos = `#divRequisitos${self._parentInstance.getSufixo}`,
            } = options;

            divRevisao = $(divRevisao);
            divRequisitos = $(divRequisitos);

            const deltaAtual = self.getQuill.getContents();
            const resultado = await self.#executaVerificacaoInconsistenciasObjetos({
                conteudo: deltaAtual,
                objetos: self._getObjetosNaTela(),
                documento_modelo_tipo_id: self.getDocumentoModeloTipoId,
            });

            divRevisao.html('');
            divRequisitos.html('');

            resultado.objetos_nao_utilizados.forEach(item => {
                item.uuid = UUIDHelper.generateUUID();
                divRevisao.append(`
                     <div class="alert alert-warning py-1" role="alert">
                         <p class="m-0">Um objeto que foi adicionado e não está sendo referenciado: <span class="fw-bolder">${item.nome}</span></p>
                         <hr class="my-1">
                         <button type="button" id="btnRemoverReferencia${item.uuid}" class="btn btn-outline-primary border-0 btn-sm">
                             Remover referencia
                         </button>
                     </div>
                 `);

                $(`#btnRemoverReferencia${item.uuid}`).on('click', async function () {
                    self.#deleteObjetoNaTela(item);
                    await self._verificarInconsistenciasObjetos();
                });
            });

            resultado.marcacoes_sem_referencia.forEach(item => {
                item.uuid = UUIDHelper.generateUUID();
                divRevisao.append(`
                     <div class="alert alert-warning py-1" role="alert">
                         <p class="m-0">Uma marcação foi adicionada e não possui referência: <a href="#" id="btnVerMarcacao${item.uuid}" class="link-offset-2 link-underline link-underline-opacity-0">${item.marcacao}</a></p>
                     </div>
                 `);

                $(`#btnVerMarcacao${item.uuid}`).on('click', function () {
                    self.#selecionarMarcacaoQuill(self.getQuill, item.marcacao, item.indice);
                });
            });

            resultado.objetos_utilizados.forEach(item => {
                item.uuid = UUIDHelper.generateUUID();
                let strMarcacaoUsada = item.marcadores_usados.map(m => m.display).join(', ');
                divRequisitos.append(`
                     <div class="alert alert-success py-1" role="alert">
                         <p class="m-0">Objeto <span class="fw-bolder">${item.nome}</span></p>
                         <hr class="my-1">
                         <p class="m-0">Campo(s) usado(s): <span class="fw-bolder">${strMarcacaoUsada}</span>.</p>
                     </div>
                 `);
            });

            return resultado;
        } catch (error) {
            commonFunctions.generateNotificationErrorCatch(error);
            return false;
        }
    }

    /**
     * Verifica inconsistências nas marcações do texto.
     * @param {Object} data - O objeto contendo o conteúdo do Quill e a lista de clientes.
     * @returns {Object} - Um objeto contendo as marcações sem referência e os objetos não utilizados.
     */
    async #executaVerificacaoInconsistenciasObjetos(data) {
        const self = this;
        const objConn = new connectAjax(self._objConfigs.url.baseDocumentoModeloTenantHelper);
        objConn.setParam(self.getDocumentoModeloTipoId);
        objConn.setAction(enumAction.POST);
        objConn.setData(data);
        const response = await objConn.envRequest();
        return response.data;
    }

    /**
     * Seleciona uma marcação no Quill com base no seu conteúdo e índice esperado.
     * @param {Object} quill - Instância do Quill.
     * @param {string} texto - Texto exato da marcação a ser encontrada.
     * @param {number} indiceEsperado - Índice esperado da marcação (1 para a primeira, 2 para a segunda, etc.).
     */
    #selecionarMarcacaoQuill(quill, texto, indiceEsperado = 1) {
        const self = this;
        const delta = quill.getContents(); // Obtém o conteúdo do editor como Delta
        let indexAtual = 0; // Índice inicial global dentro do Quill
        let ocorrencias = []; // Array para armazenar todas as posições encontradas

        for (const op of delta.ops) {
            if (op.insert && typeof op.insert === "string") {
                let pos = op.insert.indexOf(texto); // Verifica se o texto está presente na string

                while (pos !== -1) {
                    ocorrencias.push({ index: indexAtual + pos, length: texto.length });
                    pos = op.insert.indexOf(texto, pos + 1); // Continua buscando dentro da mesma string
                }
            }

            indexAtual += op.insert.length || 0; // Atualiza o índice global
        }

        if (ocorrencias.length > 0) {
            // Se o índice esperado for maior que o número de ocorrências, seleciona a última encontrada
            const { index, length } = ocorrencias[Math.min(indiceEsperado - 1, ocorrencias.length - 1)];

            // Ativar o painel relevante antes de selecionar
            $(`#painelServico${self._parentInstance.getSufixo}-tab`).trigger("click");

            // Seleciona o texto correto
            quill.setSelection(index, length);
            quill.focus();

            return;
        }

        console.warn("Marcação não encontrada:", texto);
    }
}
