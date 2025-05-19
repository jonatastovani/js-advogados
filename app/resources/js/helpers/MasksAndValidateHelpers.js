import { CommonFunctions } from "../commons/CommonFunctions";

/**
 * Classe auxiliar para manipular máscaras e validações para campos de entrada comuns.
 */
export class MasksAndValidateHelpers {
    /**
     * Valida um número de CPF (Cadastro de Pessoa Física).
     * @param {string} numCPF - O número de CPF a ser validado.
     * @returns {boolean} - Retorna true se o CPF for válido, caso contrário, false.
     */
    static validateCPF(numCPF) {
        const num = CommonFunctions.returnsOnlyNumber(numCPF);

        if (num.length !== 11 || /^(\d)\1*$/.test(num)) {
            return false;
        }

        const calcDigit = (base) => {
            let sum = 0;
            for (let i = 0; i < base; i++) {
                sum += parseInt(num.charAt(i)) * (base + 1 - i);
            }
            const rest = sum % 11;
            return rest < 2 ? 0 : 11 - rest;
        };

        const dig1 = calcDigit(9);
        const dig2 = calcDigit(10);

        return parseInt(num.charAt(9)) === dig1 && parseInt(num.charAt(10)) === dig2;
    }

    /**
     * Adiciona um evento para validar o CPF em um campo de entrada.
     * @param {{event: string, selector: string, executeTrigger: boolean}} arrData - Objeto com o evento e o seletor do campo de entrada.
     */
    static addEventCheckCPF({ event, selector, executeTrigger = false }) {
        $(selector).on(event, function () {
            const num = CommonFunctions.returnsOnlyNumber(this.value);

            if (num.length === 11) {
                const bln = MasksAndValidateHelpers.validateCPF(this.value);
                $(this).toggleClass('is-valid', bln).toggleClass('is-invalid', !bln);
            } else {
                $(this).removeClass('is-valid').toggleClass('is-invalid', num.length > 0);
            }
        });

        // Executa o evento caso executeTrigger seja true
        if (executeTrigger) {
            $(selector).trigger('change');
        }
    }


    /**
     * Formata um número de CPF (Cadastro de Pessoa Física).
     * @param {string} numCPF - O número de CPF a ser formatado.
     * @returns {string} - Retorna o CPF formatado.
     */
    static formatCPF(numCPF) {
        const num = CommonFunctions.returnsOnlyNumber(numCPF);
        return num.length === 11 ? num.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4') : num;
    }

    /**
     * Aplica uma máscara de CPF em um campo de entrada.
     * @param {string} selector - O seletor do campo de entrada onde a máscara será aplicada.
     */
    static cpfMask(selector) {
        $(selector).mask('000.000.000-00');
    }

    /**
 * Valida um número de CNPJ (Cadastro Nacional de Pessoa Jurídica).
 * @param {string} numCNPJ - O número de CNPJ a ser validado.
 * @returns {boolean} - Retorna true se o CNPJ for válido, caso contrário, false.
 */
    static validateCNPJ(numCNPJ) {
        const num = CommonFunctions.returnsOnlyNumber(numCNPJ);

        // Valida tamanho e repetição de números
        if (num.length !== 14 || /^(.)\1*$/.test(num)) {
            return false;
        }

        // Calcula os dígitos verificadores
        const calcDigit = (base) => {
            let sum = 0;
            let factor = base === 12 ? 5 : 6;

            for (let i = 0; i < base; i++) {
                sum += parseInt(num.charAt(i)) * factor--;
                if (factor < 2) factor = 9;
            }

            const rest = sum % 11;
            return rest < 2 ? 0 : 11 - rest;
        };

        return calcDigit(12) === parseInt(num.charAt(12)) && calcDigit(13) === parseInt(num.charAt(13));
    }

    /**
     * Adiciona um evento para validar o CNPJ em um campo de entrada.
     * @param {{event: string, selector: string, executeTrigger: boolean}} arrData - Objeto com o evento e o seletor do campo de entrada.
     */
    static addEventCheckCNPJ({ event, selector, executeTrigger = false }) {
        $(selector).on(event, function () {
            const num = CommonFunctions.returnsOnlyNumber(this.value);

            if (num.length === 14) {
                const isValid = MasksAndValidateHelpers.validateCNPJ(num);
                $(this).toggleClass('is-valid', isValid).toggleClass('is-invalid', !isValid);
            } else {
                $(this).removeClass('is-valid').toggleClass('is-invalid', num.length > 0);
            }
        });

        // Executa o evento caso executeTrigger seja true
        if (executeTrigger) {
            $(selector).trigger('change');
        }
    }

    /**
     * Formata um número de CNPJ (Cadastro Nacional de Pessoa Jurídica).
     * @param {string} numCNPJ - O número de CNPJ a ser formatado.
     * @returns {string} - Retorna o CNPJ formatado.
     */
    static formatCNPJ(numCNPJ) {
        const num = CommonFunctions.returnsOnlyNumber(numCNPJ);
        return num.length === 14 ? num.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5') : num;
    }

    /**
     * Aplica uma máscara de CNPJ em um campo de entrada.
     * @param {string} selector - O seletor do campo de entrada onde a máscara será aplicada.
     */
    static cnpjMask(selector) {
        $(selector).mask('00.000.000/0000-00');
    }

    /**
     * Valida um número de telefone brasileiro.
     * @param {string} phone - O número de telefone a ser validado.
     * @returns {boolean} - Retorna true se o telefone for válido, caso contrário, false.
     */
    static validatePhone(phone) {
        const number = CommonFunctions.returnsOnlyNumber(phone);

        // Valida se o número tem 10 ou 11 dígitos
        if (number.length === 10) {
            // Formato fixo: (00) 0000-0000
            return /^\d{2}\d{4}\d{4}$/.test(number);
        } else if (number.length === 11) {
            // Formato móvel: (00) 0 0000-0000
            return /^\d{2}\d{1}\d{4}\d{4}$/.test(number);
        }
        return false;
    }

    /**
     * Adiciona um evento para validar o telefone em um campo de entrada.
     * @param {{event: string, selector: string, executeTrigger: boolean}} arrData - Objeto com o evento e o seletor do campo de entrada.
     */
    static addEventCheckPhone({ event, selector, executeTrigger = false }) {
        $(selector).on(event, function () {
            const num = CommonFunctions.returnsOnlyNumber(this.value);

            // Verifica se o número é válido
            const isValid = MasksAndValidateHelpers.validatePhone(num);

            if (num.length >= 10 && num.length <= 11) {
                $(this).toggleClass('is-valid', isValid).toggleClass('is-invalid', !isValid);
            } else {
                $(this).removeClass('is-valid').toggleClass('is-invalid', num.length > 0);
            }

            // Formata o telefone se estiver válido
            if (isValid) {
                $(this).val(MasksAndValidateHelpers.formatPhone(num));
            }
        });

        // Executa o evento caso executeTrigger seja true
        if (executeTrigger) {
            $(selector).trigger('change');
        }
    }

    /**
     * Aplica uma máscara de telefone brasileiro em um campo de entrada.
     * Remove a máscara anterior antes de aplicar a nova.
     * 
     * @param {HTMLElement | jQuery} input - Campo de entrada onde a máscara será aplicada.
     * @param {string|null} num - O número de telefone a ser formatado (opcional).
     */
    static phoneMask(input, num = null) {
        const self = this;

        input = $(input); // Garante que seja um objeto jQuery

        // Remove qualquer máscara previamente aplicada
        input.unmask();

        // Obtém apenas números
        const number = num ? CommonFunctions.returnsOnlyNumber(num) : '';

        // Define a máscara com base no tamanho do número
        const mask = number.length < 11 ? '(00) 0000-00009' : '(00) 0 0000-0009';
        input.mask(mask);

        // Aplica o valor formatado se houver número
        if (number) {
            input.val(self.formatPhone(number));
        }
    }

    /**
     * Adiciona um evento para validar o telefone em um campo de entrada.
     * @param {{event: string, selector: string, executeTrigger: boolean}} arrData - Objeto com o evento e o seletor do campo de entrada.
     */
    static addEventCheckPhone({ event, selector, executeTrigger = false }) {
        const self = this;
        $(selector).on(event, function () {
            const num = CommonFunctions.returnsOnlyNumber(this.value);
            if (num.length >= 10 && num.length <= 11) {
                const isValid = MasksAndValidateHelpers.validatePhone(num);
                $(this).toggleClass('is-valid', isValid).toggleClass('is-invalid', !isValid);
            } else {
                $(this).removeClass('is-valid').toggleClass('is-invalid', num.length > 0);
            }

            self.phoneMask(this, num);
        });

        // Executa o evento caso executeTrigger seja true
        if (executeTrigger) {
            $(selector).trigger('change');
        }
    }

    /**
     * Formata um número de telefone brasileiro.
     * 
     * @param {string} num - O número de telefone.
     * @returns {string} - Retorna o número de telefone formatado.
     */
    static formatPhone(num) {
        const number = CommonFunctions.returnsOnlyNumber(num);
        return number.length < 11
            ? number.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3')
            : number.replace(/(\d{2})(\d{1})(\d{4})(\d{4})/, '($1) $2 $3-$4');
    }

    /**
     * Valida um número de CEP brasileiro.
     * @param {string} cep - O número de CEP a ser validado.
     * @returns {boolean} - Retorna true se o CEP for válido, caso contrário, false.
     */
    static validateCep(cep) {
        const number = CommonFunctions.returnsOnlyNumber(cep);
        return /^\d{8}$/.test(number);
    }

    /**
     * Adiciona um evento para validar o CEP em um campo de entrada.
     * @param {{event: string, selector: string, executeTrigger: boolean}} arrData - Objeto com o evento e o seletor do campo de entrada.
     */
    static addEventCheckCep({ event, selector, executeTrigger = false }) {
        $(selector).on(event, function () {
            const num = CommonFunctions.returnsOnlyNumber(this.value);
            const isValid = MasksAndValidateHelpers.validateCep(num);
            $(this).toggleClass('is-valid', isValid).toggleClass('is-invalid', !isValid);
        });

        // Executa o evento caso executeTrigger seja true
        if (executeTrigger) {
            $(selector).trigger('change');
        }
    }

    /**
     * Aplica uma máscara de CEP (Código de Endereçamento Postal) em um campo de entrada.
     * Remove a máscara anterior antes de aplicar a nova.
     * 
     * @param {string} selector - O seletor do campo de entrada onde a máscara será aplicada.
     */
    static cepMask(selector) {
        $(selector).unmask();
        $(selector).mask('00.000-000');
    }

    /**
     * Formata um CEP para o padrão 00.000-000, se tiver exatamente 8 dígitos.
     * Caso contrário, retorna o valor original sem alterações.
     *
     * @param {string} cep - O CEP a ser formatado.
     * @returns {string} - O CEP formatado ou o original se não tiver 8 dígitos.
     */
    static formatCep(cep) {
        const numeros = cep.replace(/\D/g, ''); // Remove tudo que não for número
        return numeros.length === 8 ? numeros.replace(/^(\d{2})(\d{3})(\d{3})$/, '$1.$2-$3') : cep;
    }

    /**
     * Inicializa as máscaras e eventos da chave Pix no modal.
     */
    static chavePixMask(select, input, options = {}) {
        const self = this;

        select = $(select);
        input = $(input);
        options.executeTrigger = options.executeTrigger ?? true;

        // Atualiza o placeholder e a máscara ao mudar o tipo da chave
        select.on('change', function () {
            const tipo = parseInt(CommonFunctions.returnsOnlyNumber(select.val()));

            if (!tipo && tipo !== 0) {
                CommonFunctions.generateNotification('Valor inválido para tipo de chave pix.', 'error');
                return;
            }

            // input.val(''); // Limpa o campo ao mudar o tipo
            self.clearValidInvalidClass(input);
            input.attr('placeholder', self.#getPlaceholderChavePix(tipo));
            const pattern = self.#getPatternChavePix(tipo);
            pattern ? input.attr('pattern', pattern) : input.removeAttr('pattern');
            self.#aplicarMascaraChavePix(tipo, input, options);
            input.attr('disabled', tipo === 0 ? true : false);
        });
    }

    static clearValidInvalidClass(input) {
        input.removeClass('is-invalid');
        input.removeClass('is-valid');
    }

    /**
     * Função para configurar máscara e validação da chave Pix.
     * 
     * @param {number|string} tipo - Tipo da chave Pix.
     * @param {jQuery} chave - Elemento de input onde será aplicada a máscara.
     */
    static #aplicarMascaraChavePix(tipo, chave, options = {}) {
        const self = this;
        const tipos = window.Enums.ChavePixTipoEnum;
        const executeTrigger = options.executeTrigger ?? true;

        chave = $(chave); // Garante que seja um objeto jQuery

        // Remove qualquer máscara existente
        chave.unmask();

        // Remove qualquer máscara existente
        chave.unmask();
        // Remove qualquer validação existente
        chave.off('input, focusout');

        switch (tipo) {
            case tipos.CPF:
                self.cpfMask(chave);
                self.addEventCheckCPF({ selector: chave, event: 'focusout', executeTrigger });
                break;

            case tipos.CNPJ:
                self.cnpjMask(chave);
                self.addEventCheckCNPJ({ selector: chave, event: 'focusout', executeTrigger });
                break;

            case tipos.TELEFONE:
                self.phoneMask(chave);
                self.addEventCheckPhone({ selector: chave, event: 'focusout', executeTrigger });
                break;

            case tipos.EMAIL:
                // chave.on('input', function () {
                //     chave.attr('pattern', '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}$');
                // });
                break;

            case tipos.CHAVE_ALEATORIA:
                // chave.on('input', function () {
                //     chave.attr('placeholder', 'Chave Aleatória');
                //     chave.attr('pattern', '^[a-zA-Z0-9]+$');
                // });
                break;

            default:
                chave.off('input');
                break;
        }
    }

    //#region Funções privadas do tipo ChavePix

    /**
     * Retorna o placeholder conforme o tipo da chave.
     * 
     * @param {number} tipo - Tipo da chave Pix.
     * @returns {string} Placeholder correspondente.
     */
    static #getPlaceholderChavePix(tipo) {
        const tipos = window.Enums.ChavePixTipoEnum;
        switch (tipo) {
            case tipos.CPF: return '000.000.000-00';
            case tipos.CNPJ: return '00.000.000/0000-00';
            case tipos.TELEFONE: return '(00) 0 0000-0000';
            case tipos.EMAIL: return 'exemplo@dominio.com';
            case tipos.CHAVE_ALEATORIA: return 'Chave Aleatória';
            case 0: return 'Selecione o tipo de chave';
            default: return 'Digite a chave Pix';
        }
    }

    /**
     * Retorna o pattern conforme o tipo da chave.
     * 
     * @param {string} tipo - Tipo da chave Pix.
     * @returns {string} Pattern correspondente.
     */
    static #getPatternChavePix(tipo) {
        const tipos = window.Enums.ChavePixTipoEnum;
        switch (tipo) {
            case tipos.CPF:
                return '\\d{3}\\.\\d{3}\\.\\d{3}-\\d{2}';
            case tipos.CNPJ:
                return '\\d{2}\\.\\d{3}\\.\\d{3}/\\d{4}-\\d{2}';
            case tipos.TELEFONE:
                return '\\(\\d{2}\\) \\d{4,5}-\\d{4}';
            case tipos.EMAIL:
            case tipos.CHAVE_ALEATORIA:
            default:
                return '';
        }
    }

    //#endregion
}
