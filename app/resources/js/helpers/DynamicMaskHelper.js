class DynamicMaskHelper {
    /**
     * Aplica uma máscara dinamicamente com base na configuração do backend.
     * @param {string} selector - O seletor do campo de input onde a máscara será aplicada.
     * @param {Object} config - O objeto de configuração contendo a regex e regras.
     */
    static applyMask(selector, config) {
        // Verifica se há uma regex na configuração
        if (config?.exp_reg) {
            const regex = config.exp_reg;

            // Define a máscara com base no padrão da regex
            const maskPattern = this.convertRegexToMask(regex);

            // Aplica a máscara usando jQuery Mask
            if (maskPattern) {
                $(selector).mask(maskPattern, {
                    placeholder: ' '
                });
            } else {
                console.warn('Máscara não gerada para a expressão regular fornecida:', regex);
            }
        } else {
            console.error('Nenhuma expressão regular encontrada na configuração.');
        }
    }

    /**
     * Converte uma expressão regular de CPF/CEP para um padrão compatível com jQuery Mask.
     * @param {string} regex - A expressão regular fornecida.
     * @returns {string} O padrão de máscara correspondente.
     */
    static convertRegexToMask(regex) {
        // Verifica o padrão do CPF na regex e retorna a máscara equivalente
        if (regex.includes('\\d{3}\\.\\d{3}\\.\\d{3}-\\d{2}')) {
            return '000.000.000-00'; // Máscara para CPF
        }

        // Caso queira expandir, adicione mais padrões aqui
        if (regex.includes('\\d{5}-\\d{3}')) {
            return '00000-000'; // Máscara para CEP
        }

        // Caso não corresponda a nada
        return null;
    }
}
