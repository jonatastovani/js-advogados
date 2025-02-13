import Quill from 'quill';

export class QuillEditorHelper {
    /**
     * Inicializa o Quill.js para um determinado campo de texto.
     * @param {string|jQuery} selector - Seletor do elemento onde o editor será aplicado.
     */
    static init(selector) {
        const $targetElement = (selector instanceof jQuery) ? selector : $(selector);

        if (!$targetElement.length) {
            console.error(`Elemento ${selector} não encontrado!`);
            return;
        }

        // Criar estrutura do editor dinamicamente
        const toolbarId = `toolbar-${$targetElement.attr('id')}`;
        const editorId = `editor-${$targetElement.attr('id')}`;

        // Criar elementos dinamicamente com jQuery
        const $wrapper = $('<div>').addClass('quill-editor-wrapper');
        const $toolbar = $('<div>').attr('id', toolbarId).html(`
            <select class="ql-font"></select>
            <select class="ql-size"></select>
            <button class="ql-bold"></button>
            <button class="ql-italic"></button>
            <button class="ql-underline"></button>
            <button class="ql-strike"></button>
            <select class="ql-color"></select>
            <select class="ql-background"></select>
            <button class="ql-script" value="sub"></button>
            <button class="ql-script" value="super"></button>
            <button class="ql-blockquote"></button>
            <button class="ql-code-block"></button>
            <button class="ql-list" value="ordered"></button>
            <button class="ql-list" value="bullet"></button>
            <button class="ql-align"></button>
            <button class="ql-link"></button>
            <button class="ql-image"></button>
        `).addClass('rounded-top-2 text-bg-light');

        const $editor = $('<div>').attr('id', editorId)
        .addClass('rounded-bottom-2')
            .css('height', '200px');

        // Esconde o campo original e insere o editor no DOM
        $targetElement.hide().after($wrapper.append($toolbar, $editor));

        // Inicializar o Quill.js
        const quill = new Quill(`#${editorId}`, {
            theme: 'snow',
            modules: {
                toolbar: `#${toolbarId}`
            }
        });

        // Traduzir os botões
        setTimeout(() => {
            this.translateToolbar(toolbarId);
        }, 500);

        return quill;
    }

    /**
     * Traduz os botões da toolbar para português.
     * @param {string} toolbarId - O ID da toolbar associada ao editor.
     */
    static translateToolbar(toolbarId) {
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

        console.log("Toolbar traduzida para PT-BR!");
    }
}
