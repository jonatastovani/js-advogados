import { commonFunctions } from "../commons/commonFunctions";

export class Select2Helpers {

    /**
     * Adiciona eventos para um elemento select2.
     * @param {jQuery} elem - O elemento jQuery ao qual o select2 será aplicado.
     * @param {string} urlApi - A URL da API para recuperar os dados do servidor.
     * @param {Object} [options={}] - Opções adicionais para personalizar o comportamento do select2.
     * @param {number} [options.minimum=3] - O número mínimo de caracteres necessários para acionar a pesquisa.
     * @param {string} [options.placeholder='Selecione uma opção'] - O texto de espaço reservado para o select2.
     * @param {jQuery} [options.dropdownParent=$(document.body)] - O elemento ao qual o dropdown do select2 será anexado.
     */
    static addEventsSelect2Api(elem, urlApi, options = {}) {
        const {
            minimum = 3,
            placeholder = 'Selecione uma opção',
            dropdownParent = $(document.body),
            dataAppend = {},
        } = options;

        elem = $(elem);
        // elem.select2({
        //     theme: 'bootstrap-5'
        // });
        elem.select2({
            theme: "bootstrap",
            language: {
                inputTooShort: function (args) {
                    var caracteres = args.minimum - args.input.length;
                    return `Digite ${caracteres} ou mais caracteres`;
                },
                noResults: function () {
                    return 'Nenhum resultado encontrado';
                },
                searching: function () {
                    return 'Pesquisando...';
                }
            },
            ajax: {
                dataType: 'json',
                delay: 250,
                transport: function (params, success) {
                    let text = params.data.term; // Captura o valor do texto
                    let csrfToken = Select2Helpers.getCsrfToken();

                    // Adiciona o valor do texto ao corpo da solicitação
                    let ajaxOptions = {
                        url: urlApi,
                        type: 'POST',
                        data: { 'text': text },
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,  // Inclui o CSRF token no cabeçalho
                            'Accept': 'application/json',
                        },
                        success: function (data) {
                            success(data.data);
                        },
                        error: function (xhr, textStatus, errorThrown) {
                            const error = Select2Helpers.errorHandling(xhr);
                            console.error(error.message);
                            // commonFunctions.generateNotification(error.message, 'error');
                        }
                    };

                    commonFunctions.deepMergeObject(ajaxOptions.data, dataAppend);

                    return $.ajax(ajaxOptions);
                },
                processResults: function (data) {
                    return {
                        results: data ? data : []
                    };
                },
                cache: true
            },
            placeholder: placeholder,
            allowClear: true,
            minimumInputLength: minimum,
            dropdownParent: dropdownParent,
        });
    }

    /**
     * Adiciona eventos para um elemento select2 com suporte a múltiplas seleções.
     * @param {jQuery} selectElem - O elemento jQuery ao qual o select2 será aplicado.
     * @param {string} urlApi - A URL da API para recuperar os dados do servidor.
     * @param {Object} [options={}] - Opções adicionais para personalizar o comportamento do select2.
     * @param {number} [options.minimum=3] - O número mínimo de caracteres necessários para acionar a pesquisa.
     * @param {string} [options.placeholder='Selecione uma ou mais opções'] - O texto de espaço reservado para o select2.
     * @param {jQuery} [options.dropdownParent=$(document.body)] - O elemento ao qual o dropdown do select2 será anexado.
     * @param {Function} [options.onSelectionChange] - Callback chamado quando a seleção mudar.
     */
    static addEventsSelect2ApiMulti(selectElem, urlApi, options = {}) {
        const {
            minimum = 3,
            placeholder = 'Selecione uma ou mais opções',
            dropdownParent = $(document.body),
            dataAppend = {},
            onSelectionChange = null,
        } = options;

        selectElem = $(selectElem);
        selectElem.select2({
            theme: "bootstrap",
            multiple: true,
            language: {
                inputTooShort: function (args) {
                    var caracteres = args.minimum - args.input.length;
                    return `Digite ${caracteres} ou mais caracteres`;
                },
                noResults: function () {
                    return 'Nenhum resultado encontrado';
                },
                searching: function () {
                    return 'Pesquisando...';
                }
            },
            ajax: {
                dataType: 'json',
                delay: 250,
                transport: function (params, success) {
                    let text = params.data.term;
                    let csrfToken = Select2Helpers.getCsrfToken();

                    let ajaxOptions = {
                        url: urlApi,
                        type: 'POST',
                        data: { 'text': text },
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        success: function (data) {
                            success(data.data);
                        },
                        error: function (xhr) {
                            const error = Select2Helpers.errorHandling(xhr);
                            console.error(error.message);
                        }
                    };

                    commonFunctions.deepMergeObject(ajaxOptions.data, dataAppend);

                    return $.ajax(ajaxOptions);
                },
                processResults: function (data) {
                    return {
                        results: data ? data : []
                    };
                },
                cache: true
            },
            placeholder: placeholder,
            allowClear: true,
            minimumInputLength: minimum,
            dropdownParent: dropdownParent,
        });

        // Se o callback for fornecido, captura as mudanças na seleção
        if (onSelectionChange) {
            selectElem.on('select2:select select2:unselect', function () {
                const selectedData = selectElem.select2('data'); // Obtém o array de seleções
                const selectedValues = selectedData.map(item => {
                    return { id: item.id, text: item.text };
                }); // Apenas os IDs (ou outros campos de interesse)
                onSelectionChange(selectedValues);
            });
        }
    }

    static getCsrfToken() {
        let csrfToken = $('meta[name="csrf-token"]').attr('content');
        if (!csrfToken) {
            const cookies = document.cookie.split(';');
            for (let cookie of cookies) {
                if (cookie.trim().startsWith('XSRF-TOKEN=')) {
                    csrfToken = decodeURIComponent(cookie.trim().substring('XSRF-TOKEN='.length));
                    break;
                }
            }
        }
        return csrfToken;
    }

    static errorHandling(xhr) {
        try {
            console.error(xhr)
            const responseText = JSON.parse(xhr.responseText);
            let mensagens = [];

            console.error('Erro HTTP:', xhr.status);
            console.error(`Código de erro: ${responseText.trace_id}`);
            if (xhr.status == 422) {
                console.error(responseText.data);
            }

            // console.log(responseText)
            if (responseText.data && responseText.data.errors) {
                // Verifica se 'errors' é um array ou um objeto
                if (Array.isArray(responseText.data.errors)) {
                    mensagens = responseText.data.errors.map(error => error);
                } else {
                    Object.keys(responseText.data.errors).forEach(key => {
                        if (responseText.data.errors[key].error) {
                            mensagens.push(responseText.data.errors[key].error);
                        } else {
                            mensagens.push(responseText.data.errors[key]);
                        }
                    });
                }
            }

            const mensagem = `${responseText.message}\n${mensagens.join('\n')}`;

            return {
                status: xhr.status,
                message: mensagem
            };
        } catch (error) {
            console.error(error);
            console.error('Erro HTTP:', error.status);
            console.error(`Descrição do erro: ${error.responseText}`);
            return {
                status: error.status,
                descricao: error.responseText
            };
        }
    }

    /**
     * Atualiza manualmente um select2 com um novo valor e texto.
     * 
     * @param {jQuery} selectElem - O campo select2 que será atualizado (jQuery object).
     * @param {string} displayText - O texto a ser exibido no select2.
     * @param {string|number} value - O valor correspondente ao texto.
     * @param {Object} [options={}] - Opções adicionais para personalizar o comportamento.
     * @param {boolean} [options.selected=true] - Define se o valor será selecionado.
     * @param {boolean} [options.clearExisting=false] - Define se as opções existentes serão limpas antes de adicionar a nova.
     * @param {boolean} [options.triggerChange=true] - Define se o evento 'change' será disparado.
     */
    static updateSelect2Value(selectElem, displayText, value, options = {}) {
        const {
            selected = true,
            clearExisting = false,
            triggerChange = true
        } = options;

        // Converte para jQuery, se necessário
        selectElem = $(selectElem);

        // Limpa as opções existentes, se configurado
        if (clearExisting) {
            selectElem.empty();
        }

        // Verifica se o valor já existe no select2
        let existingOption = selectElem.find(`option[value="${value}"]`);
        if (existingOption.length === 0) {
            // Cria e adiciona uma nova opção se ela não existir
            const newOption = new Option(displayText, value, selected, selected);
            selectElem.append(newOption);
        } else if (selected) {
            // Se já existir e a opção 'selected' for verdadeira, seleciona-a
            existingOption.prop('selected', true);
        }

        // Dispara o evento 'change' se configurado
        if (triggerChange) {
            selectElem.trigger('change');
        }
    }

    /**
     * Atualiza manualmente um select2 com múltiplos valores e textos.
     * 
     * @param {jQuery} selectElem - O campo select2 que será atualizado (jQuery object).
     * @param {Array<Object>} items - Array de objetos com os valores e textos para preencher o select2.
     *      Exemplo: [{ id: '1', text: 'Opção 1' }, { id: '2', text: 'Opção 2' }]
     * @param {Object} [options={}] - Opções adicionais para personalizar o comportamento.
     * @param {boolean} [options.clearExisting=true] - Define se as seleções existentes serão limpas antes de adicionar novas.
     * @param {boolean} [options.triggerChange=true] - Define se o evento 'change' será disparado.
     */
    static updateSelect2MultipleValues(selectElem, items, options = {}) {
        const {
            clearExisting = true,
            triggerChange = true
        } = options;

        // Converte para jQuery, se necessário
        selectElem = $(selectElem);

        // Limpa as opções existentes, se necessário
        if (clearExisting) {
            selectElem.empty();
        }

        // Itera sobre os itens e adiciona cada um como uma nova opção selecionada
        items.forEach(item => {
            const newOption = new Option(item.text, item.id, true, true);
            selectElem.append(newOption);
        });

        // Se triggerChange for verdadeiro, dispara o evento 'change' no select2
        if (triggerChange) {
            selectElem.trigger('change');
        }
    }
}