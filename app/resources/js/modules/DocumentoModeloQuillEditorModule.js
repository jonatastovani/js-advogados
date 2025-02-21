import { commonFunctions } from "../commons/commonFunctions";
import { UUIDHelper } from "../helpers/UUIDHelper";
import { QuillEditorModule } from "./QuillEditorModule";

export class DocumentoModeloQuillEditorModule extends QuillEditorModule {

    _objConfigs;
    _parentInstance;

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
        this._objConfigs.quillEditor.clientesNaTela ??= [];

        // this.adicionarBotoesClientes();
    }

    /**
     * Retorna a configuração dos botões personalizados para clientes.
     * @returns {Array} Lista de botões a serem adicionados.
     */
    #getBotoesClientesConfig() {
        return [
            {
                title: "Cliente Múltiplo",
                icone: '<i class="bi bi-people-fill"></i>',
                marcacao: "{{cliente[n].nome}}",
            },
            {
                title: "Cliente 1",
                icone: '<i class="bi bi-person-fill"></i>',
                marcacao: "{{cliente1.nome}}",
            },
            {
                title: "Cliente 2",
                icone: '<i class="bi bi-person-fill"></i>',
                marcacao: "{{cliente2.nome}}",
            }
        ];
    }

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

    addEventClientes() {
        const self = this;
        self.addEventAdicionarCliente();
        self.addEventRemoverCliente();
    }

    addEventAdicionarCliente(options = {}) {
        const self = this;
        const {
            selector = `#btnAdicionarClientePF${self._parentInstance.getSufixo}, #btnAdicionarClientePJ${self._parentInstance.getSufixo}`,
        } = options;

        $(selector).on('click', async function () {
            const dataPessoaTipo = $(this).data('pessoa-tipo');
            self._inserirClienteNaTela({ cliente_contador: self.#getContadorClienteNaTela(true), pessoa_tipo: dataPessoaTipo });
            const resultado = self._verificarInconsistencias();
        });
    }

    addEventRemoverCliente(options = {}) {
        const self = this;
        const {
            selector = `#btnRemoverCliente${self._parentInstance.getSufixo}`,
        } = options;

        $(selector).on('click', async function () {
            // Obtém o maior número de cliente na tela
            const ultimoContador = self.#getContadorClienteNaTela();

            if (ultimoContador === 0) {
                commonFunctions.generateNotification("Nenhum cliente para remover.", 'info');
                return;
            }

            // Encontrar o último cliente na lista
            const ultimoCliente = self._objConfigs.quillEditor.clientesNaTela.find(
                (item) => item.cliente_contador === ultimoContador
            );

            if (!ultimoCliente) {
                console.error("Erro ao localizar o cliente para remover.");
                return;
            }

            // Remover do array clientesNaTela
            self.#deleteContadorClienteNaTela(ultimoContador);

            // Remover o elemento do DOM
            $(`#${ultimoCliente.idAccordion}`).remove();
            const resultado = self._verificarInconsistencias();
        });
    }

    _inserirClienteNaTela(item) {
        const self = this;

        item.marcadores ??= {};

        item.marcadores = self.#marcadoresEsperadosCliente(item);
        $(`#accordionsCliente${self._parentInstance.getSufixo}`).append(self.#getHTMLAccordionCliente(item));
        self.#addEventMarcadores(item.marcadores, { id: item.idAccordion });
        self.#pushContadorClienteNaTela(item);
    }

    #getHTMLAccordionCliente(item) {
        const self = this;
        const contador = item.cliente_contador;
        item.idAccordion = UUIDHelper.generateUUID();
        const sufixo = `cliente${item.idAccordion}-${contador}`;

        const pfPj = item.pessoa_tipo;
        const btns = self.#renderBtnMarcadores(item.marcadores, { id: item.idAccordion });
        item.nome = `Cliente${pfPj}.${contador}`;

        return `
            <div class="accordion-item">
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
                    data-bs-parent="#accordionsCliente${self._parentInstance.getSufixo}" data-tipo="cliente">
                    <div class="accordion-body">
                        <div class="d-flex gap-2 d-md-grid d-lg-flex g-2 gap-xl-0 flex-wrap">
                            ${btns}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    #addEventMarcadores(marcadores, options) {
        const self = this;
        const id = options.id;

        marcadores.map(item => {
            // console.log(item)
            $(`#${id}-${item.sufixo}`).on('click', async function () {
                self._inserirMarcacaoNoEditor(item);
            })
        })
    }

    #getContadorClienteNaTela(addProximo = false) {
        const self = this;
        return self._objConfigs.quillEditor.clientesNaTela.length + (addProximo ? 1 : 0);
    }

    #pushContadorClienteNaTela(item) {
        const self = this;
        self._objConfigs.quillEditor.clientesNaTela.push(item);
    }

    #deleteContadorClienteNaTela(contador) {
        const self = this;
        self._objConfigs.quillEditor.clientesNaTela = self._objConfigs.quillEditor.clientesNaTela.filter(i => i.cliente_contador !== contador);
    }

    #renderBtnMarcadores(marcadores, options = {}) {
        const self = this;
        const id = options.id;
        let strBtns = '';

        marcadores.map(item => {
            strBtns += `
                <button id="${id}-${item.sufixo}" type="button" class="btn btn-outline-primary" data-marcacao="${item.marcacao}">
                    ${item.display}
                </button>`;
        });
        return strBtns;
    }

    #marcadoresEsperadosCliente(cliente) {
        const prefixo = 'cliente';
        const arrayOpcoes = [];

        switch (cliente.pessoa_tipo) {
            case 'PF':
                arrayOpcoes.push(...this.#camposPessoaPF());
                break;
            case 'PJ':
                arrayOpcoes.push(...this.#camposPessoaPJ());
                break;
        }
        arrayOpcoes.push(...this.#camposEndereco());

        return arrayOpcoes.map(item => {
            item.sufixo = item.marcacao;
            item.marcacao = `{{${prefixo}${cliente.pessoa_tipo}.${cliente.cliente_contador}.${item.marcacao}}}`;
            return item;
        });
    }

    #camposPessoaPF() {
        return [
            { display: 'Nome', marcacao: 'nome', },
            { display: 'Nacionalidade', marcacao: 'nacionalidade', },
            { display: 'Estado Civil', marcacao: 'estado_civil', },
            { display: 'Profissão', marcacao: 'profissao', },
            { display: 'RG', marcacao: 'rg', },
            { display: 'CPF', marcacao: 'cpf', },
        ];
    }

    #camposPessoaPJ() {
        return [
            { display: 'Razão Social', marcacao: 'razao_social', },
            { display: 'Nome Fantasia', marcacao: 'nome_fantasia', },
            { display: 'Natureza Jurídica', marcacao: 'natureza_juridica', },
            { display: 'Data de Fundação', marcacao: 'data_fundacao', },
            { display: 'Capital Social', marcacao: 'capital_social', },
            { display: 'Regime Tributário', marcacao: 'regime_tributario', },
            { display: 'Responsável Legal', marcacao: 'responsavel_legal', },
            { display: 'CPF Responsável', marcacao: 'cpf_responsavel', },
        ];
    }

    #camposEndereco() {
        return [
            { display: 'Logradouro', marcacao: 'logradouro', },
            { display: 'Número', marcacao: 'numero', },
            { display: 'Complemento', marcacao: 'complemento', },
            { display: 'Bairro', marcacao: 'bairro', },
            { display: 'Referência', marcacao: 'referencia', },
            { display: 'Cidade', marcacao: 'cidade', },
            { display: 'Estado', marcacao: 'estado', },
            { display: 'CEP', marcacao: 'cep', },
            { display: 'País', marcacao: 'pais', },
        ];
    }

    _getClientesNaTela() {
        const self = this;
        return self._objConfigs.quillEditor.clientesNaTela;
    }

    _verificarInconsistencias(options = {}) {
        const self = this;
        let {
            divRevisao = `#divRevisao${self._parentInstance.getSufixo}`,
            divRequisitos = `#divRequisitos${self._parentInstance.getSufixo}`,
        } = options;

        divRevisao = $(divRevisao);
        divRequisitos = $(divRequisitos);

        const deltaAtual = self.getQuill.getContents();
        const resultado = self.#executaVerificacaoInconsistencias({
            descricao: deltaAtual,
            clientes: self._getClientesNaTela()
        });

        console.log(resultado);
        divRevisao.html('');
        divRequisitos.html('');
        if (resultado.objetosNaoUtilizados) {
            resultado.objetosNaoUtilizados.forEach(item => {
                item.uuid = UUIDHelper.generateUUID();
                divRevisao.append(`
                    <div class="alert alert-warning py-1" role="alert">
                        <p class="m-0">Um objeto que foi adicionado e não está sendo referenciado: <span class="fw-bolder">${item.nome}</span></p>
                        <hr class="my-1">
                        <button type="button" id="btnVerMarcacao${item.uuid}" class="btn btn-outline-primary border-0 btn-sm">
                            Ver marcação
                        </button>
                    </div>
                `);
            });
        }

        if (resultado.marcacoesSemReferencia) {
            resultado.marcacoesSemReferencia.forEach(item => {
                item.uuid = UUIDHelper.generateUUID();
                divRevisao.append(`
                    <div class="alert alert-warning py-1" role="alert">
                        <p class="m-0">Uma marcação foi adicionada e não possui referência: <span class="fw-bolder">${item.marcacao}</span></p>
                        <hr class="my-1">
                        <button type="button" id="btnVerMarcacao${item.uuid}" class="btn btn-outline-primary border-0 btn-sm">
                            Ver marcação
                        </button>
                    </div>
                `);

                $(`#btnVerMarcacao${item.uuid}`).on('click', function () {
                    self.#selecionarMarcacaoNoQuill(self.getQuill, item.marcacao, item.indice);
                });
            });
        }

        if (resultado.objetosUtilizados) {
            resultado.objetosUtilizados.forEach(item => {
                item.uuid = UUIDHelper.generateUUID();
                let strMarcacaoUsada = item.marcadores_usados.map(m => m.display).join(', ');
                divRequisitos.append(`
                    <div class="alert alert-success py-1" role="alert">
                        <p class="m-0">Objeto adicionado: <span class="fw-bolder">${item.nome}</span></p>
                        <hr class="my-1">
                        <p class="m-0">Marcações usadas: <span class="fw-bolder">${strMarcacaoUsada}</span>.</p>
                    </div>
                `);
            });

        }

        return resultado;
    }

    /**
     * Verifica inconsistências nas marcações do texto.
     * @param {Object} data - O objeto contendo o conteúdo do Quill e a lista de clientes.
     * @returns {Object} - Um objeto contendo as marcações sem referência e os objetos não utilizados.
     */
    #executaVerificacaoInconsistencias(data) {
        const self = this;
        const { descricao, clientes } = data;

        // Coletar todas as marcações presentes no texto do Quill
        const marcacoesNoTexto = self.#extrairMarcacoes(descricao.ops);

        // Coletar todas as marcações válidas da lista de clientes
        const marcacoesValidas = self.#extrairMarcacoesValidasClientes(clientes);

        // Criar um mapa para contar quantas vezes cada marcação aparece
        const contadorMarcacoes = {};

        // Criar array com marcações sem referência e seus índices
        const marcacoesSemReferencia = [];

        marcacoesNoTexto.forEach(marcacao => {
            if (!marcacoesValidas.includes(marcacao)) {
                if (!contadorMarcacoes[marcacao]) {
                    contadorMarcacoes[marcacao] = 1;
                } else {
                    contadorMarcacoes[marcacao]++;
                }

                // Adicionar ao array com índice da repetição
                marcacoesSemReferencia.push({
                    marcacao: marcacao,
                    indice: contadorMarcacoes[marcacao] // Índice baseado na repetição da marcação
                });
            }
        });

        // Verificar objetos não utilizados
        const objetosNaoUtilizados = self.#verificarObjetosNaoUtilizados(marcacoesNoTexto, clientes);
        const objetosUtilizados = self.#verificarObjetosUtilizados(marcacoesNoTexto, clientes);

        return {
            marcacoesSemReferencia,
            objetosNaoUtilizados,
            objetosUtilizados
        };
    }

    /**
     * Verifica inconsistências nas marcações do texto.
     * @param {Object} data - O objeto contendo o conteúdo do Quill e a lista de clientes.
     * @returns {Object} - Um objeto contendo as marcações sem referência, os objetos não utilizados e os objetos utilizados com suas marcações.
     */
    #executaVerificacaoVariaveis(data) {
        const self = this;
        const { descricao, clientes } = data;

        // Coletar todas as marcações presentes no texto do Quill
        const marcacoesNoTexto = self.#extrairMarcacoes(descricao.ops);

        // Coletar todas as marcações válidas da lista de clientes
        const marcacoesValidas = self.#extrairMarcacoesValidasClientes(clientes);

        // Criar estrutura para contagem e organização
        const contadorMarcacoes = {};
        const marcacoesSemReferencia = [];
        const objetosUtilizados = {};
        const objetosNaoUtilizados = [];

        // Criar um mapa para facilitar a busca de objetos a partir do cliente_contador
        const mapaClientes = clientes.reduce((map, cliente) => {
            // map[`clientePF.${cliente.cliente_contador}`] = cliente;
            map[cliente.nome] = cliente;
            return map;
        }, {});

        // Verificar marcações no texto e registrar informações
        marcacoesNoTexto.forEach(marcacao => {
            if (!marcacoesValidas.includes(marcacao)) {
                // Marcações sem referência
                contadorMarcacoes[marcacao] = (contadorMarcacoes[marcacao] || 0) + 1;

                marcacoesSemReferencia.push({
                    marcacao: marcacao,
                    indice: contadorMarcacoes[marcacao] // Índice baseado na repetição da marcação
                });
            } else {
                // Extraímos o objeto e seu marcador (Exemplo: "{{clientePF.1.nome}}" → "clientePF.1" e "nome")
                const match = marcacao.match(/{{(.*?)\.(.*?)}}/);
                if (match) {
                    const objetoNome = match[1]; // Exemplo: "clientePF.1"
                    const marcador = match[2]; // Exemplo: "nome"

                    // Verifica se o objeto existe na lista de clientes
                    if (mapaClientes[objetoNome]) {
                        if (!objetosUtilizados[objetoNome]) {
                            objetosUtilizados[objetoNome] = {
                                ...mapaClientes[objetoNome], // Mantém todas as informações do objeto
                                marcacoes_utilizadas: [] // Inicializa a lista de marcações utilizadas
                            };
                        }

                        // Adicionar marcador dentro do objeto se ainda não foi registrado
                        if (!objetosUtilizados[objetoNome].marcacoes_utilizadas.includes(marcador)) {
                            objetosUtilizados[objetoNome].marcacoes_utilizadas.push(marcador);
                        }
                    }
                }
            }
        });

        // Verificar objetos adicionados, mas não utilizados no texto
        clientes.forEach(cliente => {
            const objetoNome = `clientePF.${cliente.cliente_contador}`;
            if (!objetosUtilizados[objetoNome]) {
                objetosNaoUtilizados.push(cliente);
            }
        });

        // Verificar objetos adicionados, mas não utilizados no texto
        clientes.forEach(cliente => {
            const objetoNome = `clientePF.${cliente.cliente_contador}`;
            if (!objetosUtilizados[objetoNome]) {
                objetosNaoUtilizados.push(cliente);
            }
        });

        return {
            marcacoesSemReferencia,
            objetosNaoUtilizados,
            objetosUtilizados
        };
    }

    /**
     * Extrai todas as marcações encontradas no texto do Quill.
     * @param {Object} ops - O objeto contendo o conteúdo do editor Quill.
     * @returns {Array} - Lista de marcações encontradas.
     */
    #extrairMarcacoes(ops) {
        const marcacoes = [];
        const regexMarcacao = /\{\{(.*?)\}\}/g;

        ops.forEach(op => {
            if (op.insert) {
                let match;
                while ((match = regexMarcacao.exec(op.insert)) !== null) {
                    marcacoes.push(`{{${match[1]}}}`);
                }
            }
        });

        return marcacoes;
    }

    /**
     * Extrai todas as marcações válidas da lista de clientes.
     * @param {Array} clientes - A lista de clientes com suas marcações.
     * @returns {Array} - Lista de marcações válidas.
     */
    #extrairMarcacoesValidasClientes(clientes) {
        const marcacoes = [];

        clientes.forEach(cliente => {
            if (cliente.marcadores && cliente.marcadores) {
                cliente.marcadores.forEach(marcador => {
                    marcacoes.push(marcador.marcacao);
                });
            }
        });

        return marcacoes;
    }

    /**
     * Verifica quais objetos (clientes) foram inseridos mas não possuem marcações utilizadas no texto.
     * @param {Array} marcacoesNoTexto - Lista de marcações presentes no texto.
     * @param {Array} clientes - Lista de clientes.
     * @returns {Array} - Lista de clientes que não tiveram suas marcações utilizadas.
     */
    #verificarObjetosNaoUtilizados(marcacoesNoTexto, clientes) {
        return clientes
            .map(cliente => {
                const marcacoesDoCliente = cliente.marcadores.map(m => m.marcacao);
                const estaSendoUtilizado = marcacoesDoCliente.some(m => marcacoesNoTexto.includes(m));

                return !estaSendoUtilizado ? cliente : null;
            })
            .filter(cliente => cliente !== null);
    }

    /**
     * Verifica quais objetos (clientes) foram utilizados no texto e adiciona os marcadores usados.
     * @param {Array} marcacoesNoTexto - Lista de marcações presentes no texto.
     * @param {Array} clientes - Lista de clientes.
     * @returns {Array} - Lista de clientes utilizados, incluindo a propriedade `marcadores_usados`.
     */
    #verificarObjetosUtilizados(marcacoesNoTexto, clientes) {
        return clientes
            .map(cliente => {
                const marcadoresUsados = cliente.marcadores
                    .filter(m => marcacoesNoTexto.includes(m.marcacao));
                // .map(m => m.marcacao);

                return marcadoresUsados.length > 0
                    ? { ...cliente, marcadores_usados: marcadoresUsados }
                    : null;
            })
            .filter(cliente => cliente !== null);
    }


    /**
     * Seleciona uma marcação no Quill com base no seu conteúdo e índice esperado.
     * @param {Object} quill - Instância do Quill.
     * @param {string} texto - Texto exato da marcação a ser encontrada.
     * @param {number} indiceEsperado - Índice esperado da marcação (1 para a primeira, 2 para a segunda, etc.).
     */
    #selecionarMarcacaoNoQuill(quill, texto, indiceEsperado = 1) {
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
