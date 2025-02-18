import Quill from 'quill';

export class QuillEditorModule {

    _selector;
    _quill;

    /**
     * Inicializa o Quill.js para um determinado campo de texto.
     * @param {string|jQuery} selector - Seletor do elemento onde o editor será aplicado.
     * @param {Object} options - Opções para personalizar a toolbar.
     * @param {Array} options.exclude - Lista de botões a serem removidos.
     */
    constructor(selector, options = {}) {
        const $targetElement = (selector instanceof jQuery) ? selector : $(selector);

        if (!$targetElement.length) {
            console.error(`Elemento ${selector} não encontrado!`);
            return;
        }

        this._selector = selector;

        // Criar estrutura do editor dinamicamente
        const toolbarId = `toolbar-${$targetElement.attr('id')}`;
        const editorId = `editor-${$targetElement.attr('id')}`;

        // Criar toolbar personalizada com base nas opções
        const $toolbar = this._createToolbar(toolbarId, options.exclude);

        // Criar o editor
        const $editor = $('<div>').attr('id', editorId).addClass('rounded-bottom-2 border-0');

        // Esconde o campo original e insere o editor no DOM
        $targetElement.hide().after(
            $('<div>').addClass('quill-editor-wrapper d-flex flex-column flex-fill')
                .append($toolbar, $editor)
        );

        // Inicializar o Quill.js
        this._quill = new Quill(`#${editorId}`, {
            theme: 'snow',
            modules: {
                toolbar: `#${toolbarId}`
            }
        });

        // Adicionar classes personalizadas para aparência com Bootstrap
        $(`#${editorId} .ql-editor`).addClass('form-control rounded-top-0');

        // Traduzir os botões
        setTimeout(() => {
            this._translateToolbar(toolbarId);
        }, 500);
    }

    get getSelector() {
        return this._selector;
    }

    /**
     * Retorna a instância do Quill.js associada ao editor.
     */
    get getQuill() {
        return this._quill;
    }

    /**
     * Cria a toolbar personalizada do Quill.js.
     * @param {string} toolbarId - ID único da toolbar.
     * @param {Array} exclude - Lista de botões a serem removidos.
     * @returns {jQuery} - Elemento jQuery da toolbar.
     */
    _createToolbar(toolbarId, exclude = []) {
        const defaultButtons = {
            font: '<select class="ql-font"></select>',
            size: '<select class="ql-size"></select>',
            bold: '<button class="ql-bold"></button>',
            italic: '<button class="ql-italic"></button>',
            underline: '<button class="ql-underline"></button>',
            strike: '<button class="ql-strike"></button>',
            color: '<select class="ql-color"></select>',
            background: '<select class="ql-background"></select>',
            scriptSub: '<button class="ql-script" value="sub"></button>',
            scriptSuper: '<button class="ql-script" value="super"></button>',
            blockquote: '<button class="ql-blockquote"></button>',
            code: '<button class="ql-code-block"></button>',
            listOrdered: '<button class="ql-list" value="ordered"></button>',
            listBullet: '<button class="ql-list" value="bullet"></button>',
            align: `
                <select class="ql-align">
                    <option selected></option>
                    <option value="center"></option>
                    <option value="right"></option>
                    <option value="justify"></option>
                </select>
            `,
            link: '<button class="ql-link"></button>',
            image: '<button class="ql-image"></button>',
        };

        // Remover botões excluídos da configuração
        for (const btn of exclude) {
            delete defaultButtons[btn];
        }

        // Criar toolbar com os botões restantes
        const toolbarHtml = Object.values(defaultButtons).join('');
        return $('<div>')
            .attr('id', toolbarId)
            .html(toolbarHtml)
            .addClass('rounded-top-2 bg-light');
    }

    /**
     * Traduz os botões da toolbar para português.
     * @param {string} toolbarId - O ID da toolbar associada ao editor.
     */
    _translateToolbar(toolbarId) {
        const $toolbar = $(`#${toolbarId}`);
        if (!$toolbar.length) return;

        const translations = {
            '.ql-bold': 'Negrito',
            '.ql-italic': 'Itálico',
            '.ql-underline': 'Sublinhado',
            '.ql-strike': 'Tachado',
            '.ql-color': 'Cor do texto',
            '.ql-background': 'Cor de fundo',
            '.ql-script[value="sub"]': 'Subscrito',
            '.ql-script[value="super"]': 'Sobrescrito',
            '.ql-blockquote': 'Citação',
            '.ql-code-block': 'Código',
            '.ql-list[value="ordered"]': 'Lista numerada',
            '.ql-list[value="bullet"]': 'Lista com marcadores',
            '.ql-align': 'Alinhamento',
            '.ql-link': 'Inserir link',
            '.ql-image': 'Inserir imagem'
        };

        $.each(translations, function (selector, title) {
            $toolbar.find(selector).attr('title', title);
        });
    }

    /**
     * Cria um botão personalizado para inserir marcações de clientes.
     * @param {Object} config - Configuração do botão.
     * @returns {jQuery} Elemento do botão.
     */
    _criarBotao(config) {
        let ico = config?.icone ?? '<i class="fas fa-bomb"></i>';

        return $('<button>')
            .attr('type', 'button')
            .attr('title', config.title)
            .addClass('ql-button') // Adiciona a classe do Quill
            .html(ico)
            .on("click", () => {
                this.getQuill.focus(); // Garante que o Quill recebe o foco
                this._inserirMarcacaoNoEditor(config);
            });
    }
    /**
     * Insere uma marcação personalizada no local do cursor.
     * Se não houver espaços antes da marcação, adiciona um espaço.
     * @param {Object} config - Configuração do botão.
     */
    _inserirMarcacaoNoEditor(config) {
        const quill = this.getQuill;
        const range = quill.getSelection();
        if (range) {
            const index = range.index;
            const textoAntes = quill.getText(index - 1, 1); // Captura o caractere antes do cursor

            // Se o caractere anterior não for um espaço, adicionamos um espaço antes da marcação
            const textoInserir = (textoAntes && textoAntes !== ' ') ? ' ' + config.marcacao : config.marcacao;
            quill.insertText(index, textoInserir, 'bold', true);
            quill.setSelection(index + textoInserir.length); // Move o cursor para depois da marcação
        }
    }
}