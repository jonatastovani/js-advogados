import { commonFunctions } from "../commons/commonFunctions";

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
        const num = commonFunctions.returnsOnlyNumber(numCPF);

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
     * @param {{event: string, selector: string}} arrData - Objeto com o evento e o seletor do campo de entrada.
     */
    static addEventCheckCPF({ event, selector }) {
        $(selector).on(event, function () {
            const num = commonFunctions.returnsOnlyNumber(this.value);

            if (num.length === 11) {
                const bln = MasksAndValidateHelpers.validateCPF(this.value);
                $(this).toggleClass('is-valid', bln).toggleClass('is-invalid', !bln);
            } else {
                $(this).removeClass('is-valid').toggleClass('is-invalid', num.length > 0);
            }
        });
    }

    /**
     * Formata um número de CPF (Cadastro de Pessoa Física).
     * @param {string} numCPF - O número de CPF a ser formatado.
     * @returns {string} - Retorna o CPF formatado.
     */
    static formatCPF(numCPF) {
        const num = commonFunctions.returnsOnlyNumber(numCPF);
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
        const num = commonFunctions.returnsOnlyNumber(numCNPJ);

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
     * @param {{event: string, selector: string}} arrData - Objeto com o evento e o seletor do campo de entrada.
     */
    static addEventCheckCNPJ({ event, selector }) {
        $(selector).on(event, function () {
            const num = commonFunctions.returnsOnlyNumber(this.value);

            if (num.length === 14) {
                const isValid = MasksAndValidateHelpers.validateCNPJ(num);
                $(this).toggleClass('is-valid', isValid).toggleClass('is-invalid', !isValid);
            } else {
                $(this).removeClass('is-valid').toggleClass('is-invalid', num.length > 0);
            }
        });
    }

    /**
     * Formata um número de CNPJ (Cadastro Nacional de Pessoa Jurídica).
     * @param {string} numCNPJ - O número de CNPJ a ser formatado.
     * @returns {string} - Retorna o CNPJ formatado.
     */
    static formatCNPJ(numCNPJ) {
        const num = commonFunctions.returnsOnlyNumber(numCNPJ);
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
     * Aplica uma máscara de telefone brasileiro em um campo de entrada.
     * @param {string} num - O número de telefone.
     * @param {string} selector - O seletor do campo de entrada onde a máscara será aplicada.
     */
    static phoneMask(num, selector) {
        const number = commonFunctions.returnsOnlyNumber(num);

        $(selector).mask(number.length < 11 ? '(00) 0000-00009' : '(00) 0 0000-0009');

        if (commonFunctions.returnsOnlyNumber($(selector).val()) !== number) {
            $(selector).val(this.formatPhone(number));
        }
    }

    /**
     * Formata um número de telefone brasileiro.
     * @param {string} num - O número de telefone.
     * @returns {string} - Retorna o número de telefone formatado.
     */
    static formatPhone(num) {
        const number = commonFunctions.returnsOnlyNumber(num);
        return number.length < 11
            ? number.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3')
            : number.replace(/(\d{2})(\d)(\d{4})(\d{4})/, '($1) $2 $3-$4');
    }

    /**
     * Aplica uma máscara de CEP (Código de Endereçamento Postal) em um campo de entrada.
     * @param {string} selector - O seletor do campo de entrada onde a máscara será aplicada.
     */
    static cepMask(selector) {
        $(selector).mask('00.000-000');
    }
}
