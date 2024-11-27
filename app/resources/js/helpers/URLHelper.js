
export class URLHelper {

    /**
     * Obtém o valor de um parâmetro da URL.
     * 
     * @param {string} strParam - O nome do parâmetro a ser obtido.
     * @param {string|null} [strUrl=null] - A URL da qual o parâmetro será extraído (padrão: URL atual).
     * @returns {string|null} - O valor do parâmetro ou null se não estiver presente.
     */
    static getParameterURL(strParam, strUrl = null) {
        const url = new URL(strUrl ?? window.location.href);
        const params = new URLSearchParams(url.search);
        return params.get(strParam) || null;
    }

    /**
     * Remove um parâmetro da URL sem recarregar a página.
     * 
     * @param {string} param - O nome do parâmetro a ser removido.
     */
    static removeURLParameter(param) {
        const url = new URL(window.location.href);
        const params = new URLSearchParams(url.search);

        if (params.has(param)) {
            params.delete(param);
            const newUrl = `${url.origin}${url.pathname}?${params.toString()}`;
            window.history.replaceState({}, document.title, newUrl);
        }
    }

    /**
     * Função para capturar um parâmetro específico da URL ou o último por padrão.
     * 
     * @param {number|null} index - O índice do segmento da URL que você deseja capturar. Por padrão, captura o último segmento.
     * @returns {string|null} - O valor do segmento da URL ou null se o segmento não for encontrado.
     */
    static getURLSegment(index = null) {
        const url = window.location.pathname;
        const segments = url.split('/').filter(segment => segment); // Remove segmentos vazios

        if (index === null) {
            // Retorna o último segmento por padrão
            return segments.length > 0 ? segments[segments.length - 1] : null;
        }

        // Retorna o segmento no índice fornecido, ou null se não existir
        return segments[index] || null;
    }

    /**
     * Verifica se a URL fornecida contém um domínio. Se não, adiciona o domínio base atual da página.
     * 
     * @param {string} endpoint - O endpoint fornecido, que pode ser relativo ou absoluto.
     * @returns {string} - O endpoint com o domínio completo, se necessário.
     */
    static formatEndpointUrl(endpoint) {
        const currentDomain = window.location.origin;
        if (endpoint.startsWith('http://') || endpoint.startsWith('https://')) {
            return endpoint;
        } else {
            return `${currentDomain}/${endpoint}`;
        }
    }

    /**
     * Converte um objeto em um formato de `key=value` para envio, com booleanos convertidos para 0 e 1.
     */
    static flattenObject(obj, parentKey = null) {
        const result = {};
        for (const key in obj) {
            if (obj.hasOwnProperty(key)) {
                const newKey = parentKey ? `${parentKey}[${key}]` : key;
                if (typeof obj[key] === 'object' && !Array.isArray(obj[key])) {
                    Object.assign(result, this.flattenObject(obj[key], newKey));
                } else if (Array.isArray(obj[key])) {
                    obj[key].forEach((item, index) => {
                        if (typeof item === 'object') {
                            Object.assign(result, this.flattenObject(item, `${newKey}[${index}]`));
                        } else {
                            result[`${newKey}[${index}]`] = item;
                        }
                    });
                } else {
                    // Converte booleanos explicitamente para 0 e 1
                    result[newKey] = typeof obj[key] === 'boolean' ? (obj[key] ? '1' : '0') : obj[key];
                }
            }
        }
        return result;
    }

}