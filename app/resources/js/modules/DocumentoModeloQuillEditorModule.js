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
            self.#inserirAccordionCliente({ cliente_contador: self.#getContadorClienteNaTela(true), pessoa_tipo: dataPessoaTipo });
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
            const ultimoCliente = self._parentInstance._objConfigs.quillEditor.clientesNaTela.find(
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
        });
    }

    #inserirAccordionCliente(item) {
        const self = this;

        item.marcadores ??= {};
        item.marcadores.pessoa ??= {};

        item.marcadores.pessoa = self.#marcadoresEsperadosCliente(item);
        $(`#accordionsCliente${self._parentInstance.getSufixo}`).append(self.#getHTMLAccordionCliente(item));
        self.#addEventMarcadores(item.marcadores.pessoa, { id: item.idAccordion });
        self.#pushContadorClienteNaTela(item);
    }

    #getHTMLAccordionCliente(item) {
        const self = this;
        const contador = item.cliente_contador;
        item.idAccordion = UUIDHelper.generateUUID();
        const sufixo = `cliente${item.idAccordion}-${contador}`;

        const pfPj = item.pessoa_tipo;

        // const dropdownPessoa = self.#renderBtnMarcadores(item.marcadores.pessoa, { id: item.idAccordion });
        // const body = `
        //     <div class="dropdown">
        //         <button
        //             class="btn btn-secondary dropdown-toggle"
        //             type="button" data-bs-toggle="dropdown"
        //             aria-expanded="false">
        //             Pessoa
        //         </button>
        //         <ul class="dropdown-menu">
        //             ${dropdownPessoa}
        //         </ul>
        //     </div>`;
        const btns = self.#renderBtnMarcadores(item.marcadores.pessoa, { id: item.idAccordion });

        return `
            <div class="accordion mt-2 px-0" id="${item.idAccordion}" data-tipo="cliente" data-contador="${contador}">
                <div class="accordion-item">
                    <div class="accordion-header">
                        <button class="accordion-button py-1 collapsed" type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#collapse${sufixo}"
                            aria-expanded="true"
                            aria-controls="collapse${sufixo}">
                            <span class="spanClienteNumero">Cliente${pfPj}.${contador}</span>
                        </button>
                    </div>
                    <div id="collapse${sufixo}"
                        class="accordion-collapse collapse"
                        data-bs-parent="#${item.idAccordion}" data-tipo="cliente">
                        <div class="accordion-body">
                            <div class="d-flex gap-2 d-md-grid d-lg-flex g-2 gap-xl-0 flex-wrap row-cols-xl-2 flex-shrink-1">
                                ${btns}
                            </div>
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

        self._parentInstance._objConfigs.quillEditor ??= {};
        self._parentInstance._objConfigs.quillEditor.clientesNaTela ??= [];

        return self._parentInstance._objConfigs.quillEditor.clientesNaTela.length + (addProximo ? 1 : 0);
    }

    #pushContadorClienteNaTela(item) {
        const self = this;
        self._parentInstance._objConfigs.quillEditor.clientesNaTela.push(item);
    }

    #deleteContadorClienteNaTela(contador) {
        const self = this;
        self._parentInstance._objConfigs.quillEditor.clientesNaTela = self._parentInstance._objConfigs.quillEditor.clientesNaTela.filter(i => i.cliente_contador !== contador);
    }

    // #renderBtnMarcadores(marcadores, options = {}) {
    //     const self = this;
    //     const id = options.id;

    //     const html = marcadores.map(item => {
    //         return `
    //         <li>
    //             <a id="${id}-${item.sufixo}" class="dropdown-item" data-marcacao="${item.marcacao}" href="#">
    //                 ${item.display}
    //             </a>
    //         </li>`;
    //     });
    //     return html;
    // }

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
}
