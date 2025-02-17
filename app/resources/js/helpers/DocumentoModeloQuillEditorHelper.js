import { QuillEditorHelper } from "./QuillEditorHelper";

export class DocumentoModeloQuillEditorHelper extends QuillEditorHelper {

    /**
     * Inicializa o editor Quill.js com suporte a marcações de clientes.
     * @param {string|jQuery} selector - Seletor do elemento onde o editor será aplicado.
     * @param {Object} options - Opções adicionais.
     */
    constructor(selector, options = {}) {
        super(selector, options); // Chama o construtor da classe pai

        if (!this.getQuill) {
            console.error("Erro: Quill não foi inicializado corretamente.");
            return;
        }

        // Adiar execução para garantir que a classe foi instanciada corretamente
        setTimeout(() => {
            this.#adicionarBotoesClientes();
        }, 0);
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

    /**
     * Adiciona botões personalizados para marcações de clientes no editor.
     */
    #adicionarBotoesClientes() {
        const self = this;

        const toolbarContainer = $(".ql-toolbar");
        if (!toolbarContainer.length) return;

        // Criar grupo de botões
        const grupoClientes = $('<span>');
        // .addClass('ql-formats');

        // Adicionar botões conforme configuração
        self.#getBotoesClientesConfig().forEach(botaoConfig => {
            const botao = self._criarBotao(botaoConfig);
            grupoClientes.append(botao);
        });

        // Adicionar grupo ao toolbar
        toolbarContainer.append(grupoClientes);
    }

}
