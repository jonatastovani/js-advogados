// import 'select2';
import { modalLoading } from "../components/comum/modalLoading";
import { SystemNotificationsHelper } from "../helpers/SystemNotificationsHelper";
import { connectAjax } from "./connectAjax";
import { enumAction } from "./enumAction";
import instanceManager from "./instanceManager";
import './select2-4.0.2';

export class commonFunctions {

    /**
     * Removes all non-numeric characters from a string.
     * @param {string} num - The string to process.
     * @returns {string} - The string with only numeric characters.
     */
    static returnsOnlyNumber(num) {
        return String(num).replace(/\D/g, '');
    }

    /**
     * Gets the values ​​of a form's elements and returns an object or formatted string.
     *
     * @param {string} container - Container to be processed.
     * @param {number} returnType - The desired return type: 1 for object, 2 for string.
     * @param {boolean} blnDisabled - Indicates whether disabled elements should be included (true) or excluded (false).
     * @param {boolean} keyId - Defines whether the "name" attribute (keyId=false) or the "id" attribute (keyId=true) should be used as a key in the return object.
     *
     * @returns {object|string} - An object with the values ​​of the form elements (returnType 1)
     *                            or a string formatted with the element values ​​(returnType 2).
     */
    static getInputsValues(container, returnType = 1, blnDisabled = false, keyId = false) {
        const formData = {};
        let strReturn = '';

        const elemInput = $(container).find('input, select, textarea');

        elemInput.each(function () {
            const element = $(this)[0];
            const id = element.id;
            const name = element.name;
            const val = element.value;
            const disabled = element.disabled;

            if (blnDisabled || (!blnDisabled && !disabled)) {
                if (element.type === 'radio') {
                    if (element.checked) {
                        if ((id && keyId)) {
                            formData[id] = val.trim();
                            strReturn += `${id}:${val.trim()}\n`;
                        } else if ((name && !keyId)) {
                            formData[name] = val.trim();
                            strReturn += `${name}:${val.trim()}\n`;
                        }
                    }
                } else if (element.type === 'checkbox') {
                    if (element.checked) {
                        if ((id && keyId)) {
                            formData[id] = true;
                            strReturn += `${id}:${true}\n`;
                        } else if ((name && !keyId)) {
                            formData[name] = true;
                            strReturn += `${name}:${true}\n`;
                        }
                    } else {
                        if ((id && keyId)) {
                            formData[id] = false;
                            strReturn += `${id}:${false}\n`;
                        } else if ((name && !keyId)) {
                            formData[name] = false;
                            strReturn += `${name}:${false}\n`;
                        }
                    }
                } else {
                    if ((id && keyId)) {
                        formData[id] = val.trim();
                        strReturn += `${id}:${val.trim()}\n`;
                    } else if ((name && !keyId)) {
                        formData[name] = val.trim();
                        strReturn += `${name}:${val.trim()}\n`;
                    }
                }
            }
        });

        switch (returnType) {
            case 1:
                return formData;

            case 2:
                return strReturn;
        }
    }

    /**
     * Capitalizes the first letter of a text.
     * @param {string} text - The text to be processed.
     * @returns {string} - The text with the first letter capitalized.
     */
    static firstUppercaseLetter(text) {

        if (text.length === 0) {
            return text;
        }

        return text.charAt(0).toUpperCase() + text.slice(1);

    }

    /**
     * Aplica uma máscara personalizada a um elemento de entrada de texto, removendo qualquer máscara existente primeiro.
     *
     * @param {jQuery} elem - O elemento de entrada ao qual a máscara será aplicada.
     * @param {Object} metadata - Metadados que personalizam a máscara.
     * @param {string} [metadata.format] - A máscara de formato desejada (padrão: '0,99' para números com duas casas decimais).
     * @param {Object} [metadata.before] - Configurações para dígitos antes do ponto decimal.
     * @param {number} [metadata.before.quantity] - O número de dígitos antes do ponto decimal.
     * @param {Object} [metadata.after] - Configurações para dígitos depois do ponto decimal.
     * @param {number} [metadata.after.quantity] - O número de dígitos depois do ponto decimal.
     * @param {boolean} [metadata.reverse=false] - Define se a máscara deve ser aplicada no modo reverso (da direita para a esquerda).
     */
    static applyCustomNumberMask(elem, metadata = {}) {
        // Remover qualquer máscara anterior
        elem.unmask();

        // Verifica se foi fornecido um formato customizado, caso contrário, usa o padrão '0,99'
        let format = metadata.format || '0,99';

        // Se `before.quantity` ou `after.quantity` forem fornecidos, personaliza a máscara
        if ((metadata.before && metadata.before.quantity) || (metadata.after && metadata.after.quantity)) {
            // Gerar a parte antes do ponto decimal, se `before.quantity` foi fornecido
            if (metadata.before && metadata.before.quantity) {
                const beforeDigits = '0'.repeat(metadata.before.quantity);
                format = beforeDigits; // Substitui o formato padrão
            }

            // Gerar a parte após o ponto decimal, se `after.quantity` foi fornecido
            if (metadata.after && metadata.after.quantity) {
                const afterDigits = '9'.repeat(metadata.after.quantity);
                format += `,${afterDigits}`; // Adiciona os dígitos após a vírgula
            }
        }

        // Aplicar a máscara ao elemento
        elem.mask(format, { reverse: metadata.reverse || false });
    }

    /**
     * Formats a number as a string in Brazilian monetary format (with commas and currency symbol).
     * @param {number} number - The number to format.
     * @returns {string} - A string in Brazilian monetary format.
     */
    static formatNumberToCurrency(number) {
        return number.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    }

    /**
     * Removes commas, periods, and other non-numeric characters from a string in monetary or fractional format, keeping only the digits.
     * @param {string} currency - The string in currency format to be unformatted.
     * @returns {number} - The deformatted number.
     */
    static removeCommasFromCurrencyOrFraction(currency = '0') {
        const formattedCurrency = currency.replace(/[^0-9,-]+/g, '');
        const formattedCurrencyWithDecimalPoint = formattedCurrency.replace(',', '.');
        return Number(formattedCurrencyWithDecimalPoint);
    }

    /**
     * Formata um número como moeda com vírgulas e frações.
     *
     * @param {number|string} number - O número a ser formatado.
     * @param {Object} metadata - Um objeto contendo opções de formatação.
     * @param {number} metadata.decimalPlaces - O número de casas decimais a serem exibidas.
     * @returns {string} A string de moeda formatada.
     */
    static formatWithCurrencyCommasOrFraction(number, metadata = {}) {
        const decimalPlaces = metadata.decimalPlaces || 2;

        // Verifica se o valor é numérico, caso contrário converte
        if (isNaN(Number(number))) {
            number = commonFunctions.returnsOnlyNumber(number);
        }

        const formattedNumber = Number(number).toLocaleString('pt-BR', {
            minimumFractionDigits: decimalPlaces,
            maximumFractionDigits: decimalPlaces,
        });

        return formattedNumber;
    }

    /**
     * Formats a number with commas and limits the number of decimal places.
     *
     * @param {number} number - The number to format.
     * @param {Object} options - Formatting options.
     * @param {number} options.decimalPlaces - The number of decimal places to display. Default is 2.
     * @returns {string} The formatted string.
     */
    static formatNumberWithLimitDecimalPlaces(number, options = {}) {
        const decimalPlaces = options.decimalPlaces || 2;

        const formattedNumber = Number(number.toFixed(decimalPlaces));

        return formattedNumber;
    }

    /**
     * Preenche um elemento de seleção com dados de uma API.
     *
     * @param {jQuery} elem - O elemento de seleção encapsulado em jQuery a ser preenchido.
     * @param {string} urlApi – A URL da API da qual buscar dados.
     * @param {Object} options - Opções adicionais para personalizar o processo de preenchimento.
     * @param {boolean} options.insertFirstOption - Se deseja inserir a primeira opção (padrão: true).
     * @param {string} options.firstOptionName - O nome da primeira opção (padrão: 'Selecione').
     * @param {string} options.firstOptionValue - O valor da primeira opção (padrão: 0).
     * @param {string} options.selectedIdOption - O ID da opção a ser marcada como selecionada (padrão: o valor atual do elemento select).
     * @param {string} options.displayColumnName - O nome da coluna a ser exibida nas opções (padrão: 'nome').
     * @param {string} options.typeRequest - O tipo de solicitação (por exemplo, "GET" ou "POST").
     * @param {Object} options.envData - Os dados da solicitação (por exemplo, os dados de envio).
     * @returns {Promise} - Uma promessa que é resolvida quando o elemento selecionado é preenchido ou rejeitado por erro.
     */
    static async fillSelect(elem, urlApi, options = {}) {
        const {
            insertFirstOption = true,
            firstOptionName = 'Selecione',
            firstOptionValue = 0,
            selectedIdOption = elem.val(),
            displayColumnName = 'nome',
            typeRequest = enumAction.GET,
            envData = {},
        } = options;

        const objConn = new connectAjax(urlApi);

        try {
            let response;

            if (typeRequest === enumAction.GET) {
                response = await objConn.getRequest();
            } else if (typeRequest === enumAction.POST) {
                objConn.setAction(typeRequest);
                objConn.setData(envData);
                response = await objConn.envRequest();
            } else {
                throw new Error('Tipo de solicitação inválido. Use "GET", "POST" ou "PUT".');
            }

            let strOptions = '';

            if (insertFirstOption) {
                strOptions += `<option value="${firstOptionValue}">${firstOptionName}</option>`;
            }

            response.data.forEach(result => {
                const id = result.id;
                const valor = result[displayColumnName];
                const strSelected = (id == selectedIdOption ? ' selected' : '');
                strOptions += `\n<option value="${id}"${strSelected}>${valor}</option>`;
            });

            elem.html(strOptions);
            return Promise.resolve('A lista foi carregada com sucesso!');
        } catch (error) {
            elem.html(`<option>'Erro ao preencher'</option>`);
            commonFunctions.generateNotificationErrorCatch(error)
            return Promise.reject(error);
        }
    }

    /**
     * Preenche um elemento de seleção (select) com opções geradas a partir de um array de objetos.
     * @param {jQuery} elem - O elemento jQuery select que será preenchido.
     * @param {Array<Object>} array - O array de objetos a partir do qual as opções serão geradas.
     * @param {Object} [options={}] - Opções adicionais para personalizar o preenchimento do select.
     * @param {boolean} [options.insertFirstOption=true] - Indica se deve ser inserida uma primeira opção no select.
     * @param {string} [options.firstOptionName='Selecione'] - O texto da primeira opção.
     * @param {string} [options.firstOptionValue=''] - O valor da primeira opção.
     * @param {string} [options.selectedIdOption=elem.val()] - O valor do atributo "value" da opção que deve ser selecionada por padrão.
     * @param {string} [options.displayColumnName='name'] - O nome da coluna no objeto do array que contém os valores a serem exibidos nas opções.
     * @param {string} [options.trigger=undefined] - O evento a ser aplicado após a inserção dos dados.
     * @returns {Promise<string>} - Uma Promise que resolve com uma mensagem indicando o sucesso do preenchimento do select ou rejeita com um erro se ocorrer algum problema.
     */
    static async fillSelectArray(elem, array, options = {}) {
        const {
            insertFirstOption = true,
            firstOptionName = 'Selecione',
            firstOptionValue = '',
            selectedIdOption = elem.val(),
            displayColumnName = 'nome',
            trigger = undefined,
        } = options;

        try {
            let strOptions = '';

            if (insertFirstOption) {
                strOptions += `<option value="${firstOptionValue}">${firstOptionName}</option>`;
            }

            array.forEach(item => {
                const id = item.id;
                const valor = item[displayColumnName];
                const strSelected = (id == selectedIdOption ? ' selected' : '');
                strOptions += `\n<option value="${id}"${strSelected}>${valor}</option>`;
            });

            elem.html(strOptions);
            if (trigger) {
                elem.trigger(trigger);
            }
            return Promise.resolve('A lista foi carregada com sucesso!');
        } catch (error) {
            const errorMessage = 'Erro ao preencher';
            console.error(error);
            elem.html(`<option>${errorMessage}</option>`);
            commonFunctions.generateNotification(error.message, 'error');
            return Promise.reject(error);
        }
    }

    /**
     * Gera um formulário e redireciona o usuário para a URL especificada.
     *
     * @param {string} redirecionamento - O URL para redirecionar após o envio do formulário.
     * @param {Object[]} arrInputs – Um array contendo dados de entrada para o formulário.
     * @param {Object} options - Opções adicionais para configurar atributos do formulário e botão de envio.
     * @param {Object} options.formAttr – Atributos do formulário.
     * @param {string} options.formAttr.method - O atributo do método para o formulário (padrão: 'POST').
     * @param {boolean} options.formAttr.hidden - Especifica se o formulário deve ser oculto (padrão: true).
     * @param {string} options.formAttr.id – O atributo ID do formulário.
     * @param {string} options.formAttr.class – O atributo de classe CSS para o formulário.
     * @param {string} options.formAttr.target – O atributo target para o formulário (padrão: '_self').
     * @param {Object} options.submit – Atributos para o botão enviar.
     * @param {string} options.submit.name - O atributo de nome para o botão de envio (padrão: 'submit').
     * @param {boolean} options.submit.hidden - Especifica se o botão de envio deve ser oculto (padrão: false).
     * @param {string} options.submit.value - O atributo de valor para o botão de envio (padrão: 'Enviar').
     * @param {string} options.submit.id – O atributo de ID do botão de envio.
     * @param {string} options.submit.class – O atributo de classe CSS para o botão de envio.
     * @param {string} options.returnElem - Determina o elemento a ser retornado ('form' para o formulário inteiro, 'submit' para o botão de envio).
     * @returns {HTMLFormElement|HTMLInputElement} - O elemento do formulário ou botão de envio com base na opção de retorno especificada.
     */
    static redirectForm(redirect, arrInputs, options = {}) {
        const { formAttr = { method: 'POST', hidden: true, id: '', class: '', target: '_self' },
            submit = { name: 'submit', hidden: false, value: 'Enviar', id: '', class: '' },
            returnElem = 'submit' } = options;

        let form = document.createElement('form');
        form.id = formAttr.id || '';
        form.hidden = formAttr.hidden || false;
        form.method = formAttr.method || 'POST';
        form.action = redirect;
        form.target = formAttr.target || '_self';

        arrInputs.forEach(input => {
            let newInput = document.createElement('input');
            newInput.type = input.type || 'hidden';
            newInput.name = input.name;
            if (Array.isArray(input.value)) {
                newInput.value = JSON.stringify(input.value);
            } else {
                newInput.value = input.value;
            }
            form.appendChild(newInput);
        });

        let submitButton = document.createElement('input');
        submitButton.type = 'submit';
        submitButton.id = submit.id || '';
        submitButton.className = submit.class || '';
        submitButton.name = submit.name || '';
        submitButton.hidden = submit.hidden || false;
        submitButton.value = submit.value || 'Enviar';
        form.appendChild(submitButton);
        document.body.appendChild(form);

        let returnElement = submitButton;

        switch (returnElem) {
            case 'form':
                returnElement = form;
                break;
            default:
                returnElement = submitButton;
        }

        return returnElement;
    }

    static setItemLocalStorage(name, value) {
        localStorage.setItem(name, value);
    }

    static getItemLocalStorage(name) {
        return localStorage.getItem(name);
    }

    /**
     * Gets an array of default values considered as invalid for generating filters.
     *
     * @returns {Array} - An array of default values.
     */
    static getInvalidsDefaultValuesGenerateFilters() {
        return ['undefined', undefined, 'null', null, '0', 0, ''];
    }

    /**
     * Formats a date to ISO format.
     *
     * @param {Date} date - The date to be formatted (default is the current date if not provided).
     * @returns {string} - The formatted date string in ISO format.
     */
    static formatToISODate(date) {
        if (!date) {
            date = new Date();
        }

        // const isoString = date.toISOString();
        // const offsetMinutes = date.getTimezoneOffset();
        // const offsetHours = Math.abs(offsetMinutes) / 60;
        // const offsetSign = offsetMinutes > 0 ? '-' : '+';

        // return `${isoString.slice(0, -1)}${offsetSign}${this.padNumberWithZero(offsetHours)}:${this.padNumberWithZero(offsetMinutes % 60)}`;

        const year = date.getUTCFullYear();
        const month = this.padNumberWithZero(date.getUTCMonth() + 1);
        const day = this.padNumberWithZero(date.getUTCDate());
        const hours = this.padNumberWithZero(date.getUTCHours());
        const minutes = this.padNumberWithZero(date.getUTCMinutes());
        const seconds = this.padNumberWithZero(date.getUTCSeconds());
        const milliseconds = this.padNumberWithZero(date.getUTCMilliseconds(), 5);

        return `${year}-${month}-${day}T${hours}:${minutes}:${seconds}.${milliseconds}Z`;

    }

    /**
     * Pads a number with zeros to the specified length.
     *
     * @param {number} number - The number to be padded.
     * @param {number} length - The desired length of the padded number (default is 2).
     * @returns {string} - The padded number as a string.
     */
    static padNumberWithZero(number, length = 2) {
        return number.toString().padStart(length, '0');
    }

    /**
     * Gera um objeto de filtro de data com base nos valores início e fim.
     *
     * @param {string} valorInicio - A data de início do filtro (no formato AAAA-MM-DD).
     * @param {string} valorFim - A data de fim do filtro (no formato AAAA-MM-DD).
     * @returns {Object} – O objeto de filtro de data.
     */
    static gerarFiltroData(valorInicio, valorFim) {
        let filtro = {};

        if (valorInicio) {
            filtro.inicio = valorInicio;
        }

        if (valorFim) {
            filtro.fim = valorFim;
        }

        return filtro;
    }

    /**
     * Lida com o evento de mudança para botões de rádio ou checkboxes, habilitando ou desabilitando elementos de entrada com base na seleção.
     *
     * @param {string} button - O seletor para o botão de rádio ou checkbox.
     * @param {Array} arr - Um array de objetos representando elementos de entrada e suas propriedades associadas.
     * Cada objeto no array deve ter as seguintes propriedades:
     * - div_group: O seletor para o grupo de elementos de entrada relacionados.
     * - button: O seletor para o botão de rádio ou checkbox associado.
     * - input: Um array de seletores para os elementos de entrada relacionados.
     */
    static eventRBCkBHidden(button, arr) {
        const rb = $(button);
        rb.on('change', function () {
            let arrInputsHidden = [];
            arr.forEach(element => {
                if (element.div_group) {
                    const group = $(element.div_group);
                    element.input.forEach(inp => {
                        const input = $(inp);
                        if ($(this).attr('type') === 'radio') {
                            if ($(element.button).attr('id') != $(this).attr('id')) {
                                if (arrInputsHidden.length > 0) {
                                    let index = arrInputsHidden.findIndex((el) => el.input.attr('id') === input.attr('id'));
                                    if (index == -1) {
                                        arrInputsHidden.push({ input: input, group: group, status: 'hidden' });
                                    }
                                } else {
                                    arrInputsHidden.push({ input: input, group: group, status: 'hidden' });
                                }
                            }
                            if ($(element.button).attr('id') === $(this).attr('id')) {
                                if (arrInputsHidden.length > 0) {
                                    let index = arrInputsHidden.findIndex((el) => el.input.attr('id') === input.attr('id'));
                                    if (index > -1) {
                                        arrInputsHidden[index].status = 'show';
                                    } else {
                                        arrInputsHidden.push({ input: input, group: group, status: 'show' });
                                    }
                                } else {
                                    arrInputsHidden.push({ input: input, group: group, status: 'show' });
                                }
                            }
                        } else if ($(rb).attr('type') === 'checkbox') {
                            if ($(element.button).prop('checked')) {
                                if (arrInputsHidden.length > 0) {
                                    let index = arrInputsHidden.findIndex((el) => el.input.attr('id') === input.attr('id'));
                                    if (index > -1) {
                                        arrInputsHidden[index].status = 'show';
                                    } else {
                                        arrInputsHidden.push({ input: input, group: group, status: 'show' });
                                    }
                                } else {
                                    arrInputsHidden.push({ input: input, group: group, status: 'show' });
                                }
                            } else {
                                if (arrInputsHidden.length > 0) {
                                    let index = arrInputsHidden.findIndex((el) => el.input.attr('id') === input.attr('id'));
                                    if (index == -1) {
                                        arrInputsHidden.push({ input: input, group: group, status: 'hidden' });
                                    }
                                } else {
                                    arrInputsHidden.push({ input: input, group: group, status: 'hidden' });
                                }
                            }
                        }
                    });
                }
            });
            if (arrInputsHidden.length > 0) {
                arrInputsHidden.forEach(element => {
                    if (element.status === 'show') {
                        element.input.removeAttr('disabled');
                        element.group.show('fast');
                    } else {
                        element.input.attr('disabled', true);
                        element.group.hide('fast');
                    }
                });
            }
        });
    }

    /**
     * Sets up default event handlers for modals, such as close, cancel, and save actions.
     *
     * @param {Object} self - The reference to the current object.
     * @param {Object} options - Additional options to configure the event handlers.
     * @param {boolean} options.formRegister - Whether to include additional event handlers for registration forms (default: false).
     */
    static eventDefaultModals(self, options = {}) {
        const { formRegister = false,
            inputsSearchs = null
        } = options;

        const idModal = self.getIdModal;
        const modal = $(idModal);

        modal.find(".btn-save").on("click", function (e) {
            e.preventDefault();
            self.saveButtonAction();
        });

        modal.find('.btn-close').on('click', function () {
            self.setEndTimer = true;
        });

        modal.find('.btn-cancel').on('click', function () {
            if (formRegister == true) {
                if (typeof self.modalCancel === 'function') {
                    self.modalCancel();
                } else {
                    self.setEndTimer = true;
                }
            } else {
                self.setEndTimer = true;
            }
        });

        modal.on('keydown', function (e) {
            if (e.key === 'Escape') {
                e.stopPropagation();
                self.setEndTimer = true;
            }
        });

        if (formRegister == true) {
            this.addDefaultRegistrationModalEvents(self);
        }

        if (inputsSearchs != null) {
            this.addDefaultSearchModalEvents(self, inputsSearchs);
        }

    }

    /**
     * Sets up additional event handlers for popups related to registration forms.
     *
     * @param {Object} self - The reference to the current object.
     */
    static addDefaultRegistrationModalEvents(self) {

        const idModal = self.getIdModal;
        const modal = $(idModal);

        modal.find('form').on('keydown', function (e) {
            if (e.key === 'Escape') {
                e.stopPropagation();
                if (typeof self.modalCancel === 'function') {
                    self.modalCancel();
                } else {
                    self.setEndTimer = true;
                }
            }
        });

    }

    static addDefaultSearchModalEvents(self, inputsSearchs) {

        inputsSearchs.on("input", function () {
            clearTimeout(self.timerSearch);
            self.timerSearch = setTimeout(function () {
                self.generateFilters();
            }, 1000);

        });

    }

    static addEventToggleDiv(dataSearchDiv, toggleButton, options = {}) {
        const { self = null, minWidht = 991 } = options;

        function toggleDataSearch() {
            const screenWidth = $(window).width();

            if (screenWidth <= minWidht) {
                dataSearchDiv.hide("slow");
                toggleButton.show("slow");
            } else {
                dataSearchDiv.show("slow");
                toggleButton.hide("slow");
            }

        }

        toggleButton.click(function () {
            dataSearchDiv.slideToggle();
        });

        $(window).on('resize.toggleDataSearch', function () {
            toggleDataSearch();
        });

        $(':input').on('focus', function () {
            $(window).off('resize.toggleDataSearch');
        });

        $(':input').on('blur', function () {
            $(window).on('resize.toggleDataSearch', function () {
                toggleDataSearch();
            });
        });

        toggleDataSearch();
    }

    static hiddenInputValue(elem, btn, options = {}) {
        const {
            titleShow = 'Exibir', titleHidden = 'Ocultar'
        } = options;

        const ico = $(btn).find("i");

        const getHidden = () => {
            return this.getItemLocalStorage('hidden_data');
        }

        if (getHidden() == null) {
            this.setItemLocalStorage('hidden_data', true);
        };

        if (getHidden() == 'true') {
            elem.attr("type", "password");
            ico.removeClass("bi bi-eye-slash-fill").addClass("bi bi-eye-fill");
            $(btn).attr('title', titleShow);
        } else {
            $(elem).attr("type", "text");
            ico.removeClass("bi bi-eye-fill").addClass("bi bi-eye-slash-fill");
            $(btn).attr('title', titleHidden);
        }

    }

    static simulateLoading(elem, status = true) {
        if (status) {
            if (!$(elem).find('.spinner-border').length) {
                $(elem).prepend(`<span class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>`);
            }
            $(elem).addClass('disabled').attr('disabled', true);
            $(elem).find('.spinner-border').removeClass('d-none');
        } else {
            $(elem).removeClass('disabled').removeAttr('disabled');
            $(elem).find('.spinner-border').addClass('d-none');
        }
    }

    /**
     * Abre um modal setando a classe no instanceManager o modalLoading aberto.
     *
     * @param {boolean} displayBln - Status de visibilidade do modal.
     * @param {Object} [options={}] - Opções para personalizar o modal.
     * @param {string} [options.message=null] - Mensagem personalizada a ser exibida no modal.
     * @param {string} [options.title=null] - Título para o modal (Opcional).
     * @param {jQuery} [options.elementFocus=null] - Elemento para ser redirecionado o foco após ser fechado o modal.
     */
    static async loadingModalDisplay(displayBln = true, options = {}) {
        const {
            message = null,
            title = null,
            elementFocus = null,
        } = options;

        const loading = instanceManager.setInstance('modalLoading', new modalLoading());
        const isModalVisible = $(loading.getIdModal).hasClass('show');

        // Se o estado já é o desejado, retorna a Promise imediatamente
        if ((displayBln && isModalVisible) || (!displayBln && !isModalVisible)) {
            return Promise.resolve(true);
        }

        if (displayBln) {
            const objDataEnv = {
                title: title,
                message: message,
                elementFocus: elementFocus
            }
            loading.setDataEnvModal = objDataEnv;
            await loading.modalOpen();
            return true;
        } else {
            await loading.modalClose();
            return true;
        }
    }

    static async getRecurseWithTrashed(urlApi, options = {}) {
        const {
            // data = { trashed: true },
            data = {},
            action = enumAction.GET,
            param = '?withTrashed=1',
        } = options;

        try {
            const obj = new connectAjax(urlApi);
            obj.setParam(param);
            obj.setAction(action);
            obj.setData(data);
            const response = action === enumAction.GET ? await obj.getRequest() : await obj.envRequest();
            return response;
        } catch (error) {
            console.error(error);
            throw error;
        }

    }

    static formatStringToHTML(str) {
        str = str.replace(/  /g, '&nbsp; ');
        str = str.replace(/'/g, '&apos;');
        str = str.replace(/"/g, '&quot;');
        str = str.replace(/</g, '&lt;');
        str = str.replace(/>/g, '&gt;');
        str = str.replace(/\n/g, '<br>');
        str = str.trim();
        return str;
    }

    /**
     * Função que retorna uma mensagem de alerta em HTML.
     * @param {string} message - A mensagem de alerta.
     * @param {string} type - O tipo temático do alerta.
     * @param {Object} options - Opções para personalizar o alerta (opcional).
     * @param {string} options.ico - O ícone do alerta (opcional, padrão é um sino).
     * @param {boolean} options.dismissible - Configura se o alerta poderá ser dispensado (opcional, padrão true).
     * @param {Array<string>} options.itemsArray - Itens adicionais para a mensagem (opcional, padrão é uma matriz vazia).
     * @returns {string} - A mensagem de alerta em HTML.
     */
    static returnHTMLAlert(message, type, options = {}) {
        const {
            ico = null,
            dismissible = true,
            itemsArray = [],
        } = options

        let thematic = '';
        let icoHeading = '';
        switch (type) {
            case 'success':
                thematic = 'alert-success';
                icoHeading = 'bi bi-check2-circle'
                break;
            case 'error':
                thematic = 'alert-danger';
                icoHeading = 'bi bi-bug'
                break;
            case 'warning':
                thematic = 'alert-warning';
                icoHeading = 'bi bi-exclamation-triangle'
                break;
            case 'info':
                thematic = 'alert-info';
                icoHeading = 'bi bi-info-circle'
                break;
            default:
                thematic = 'alert-light';
                icoHeading = 'bi bi-bell'
        }
        icoHeading = ico ? ico : icoHeading;

        let strItemsMessage = commonFunctions.returnArrayToHTML(itemsArray);
        strItemsMessage ? strItemsMessage = `<hr><ol class="mb-0">${strItemsMessage}</ol>` : '';

        let btnDismiss = '';
        if (dismissible) {
            thematic += ' alert-dismissible';
            btnDismiss = '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>'
        }

        const htmlMessage = `<h6 class="alert-heading"><i class="${icoHeading}"></i> ${message}</h6>${btnDismiss}`;
        const htmlAlert = `<div class="alert ${thematic}" role="alert">${htmlMessage}${strItemsMessage}</div>`

        return htmlAlert;
    }

    static returnArrayToHTML(array, options = {}) {
        const {
            tag = 'li'
        } = options;

        let strItems = '';
        array.forEach(item => {
            strItems += `<${tag}>${item}</${tag}>`
        });

        return strItems;
    }

    static generateNotification(message, type, options = {}) {
        const {
            messageTag = 'h6',
            messageClass = '',
            applyTag = true,
            itemsArray = null,
            itemsTag = 'li',
            autoRender = true,
            traceId = undefined,
        } = options;

        switch (type) {
            case 'warning':
                console.warn(message);
                break;

            case 'error':
                console.error(message);
                break;

            default:
                console.log(message);
                break;
        }

        if (applyTag) {
            const cls = messageClass ? `class="${messageClass}"` : '';
            message = `<${messageTag} ${cls}>${message}</${messageTag}>`
        }

        let strItems = '';
        if (itemsArray) {
            strItems = commonFunctions.returnArrayToHTML(itemsArray, { tag: itemsTag });
            strItems = strItems ? `<hr class="m-1"><ol class="mb-0">${strItems}</ol>` : '';
            message += strItems;
        }

        return new Promise(async function (resolve) {
            const notification = new SystemNotificationsHelper(message, type);
            notification.setTraceId = traceId;
            resolve(await notification.render());
        })
    }

    static generateNotificationErrorCatch(error) {
        console.error(error);
        commonFunctions.generateNotification(
            commonFunctions.firstUppercaseLetter(error.message),
            'error',
            {
                traceId: error.traceId,
                itemsArray: error.itemsArray,
            }
        );
    }

    /**
     * Verifica uma informação e aplica a classe de válido ou inválido caso seja enviado o elemento.
     * @param {string} [data] - A informação que será validada.
     * @param {Object} [options={}] - Opções para personalizar a verificação.
     * @param {jQuery} [options.field=undefined] - O elemento jQuery que será aplicado a identificação visual de válido ou inválido.
     * @param {string} [options.messageInvalid=null] - A mensagem a ser exibida se o valor editado for considerado inválido.
     * @param {boolean} [options.setFocus=false] - Setar o foco para o elemento enviado caso a verificação retornar inválida.
     * @param {boolean} [options.returnForcedFalse=false] - Retorna o valor falso de maneira forçada para caso de verificação em massa no chamador desta função.
     * @returns {boolean} - Retorna True se a validação está OK, caso contrário retornará False.
     */
    static verificationData(data, options = {}) {
        const {
            field = undefined,
            messageInvalid = null,
            typeNotification = 'warning',
            setFocus = false,
            returnForcedFalse = false
        } = options;
        if (commonFunctions.getInvalidsDefaultValuesGenerateFilters().includes(data)) {
            if (messageInvalid) {
                commonFunctions.generateNotification(messageInvalid, typeNotification);
            }
            if (field) {
                $(field).removeClass('is-valid').addClass('is-invalid');
                if (setFocus) {
                    $(field).focus();
                }
            }
            return false;
        } else {
            if (field) {
                $(field).removeClass('is-invalid').addClass('is-valid');
            }
            return returnForcedFalse ? false : true;
        }
    }

    static checkScrollbar(element) {
        const content = $(element)[0];
        if (content.scrollHeight > content.clientHeight) {
            return true
        } else {
            return false;
        }
    }

    /**
     * Atualiza o valor da barra de progresso com base no seletor fornecido.
     *
     * @param {string} selector - O seletor jQuery para a barra de progresso.
     * @param {number} valor - O valor a ser definido na barra de progresso (0 a 100).
     */
    static atualizarProgressBar(selector, valor) {
        // Seleciona o elemento da barra de progresso
        const progressBar = $(selector);

        if (progressBar.length) {
            // Limita o valor para o intervalo de 0 a 100
            valor = Math.min(Math.max(valor, 0), 100);

            // Atualiza a largura da barra de progresso e os atributos relevantes
            progressBar.css('width', valor + '%');
            progressBar.parent().attr('aria-valuenow', valor);
            // progressBar.text('Comprometimento ' + valor + '%');
        }
    }

    // static async checkPermissions(arrayPermissions) {
    //     // try {
    //     //     const obj = new connectAjax(`${urlLocalAuthSession}/checkPermissions`);
    //     //     obj.setAction(enumAction.POST);
    //     //     obj.setData({ arrayPermissions: arrayPermissions })
    //     //     const response = await obj.envRequest();
    //     //     return response.data.permission;
    //     // } catch (error) {
    //     //     console.error(error);
    //     //     const traceId = error.traceId ? error.traceId : undefined;
    //     //     commonFunctions.generateNotification(commonFunctions.firstUppercaseLetter(error.message), 'error', { traceId: traceId });
    //     //     return false;
    //     // }
    //     const obj = instanceManager.setInstance('managerPermissions', new managerPermissions());
    //     if (await obj.hasPermission(arrayPermissions)) {
    //         return true;
    //     }
    //     return false;
    // }

    // /**
    //  * Faz uma verificação de permissões e retorna um valor boleano.
    //  * @param {Object} [options={}] - Opções para personalizar a verificação.
    //  * @param {Array} [options.arrayPermissions=[]] - Array de objeto das permissões a serem verificadas no seguinte padrão [{permission:'permission:r', name:'Permissão'}, {...}].
    //  * @param {String} [options.message=''] - Mensagem para ser exibida em casos de insucessos.
    //  * @param {Boolean} [options.blnReturnArray=false] - Retorna um array com as informações ao invés de exibir a mensagem.
    //  */
    // static async checkPermissionsValidations(options = {}) {
    //     const translate = (level) => {
    //         switch (level) {
    //             case 'r':
    //                 return 'ver';
    //             case 'w':
    //             case 'rw':
    //                 return 'editar';
    //             case 'm':
    //             case 'rwm':
    //                 return 'permissão total';
    //             default:
    //                 return level;
    //         }
    //     }
    //     const {
    //         arrayPermissions = [],
    //         message = 'As permissões mínimas não foram atendidas',
    //         blnReturnArray = false
    //     } = options;
    //     const setFails = new Set();
    //     for (const permission of arrayPermissions) {
    //         if (!await commonFunctions.checkPermissions([permission.permission])) {
    //             const parts = permission.permission.split(':');
    //             setFails.add(`${permission.name}: ${commonFunctions.firstUppercaseLetter(translate(parts[1]))}`);
    //         }
    //     }
    //     if (blnReturnArray) {
    //         return {
    //             status: setFails.size ? false : true,
    //             itemsArray: setFails.size ? Array.from(setFails) : []
    //         }
    //     } else {
    //         if (setFails.size) {
    //             commonFunctions.generateNotification(message, 'error', { itemsArray: setFails.values() })
    //             return false;
    //         }
    //         return true;
    //     }
    // }

    /**
     * Merge objetos recursivamente. Se o valor for um objeto, chama o método
     * recursivamente. Caso contrário, sobrescreve o valor diretamente.
     * @param {Object} target - Objeto alvo da merge
     * @param {Object} source - Objeto fonte da merge
     * @return {Object} O objeto resultante da merge
     */
    static deepMergeObject(target = {}, source = {}) {
        // Garante que o target e source são objetos
        if (typeof target !== "object" || target === null) {
            throw new TypeError("O target deve ser um objeto válido.");
        }
        if (typeof source !== "object" || source === null) {
            throw new TypeError("O source deve ser um objeto válido.");
        }

        // Itera pelas chaves do objeto source
        for (const key in source) {
            if (source.hasOwnProperty(key)) {
                if (
                    typeof source[key] === "object" &&
                    source[key] !== null &&
                    !Array.isArray(source[key])
                ) {
                    // Inicializa target[key] como objeto vazio se não existir
                    if (!target[key] || typeof target[key] !== "object") {
                        target[key] = {};
                    }
                    // Chamada recursiva
                    commonFunctions.deepMergeObject(target[key], source[key]);
                } else {
                    // Caso contrário, sobrescrevemos o valor diretamente
                    target[key] = source[key];
                }
            }
        }
        return target;
    }

    /**
     * Função genérica para lidar com modais e buscar dados
     * @param {string} selector O seletor do botão que abre o modal
     * @param {class} modalClass A classe do modal (por exemplo, modalContaTenant)
     * @param {Function} buscarFunc A função de busca (por exemplo, self.#buscarContas)
     * @param {Object} options Opções adicionais
     * @param {Object} options.dataEnvAppend Dados adicionais que serão passados ao modal
     */
    static handleModal(self, selector, modalClass, buscarFunc, options = {}) {

        $(selector).on('click', async function () {
            const btn = $(this);
            commonFunctions.simulateLoading(btn);

            try {
                modalClass = new modalClass();

                let dataEnvModal = {
                    attributes: {
                        select: {
                            quantity: 1,
                            autoReturn: true,
                        }
                    }
                };

                if (options.dataEnvAppend) {
                    commonFunctions.deepMergeObject(dataEnvModal, options.dataEnvAppend)
                }

                modalClass.setDataEnvModal = dataEnvModal;

                if (typeof self._modalHideShow === 'function') {
                    await self._modalHideShow(false);
                }

                const response = await modalClass.modalOpen();

                if (response.refresh) {
                    if (response.selected) {
                        buscarFunc(response.selected.id);
                    } else {
                        buscarFunc();
                    }
                }
            } catch (error) {
                commonFunctions.generateNotificationErrorCatch(error);
            } finally {
                commonFunctions.simulateLoading(btn, false);
                if (typeof self._modalHideShow === 'function') {
                    await self._modalHideShow();
                }
            }
        });
    }

}